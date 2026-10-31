<?php
declare(strict_types=1);

namespace App\Models;

class Event extends BaseModel
{
    public function getCategories(): array
    {
        return $this->fetchAll(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM events e WHERE e.category_id = c.id AND e.status IN ('Published','Ongoing','Completed')) AS event_count
             FROM event_categories c
             WHERE c.status = 'Active'
             ORDER BY c.display_order ASC, c.id ASC"
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT e.*,
                    c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon,
                    c.color_badge AS category_color, c.is_blood_donation,
                    o.name AS organization_name, o.slug AS organization_slug, o.logo AS organization_logo,
                    o.verification_status AS organization_verification, o.owner_user_id AS organization_owner_id,
                    o.contact_phone AS organization_phone, o.contact_email AS organization_email
             FROM events e
             INNER JOIN event_categories c ON c.id = e.category_id
             INNER JOIN organizations o ON o.id = e.organization_id
             WHERE e.id = :id",
            ['id' => $id]
        );
    }

    public function getEventImages(int $eventId): array
    {
        return $this->fetchAll(
            "SELECT * FROM event_images WHERE event_id = :eid ORDER BY display_order ASC, id ASC",
            ['eid' => $eventId]
        );
    }

    public function getEventRequirements(int $eventId): array
    {
        return $this->fetchAll(
            "SELECT * FROM event_requirements WHERE event_id = :eid ORDER BY display_order ASC, id ASC",
            ['eid' => $eventId]
        );
    }

    public function searchEvents(array $filters = [], int $page = 1, int $perPage = 6): array
    {
        $where = ["e.status IN ('Published', 'Ongoing', 'Completed')"];
        $params = [];

        if (!empty($filters['keyword'])) {
            $where[] = "(e.title LIKE :kw OR e.short_description LIKE :kw OR e.location LIKE :kw)";
            $params['kw'] = '%' . trim((string)$filters['keyword']) . '%';
        }
        if (!empty($filters['category_id'])) {
            $where[] = "e.category_id = :cat_id";
            $params['cat_id'] = (int)$filters['category_id'];
        }
        if (!empty($filters['category_slug'])) {
            $where[] = "c.slug = :cat_slug";
            $params['cat_slug'] = trim((string)$filters['category_slug']);
        }
        if (!empty($filters['province'])) {
            $where[] = "e.location LIKE :prov";
            $params['prov'] = '%' . trim((string)$filters['province']) . '%';
        }
        if (!empty($filters['status']) && in_array($filters['status'], ['Published', 'Ongoing', 'Completed'], true)) {
            $where[] = "e.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "e.start_time >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['is_blood_donation'])) {
            $where[] = "c.is_blood_donation = 1";
        }

        $whereSql = implode(' AND ', $where);

        $countSql = "SELECT COUNT(*)
                     FROM events e
                     INNER JOIN event_categories c ON c.id = e.category_id
                     INNER JOIN organizations o ON o.id = e.organization_id
                     WHERE {$whereSql}";
        $total = (int)$this->fetchValue($countSql, $params);

        $sort = $filters['sort'] ?? 'upcoming';
        $orderBy = match ($sort) {
            'newest' => 'e.id DESC',
            'popular' => 'e.registered_count DESC, e.start_time ASC',
            'hours' => 'e.max_hours DESC',
            default => "CASE WHEN e.status = 'Published' THEN 0 ELSE 1 END ASC, e.start_time ASC",
        };

        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT e.*,
                       c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon,
                       c.color_badge AS category_color, c.is_blood_donation,
                       o.name AS organization_name, o.logo AS organization_logo, o.verification_status
                FROM events e
                INNER JOIN event_categories c ON c.id = e.category_id
                INNER JOIN organizations o ON o.id = e.organization_id
                WHERE {$whereSql}
                ORDER BY {$orderBy}
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int)ceil($total / $perPage)),
        ];
    }

    public function getFeaturedEvents(int $limit = 6): array
    {
        return $this->fetchAll(
            "SELECT e.*,
                    c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon,
                    c.color_badge AS category_color, c.is_blood_donation,
                    o.name AS organization_name, o.logo AS organization_logo
             FROM events e
             INNER JOIN event_categories c ON c.id = e.category_id
             INNER JOIN organizations o ON o.id = e.organization_id
             WHERE e.status IN ('Published', 'Ongoing', 'Completed')
             ORDER BY CASE WHEN e.status = 'Published' THEN 0 ELSE 1 END ASC, e.is_featured DESC, e.start_time ASC
             LIMIT " . (int)$limit
        );
    }

    public function getByOrganization(int $orgId): array
    {
        return $this->fetchAll(
            "SELECT e.*, c.name AS category_name, c.is_blood_donation,
                    (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status IN ('Confirmed','Attended')) AS active_registrations,
                    (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status = 'Waitlisted') AS waitlist_count,
                    (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status = 'Attended') AS attended_count
             FROM events e
             INNER JOIN event_categories c ON c.id = e.category_id
             WHERE e.organization_id = :org_id
             ORDER BY e.id DESC",
            ['org_id' => $orgId]
        );
    }

    public function getAllForAdmin(string $status = '', string $search = ''): array
    {
        $sql = "SELECT e.*, c.name AS category_name, o.name AS organization_name, u.full_name AS creator_name
                FROM events e
                INNER JOIN event_categories c ON c.id = e.category_id
                INNER JOIN organizations o ON o.id = e.organization_id
                INNER JOIN users u ON u.id = e.created_by
                WHERE 1=1";
        $params = [];
        if ($status !== '') {
            $sql .= " AND e.status = :status";
            $params['status'] = $status;
        }
        if ($search !== '') {
            $sql .= " AND (e.title LIKE :q OR o.name LIKE :q OR e.location LIKE :q)";
            $params['q'] = '%' . $search . '%';
        }
        $sql .= " ORDER BY CASE WHEN e.status = 'Pending' THEN 0 ELSE 1 END ASC, e.id DESC";
        return $this->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $slug = slugify((string)$data['title']) . '-' . random_int(1000, 9999);

        $this->execute(
            "INSERT INTO events (
                organization_id, category_id, created_by, title, slug, cover_image,
                short_description, content, start_time, end_time, registration_deadline,
                location, province, map_url, max_volunteers, registered_count, max_hours,
                benefits, guidelines, medical_unit, blood_target_units, pre_donation_instructions,
                contact_info, status, is_featured, created_at, updated_at
             ) VALUES (
                :organization_id, :category_id, :created_by, :title, :slug, :cover_image,
                :short_description, :content, :start_time, :end_time, :registration_deadline,
                :location, :province, :map_url, :max_volunteers, 0, :max_hours,
                :benefits, :guidelines, :medical_unit, :blood_target_units, :pre_donation_instructions,
                :contact_info, :status, 0, :now, :now
             )",
            [
                'organization_id' => (int)$data['organization_id'],
                'category_id' => (int)$data['category_id'],
                'created_by' => (int)$data['created_by'],
                'title' => trim((string)$data['title']),
                'slug' => $slug,
                'cover_image' => !empty($data['cover_image']) ? $data['cover_image'] : '/assets/images/events/environment-cleanup.jpg',
                'short_description' => trim((string)$data['short_description']),
                'content' => trim((string)$data['content']),
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'registration_deadline' => $data['registration_deadline'],
                'location' => trim((string)$data['location']),
                'province' => $data['province'] ?? 'TP. Hồ Chí Minh',
                'map_url' => $data['map_url'] ?? null,
                'max_volunteers' => max(1, (int)$data['max_volunteers']),
                'max_hours' => max(0.5, (float)($data['max_hours'] ?? 4.0)),
                'benefits' => $data['benefits'] ?? null,
                'guidelines' => $data['guidelines'] ?? null,
                'medical_unit' => $data['medical_unit'] ?? null,
                'blood_target_units' => !empty($data['blood_target_units']) ? (int)$data['blood_target_units'] : null,
                'pre_donation_instructions' => $data['pre_donation_instructions'] ?? null,
                'contact_info' => $data['contact_info'] ?? null,
                'status' => $data['status'] ?? 'Pending',
                'now' => $now,
            ]
        );
        $eventId = $this->lastInsertId();

        if (!empty($data['requirements']) && is_array($data['requirements'])) {
            $order = 1;
            foreach ($data['requirements'] as $reqText) {
                $reqText = trim((string)$reqText);
                if ($reqText !== '') {
                    $this->execute(
                        "INSERT INTO event_requirements (event_id, requirement_text, is_mandatory, display_order, created_at, updated_at)
                         VALUES (:eid, :txt, 1, :ord, :now, :now)",
                        ['eid' => $eventId, 'txt' => $reqText, 'ord' => $order++, 'now' => $now]
                    );
                }
            }
        }

        return $eventId;
    }

    public function update(int $id, array $data): bool
    {
        return $this->execute(
            "UPDATE events
             SET category_id = :category_id,
                 title = :title,
                 short_description = :short_description,
                 content = :content,
                 start_time = :start_time,
                 end_time = :end_time,
                 registration_deadline = :registration_deadline,
                 location = :location,
                 province = :province,
                 map_url = :map_url,
                 max_volunteers = :max_volunteers,
                 max_hours = :max_hours,
                 benefits = :benefits,
                 guidelines = :guidelines,
                 medical_unit = :medical_unit,
                 blood_target_units = :blood_target_units,
                 pre_donation_instructions = :pre_donation_instructions,
                 contact_info = :contact_info,
                 cover_image = COALESCE(:cover_image, cover_image),
                 updated_at = :now
             WHERE id = :id",
            [
                'category_id' => (int)$data['category_id'],
                'title' => trim((string)$data['title']),
                'short_description' => trim((string)$data['short_description']),
                'content' => trim((string)$data['content']),
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'registration_deadline' => $data['registration_deadline'],
                'location' => trim((string)$data['location']),
                'province' => $data['province'] ?? 'TP. Hồ Chí Minh',
                'map_url' => $data['map_url'] ?? null,
                'max_volunteers' => max(1, (int)$data['max_volunteers']),
                'max_hours' => max(0.5, (float)($data['max_hours'] ?? 4.0)),
                'benefits' => $data['benefits'] ?? null,
                'guidelines' => $data['guidelines'] ?? null,
                'medical_unit' => $data['medical_unit'] ?? null,
                'blood_target_units' => !empty($data['blood_target_units']) ? (int)$data['blood_target_units'] : null,
                'pre_donation_instructions' => $data['pre_donation_instructions'] ?? null,
                'contact_info' => $data['contact_info'] ?? null,
                'cover_image' => $data['cover_image'] ?? null,
                'now' => date('Y-m-d H:i:s'),
                'id' => $id,
            ]
        );
    }

    public function updateStatus(int $eventId, string $status, ?int $approvedBy = null, ?string $rejectionReason = null): bool
    {
        $now = date('Y-m-d H:i:s');
        return $this->execute(
            "UPDATE events
             SET status = :status,
                 approved_by = COALESCE(:approved_by, approved_by),
                 approved_at = CASE WHEN :status_check IN ('Approved', 'Published') THEN :now ELSE approved_at END,
                 rejection_reason = :reason,
                 updated_at = :now
             WHERE id = :id",
            [
                'status' => $status,
                'status_check' => $status,
                'approved_by' => $approvedBy,
                'reason' => $rejectionReason,
                'now' => $now,
                'id' => $eventId,
            ]
        );
    }

    public function syncRegisteredCount(int $eventId): int
    {
        $count = (int)$this->fetchValue(
            "SELECT COUNT(*) FROM registrations WHERE event_id = :eid AND status IN ('Confirmed', 'Attended')",
            ['eid' => $eventId]
        );
        $this->execute(
            "UPDATE events SET registered_count = :cnt, updated_at = :now WHERE id = :eid",
            ['cnt' => $count, 'now' => date('Y-m-d H:i:s'), 'eid' => $eventId]
        );
        return $count;
    }
}
