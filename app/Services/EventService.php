<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Validator;
use App\Middleware\AuthMiddleware;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Organization;

class EventService
{
    private Event $eventModel;
    private Organization $orgModel;
    private Notification $notificationModel;
    private AuditLog $auditModel;

    public function __construct()
    {
        $this->eventModel = new Event();
        $this->orgModel = new Organization();
        $this->notificationModel = new Notification();
        $this->auditModel = new AuditLog();
    }

    public function createEvent(array $actorUser, array $input): int
    {
        AuthMiddleware::assertRole($actorUser, ['organizer', 'admin'], 'tạo sự kiện');

        $validator = new Validator($input);
        $validator->required('title', 'Tên sự kiện')
                  ->minLength('title', 5, 'Tên sự kiện')
                  ->required('category_id', 'Danh mục sự kiện')
                  ->required('short_description', 'Mô tả ngắn')
                  ->required('content', 'Nội dung chi tiết')
                  ->required('start_time', 'Thời gian bắt đầu')
                  ->required('end_time', 'Thời gian kết thúc')
                  ->required('location', 'Địa điểm tổ chức')
                  ->required('max_volunteers', 'Số lượng tình nguyện viên tối đa')
                  ->numericMin('max_volunteers', 1, 'Số lượng tình nguyện viên tối đa')
                  ->numericMin('max_hours', 0.5, 'Số giờ tình nguyện tối đa');

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->firstError());
        }

        if (strtotime((string)$input['end_time']) <= strtotime((string)$input['start_time'])) {
            throw new \InvalidArgumentException('Thời gian kết thúc phải sau thời gian bắt đầu.');
        }

        $orgId = (int)($input['organization_id'] ?? 0);
        if ($actorUser['role_slug'] === 'organizer') {
            $org = $this->orgModel->findByOwnerUserId((int)$actorUser['id']);
            if (!$org) {
                throw new \RuntimeException('Bạn cần khởi tạo hồ sơ tổ chức trước khi tạo sự kiện.');
            }
            if ($org['status'] === 'Locked' || $org['verification_status'] === 'Locked') {
                throw new \RuntimeException('Tổ chức của bạn đang bị khóa nên không thể tạo sự kiện mới.');
            }
            $orgId = (int)$org['id'];
        }

        // Organizer cannot self-approve their own event! Only Draft or Pending allowed on creation by Organizer.
        $requestedStatus = $input['status'] ?? 'Pending';
        if ($actorUser['role_slug'] === 'organizer') {
            $status = ($requestedStatus === 'Draft') ? 'Draft' : 'Pending';
        } else {
            $status = in_array($requestedStatus, ['Draft', 'Pending', 'Published'], true) ? $requestedStatus : 'Pending';
        }

        $regDeadline = !empty($input['registration_deadline'])
            ? $input['registration_deadline']
            : date('Y-m-d H:i:s', strtotime((string)$input['start_time']) - 3600);

        $eventId = $this->eventModel->create([
            'organization_id' => $orgId,
            'category_id' => (int)$input['category_id'],
            'created_by' => (int)$actorUser['id'],
            'title' => $input['title'],
            'cover_image' => $input['cover_image'] ?? null,
            'short_description' => $input['short_description'],
            'content' => $input['content'],
            'start_time' => $input['start_time'],
            'end_time' => $input['end_time'],
            'registration_deadline' => $regDeadline,
            'location' => $input['location'],
            'province' => $input['province'] ?? 'TP. Hồ Chí Minh',
            'map_url' => $input['map_url'] ?? null,
            'max_volunteers' => (int)$input['max_volunteers'],
            'max_hours' => (float)($input['max_hours'] ?? 4.0),
            'benefits' => $input['benefits'] ?? null,
            'guidelines' => $input['guidelines'] ?? null,
            'medical_unit' => $input['medical_unit'] ?? null,
            'blood_target_units' => $input['blood_target_units'] ?? null,
            'pre_donation_instructions' => $input['pre_donation_instructions'] ?? null,
            'contact_info' => $input['contact_info'] ?? null,
            'status' => $status,
            'requirements' => $input['requirements'] ?? [],
        ]);

        $this->auditModel->log((int)$actorUser['id'], 'CREATE_EVENT', 'Event', $eventId, null, [
            'title' => $input['title'],
            'status' => $status,
        ]);

        return $eventId;
    }

    public function updateEvent(array $actorUser, int $eventId, array $input): bool
    {
        AuthMiddleware::assertRole($actorUser, ['organizer', 'admin'], 'chỉnh sửa sự kiện');

        $event = $this->eventModel->findById($eventId);
        if (!$event) {
            throw new \RuntimeException('Không tìm thấy sự kiện.');
        }

        // Ownership check: Organizer cannot edit another organization's event
        if ($actorUser['role_slug'] === 'organizer') {
            $org = $this->orgModel->findByOwnerUserId((int)$actorUser['id']);
            if (!$org || (int)$event['organization_id'] !== (int)$org['id']) {
                throw new \RuntimeException('Bạn không có quyền chỉnh sửa sự kiện thuộc tổ chức khác.');
            }
        }

        $regDeadline = !empty($input['registration_deadline'])
            ? $input['registration_deadline']
            : $event['registration_deadline'];

        $this->eventModel->update($eventId, [
            'category_id' => $input['category_id'] ?? $event['category_id'],
            'title' => $input['title'] ?? $event['title'],
            'short_description' => $input['short_description'] ?? $event['short_description'],
            'content' => $input['content'] ?? $event['content'],
            'start_time' => $input['start_time'] ?? $event['start_time'],
            'end_time' => $input['end_time'] ?? $event['end_time'],
            'registration_deadline' => $regDeadline,
            'location' => $input['location'] ?? $event['location'],
            'province' => $input['province'] ?? $event['province'],
            'map_url' => $input['map_url'] ?? $event['map_url'],
            'max_volunteers' => $input['max_volunteers'] ?? $event['max_volunteers'],
            'max_hours' => $input['max_hours'] ?? $event['max_hours'],
            'benefits' => $input['benefits'] ?? $event['benefits'],
            'guidelines' => $input['guidelines'] ?? $event['guidelines'],
            'medical_unit' => $input['medical_unit'] ?? $event['medical_unit'],
            'blood_target_units' => $input['blood_target_units'] ?? $event['blood_target_units'],
            'pre_donation_instructions' => $input['pre_donation_instructions'] ?? $event['pre_donation_instructions'],
            'contact_info' => $input['contact_info'] ?? $event['contact_info'],
            'cover_image' => $input['cover_image'] ?? null,
        ]);

        $this->auditModel->log((int)$actorUser['id'], 'UPDATE_EVENT', 'Event', $eventId);
        return true;
    }

    public function reviewEvent(array $actorUser, int $eventId, string $decision, ?string $rejectionReason = null): bool
    {
        // Strictly only Admin can approve/reject events
        AuthMiddleware::assertRole($actorUser, ['admin'], 'duyệt hoặc từ chối sự kiện');

        $event = $this->eventModel->findById($eventId);
        if (!$event) {
            throw new \RuntimeException('Không tìm thấy sự kiện.');
        }

        $newStatus = match ($decision) {
            'approve', 'Published', 'Approved' => 'Published',
            'reject', 'Rejected' => 'Rejected',
            default => throw new \InvalidArgumentException('Quyết định kiểm duyệt không hợp lệ.'),
        };

        $this->eventModel->updateStatus($eventId, $newStatus, (int)$actorUser['id'], $rejectionReason);

        $orgOwnerId = (int)$event['organization_owner_id'];
        if ($newStatus === 'Published') {
            $this->notificationModel->create(
                $orgOwnerId,
                'Sự kiện đã được phê duyệt & công khai!',
                'Sự kiện "' . $event['title'] . '" của bạn đã được Admin phê duyệt và mở đăng ký.',
                'Event',
                '/events/' . $eventId
            );
        } else {
            $this->notificationModel->create(
                $orgOwnerId,
                'Sự kiện chưa được duyệt',
                'Sự kiện "' . $event['title'] . '" bị từ chối. Lý do: ' . ($rejectionReason ?: 'Cần bổ sung thông tin chi tiết.'),
                'Event',
                '/organizer/dashboard'
            );
        }

        $this->auditModel->log(
            (int)$actorUser['id'],
            $newStatus === 'Published' ? 'APPROVE_EVENT' : 'REJECT_EVENT',
            'Event',
            $eventId,
            ['status' => $event['status']],
            ['status' => $newStatus, 'reason' => $rejectionReason]
        );

        return true;
    }

    public function changeOrganizerEventState(array $actorUser, int $eventId, string $newState): bool
    {
        AuthMiddleware::assertRole($actorUser, ['organizer', 'admin'], 'cập nhật trạng thái sự kiện');

        $event = $this->eventModel->findById($eventId);
        if (!$event) {
            throw new \RuntimeException('Không tìm thấy sự kiện.');
        }

        if ($actorUser['role_slug'] === 'organizer') {
            $org = $this->orgModel->findByOwnerUserId((int)$actorUser['id']);
            if (!$org || (int)$event['organization_id'] !== (int)$org['id']) {
                throw new \RuntimeException('Bạn không có quyền quản lý sự kiện thuộc tổ chức khác.');
            }
            // Organizer cannot set Published/Approved directly if event is Draft/Pending/Rejected
            if (in_array($newState, ['Approved', 'Published'], true) && in_array($event['status'], ['Draft', 'Pending', 'Rejected'], true)) {
                throw new \RuntimeException('Ban tổ chức không được phép tự duyệt sự kiện của mình.');
            }
        }

        $allowedTransitions = ['Pending', 'Ongoing', 'Completed', 'Cancelled'];
        if (!in_array($newState, $allowedTransitions, true) && $actorUser['role_slug'] !== 'admin') {
            throw new \InvalidArgumentException('Trạng thái chuyển đổi không hợp lệ.');
        }

        $this->eventModel->updateStatus($eventId, $newState);
        $this->auditModel->log((int)$actorUser['id'], 'CHANGE_EVENT_STATUS', 'Event', $eventId, ['status' => $event['status']], ['status' => $newState]);
        return true;
    }
}
