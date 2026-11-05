<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Organization;

class OrganizationController extends Controller
{
    private Organization $orgModel;
    private Event $eventModel;
    private AuditLog $auditModel;

    public function __construct()
    {
        $this->orgModel = new Organization();
        $this->eventModel = new Event();
        $this->auditModel = new AuditLog();
    }

    public function index(): void
    {
        $search = trim((string)($_GET['q'] ?? ''));
        $organizations = $this->orgModel->getAll($search);

        $this->render('organizations/index', [
            'pageTitle' => 'Các Tổ chức & Câu lạc bộ Tình nguyện – Góp Gió Volunteer',
            'organizations' => $organizations,
            'search' => $search,
        ]);
    }

    public function show(string $id): void
    {
        $orgId = (int)$id;
        $org = $this->orgModel->findById($orgId);
        if (!$org) {
            http_response_code(404);
            $this->render('layouts/404', ['pageTitle' => 'Không tìm thấy tổ chức']);
            return;
        }

        $events = $this->eventModel->getByOrganization($orgId);
        $publicEvents = array_values(array_filter($events, fn($e) => in_array($e['status'], ['Published', 'Ongoing', 'Completed'], true)));
        $members = $this->orgModel->getMembers($orgId);

        $this->render('organizations/show', [
            'pageTitle' => $org['name'] . ' – Tổ chức Tình nguyện | Góp Gió Volunteer',
            'org' => $org,
            'events' => $publicEvents,
            'members' => $members,
        ]);
    }

    public function saveProfile(): void
    {
        $user = AuthMiddleware::requireRoles(['organizer', 'admin']);
        try {
            $logoUrl = null;
            if (!empty($_FILES['logo_file']['tmp_name'])) {
                $logoUrl = handle_secure_upload($_FILES['logo_file'], 'organizations');
            } elseif (!empty($_POST['logo_url'])) {
                $logoUrl = trim((string)$_POST['logo_url']);
            }

            $existing = $this->orgModel->findByOwnerUserId((int)$user['id']);
            if ($existing) {
                $this->orgModel->update((int)$existing['id'], [
                    'name' => $_POST['name'] ?? $existing['name'],
                    'description' => $_POST['description'] ?? $existing['description'],
                    'address' => $_POST['address'] ?? $existing['address'],
                    'contact_email' => $_POST['contact_email'] ?? $existing['contact_email'],
                    'contact_phone' => $_POST['contact_phone'] ?? $existing['contact_phone'],
                    'website' => $_POST['website'] ?? $existing['website'],
                    'logo' => $logoUrl,
                ]);
                $this->auditModel->log((int)$user['id'], 'UPDATE_ORGANIZATION', 'Organization', (int)$existing['id']);
                Session::setFlash('success', 'Đã cập nhật hồ sơ tổ chức thành công.');
            } else {
                $orgId = $this->orgModel->create([
                    'owner_user_id' => (int)$user['id'],
                    'name' => $_POST['name'] ?? ('Tổ chức của ' . $user['full_name']),
                    'description' => $_POST['description'] ?? '',
                    'address' => $_POST['address'] ?? '',
                    'contact_email' => $_POST['contact_email'] ?? $user['email'],
                    'contact_phone' => $_POST['contact_phone'] ?? $user['phone'],
                    'website' => $_POST['website'] ?? null,
                    'logo' => $logoUrl,
                ]);
                $this->auditModel->log((int)$user['id'], 'CREATE_ORGANIZATION', 'Organization', $orgId);
                Session::setFlash('success', 'Đã khởi tạo hồ sơ tổ chức thành công.');
            }
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('/organizer/dashboard?tab=organization');
    }

    public function submitVerification(): void
    {
        $user = AuthMiddleware::requireRoles(['organizer']);
        try {
            $org = $this->orgModel->findByOwnerUserId((int)$user['id']);
            if (!$org) {
                throw new \RuntimeException('Vui lòng tạo hồ sơ tổ chức trước khi gửi yêu cầu xác minh.');
            }

            $docUrl = '/uploads/organizations/minh_chung_to_chuc.pdf';
            if (!empty($_FILES['document_file']['tmp_name'])) {
                $docUrl = handle_secure_upload($_FILES['document_file'], 'organizations') ?: $docUrl;
            } elseif (!empty($_POST['document_url'])) {
                $docUrl = trim((string)$_POST['document_url']);
            }

            $verifId = $this->orgModel->submitVerification((int)$org['id'], (int)$user['id'], [
                'document_title' => $_POST['document_title'] ?? 'Giấy phép / Quyết định thành lập tổ chức',
                'document_url' => $docUrl,
                'registration_number' => $_POST['registration_number'] ?? '',
                'notes' => $_POST['notes'] ?? '',
            ]);

            $this->auditModel->log((int)$user['id'], 'SUBMIT_ORG_VERIFICATION', 'OrganizationVerification', $verifId);
            Session::setFlash('success', 'Đã gửi hồ sơ đề nghị xác minh tổ chức tới Admin.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect('/organizer/dashboard?tab=organization');
    }
}
