<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AttendanceController;
use App\Controllers\AuthController;
use App\Controllers\CertificateController;
use App\Controllers\EventController;
use App\Controllers\HomeController;
use App\Controllers\NotificationController;
use App\Controllers\OrganizationController;
use App\Controllers\OrganizerController;
use App\Controllers\RegistrationController;
use App\Controllers\VolunteerController;
use App\Core\Router;

/** @var Router $router */

// Public & Guest Routes
$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [HomeController::class, 'about']);
$router->get('/leaderboard', [HomeController::class, 'leaderboard']);
$router->get('/blood-donation', [HomeController::class, 'bloodDonation']);
$router->get('/health', [HomeController::class, 'health']);

// Events Catalog & Detail
$router->get('/events', [EventController::class, 'index']);
$router->get('/events/{id}', [EventController::class, 'show']);
$router->post('/events/{id}/review', [EventController::class, 'submitReview'], ['volunteer']);
$router->post('/events/{id}/report', [EventController::class, 'submitReport']);

// Organizations
$router->get('/organizations', [OrganizationController::class, 'index']);
$router->get('/organizations/{id}', [OrganizationController::class, 'show']);
$router->post('/organizations/save', [OrganizationController::class, 'saveProfile'], ['organizer', 'admin']);
$router->post('/organizations/verify-request', [OrganizationController::class, 'submitVerification'], ['organizer']);

// Authentication & Profile
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'sendResetLink']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);
$router->post('/profile/update', [AuthController::class, 'updateProfile']);
$router->post('/profile/password', [AuthController::class, 'changePassword']);

// Event Registration & QR Code
$router->post('/events/{eventId}/register', [RegistrationController::class, 'register'], ['volunteer']);
$router->post('/registrations/{registrationId}/cancel', [RegistrationController::class, 'cancel']);
$router->get('/registrations/{registrationId}/qr.svg', [RegistrationController::class, 'qrCodeSvg']);

// Certificates & Public Verification
$router->get('/certificate/verify', [CertificateController::class, 'verifyLookup']);
$router->get('/certificate/verify/{verificationCode}', [CertificateController::class, 'verify']);
$router->get('/certificate/download/{verificationCode}', [CertificateController::class, 'downloadPdf']);
$router->post('/certificates/issue', [CertificateController::class, 'issue'], ['organizer', 'admin']);

// Role Dashboards
$router->get('/volunteer/dashboard', [VolunteerController::class, 'dashboard'], ['volunteer']);

// Organizer Routes
$router->get('/organizer/dashboard', [OrganizerController::class, 'dashboard'], ['organizer', 'admin']);
$router->post('/organizer/events/create', [OrganizerController::class, 'createEvent'], ['organizer', 'admin']);
$router->post('/organizer/events/{id}/update', [OrganizerController::class, 'updateEvent'], ['organizer', 'admin']);
$router->post('/organizer/events/{id}/state', [OrganizerController::class, 'changeEventState'], ['organizer', 'admin']);
$router->get('/organizer/events/{eventId}/export-csv', [OrganizerController::class, 'exportCsv'], ['organizer', 'admin']);
$router->get('/organizer/events/{eventId}/scanner', [AttendanceController::class, 'scanner'], ['organizer', 'admin']);

// Attendance, QR Scan & Volunteer Hours API / Actions
$router->post('/api/attendance/scan', [AttendanceController::class, 'apiScan'], ['organizer', 'admin']);
$router->post('/attendance/hours/approve', [AttendanceController::class, 'approveHours'], ['organizer', 'admin']);
$router->post('/attendance/blood/update', [AttendanceController::class, 'updateBloodAppointment'], ['organizer', 'admin']);

// Admin Routes
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], ['admin']);
$router->post('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'], ['admin']);
$router->post('/admin/organizations/{id}/verify', [AdminController::class, 'verifyOrganization'], ['admin']);
$router->post('/admin/events/{id}/review', [AdminController::class, 'reviewEvent'], ['admin']);
$router->post('/admin/categories/create', [AdminController::class, 'saveCategory'], ['admin']);
$router->post('/admin/hours/adjust', [AdminController::class, 'adjustHours'], ['admin']);
$router->post('/admin/certificates/{id}/toggle-status', [AdminController::class, 'toggleCertificateStatus'], ['admin']);
$router->post('/admin/reviews/{id}/moderate', [AdminController::class, 'moderateReview'], ['admin']);
$router->post('/admin/reports/{id}/resolve', [AdminController::class, 'resolveReport'], ['admin']);
$router->post('/admin/notifications/broadcast', [AdminController::class, 'broadcastNotification'], ['admin']);
$router->post('/admin/settings/update', [AdminController::class, 'updateSettings'], ['admin']);

// Notifications API (Polling & Actions)
$router->get('/api/notifications/unread', [NotificationController::class, 'unreadCount']);
$router->post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
$router->post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
$router->post('/notifications/{id}/delete', [NotificationController::class, 'delete']);
