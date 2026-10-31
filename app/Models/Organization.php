<?php
declare(strict_types=1);

namespace App\Models;

class Organization extends BaseModel
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT o.*, u.full_name AS owner_name, u.email AS owner_email,
                    (SELECT COUNT(*) FROM events e WHERE e.organization_id = o.id AND e.status IN ('Published','Ongoing','Completed')) AS event_count,
                    COALESCE((SELECT AVG(rv.rating) FROM reviews rv WHERE rv.organization_id = o.id AND rv.status = 'Visible'), 5.0) AS avg_rating
             FROM organizations o
             INNER JOIN users u ON u.id = o.owner_user_id
             WHERE o.id = :id",
            ['id' => $id]
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne(
            "SELECT o.*, u.full_name AS owner_name, u.email AS owner_email
             FROM organizations o
             INNER JOIN users u ON u.id = o.owner_user_id
             WHERE o.slug = :slug",
            ['slug' => $slug]
        );
    }

    public function findByOwnerUserId(int $userId): ?array
    {
        return $this->fetchOne(
            "SELECT o.*
             FROM organizations o
             WHERE o.owner_user_id = :uid
             ORDER BY o.id ASC LIMIT 1",
            ['uid' => $userId]
        );
    }

    public function getAll(string $search = '', string $verificationStatus = ''): array
    {
        $sql = "SELECT o.*, u.full_name AS owner_name, u.email AS owner_email,
                       (SELECT COUNT(*) FROM events e WHERE e.organization_id = o.id) AS total_events,
                       COALESCE((SELECT SUM(vh.hours) FROM volunteer_hours vh INNER JOIN events ev ON ev.id = vh.event_id WHERE ev.organization_id = o.id AND vh.status = 'Approved'), 0) AS total_hours
                FROM organizations o
                INNER JOIN users u ON u.id = o.owner_user_id
                WHERE 1=1";
        $params = [];
        if ($search !== '') {
            $sql .= " AND (o.name LIKE :q OR o.address LIKE :q OR o.contact_email LIKE :q)";
            $params['q'] = '%' . $search . '%';
        }
        if ($verificationStatus !== '') {
            $sql .= " AND o.verification_status = :vstatus";
            $params['vstatus'] = $verificationStatus;
        }
        $sql .= " ORDER BY o.id DESC";
        return $this->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $slug = slugify((string)$data['name']) . '-' . random_int(100, 999);
        $this->execute(
            "INSERT INTO organizations (
                owner_user_id, name, slug, logo, description, address,
                contact_email, contact_phone, website, verification_status, status, created_at, updated_at
             ) VALUES (
                :owner_user_id, :name, :slug, :logo, :description, :address,
                :contact_email, :contact_phone, :website, :verification_status, 'Active', :now, :now
             )",
            [
                'owner_user_id' => (int)$data['owner_user_id'],
                'name' => trim((string)$data['name']),
                'slug' => $slug,
                'logo' => $data['logo'] ?? '/assets/images/organizations/org-vnuhcm.jpg',
                'description' => trim((string)$data['description']),
                'address' => trim((string)$data['address']),
                'contact_email' => trim((string)$data['contact_email']),
                'contact_phone' => trim((string)$data['contact_phone']),
                'website' => $data['website'] ?? null,
                'verification_status' => $data['verification_status'] ?? 'Pending',
                'now' => $now,
            ]
        );
        $orgId = $this->lastInsertId();

        // Add owner to organization_members
        $this->execute(
            "INSERT INTO organization_members (organization_id, user_id, member_role, status, joined_at, created_at, updated_at)
             VALUES (:org_id, :uid, 'Owner', 'Active', :now, :now, :now)",
            ['org_id' => $orgId, 'uid' => (int)$data['owner_user_id'], 'now' => $now]
        );

        return $orgId;
    }

    public function update(int $id, array $data): bool
    {
        return $this->execute(
            "UPDATE organizations
             SET name = :name,
                 description = :description,
                 address = :address,
                 contact_email = :contact_email,
                 contact_phone = :contact_phone,
                 website = :website,
                 logo = COALESCE(:logo, logo),
                 updated_at = :now
             WHERE id = :id",
            [
                'name' => trim((string)$data['name']),
                'description' => trim((string)$data['description']),
                'address' => trim((string)$data['address']),
                'contact_email' => trim((string)$data['contact_email']),
                'contact_phone' => trim((string)$data['contact_phone']),
                'website' => $data['website'] ?? null,
                'logo' => $data['logo'] ?? null,
                'now' => date('Y-m-d H:i:s'),
                'id' => $id,
            ]
        );
    }

    public function submitVerification(int $orgId, int $userId, array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(
            "INSERT INTO organization_verifications (
                organization_id, submitted_by, document_title, document_url, registration_number, notes, status, created_at, updated_at
             ) VALUES (
                :org_id, :uid, :title, :url, :reg_num, :notes, 'Pending', :now, :now
             )",
            [
                'org_id' => $orgId,
                'uid' => $userId,
                'title' => trim((string)$data['document_title']),
                'url' => $data['document_url'] ?? '/uploads/organizations/minh_chung.pdf',
                'reg_num' => $data['registration_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'now' => $now,
            ]
        );
        $this->execute(
            "UPDATE organizations SET verification_status = 'Pending', updated_at = :now WHERE id = :id",
            ['now' => $now, 'id' => $orgId]
        );
        return $this->lastInsertId();
    }

    public function updateVerificationStatus(int $orgId, string $verificationStatus, int $adminId, ?string $feedback = null): bool
    {
        $now = date('Y-m-d H:i:s');
        $orgStatus = ($verificationStatus === 'Locked') ? 'Locked' : 'Active';
        $this->execute(
            "UPDATE organizations
             SET verification_status = :vstatus,
                 status = :ostatus,
                 verified_at = :verified_at,
                 verified_by = :admin_id,
                 updated_at = :now
             WHERE id = :id",
            [
                'vstatus' => $verificationStatus,
                'ostatus' => $orgStatus,
                'verified_at' => $verificationStatus === 'Verified' ? $now : null,
                'admin_id' => $adminId,
                'now' => $now,
                'id' => $orgId,
            ]
        );

        $verifStatusMap = [
            'Verified' => 'Approved',
            'Rejected' => 'Rejected',
            'Supplement_Required' => 'Supplement_Required',
            'Locked' => 'Rejected',
        ];
        if (isset($verifStatusMap[$verificationStatus])) {
            $this->execute(
                "UPDATE organization_verifications
                 SET status = :st, admin_feedback = :fb, reviewed_by = :aid, reviewed_at = :now, updated_at = :now
                 WHERE organization_id = :org_id",
                [
                    'st' => $verifStatusMap[$verificationStatus],
                    'fb' => $feedback,
                    'aid' => $adminId,
                    'now' => $now,
                    'org_id' => $orgId,
                ]
            );
        }
        return true;
    }

    public function getVerificationsByOrg(int $orgId): array
    {
        return $this->fetchAll(
            "SELECT * FROM organization_verifications WHERE organization_id = :org_id ORDER BY id DESC",
            ['org_id' => $orgId]
        );
    }

    public function getMembers(int $orgId): array
    {
        return $this->fetchAll(
            "SELECT om.*, u.full_name, u.email, u.phone, u.avatar
             FROM organization_members om
             INNER JOIN users u ON u.id = om.user_id
             WHERE om.organization_id = :org_id
             ORDER BY om.id ASC",
            ['org_id' => $orgId]
        );
    }
}
