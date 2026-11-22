<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Middleware\AuthMiddleware;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Registration;
use Dompdf\Dompdf;
use Dompdf\Options;
use PDO;

class CertificateService
{
    private PDO $db;
    private Certificate $certModel;
    private Registration $regModel;
    private Attendance $attendanceModel;
    private Organization $orgModel;
    private Notification $notificationModel;
    private AuditLog $auditModel;
    private AttendanceService $attendanceService;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->certModel = new Certificate($this->db);
        $this->regModel = new Registration($this->db);
        $this->attendanceModel = new Attendance($this->db);
        $this->orgModel = new Organization($this->db);
        $this->notificationModel = new Notification($this->db);
        $this->auditModel = new AuditLog($this->db);
        $this->attendanceService = new AttendanceService();
    }

    /**
     * Issue a Certificate for a Registration ONLY after Volunteer Hours are Approved.
     * Prevents duplicate certificates for the same registration.
     */
    public function issueCertificate(array $actorUser, int $registrationId): array
    {
        AuthMiddleware::assertRole($actorUser, ['organizer', 'admin'], 'cấp chứng nhận tình nguyện');

        $reg = $this->regModel->findById($registrationId);
        if (!$reg) {
            throw new \RuntimeException('Không tìm thấy thông tin đăng ký tham gia.');
        }

        if ($actorUser['role_slug'] === 'organizer') {
            $org = $this->orgModel->findByOwnerUserId((int)$actorUser['id']);
            if (!$org || (int)$org['id'] !== (int)$reg['organization_id']) {
                throw new \RuntimeException('Bạn không có quyền cấp chứng nhận cho sự kiện thuộc tổ chức khác.');
            }
        }

        // Check if already issued
        $existingCert = $this->certModel->findByRegistrationId($registrationId);
        if ($existingCert) {
            return $existingCert;
        }

        // Must have Approved volunteer hours!
        $vh = $this->attendanceModel->findVolunteerHoursByRegistrationId($registrationId);
        if (!$vh || $vh['status'] !== 'Approved' || (float)$vh['hours'] <= 0) {
            throw new \RuntimeException('Chưa thể cấp chứng nhận khi số giờ tình nguyện chưa được phê duyệt (Approved).');
        }

        $now = date('Y-m-d H:i:s');
        $issuedDate = date('Y-m-d');
        $certCode = 'GG-CERT-' . date('Y') . '-' . str_pad((string)$registrationId, 4, '0', STR_PAD_LEFT);
        $verificationCode = 'GGVERIFY' . date('Y') . strtoupper(bin2hex(random_bytes(5)));

        $stmt = $this->db->prepare(
            "INSERT INTO certificates (
                certificate_code, verification_code, user_id, event_id, organization_id,
                registration_id, volunteer_hour_id, recipient_name, event_title, organization_name,
                verified_hours, issued_date, issued_by, status, created_at, updated_at
             ) VALUES (
                :c_code, :v_code, :uid, :eid, :oid,
                :rid, :vhid, :r_name, :e_title, :o_name,
                :hours, :idate, :iby, 'Valid', :now, :now
             )"
        );
        $stmt->execute([
            'c_code' => $certCode,
            'v_code' => $verificationCode,
            'uid' => (int)$reg['user_id'],
            'eid' => (int)$reg['event_id'],
            'oid' => (int)$reg['organization_id'],
            'rid' => $registrationId,
            'vhid' => (int)$vh['id'],
            'r_name' => $reg['volunteer_name'],
            'e_title' => $reg['event_title'],
            'o_name' => $reg['organization_name'],
            'hours' => (float)$vh['hours'],
            'idate' => $issuedDate,
            'iby' => (int)$actorUser['id'],
            'now' => $now,
        ]);

        $certId = (int)$this->db->lastInsertId();

        $this->notificationModel->create(
            (int)$reg['user_id'],
            'Chúc mừng! Bạn vừa nhận được Chứng nhận Tình nguyện',
            'Chứng nhận mã [' . $certCode . '] cho sự kiện "' . $reg['event_title'] . '" đã sẵn sàng để tải xuống PDF.',
            'Certificate',
            '/certificate/verify/' . $verificationCode
        );

        $this->auditModel->log((int)$actorUser['id'], 'ISSUE_CERTIFICATE', 'Certificate', $certId, null, [
            'certificate_code' => $certCode,
            'verification_code' => $verificationCode,
            'hours' => (float)$vh['hours'],
        ]);

        return $this->certModel->findById($certId);
    }

    public function verifyCertificate(string $verificationCode): ?array
    {
        return $this->certModel->findByVerificationCode($verificationCode);
    }

    /**
     * Render Certificate PDF bytes using Dompdf with Vietnamese DejaVu Sans Unicode support
     */
    public function generatePdfBytes(array $certificate): string
    {
        $verifyUrl = rtrim((string)env('APP_URL', 'http://localhost:8080'), '/') . '/certificate/verify/' . $certificate['verification_code'];
        $qrSvg = $this->attendanceService->generateQrSvg($verifyUrl, 130);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $recipientName = e($certificate['recipient_name']);
        $eventTitle = e($certificate['event_title']);
        $orgName = e($certificate['organization_name']);
        $hours = number_format((float)$certificate['verified_hours'], 1);
        $issuedDate = format_date($certificate['issued_date']);
        $certCode = e($certificate['certificate_code']);
        $verifyCode = e($certificate['verification_code']);
        $eventPeriod = format_date($certificate['start_time'] ?? $certificate['issued_date']);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 landscape;
            margin: 18mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1E293B;
            margin: 0;
            padding: 0;
            background-color: #FFFFFF;
        }
        .cert-frame {
            border: 5px solid #15803D;
            padding: 8px;
            background: #FFFFFF;
        }
        .cert-inner {
            border: 2px solid #16A34A;
            padding: 28px 36px;
            background-color: #F8FAFC;
            text-align: center;
            position: relative;
        }
        .brand-header {
            color: #15803D;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .brand-slogan {
            color: #475569;
            font-size: 11px;
            margin-bottom: 18px;
        }
        .cert-title {
            color: #15803D;
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 8px 0 6px 0;
        }
        .cert-subtitle {
            font-size: 13px;
            color: #475569;
            margin-bottom: 16px;
        }
        .recipient-name {
            font-size: 26px;
            font-weight: bold;
            color: #0F172A;
            border-bottom: 2px solid #16A34A;
            display: inline-block;
            padding: 0 28px 6px 28px;
            margin-bottom: 16px;
        }
        .event-box {
            margin: 10px auto 18px auto;
            max-width: 85%;
            font-size: 14px;
            line-height: 1.6;
        }
        .event-title {
            font-size: 17px;
            font-weight: bold;
            color: #15803D;
        }
        .hours-pill {
            display: inline-block;
            background-color: #DCFCE7;
            color: #15803D;
            border: 1px solid #16A34A;
            padding: 6px 18px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin: 8px 0 18px 0;
        }
        .footer-table {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
        }
        .footer-table td {
            vertical-align: bottom;
            text-align: center;
            width: 33.33%;
            font-size: 12px;
        }
        .meta-left {
            text-align: left !important;
            font-size: 11px;
            color: #475569;
            line-height: 1.6;
        }
        .sign-title {
            font-weight: bold;
            color: #1E293B;
            font-size: 13px;
            margin-bottom: 35px;
        }
        .sign-org {
            font-weight: bold;
            color: #15803D;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="cert-frame">
        <div class="cert-inner">
            <div class="brand-header">GÓP GIÓ VOLUNTEER PLATFORM</div>
            <div class="brand-slogan">Một hành động nhỏ – Ngàn giá trị lớn</div>

            <div class="cert-title">GIẤY CHỨNG NHẬN HOẠT ĐỘNG TÌNH NGUYỆN</div>
            <div class="cert-subtitle">Ban Điều Phối Góp Gió Volunteer cùng Đơn vị Tổ chức trân trọng chứng nhận:</div>

            <div class="recipient-name">{$recipientName}</div>

            <div class="event-box">
                Đã tham gia tích cực và hoàn thành xuất sắc nhiệm vụ tình nguyện trong chương trình:<br>
                <span class="event-title">"{$eventTitle}"</span><br>
                Do đơn vị <strong>{$orgName}</strong> tổ chức vào ngày <strong>{$eventPeriod}</strong>.
            </div>

            <div class="hours-pill">
                Thời lượng đóng góp được xác nhận: {$hours} Giờ Tình Nguyện
            </div>

            <table class="footer-table">
                <tr>
                    <td class="meta-left">
                        <strong>Mã chứng nhận:</strong> {$certCode}<br>
                        <strong>Mã xác minh:</strong> {$verifyCode}<br>
                        <strong>Ngày cấp:</strong> {$issuedDate}<br>
                        <strong>Tra cứu trực tuyến:</strong> /certificate/verify/{$verifyCode}
                    </td>
                    <td>
                        <img src="{$qrBase64}" width="96" height="96" alt="QR Verify"/><br>
                        <span style="font-size:10px;color:#64748B;">Quét QR để xác thực</span>
                    </td>
                    <td>
                        <div class="sign-title">ĐẠI DIỆN ĐƠN VỊ TỔ CHỨC</div>
                        <div class="sign-org">{$orgName}</div>
                        <div style="font-size:11px;color:#16A34A;margin-top:4px;">(Đã ký xác thực điện tử)</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
HTML;

        if (class_exists(Options::class) && class_exists(Dompdf::class)) {
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $pdfOutput = $dompdf->output();
        } else {
            $pdfOutput = $this->generateFallbackPdfBytes($certificate);
        }

        // Save copy in storage/certificates
        $certDir = STORAGE_PATH . '/certificates';
        if (!is_dir($certDir)) {
            @mkdir($certDir, 0775, true);
        }
        $fileName = $certificate['certificate_code'] . '.pdf';
        $fullPath = $certDir . '/' . $fileName;
        @file_put_contents($fullPath, $pdfOutput);

        $this->db->prepare("UPDATE certificates SET pdf_path = :path WHERE id = :id")->execute([
            'path' => 'storage/certificates/' . $fileName,
            'id' => (int)$certificate['id'],
        ]);

        return $pdfOutput;
    }

    private function generateFallbackPdfBytes(array $certificate): string
    {
        $code = (string)($certificate['certificate_code'] ?? 'GG-CERT');
        $vCode = (string)($certificate['verification_code'] ?? '');
        $hours = number_format((float)($certificate['verified_hours'] ?? 0), 1);
        $date = (string)($certificate['issued_date'] ?? date('Y-m-d'));
        $stream = "BT /F1 18 Tf 60 500 Td (GOP GIO VOLUNTEER - CERTIFICATE OF VOLUNTEERING) Tj "
            . "/F1 13 Tf 0 -35 Td (Certificate Code: {$code}) Tj "
            . "0 -25 Td (Verification Code: {$vCode}) Tj "
            . "0 -25 Td (Verified Hours: {$hours} hours) Tj "
            . "0 -25 Td (Issued Date: {$date}) Tj ET";
        $len = strlen($stream);

        return "%PDF-1.4\n"
            . "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n"
            . "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n"
            . "3 0 obj << /Type /Page /Parent 2 0 R /Resources << /Font << /F1 4 0 R >> >> /MediaBox [0 0 842 595] /Contents 5 0 R >> endobj\n"
            . "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n"
            . "5 0 obj << /Length {$len} >> stream\n{$stream}\nendstream endobj\n"
            . "xref\n0 6\n0000000000 65535 f \n"
            . "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n0\n%%EOF";
    }
}
