<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

class StatisticsService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getCommunityStats(): array
    {
        $totalVolunteers = (int)$this->db->query(
            "SELECT COUNT(*) FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.slug = 'volunteer' AND u.status = 'Active'"
        )->fetchColumn();

        $totalEvents = (int)$this->db->query(
            "SELECT COUNT(*) FROM events WHERE status IN ('Published', 'Ongoing', 'Completed')"
        )->fetchColumn();

        $totalHours = (float)$this->db->query(
            "SELECT COALESCE(SUM(hours), 0) FROM volunteer_hours WHERE status = 'Approved'"
        )->fetchColumn();

        $totalOrganizations = (int)$this->db->query(
            "SELECT COUNT(*) FROM organizations WHERE verification_status = 'Verified' AND status = 'Active'"
        )->fetchColumn();

        $totalBloodAppointments = (int)$this->db->query(
            "SELECT COUNT(*) FROM blood_donation_appointments WHERE status != 'Cancelled'"
        )->fetchColumn();

        return [
            'total_volunteers' => $totalVolunteers,
            'total_events' => $totalEvents,
            'total_hours' => round($totalHours, 1),
            'total_organizations' => $totalOrganizations,
            'total_blood_appointments' => $totalBloodAppointments,
        ];
    }

    /**
     * Module 14: Leaderboards (strictly only Approved volunteer hours!)
     */
    public function getTopVolunteers(string $period = 'all', int $limit = 10): array
    {
        $dateFilter = '';
        $params = [];

        if ($period === 'month') {
            // Current or most recent active month in 2026
            $dateFilter = " AND vh.created_at >= :start_date";
            $params['start_date'] = '2026-10-01 00:00:00';
        } elseif ($period === 'year') {
            $dateFilter = " AND vh.created_at >= :start_date";
            $params['start_date'] = '2026-01-01 00:00:00';
        }

        $sql = "SELECT u.id, u.full_name, u.avatar, u.address, u.blood_type,
                       COALESCE(SUM(vh.hours), 0) AS confirmed_hours,
                       COUNT(DISTINCT vh.event_id) AS events_attended,
                       (SELECT COUNT(*) FROM certificates c WHERE c.user_id = u.id AND c.status = 'Valid') AS certificates_count
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                LEFT JOIN volunteer_hours vh ON vh.user_id = u.id AND vh.status = 'Approved' {$dateFilter}
                WHERE r.slug = 'volunteer' AND u.status = 'Active'
                GROUP BY u.id, u.full_name, u.avatar, u.address, u.blood_type
                HAVING confirmed_hours > 0
                ORDER BY confirmed_hours DESC, events_attended DESC
                LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTopOrganizations(int $limit = 6): array
    {
        return $this->db->query(
            "SELECT o.id, o.name, o.slug, o.logo, o.address, o.verification_status,
                    COUNT(DISTINCT e.id) AS total_events,
                    COUNT(DISTINCT r.id) AS total_participants,
                    COALESCE((
                        SELECT SUM(vh.hours)
                        FROM volunteer_hours vh
                        INNER JOIN events ev ON ev.id = vh.event_id
                        WHERE ev.organization_id = o.id AND vh.status = 'Approved'
                    ), 0) AS total_hours,
                    COALESCE((
                        SELECT AVG(rv.rating)
                        FROM reviews rv
                        WHERE rv.organization_id = o.id AND rv.status = 'Visible'
                    ), 5.0) AS avg_rating
             FROM organizations o
             LEFT JOIN events e ON e.organization_id = o.id AND e.status IN ('Published', 'Ongoing', 'Completed')
             LEFT JOIN registrations r ON r.event_id = e.id AND r.status IN ('Confirmed', 'Attended')
             WHERE o.status = 'Active'
             GROUP BY o.id, o.name, o.slug, o.logo, o.address, o.verification_status
             ORDER BY total_hours DESC, total_events DESC
             LIMIT " . (int)$limit
        )->fetchAll();
    }

    public function getVolunteerDashboardStats(int $userId): array
    {
        $stmtHours = $this->db->prepare(
            "SELECT COALESCE(SUM(hours), 0) FROM volunteer_hours WHERE user_id = :uid AND status = 'Approved'"
        );
        $stmtHours->execute(['uid' => $userId]);
        $totalHours = round((float)$stmtHours->fetchColumn(), 1);

        $stmtEvents = $this->db->prepare(
            "SELECT COUNT(*) FROM registrations WHERE user_id = :uid AND status = 'Attended'"
        );
        $stmtEvents->execute(['uid' => $userId]);
        $attendedEvents = (int)$stmtEvents->fetchColumn();

        $stmtUpcoming = $this->db->prepare(
            "SELECT COUNT(*) FROM registrations r INNER JOIN events e ON e.id = r.event_id
             WHERE r.user_id = :uid AND r.status IN ('Confirmed', 'Waitlisted') AND e.status IN ('Published', 'Ongoing')"
        );
        $stmtUpcoming->execute(['uid' => $userId]);
        $upcomingEvents = (int)$stmtUpcoming->fetchColumn();

        $stmtCerts = $this->db->prepare(
            "SELECT COUNT(*) FROM certificates WHERE user_id = :uid AND status = 'Valid'"
        );
        $stmtCerts->execute(['uid' => $userId]);
        $totalCertificates = (int)$stmtCerts->fetchColumn();

        // Monthly chart data (Months 06/2026 - 12/2026)
        $months = ['2026-06', '2026-07', '2026-08', '2026-09', '2026-10', '2026-11', '2026-12'];
        $monthlyHours = array_fill_keys($months, 0.0);

        $stmtRows = $this->db->prepare(
            "SELECT vh.hours, e.start_time
             FROM volunteer_hours vh
             INNER JOIN events e ON e.id = vh.event_id
             WHERE vh.user_id = :uid AND vh.status = 'Approved'"
        );
        $stmtRows->execute(['uid' => $userId]);
        foreach ($stmtRows->fetchAll() as $row) {
            $ym = substr((string)$row['start_time'], 0, 7);
            if (isset($monthlyHours[$ym])) {
                $monthlyHours[$ym] += (float)$row['hours'];
            }
        }

        return [
            'total_hours' => $totalHours,
            'attended_events' => $attendedEvents,
            'upcoming_events' => $upcomingEvents,
            'total_certificates' => $totalCertificates,
            'badge' => volunteer_rank_badge($totalHours),
            'chart_labels' => ['Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            'chart_values' => array_values($monthlyHours),
        ];
    }

    public function getOrganizerDashboardStats(int $orgId): array
    {
        $stmtTotalEv = $this->db->prepare("SELECT COUNT(*) FROM events WHERE organization_id = :oid");
        $stmtTotalEv->execute(['oid' => $orgId]);
        $totalEvents = (int)$stmtTotalEv->fetchColumn();

        $stmtActiveEv = $this->db->prepare(
            "SELECT COUNT(*) FROM events WHERE organization_id = :oid AND status IN ('Published', 'Ongoing')"
        );
        $stmtActiveEv->execute(['oid' => $orgId]);
        $activeEvents = (int)$stmtActiveEv->fetchColumn();

        $stmtReg = $this->db->prepare(
            "SELECT COUNT(*) FROM registrations r INNER JOIN events e ON e.id = r.event_id
             WHERE e.organization_id = :oid AND r.status != 'Cancelled'"
        );
        $stmtReg->execute(['oid' => $orgId]);
        $totalRegistrations = (int)$stmtReg->fetchColumn();

        $stmtAttended = $this->db->prepare(
            "SELECT COUNT(*) FROM registrations r INNER JOIN events e ON e.id = r.event_id
             WHERE e.organization_id = :oid AND r.status = 'Attended'"
        );
        $stmtAttended->execute(['oid' => $orgId]);
        $totalAttended = (int)$stmtAttended->fetchColumn();

        $stmtHours = $this->db->prepare(
            "SELECT COALESCE(SUM(vh.hours), 0) FROM volunteer_hours vh INNER JOIN events e ON e.id = vh.event_id
             WHERE e.organization_id = :oid AND vh.status = 'Approved'"
        );
        $stmtHours->execute(['oid' => $orgId]);
        $totalHours = round((float)$stmtHours->fetchColumn(), 1);

        return [
            'total_events' => $totalEvents,
            'active_events' => $activeEvents,
            'total_registrations' => $totalRegistrations,
            'total_attended' => $totalAttended,
            'total_hours' => $totalHours,
        ];
    }

    public function getAdminDashboardStats(): array
    {
        $totalUsers = (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalVolunteers = (int)$this->db->query(
            "SELECT COUNT(*) FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.slug = 'volunteer'"
        )->fetchColumn();
        $totalOrganizations = (int)$this->db->query("SELECT COUNT(*) FROM organizations")->fetchColumn();
        $totalEvents = (int)$this->db->query("SELECT COUNT(*) FROM events")->fetchColumn();
        $totalRegistrations = (int)$this->db->query("SELECT COUNT(*) FROM registrations WHERE status != 'Cancelled'")->fetchColumn();
        $totalHours = round((float)$this->db->query(
            "SELECT COALESCE(SUM(hours), 0) FROM volunteer_hours WHERE status = 'Approved'"
        )->fetchColumn(), 1);
        $pendingEvents = (int)$this->db->query("SELECT COUNT(*) FROM events WHERE status = 'Pending'")->fetchColumn();
        $pendingOrganizations = (int)$this->db->query(
            "SELECT COUNT(*) FROM organizations WHERE verification_status = 'Pending'"
        )->fetchColumn();

        // Monthly Chart Data (07/2026 - 12/2026)
        $months = ['2026-07', '2026-08', '2026-09', '2026-10', '2026-11', '2026-12'];
        $eventsByMonth = array_fill_keys($months, 0);
        $participationsByMonth = array_fill_keys($months, 0);
        $hoursByMonth = array_fill_keys($months, 0.0);

        foreach ($this->db->query("SELECT start_time FROM events")->fetchAll() as $ev) {
            $ym = substr((string)$ev['start_time'], 0, 7);
            if (isset($eventsByMonth[$ym])) {
                $eventsByMonth[$ym]++;
            }
        }

        foreach ($this->db->query(
            "SELECT e.start_time FROM registrations r INNER JOIN events e ON e.id = r.event_id WHERE r.status IN ('Confirmed','Attended')"
        )->fetchAll() as $reg) {
            $ym = substr((string)$reg['start_time'], 0, 7);
            if (isset($participationsByMonth[$ym])) {
                $participationsByMonth[$ym]++;
            }
        }

        foreach ($this->db->query(
            "SELECT vh.hours, e.start_time FROM volunteer_hours vh INNER JOIN events e ON e.id = vh.event_id WHERE vh.status = 'Approved'"
        )->fetchAll() as $vh) {
            $ym = substr((string)$vh['start_time'], 0, 7);
            if (isset($hoursByMonth[$ym])) {
                $hoursByMonth[$ym] += (float)$vh['hours'];
            }
        }

        $categoryDistribution = $this->db->query(
            "SELECT c.name, c.color_badge, COUNT(e.id) AS event_count
             FROM event_categories c
             LEFT JOIN events e ON e.category_id = c.id
             GROUP BY c.id, c.name, c.color_badge
             ORDER BY c.display_order ASC"
        )->fetchAll();

        return [
            'kpis' => [
                'total_users' => $totalUsers,
                'total_volunteers' => $totalVolunteers,
                'total_organizations' => $totalOrganizations,
                'total_events' => $totalEvents,
                'total_registrations' => $totalRegistrations,
                'total_hours' => $totalHours,
                'pending_events' => $pendingEvents,
                'pending_organizations' => $pendingOrganizations,
            ],
            'charts' => [
                'month_labels' => ['Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                'events_by_month' => array_values($eventsByMonth),
                'participations_by_month' => array_values($participationsByMonth),
                'hours_by_month' => array_values($hoursByMonth),
                'categories' => $categoryDistribution,
            ],
            'top_volunteers' => $this->getTopVolunteers('all', 5),
            'top_organizations' => $this->getTopOrganizations(5),
        ];
    }
}
// [v1-stats-draft-1] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #1
// [v1-stats-draft-2] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #2
// [v1-stats-draft-3] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #3
// [v1-stats-draft-4] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #4
// [v1-stats-draft-5] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #5
// [v1-stats-draft-6] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #6
// [v1-stats-draft-7] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #7
// [v1-stats-draft-8] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #8
// [v1-stats-draft-9] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #9
// [v1-stats-draft-10] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #10
// [v1-stats-draft-11] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #11
// [v1-stats-draft-12] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #12
// [v1-stats-draft-13] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #13
// [v1-stats-draft-14] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #14
// [v1-stats-draft-15] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #15
// [v1-stats-draft-16] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #16
// [v1-stats-draft-17] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #17
// [v1-stats-draft-18] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #18
// [v1-stats-draft-19] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #19
// [v1-stats-draft-20] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #20
// [v1-stats-draft-21] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #21
// [v1-stats-draft-22] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #22
// [v1-stats-draft-23] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #23
// [v1-stats-draft-24] Tinh toan chi so gio tinh nguyen va bieu do thong ke ca nhan #24
