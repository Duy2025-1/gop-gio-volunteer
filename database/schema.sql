-- =============================================================================
-- GÓP GIÓ VOLUNTEER - DATABASE SCHEMA (MySQL 8.0)
-- Nền tảng kết nối tình nguyện viên với các hoạt động thiện nguyện và hiến máu
-- Slogan: Một hành động nhỏ – Ngàn giá trị lớn.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `blood_donation_appointments`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `certificates`;
DROP TABLE IF EXISTS `volunteer_hours`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `waitlist`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `event_requirements`;
DROP TABLE IF EXISTS `event_images`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `event_categories`;
DROP TABLE IF EXISTS `organization_verifications`;
DROP TABLE IF EXISTS `organization_members`;
DROP TABLE IF EXISTS `organizations`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;

-- -----------------------------------------------------------------------------
-- 1. TABLE: roles
-- -----------------------------------------------------------------------------
CREATE TABLE `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. TABLE: users
-- -----------------------------------------------------------------------------
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id` INT UNSIGNED NOT NULL,
    `full_name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(25) DEFAULT NULL,
    `birth_date` DATE DEFAULT NULL,
    `gender` ENUM('Male', 'Female', 'Other') DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `blood_type` VARCHAR(10) DEFAULT NULL,
    `status` ENUM('Active', 'Locked', 'Pending') NOT NULL DEFAULT 'Active',
    `failed_login_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
    `locked_until` DATETIME DEFAULT NULL,
    `remember_token` VARCHAR(100) DEFAULT NULL,
    `reset_token` VARCHAR(100) DEFAULT NULL,
    `reset_token_expires_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_role_id` (`role_id`),
    KEY `idx_users_status` (`status`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. TABLE: organizations
-- -----------------------------------------------------------------------------
CREATE TABLE `organizations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(180) NOT NULL,
    `slug` VARCHAR(200) NOT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `description` TEXT NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `contact_email` VARCHAR(150) NOT NULL,
    `contact_phone` VARCHAR(30) NOT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `verification_status` ENUM('Pending', 'Verified', 'Rejected', 'Supplement_Required', 'Locked') NOT NULL DEFAULT 'Pending',
    `verified_at` DATETIME DEFAULT NULL,
    `verified_by` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('Active', 'Locked') NOT NULL DEFAULT 'Active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_organizations_slug` (`slug`),
    KEY `idx_organizations_owner` (`owner_user_id`),
    KEY `idx_organizations_verification` (`verification_status`),
    CONSTRAINT `fk_organizations_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_organizations_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. TABLE: organization_members
-- -----------------------------------------------------------------------------
CREATE TABLE `organization_members` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `member_role` ENUM('Owner', 'Manager', 'Coordinator') NOT NULL DEFAULT 'Coordinator',
    `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_org_member` (`organization_id`, `user_id`),
    KEY `idx_org_members_user` (`user_id`),
    CONSTRAINT `fk_org_members_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_org_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. TABLE: organization_verifications
-- -----------------------------------------------------------------------------
CREATE TABLE `organization_verifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` INT UNSIGNED NOT NULL,
    `submitted_by` INT UNSIGNED NOT NULL,
    `document_title` VARCHAR(180) NOT NULL,
    `document_url` VARCHAR(255) NOT NULL,
    `registration_number` VARCHAR(80) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `status` ENUM('Pending', 'Approved', 'Rejected', 'Supplement_Required') NOT NULL DEFAULT 'Pending',
    `admin_feedback` TEXT DEFAULT NULL,
    `reviewed_by` INT UNSIGNED DEFAULT NULL,
    `reviewed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_org_verifications_org` (`organization_id`),
    KEY `idx_org_verifications_status` (`status`),
    CONSTRAINT `fk_org_verif_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_org_verif_submitter` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_org_verif_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. TABLE: event_categories
-- -----------------------------------------------------------------------------
CREATE TABLE `event_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL,
    `icon` VARCHAR(80) NOT NULL DEFAULT 'fa-hand-holding-heart',
    `color_badge` VARCHAR(30) NOT NULL DEFAULT '#16A34A',
    `description` VARCHAR(255) DEFAULT NULL,
    `is_blood_donation` TINYINT(1) NOT NULL DEFAULT 0,
    `display_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('Active', 'Hidden') NOT NULL DEFAULT 'Active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_event_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. TABLE: events
-- -----------------------------------------------------------------------------
CREATE TABLE `events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `title` VARCHAR(220) NOT NULL,
    `slug` VARCHAR(240) NOT NULL,
    `cover_image` VARCHAR(500) NOT NULL,
    `short_description` VARCHAR(500) NOT NULL,
    `content` TEXT NOT NULL,
    `start_time` DATETIME NOT NULL,
    `end_time` DATETIME NOT NULL,
    `registration_deadline` DATETIME NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    `province` VARCHAR(100) NOT NULL DEFAULT 'TP. Hồ Chí Minh',
    `map_url` VARCHAR(500) DEFAULT NULL,
    `max_volunteers` INT UNSIGNED NOT NULL DEFAULT 50,
    `registered_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `max_hours` DECIMAL(5,2) NOT NULL DEFAULT 4.00,
    `benefits` TEXT DEFAULT NULL,
    `guidelines` TEXT DEFAULT NULL,
    `medical_unit` VARCHAR(200) DEFAULT NULL,
    `blood_target_units` INT UNSIGNED DEFAULT NULL,
    `pre_donation_instructions` TEXT DEFAULT NULL,
    `contact_info` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('Draft', 'Pending', 'Approved', 'Rejected', 'Published', 'Ongoing', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `rejection_reason` TEXT DEFAULT NULL,
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_events_slug` (`slug`),
    KEY `idx_events_org` (`organization_id`),
    KEY `idx_events_category` (`category_id`),
    KEY `idx_events_status` (`status`),
    KEY `idx_events_start_time` (`start_time`),
    KEY `idx_events_province` (`province`),
    CONSTRAINT `fk_events_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_events_category` FOREIGN KEY (`category_id`) REFERENCES `event_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_events_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_events_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 8. TABLE: event_images
-- -----------------------------------------------------------------------------
CREATE TABLE `event_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT UNSIGNED NOT NULL,
    `image_url` VARCHAR(500) NOT NULL,
    `caption` VARCHAR(255) DEFAULT NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_event_images_event` (`event_id`),
    CONSTRAINT `fk_event_images_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 9. TABLE: event_requirements
-- -----------------------------------------------------------------------------
CREATE TABLE `event_requirements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT UNSIGNED NOT NULL,
    `requirement_text` VARCHAR(255) NOT NULL,
    `is_mandatory` TINYINT(1) NOT NULL DEFAULT 1,
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_event_requirements_event` (`event_id`),
    CONSTRAINT `fk_event_requirements_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 10. TABLE: registrations
-- -----------------------------------------------------------------------------
CREATE TABLE `registrations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `registration_code` VARCHAR(40) NOT NULL,
    `qr_token` VARCHAR(100) NOT NULL,
    `qr_token_hash` CHAR(64) NOT NULL,
    `status` ENUM('Pending', 'Confirmed', 'Waitlisted', 'Cancelled', 'Attended', 'Absent') NOT NULL DEFAULT 'Confirmed',
    `motivation_note` TEXT DEFAULT NULL,
    `emergency_contact` VARCHAR(120) DEFAULT NULL,
    `cancel_reason` VARCHAR(255) DEFAULT NULL,
    `cancelled_at` DATETIME DEFAULT NULL,
    `registered_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `confirmed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_registrations_event_user` (`event_id`, `user_id`),
    UNIQUE KEY `uk_registrations_code` (`registration_code`),
    UNIQUE KEY `uk_registrations_qr_token` (`qr_token`),
    UNIQUE KEY `uk_registrations_qr_hash` (`qr_token_hash`),
    KEY `idx_registrations_user` (`user_id`),
    KEY `idx_registrations_status` (`status`),
    CONSTRAINT `fk_registrations_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_registrations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 11. TABLE: waitlist
-- -----------------------------------------------------------------------------
CREATE TABLE `waitlist` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `registration_id` INT UNSIGNED NOT NULL,
    `position_number` INT UNSIGNED NOT NULL DEFAULT 1,
    `status` ENUM('Waiting', 'Promoted', 'Expired', 'Cancelled') NOT NULL DEFAULT 'Waiting',
    `promoted_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_waitlist_registration` (`registration_id`),
    KEY `idx_waitlist_event_status` (`event_id`, `status`, `position_number`),
    CONSTRAINT `fk_waitlist_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_waitlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_waitlist_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 12. TABLE: attendance
-- -----------------------------------------------------------------------------
CREATE TABLE `attendance` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `registration_id` INT UNSIGNED NOT NULL,
    `event_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `check_in_time` DATETIME DEFAULT NULL,
    `check_out_time` DATETIME DEFAULT NULL,
    `check_in_method` ENUM('QR', 'Manual') NOT NULL DEFAULT 'QR',
    `check_out_method` ENUM('QR', 'Manual') DEFAULT NULL,
    `checked_in_by` INT UNSIGNED DEFAULT NULL,
    `checked_out_by` INT UNSIGNED DEFAULT NULL,
    `calculated_hours` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('CheckedIn', 'CheckedOut', 'Verified', 'Disputed') NOT NULL DEFAULT 'CheckedIn',
    `notes` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_attendance_registration` (`registration_id`),
    KEY `idx_attendance_event` (`event_id`),
    KEY `idx_attendance_user` (`user_id`),
    CONSTRAINT `fk_attendance_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_attendance_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_attendance_checkin_by` FOREIGN KEY (`checked_in_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_attendance_checkout_by` FOREIGN KEY (`checked_out_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 13. TABLE: volunteer_hours
-- -----------------------------------------------------------------------------
CREATE TABLE `volunteer_hours` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `event_id` INT UNSIGNED NOT NULL,
    `registration_id` INT UNSIGNED NOT NULL,
    `attendance_id` INT UNSIGNED NOT NULL,
    `hours` DECIMAL(5,2) NOT NULL,
    `status` ENUM('Pending', 'Approved', 'Rejected', 'Adjusted') NOT NULL DEFAULT 'Pending',
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `adjustment_reason` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_volunteer_hours_registration` (`registration_id`),
    KEY `idx_volunteer_hours_user_status` (`user_id`, `status`),
    KEY `idx_volunteer_hours_event` (`event_id`),
    CONSTRAINT `fk_vh_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_vh_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_vh_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_vh_attendance` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_vh_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 14. TABLE: certificates
-- -----------------------------------------------------------------------------
CREATE TABLE `certificates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `certificate_code` VARCHAR(50) NOT NULL,
    `verification_code` VARCHAR(64) NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `event_id` INT UNSIGNED NOT NULL,
    `organization_id` INT UNSIGNED NOT NULL,
    `registration_id` INT UNSIGNED NOT NULL,
    `volunteer_hour_id` INT UNSIGNED NOT NULL,
    `recipient_name` VARCHAR(150) NOT NULL,
    `event_title` VARCHAR(220) NOT NULL,
    `organization_name` VARCHAR(180) NOT NULL,
    `verified_hours` DECIMAL(5,2) NOT NULL,
    `issued_date` DATE NOT NULL,
    `issued_by` INT UNSIGNED NOT NULL,
    `pdf_path` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('Valid', 'Revoked') NOT NULL DEFAULT 'Valid',
    `revoked_reason` VARCHAR(255) DEFAULT NULL,
    `revoked_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_certificates_code` (`certificate_code`),
    UNIQUE KEY `uk_certificates_verification` (`verification_code`),
    UNIQUE KEY `uk_certificates_registration` (`registration_id`),
    KEY `idx_certificates_user` (`user_id`),
    KEY `idx_certificates_event` (`event_id`),
    CONSTRAINT `fk_cert_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cert_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cert_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cert_reg` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cert_vh` FOREIGN KEY (`volunteer_hour_id`) REFERENCES `volunteer_hours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cert_issuer` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 15. TABLE: notifications
-- -----------------------------------------------------------------------------
CREATE TABLE `notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(180) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('Registration', 'Event', 'Attendance', 'Hours', 'Certificate', 'System') NOT NULL DEFAULT 'System',
    `link_url` VARCHAR(255) DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `read_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_user_read` (`user_id`, `is_read`, `created_at`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 16. TABLE: reviews
-- -----------------------------------------------------------------------------
CREATE TABLE `reviews` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT UNSIGNED NOT NULL,
    `organization_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `registration_id` INT UNSIGNED NOT NULL,
    `rating` TINYINT UNSIGNED NOT NULL,
    `comment` TEXT NOT NULL,
    `status` ENUM('Visible', 'Hidden', 'Flagged') NOT NULL DEFAULT 'Visible',
    `moderated_by` INT UNSIGNED DEFAULT NULL,
    `moderation_note` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_reviews_registration` (`registration_id`),
    KEY `idx_reviews_event` (`event_id`, `status`),
    KEY `idx_reviews_org` (`organization_id`, `status`),
    CONSTRAINT `fk_reviews_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reviews_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reviews_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reviews_moderator` FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 17. TABLE: reports
-- -----------------------------------------------------------------------------
CREATE TABLE `reports` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reporter_id` INT UNSIGNED NOT NULL,
    `target_type` ENUM('Event', 'Organization', 'Review', 'User') NOT NULL,
    `target_id` INT UNSIGNED NOT NULL,
    `reason` VARCHAR(180) NOT NULL,
    `details` TEXT DEFAULT NULL,
    `status` ENUM('Pending', 'Reviewing', 'Resolved', 'Dismissed') NOT NULL DEFAULT 'Pending',
    `resolved_by` INT UNSIGNED DEFAULT NULL,
    `resolution_note` TEXT DEFAULT NULL,
    `resolved_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_reports_status` (`status`),
    KEY `idx_reports_target` (`target_type`, `target_id`),
    CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reports_resolver` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 18. TABLE: audit_logs
-- -----------------------------------------------------------------------------
CREATE TABLE `audit_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(120) NOT NULL,
    `entity_type` VARCHAR(80) NOT NULL,
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `old_values` TEXT DEFAULT NULL,
    `new_values` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_audit_logs_user` (`user_id`),
    KEY `idx_audit_logs_entity` (`entity_type`, `entity_id`),
    KEY `idx_audit_logs_action` (`action`),
    CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 19. TABLE: blood_donation_appointments
-- -----------------------------------------------------------------------------
CREATE TABLE `blood_donation_appointments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `registration_id` INT UNSIGNED NOT NULL,
    `appointment_code` VARCHAR(40) NOT NULL,
    `time_slot` VARCHAR(60) NOT NULL,
    `blood_group_declared` VARCHAR(10) DEFAULT 'Chưa rõ',
    `donation_volume_ml` INT UNSIGNED DEFAULT 350,
    `medical_unit_name` VARCHAR(200) NOT NULL,
    `status` ENUM('Scheduled', 'Arrived', 'Completed', 'Deferred', 'Cancelled') NOT NULL DEFAULT 'Scheduled',
    `medical_staff_confirmed_by` VARCHAR(150) DEFAULT NULL,
    `medical_confirmation_note` VARCHAR(255) DEFAULT NULL,
    `confirmed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_blood_appt_code` (`appointment_code`),
    UNIQUE KEY `uk_blood_appt_reg` (`registration_id`),
    KEY `idx_blood_appt_event` (`event_id`, `time_slot`),
    KEY `idx_blood_appt_user` (`user_id`),
    CONSTRAINT `fk_blood_appt_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_blood_appt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_blood_appt_reg` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 20. TABLE: system_settings
-- -----------------------------------------------------------------------------
CREATE TABLE `system_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT NOT NULL,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `description` VARCHAR(255) DEFAULT NULL,
    `updated_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_system_settings_key` (`setting_key`),
    CONSTRAINT `fk_system_settings_updater` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
