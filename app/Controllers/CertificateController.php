<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Certificate;
use App\Services\AttendanceService;
use App\Services\CertificateService;

class CertificateController extends Controller
{
    private CertificateService $certService;
    private Certificate $certModel;
    private AttendanceService $attendanceService;

    public function __construct()
    {
        $this->certService = new CertificateService();
        $this->certModel = new Certificate();
        $this->attendanceService = new AttendanceService();
    }

    public function verify(string $verificationCode): void
    {
        $cert = $this->certService->verifyCertificate($verificationCode);
        $qrSvg = null;
        if ($cert) {
            $verifyUrl = rtrim((string)env('APP_URL', 'http://localhost:8080'), '/') . '/certificate/verify/' . $cert['verification_code'];
            $qrSvg = $this->attendanceService->generateQrSvg($verifyUrl, 160);
        }

        $this->render('certificates/verify', [
            'pageTitle' => 'Xác minh Chứng nhận Tình nguyện – Góp Gió Volunteer',
            'certificate' => $cert,
            'searchedCode' => $verificationCode,
            'qrSvg' => $qrSvg,
        ]);
    }

    public function verifyLookup(): void
    {
        $code = trim((string)($_GET['code'] ?? ''));
        if ($code !== '') {
            $this->redirect('/certificate/verify/' . urlencode($code));
        }
        $this->render('certificates/verify', [
            'pageTitle' => 'Tra cứu & Xác minh Chứng nhận – Góp Gió Volunteer',
            'certificate' => null,
            'searchedCode' => '',
            'qrSvg' => null,
        ]);
    }

    public function issue(): void
    {
        $user = AuthMiddleware::requireRoles(['organizer', 'admin']);
        try {
            $regId = (int)($_POST['registration_id'] ?? 0);
            $cert = $this->certService->issueCertificate($user, $regId);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Đã cấp chứng nhận [' . $cert['certificate_code'] . '] thành công!',
                    'data' => $cert,
                ]);
            }

            Session::setFlash('success', 'Đã cấp chứng nhận [' . $cert['certificate_code'] . '] thành công!');
        } catch (\Throwable $e) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            Session::setFlash('error', $e->getMessage());
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/organizer/dashboard');
    }

    public function downloadPdf(string $verificationCode): void
    {
        $cert = $this->certService->verifyCertificate($verificationCode);
        if (!$cert) {
            http_response_code(404);
            $this->render('layouts/404', ['pageTitle' => 'Không tìm thấy chứng nhận']);
            return;
        }

        if ($cert['status'] === 'Revoked') {
            Session::setFlash('error', 'Chứng nhận này đã bị thu hồi và không còn hiệu lực tải xuống.');
            $this->redirect('/certificate/verify/' . urlencode($verificationCode));
        }

        $pdfBytes = $this->certService->generatePdfBytes($cert);
        $fileName = $cert['certificate_code'] . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($pdfBytes));
        echo $pdfBytes;
        exit;
    }
}
