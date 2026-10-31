-- =============================================================================
-- GÓP GIÓ VOLUNTEER - SEED DATA (MySQL 8.0)
-- Dữ liệu mẫu thực tế cho toàn bộ 20 bảng (Sử dụng hình ảnh thực tế tại /assets/images/)
-- Tài khoản Demo:
--   1. Admin:      admin@gopgio.vn         | Mật khẩu: Admin@123456
--   2. Organizer:  org.dhqg@gopgio.vn      | Mật khẩu: Org@123456
--                  org.hienmau@gopgio.vn   | Mật khẩu: Org@123456
--                  org.xanh@gopgio.vn      | Mật khẩu: Org@123456
--   3. Volunteer:  minhanh.tnv@gopgio.vn   | Mật khẩu: Volunteer@123
--                  hoangnam.tnv@gopgio.vn  | Mật khẩu: Volunteer@123
--                  thuha.tnv@gopgio.vn     | Mật khẩu: Volunteer@123
--                  quocbao.tnv@gopgio.vn   | Mật khẩu: Volunteer@123
--                  khanhlinh.tnv@gopgio.vn | Mật khẩu: Volunteer@123
--                  ductrong.tnv@gopgio.vn  | Mật khẩu: Volunteer@123
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. SEED: roles
-- -----------------------------------------------------------------------------
INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Quản trị viên', 'admin', 'Quản trị toàn bộ hệ thống Góp Gió Volunteer'),
(2, 'Ban tổ chức', 'organizer', 'Đại diện câu lạc bộ, tổ chức xã hội hoặc đơn vị hiến máu'),
(3, 'Tình nguyện viên', 'volunteer', 'Sinh viên và công dân tham gia các hoạt động thiện nguyện');

-- -----------------------------------------------------------------------------
-- 2. SEED: users (1 Admin, 3 Organizers, 6 Volunteers)
-- -----------------------------------------------------------------------------
INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `password_hash`, `phone`, `birth_date`, `gender`, `avatar`, `bio`, `address`, `blood_type`, `status`) VALUES
(1, 1, 'Trần Quản Trị Hệ Thống', 'admin@gopgio.vn', '$2y$10$srxbY1Zs5PdfzGNa1SzRgOGYQxl08CVV7yx8uDi4Xvdd.CXl4mYem', '0901234567', '1995-04-15', 'Male', '/assets/images/avatars/user-admin.jpg', 'Quản trị viên điều phối nền tảng Góp Gió Volunteer.', 'Quận 1, TP. Hồ Chí Minh', 'O+', 'Active'),
(2, 2, 'Nguyễn Lê Hoàng (ĐHQG)', 'org.dhqg@gopgio.vn', '$2y$10$lzbVyVZo4YQDus/SCAc1W.NJtPItfZ9fSrRq6YIapcL5Ts0IxSQJq', '0912345678', '1999-08-20', 'Male', '/assets/images/avatars/user-org1.jpg', 'Chủ nhiệm CLB Sinh Viên Tình Nguyện ĐHQG TP.HCM.', 'Khu đô thị ĐHQG, TP. Thủ Đức, TP.HCM', 'A+', 'Active'),
(3, 2, 'BS. Phạm Thị Thanh Thảo', 'org.hienmau@gopgio.vn', '$2y$10$lzbVyVZo4YQDus/SCAc1W.NJtPItfZ9fSrRq6YIapcL5Ts0IxSQJq', '0923456789', '1988-11-10', 'Female', '/assets/images/avatars/user-org2.jpg', 'Điều phối viên Trung tâm Hiến Máu Nhân Đạo TP.HCM.', 'Quận 5, TP. Hồ Chí Minh', 'B+', 'Active'),
(4, 2, 'Lê Minh Tuấn (Sài Gòn Xanh)', 'org.xanh@gopgio.vn', '$2y$10$lzbVyVZo4YQDus/SCAc1W.NJtPItfZ9fSrRq6YIapcL5Ts0IxSQJq', '0934567890', '1997-02-25', 'Male', '/assets/images/avatars/user-org3.jpg', 'Điều phối Mạng Lưới Môi Trường Sài Gòn Xanh.', 'Quận Bình Thạnh, TP. Hồ Chí Minh', 'O+', 'Active'),
(5, 3, 'Nguyễn Ngọc Minh Anh', 'minhanh.tnv@gopgio.vn', '$2y$10$1rw8Ux8Yt0rkZBrftUGLlePiEuOYhIMaNK1dhnoUwEFtVpjyLZ4q2', '0945678901', '2004-05-12', 'Female', '/assets/images/avatars/user-vol1.jpg', 'Sinh viên năm 3 ĐH Bách Khoa, tham gia các hoạt động hỗ trợ lớp học vùng ven và hiến máu tình nguyện.', 'TP. Thủ Đức, TP. Hồ Chí Minh', 'O+', 'Active'),
(6, 3, 'Võ Hoàng Nam', 'hoangnam.tnv@gopgio.vn', '$2y$10$1rw8Ux8Yt0rkZBrftUGLlePiEuOYhIMaNK1dhnoUwEFtVpjyLZ4q2', '0956789012', '2003-09-18', 'Male', '/assets/images/avatars/user-vol2.jpg', 'Tình nguyện viên tham gia các đợt làm sạch kênh rạch và trồng rừng ngập mặn.', 'Quận Gò Vấp, TP. Hồ Chí Minh', 'A+', 'Active'),
(7, 3, 'Đặng Thu Hà', 'thuha.tnv@gopgio.vn', '$2y$10$1rw8Ux8Yt0rkZBrftUGLlePiEuOYhIMaNK1dhnoUwEFtVpjyLZ4q2', '0967890123', '2004-12-03', 'Female', '/assets/images/avatars/user-vol3.jpg', 'Sinh viên ĐH Kinh Tế, phụ trách phân loại sách và hỗ trợ bếp ăn bệnh viện.', 'Quận 3, TP. Hồ Chí Minh', 'B+', 'Active'),
(8, 3, 'Phan Quốc Bảo', 'quocbao.tnv@gopgio.vn', '$2y$10$1rw8Ux8Yt0rkZBrftUGLlePiEuOYhIMaNK1dhnoUwEFtVpjyLZ4q2', '0978901234', '2002-07-22', 'Male', '/assets/images/avatars/user-vol4.jpg', 'Tham gia hiến máu định kỳ và hỗ trợ điều phối hậu cần sự kiện.', 'Quận Tân Bình, TP. Hồ Chí Minh', 'AB+', 'Active'),
(9, 3, 'Bùi Khánh Linh', 'khanhlinh.tnv@gopgio.vn', '$2y$10$1rw8Ux8Yt0rkZBrftUGLlePiEuOYhIMaNK1dhnoUwEFtVpjyLZ4q2', '0989012345', '2005-03-14', 'Female', '/assets/images/avatars/user-vol5.jpg', 'Sinh viên ĐH Khoa học Tự nhiên, thành viên đội hình tình nguyện cộng đồng.', 'Quận 10, TP. Hồ Chí Minh', 'O-', 'Active'),
(10, 3, 'Hoàng Đức Trọng', 'ductrong.tnv@gopgio.vn', '$2y$10$1rw8Ux8Yt0rkZBrftUGLlePiEuOYhIMaNK1dhnoUwEFtVpjyLZ4q2', '0990123456', '2003-10-30', 'Male', '/assets/images/avatars/user-vol6.jpg', 'Sinh viên tình nguyện tại Quận 7, TP. Hồ Chí Minh.', 'Quận 7, TP. Hồ Chí Minh', 'A+', 'Active');

-- -----------------------------------------------------------------------------
-- 3. SEED: organizations
-- -----------------------------------------------------------------------------
INSERT INTO `organizations` (`id`, `owner_user_id`, `name`, `slug`, `logo`, `description`, `address`, `contact_email`, `contact_phone`, `website`, `verification_status`, `verified_at`, `verified_by`, `status`) VALUES
(1, 2, 'CLB Sinh Viên Tình Nguyện ĐHQG TP.HCM', 'clb-sinh-vien-tinh-nguyen-dhqg-tphcm', '/assets/images/organizations/org-vnuhcm.jpg', 'Tổ chức tình nguyện trực thuộc Hội Sinh viên Đại học Quốc gia TP.HCM, triển khai các chương trình trao tủ sách, lớp học cuối tuần, bếp ăn thiện nguyện và hỗ trợ sinh viên khó khăn.', 'Nhà Văn hóa Sinh viên ĐHQG, Khu phố 6, P. Linh Trung, TP. Thủ Đức, TP.HCM', 'clbtinhnguyen@vnuhcm.edu.vn', '02837242160', 'https://vnuhcm.edu.vn', 'Verified', '2026-06-10 09:00:00', 1, 'Active'),
(2, 3, 'Trung Tâm Kết Nối Hiến Máu Nhân Đạo Sài Gòn', 'trung-tam-ket-noi-hien-mau-nhan-dao-sai-gon', '/assets/images/organizations/org-redcross.jpg', 'Đơn vị phối hợp cùng Bệnh viện Truyền máu Huyết học và Hội Chữ Thập Đỏ tổ chức các đợt hiến máu tình nguyện định kỳ tại các quận, huyện và trường đại học trên địa bàn TP.HCM.', '118 Hồng Bàng, Phường 12, Quận 5, TP. Hồ Chí Minh', 'lienhe@hienmausaigon.org.vn', '02839571342', 'https://giotmauvang.org.vn', 'Verified', '2026-06-12 14:30:00', 1, 'Active'),
(3, 4, 'Mạng Lưới Hành Động Môi Trường Sài Gòn Xanh', 'mang-luoi-hanh-dong-moi-truong-sai-gon-xanh', '/assets/images/organizations/org-saigonxanh.jpg', 'Nhóm hoạt động môi trường tổ chức các đợt ra quân cuối tuần thu gom rác thải nhựa ven kênh rạch, trồng rừng ngập mặn Cần Giờ và hướng dẫn phân loại rác tại nguồn.', '45 Điện Biên Phủ, Phường 15, Quận Bình Thạnh, TP. Hồ Chí Minh', 'banbientap@saigonxanh.org', '0934567890', 'https://saigonxanh.org', 'Verified', '2026-07-01 10:15:00', 1, 'Active');

-- -----------------------------------------------------------------------------
-- 4. SEED: organization_members
-- -----------------------------------------------------------------------------
INSERT INTO `organization_members` (`id`, `organization_id`, `user_id`, `member_role`, `status`, `joined_at`) VALUES
(1, 1, 2, 'Owner', 'Active', '2026-06-01 08:00:00'),
(2, 2, 3, 'Owner', 'Active', '2026-06-02 08:00:00'),
(3, 3, 4, 'Owner', 'Active', '2026-06-05 08:00:00'),
(4, 1, 5, 'Coordinator', 'Active', '2026-07-10 09:00:00');

-- -----------------------------------------------------------------------------
-- 5. SEED: organization_verifications
-- -----------------------------------------------------------------------------
INSERT INTO `organization_verifications` (`id`, `organization_id`, `submitted_by`, `document_title`, `document_url`, `registration_number`, `notes`, `status`, `admin_feedback`, `reviewed_by`, `reviewed_at`) VALUES
(1, 1, 2, 'Quyết định thành lập CLB Tình Nguyện ĐHQG TP.HCM', '/uploads/organizations/qd_thanh_lap_clb_dhqg.pdf', 'QĐ-142/HSV-ĐHQG', 'Hồ sơ xác nhận từ Ban Thư ký Hội Sinh viên ĐHQG.', 'Approved', 'Hồ sơ đầy đủ, hợp lệ. Đã xác minh.', 1, '2026-06-10 09:00:00'),
(2, 2, 3, 'Giấy chứng nhận phối hợp vận động hiến máu tình nguyện', '/uploads/organizations/gcn_hien_mau_tphcm.pdf', 'GCN-88/CTĐ-TPHCM', 'Đơn vị y tế trực thuộc Hội Chữ Thập Đỏ.', 'Approved', 'Xác minh thành công đơn vị tổ chức hiến máu.', 1, '2026-06-12 14:30:00'),
(3, 3, 4, 'Hồ sơ đăng ký hoạt động Mạng Lưới Sài Gòn Xanh', '/uploads/organizations/hoso_saigon_xanh.pdf', 'ĐK-209/TNMT-SGX', 'Cam kết an toàn lao động và bảo hộ cho tình nguyện viên.', 'Approved', 'Đã duyệt xác minh tổ chức.', 1, '2026-07-01 10:15:00');

-- -----------------------------------------------------------------------------
-- 6. SEED: event_categories
-- -----------------------------------------------------------------------------
INSERT INTO `event_categories` (`id`, `name`, `slug`, `icon`, `color_badge`, `description`, `is_blood_donation`, `display_order`, `status`) VALUES
(1, 'Hiến máu nhân đạo', 'hien-mau', 'droplet', '#B91C1C', 'Đặt lịch hiến máu tình nguyện và hỗ trợ công tác đón tiếp tại các điểm tiếp nhận máu', 1, 1, 'Active'),
(2, 'Bảo vệ môi trường', 'moi-truong', 'trees', '#15803D', 'Thu gom rác thải kênh rạch, làm sạch bờ biển và trồng cây rừng ngập mặn', 0, 2, 'Active'),
(3, 'Giáo dục và trẻ em', 'giao-duc', 'book-open', '#1D4ED8', 'Trao tặng tủ sách, tổ chức lớp học kỹ năng và hỗ trợ học sinh vùng ngoại thành', 0, 3, 'Active'),
(4, 'Thiện nguyện và từ thiện', 'tu-thien', 'utensils', '#B45309', 'Chuẩn bị suất ăn cho bệnh nhân nghèo và trao nhu yếu phẩm cho người khó khăn', 0, 4, 'Active'),
(5, 'Phát triển cộng đồng', 'cong-dong', 'users', '#4338CA', 'Tập huấn sơ cấp cứu, hỗ trợ kỳ thi và các chương trình văn hóa địa phương', 0, 5, 'Active'),
(6, 'Hỗ trợ xã hội', 'ho-tro-xa-hoi', 'heart-handshake', '#0F766E', 'Thăm hỏi, chăm sóc người cao tuổi neo đơn và hỗ trợ các mái ấm xã hội', 0, 6, 'Active');

-- -----------------------------------------------------------------------------
-- 7. SEED: events (8 distinct events with local real photos in /assets/images/events/)
-- -----------------------------------------------------------------------------
INSERT INTO `events` (
    `id`, `organization_id`, `category_id`, `created_by`, `title`, `slug`, `cover_image`,
    `short_description`, `content`, `start_time`, `end_time`, `registration_deadline`,
    `location`, `province`, `map_url`, `max_volunteers`, `registered_count`, `max_hours`,
    `benefits`, `guidelines`, `medical_unit`, `blood_target_units`, `pre_donation_instructions`,
    `contact_info`, `status`, `is_featured`, `approved_by`, `approved_at`
) VALUES
(
    1, 2, 1, 3,
    'Ngày hội hiến máu Giọt Hồng Sẻ Chia – Đợt tháng 11/2026',
    'ngay-hoi-hien-mau-giot-hong-se-chia-dot-thang-11-2026',
    '/assets/images/events/blood-donation.jpg',
    'Tiếp nhận máu tình nguyện theo khung giờ đặt trước và điều phối tình nguyện viên hướng dẫn người hiến máu tại Quận Tân Bình.',
    'Chương trình phối hợp cùng Bệnh viện Truyền máu Huyết học TP.HCM tổ chức tiếp nhận dự kiến 350 đơn vị máu phục vụ cấp cứu và điều trị. Người tham gia có thể chọn trước khung giờ (Ca sáng hoặc Ca chiều) để giảm thời gian chờ đợi. Tình nguyện viên hỗ trợ sẽ phụ trách bàn hướng dẫn khai báo thông tin, phát nước uống và hỗ trợ khu vực nghỉ sau hiến máu.',
    '2026-11-15 07:30:00', '2026-11-15 16:30:00', '2026-11-14 20:00:00',
    'Trung tâm Hiến Máu Nhân Đạo, 106 Thiên Phước, Quận Tân Bình, TP.HCM', 'TP. Hồ Chí Minh',
    'https://maps.google.com/?q=106+Thien+Phuoc+Tan+Binh+TPHCM',
    60, 4, 5.00,
    'Cấp giấy chứng nhận hiến máu tình nguyện hoặc chứng nhận 5.0 giờ hoạt động tình nguyện, hỗ trợ suất ăn nhẹ tại điểm.',
    'Mang theo CCCD hoặc ứng dụng VNeID định danh mức 2. Có mặt trước khung giờ đã chọn 15 phút để thực hiện Check-in QR.',
    'Bệnh viện Truyền máu Huyết học TP.HCM', 350,
    'Đêm trước ngày hiến máu ngủ đủ tối thiểu 6 giờ, không sử dụng rượu bia, ăn sáng nhẹ ít chất béo và uống đủ nước trước khi đến điểm tiếp nhận.',
    'BS. Thanh Thảo - 0923456789',
    'Published', 1, 1, '2026-10-15 10:00:00'
),
(
    2, 3, 2, 4,
    'Ra quân làm sạch bờ kênh Nhiêu Lộc và phân loại rác nhựa tái chế',
    'chien-dich-khoi-thong-dong-chay-kenh-nhieu-loc-chu-nhat-xanh',
    '/assets/images/events/environment-cleanup.jpg',
    'Hoạt động cuối tuần thu gom rác thải nhựa ven kênh, phân loại rác tái chế và phát túi sinh học tại khu vực Cầu Công Lý.',
    'Đợt ra quân định kỳ của Mạng Lưới Sài Gòn Xanh tập trung xử lý lượng rác nhựa tồn đọng dọc bờ kè kênh Nhiêu Lộc đoạn qua Quận 3. Tình nguyện viên được chia thành 4 tổ: tổ thu gom ven bờ, tổ phân loại chai nhựa và lon kim loại, tổ hậu cần dụng cụ và tổ tuyên truyền hạn chế túi nilon dùng một lần cho hộ dân xung quanh.',
    '2026-11-22 07:00:00', '2026-11-22 11:30:00', '2026-11-20 22:00:00',
    'Công viên bờ kênh Nhiêu Lộc (Đoạn Cầu Công Lý), Quận 3, TP.HCM', 'TP. Hồ Chí Minh',
    'https://maps.google.com/?q=Cau+Cong+Ly+Quan+3+TPHCM',
    50, 3, 4.50,
    'Cung cấp đầy đủ găng tay bảo hộ chống cắt, ủng cao su, kẹp gắp rác, nước uống và chứng nhận 4.5 giờ tình nguyện.',
    'Mặc trang phục gọn gàng, đi giày kín mũi, khuyến khích mang theo bình nước cá nhân.',
    NULL, NULL, NULL,
    'Lê Minh Tuấn - 0934567890',
    'Published', 1, 1, '2026-10-18 11:00:00'
),
(
    3, 1, 3, 2,
    'Trao tủ sách thiếu nhi và lớp học kỹ năng cuối tuần tại xã đảo Thạnh An',
    'hanh-trinh-anh-sang-tri-thuc-trao-tu-sach-day-hoc-cuoi-tuan',
    '/assets/images/events/education-support.jpg',
    'Lắp đặt 4 kệ sách thư viện, phân loại 500 đầu sách và tổ chức buổi học thực hành khoa học cho học sinh tiểu học Cần Giờ.',
    'Chương trình do CLB Sinh Viên Tình Nguyện ĐHQG TP.HCM tổ chức nhằm bổ sung nguồn sách đọc thiếu nhi và sách khoa học thường thức cho học sinh Trường Tiểu học Thạnh An. Tình nguyện viên sẽ tham gia đóng kệ gỗ, phân loại sách theo độ tuổi, tổ chức 3 lớp học kỹ năng phòng chống đuối nước và trao 50 phần quà học tập.',
    '2026-11-29 06:30:00', '2026-11-29 16:30:00', '2026-11-26 23:59:00',
    'Trường Tiểu học Thạnh An, Xã đảo Thạnh An, Huyện Cần Giờ, TP.HCM', 'TP. Hồ Chí Minh',
    'https://maps.google.com/?q=Thanh+An+Can+Gio+TPHCM',
    40, 3, 8.00,
    'Xe đưa đón khứ hồi từ Nhà Văn hóa Sinh viên ĐHQG, vé đò qua đảo, ăn trưa tập thể và chứng nhận 8.0 giờ tình nguyện.',
    'Tập trung đúng 06:15 tại Nhà Văn hóa Sinh viên ĐHQG để điểm danh QR trước khi xe xuất phát.',
    NULL, NULL, NULL,
    'Nguyễn Lê Hoàng - 0912345678',
    'Published', 1, 1, '2026-10-20 09:30:00'
),
(
    4, 1, 4, 2,
    'Bếp ăn thiện nguyện – Chuẩn bị 500 suất cơm trưa cho bệnh nhân nghèo',
    'bep-an-yeu-thuong-500-suat-com-am-long-benh-nhan-ngheo',
    '/assets/images/events/charity-kitchen.jpg',
    'Sơ chế nguyên liệu, nấu và đóng hộp 500 suất cơm chay dinh dưỡng gửi tới người nhà bệnh nhân có hoàn cảnh khó khăn.',
    'Hoạt động diễn ra tại Bếp Từ Thiện Hoa Sen vào tháng 09/2026 với sự tham gia của 35 tình nguyện viên. Các khâu từ nhặt rau, sơ chế thực phẩm, chia suất ăn vào hộp bã mía phân hủy sinh học đến trao tận tay người nhà bệnh nhân đều được thực hiện đảm bảo an toàn vệ sinh thực phẩm.',
    '2026-09-12 06:00:00', '2026-09-12 12:00:00', '2026-09-10 20:00:00',
    'Bếp Từ Thiện Hoa Sen, 24 Nguyễn Chí Thanh, Quận 10, TP.HCM', 'TP. Hồ Chí Minh',
    'https://maps.google.com/?q=24+Nguyen+Chi+Thanh+Quan+10+TPHCM',
    35, 4, 6.00,
    'Chứng nhận hoàn thành 6.0 giờ tình nguyện, hỗ trợ bữa sáng và bữa trưa tại bếp.',
    'Đeo khẩu trang, tạp dề và bao tóc trong suốt quá trình sơ chế và đóng gói thức ăn.',
    NULL, NULL, NULL,
    'Nguyễn Lê Hoàng - 0912345678',
    'Completed', 0, 1, '2026-09-01 08:00:00'
),
(
    5, 3, 2, 4,
    'Ngày hội trồng 1.000 cây đước bảo vệ rừng ngập mặn Cần Giờ',
    'ngay-hoi-trong-1000-cay-duoc-giu-rung-ngap-man-can-gio',
    '/assets/images/events/mangrove-planting.jpg',
    'Phối hợp cùng Ban quản lý Rừng phòng hộ Cần Giờ gieo trồng 1.000 cây đước con tại khu vực bãi bồi.',
    'Chương trình trồng rừng ngập mặn tháng 10/2026 đã hoàn thành với 1.000 cây giống được trồng mới dưới sự hướng dẫn kỹ thuật của cán bộ lâm nghiệp địa phương, đồng thời kết hợp thu gom rác nhựa trôi dạt ven khu vực rừng ngập mặn.',
    '2026-10-04 06:30:00', '2026-10-04 15:30:00', '2026-10-01 22:00:00',
    'Khu Dự trữ Sinh quyển Rừng ngập mặn Cần Giờ, TP.HCM', 'TP. Hồ Chí Minh',
    'https://maps.google.com/?q=Rung+Ngap+Man+Can+Gio',
    45, 4, 7.50,
    'Xe đưa đón, ủng lội bùn, ăn trưa và chứng nhận 7.5 giờ tình nguyện.',
    'Chuẩn bị thêm một bộ quần áo dự phòng để thay sau khi hoàn thành trồng cây tại bãi bồi.',
    NULL, NULL, NULL,
    'Lê Minh Tuấn - 0934567890',
    'Completed', 1, 1, '2026-09-20 14:00:00'
),
(
    6, 2, 1, 3,
    'Chủ Nhật Đỏ – Ngày hội hiến máu sinh viên tại Khu đô thị ĐHQG TP.HCM',
    'chu-nhat-do-hien-mau-cuu-nguoi-tai-khu-do-thi-dhqg',
    '/assets/images/events/university-blood-drive.jpg',
    'Đợt vận động hiến máu tình nguyện học kỳ 1 dành cho sinh viên và giảng viên các trường đại học thành viên ĐHQG.',
    'Sự kiện phối hợp giữa Trung tâm Hiến Máu Nhân Đạo và CLB Sinh Viên Tình Nguyện ĐHQG, tổ chức ngay tại Hội trường Nhà Điều hành ĐHQG TP.HCM. Sinh viên có thể đăng ký làm người hiến máu theo ca hoặc đăng ký làm tình nguyện viên hỗ trợ điều phối.',
    '2026-12-06 07:30:00', '2026-12-06 16:00:00', '2026-12-05 18:00:00',
    'Hội trường Trần Chí Đáo, Nhà Điều hành ĐHQG TP.HCM, TP. Thủ Đức', 'TP. Hồ Chí Minh',
    'https://maps.google.com/?q=Nha+Dieu+Hanh+DHQG+TPHCM',
    80, 2, 4.50,
    'Giấy chứng nhận hiến máu tình nguyện, quà bồi dưỡng theo quy định y tế và xác nhận 4.5 giờ hoạt động xã hội.',
    'Đặt lịch khung giờ trước trên hệ thống để được ưu tiên khám sàng lọc nhanh.',
    'Trung tâm Hiến Máu Nhân Đạo TP.HCM', 500,
    'Ăn sáng đầy đủ, uống nhiều nước lọc, mang theo CCCD hoặc Thẻ sinh viên.',
    'BS. Thanh Thảo - 0923456789',
    'Published', 1, 1, '2026-10-25 15:00:00'
),
(
    7, 1, 6, 2,
    'Chăm sóc và trò chuyện cùng người cao tuổi tại Trung tâm Dưỡng lão Thị Nghè',
    'chu-nhat-am-ap-tai-trung-tam-duong-lao-thi-nghe',
    '/assets/images/events/elderly-care.jpg',
    'Thăm hỏi, đọc báo, cắt tỉa cây cảnh khuôn viên và hỗ trợ sinh hoạt cuối tuần cùng các cụ già neo đơn.',
    'Đợt sinh hoạt định kỳ quy mô nhỏ (tối đa 2 tình nguyện viên điều phối chính thức trong nhóm chăm sóc trực tiếp, các đăng ký tiếp theo sẽ vào Danh sách chờ) nhằm hỗ trợ dọn dẹp khu vườn sinh hoạt chung, đọc sách báo và trò chuyện cùng các cụ tại Trung tâm Dưỡng lão Thị Nghè.',
    '2026-11-18 08:00:00', '2026-11-18 11:30:00', '2026-11-17 18:00:00',
    '153 Xô Viết Nghệ Tĩnh, Phường 17, Quận Bình Thạnh, TP.HCM', 'TP. Hồ Chí Minh',
    'https://maps.google.com/?q=153+Xo+Viet+Nghe+Tinh+Binh+Thanh',
    2, 2, 3.50,
    'Chứng nhận 3.5 giờ tình nguyện và tài liệu hướng dẫn kỹ năng hỗ trợ người cao tuổi.',
    'Giữ trật tự, giao tiếp nhẹ nhàng, lễ phép và tuân thủ quy định của trung tâm dưỡng lão.',
    NULL, NULL, NULL,
    'Nguyễn Lê Hoàng - 0912345678',
    'Published', 0, 1, '2026-10-26 10:00:00'
),
(
    8, 3, 5, 4,
    'Tập huấn kỹ năng sơ cấp cứu ban đầu cho đội ngũ tình nguyện viên cộng đồng',
    'tap-huan-ky-nang-so-cuu-cong-dong-va-phong-chong-thien-tai-2026',
    '/assets/images/events/first-aid-training.jpg',
    'Sự kiện đang chờ Quản trị viên phê duyệt: Thực hành kỹ năng ép tim ngoài lồng ngực (CPR) và sơ cứu chấn thương.',
    'Buổi tập huấn trang bị kiến thức và thực hành xử trí tình huống khẩn cấp cho các nhóm trưởng tình nguyện trước khi tham gia các chiến dịch dã ngoại, môi trường và hỗ trợ cộng đồng.',
    '2026-12-12 08:00:00', '2026-12-12 12:00:00', '2026-12-10 20:00:00',
    'Nhà Văn hóa Thanh Niên, 04 Phạm Ngọc Thạch, Quận 1, TP.HCM', 'TP. Hồ Chí Minh',
    'https://maps.google.com/?q=Nha+Van+Hoa+Thanh+Nien+TPHCM',
    40, 0, 4.00,
    'Chứng nhận hoàn thành buổi tập huấn sơ cấp cứu và 4.0 giờ tình nguyện.',
    'Mặc trang phục thoải mái để thực hành thao tác trực tiếp trên mô hình.',
    NULL, NULL, NULL,
    'Lê Minh Tuấn - 0934567890',
    'Pending', 0, NULL, NULL
);

-- -----------------------------------------------------------------------------
-- 8. SEED: event_images
-- -----------------------------------------------------------------------------
INSERT INTO `event_images` (`id`, `event_id`, `image_url`, `caption`, `display_order`) VALUES
(1, 1, '/assets/images/events/blood-donation.jpg', 'Khu vực khám sàng lọc và tiếp nhận máu tình nguyện', 1),
(2, 1, '/assets/images/events/university-blood-drive.jpg', 'Tình nguyện viên hỗ trợ hướng dẫn người đăng ký hiến máu', 2),
(3, 2, '/assets/images/events/environment-cleanup.jpg', 'Nhóm tình nguyện viên thu gom và phân loại rác nhựa ven bờ', 1),
(4, 3, '/assets/images/events/education-support.jpg', 'Hoạt động trao sách và sinh hoạt cùng các em học sinh', 1),
(5, 4, '/assets/images/events/charity-kitchen.jpg', 'Chuẩn bị các phần ăn tại Bếp Từ Thiện Hoa Sen', 1),
(6, 5, '/assets/images/events/mangrove-planting.jpg', 'Trồng cây đước con tại khu dự trữ sinh quyển Cần Giờ', 1);

-- -----------------------------------------------------------------------------
-- 9. SEED: event_requirements
-- -----------------------------------------------------------------------------
INSERT INTO `event_requirements` (`id`, `event_id`, `requirement_text`, `is_mandatory`, `display_order`) VALUES
(1, 1, 'Độ tuổi từ 18 đến 60 tuổi, cân nặng từ 45kg trở lên, mang theo CCCD hoặc VNeID mức 2.', 1, 1),
(2, 1, 'Không mắc các bệnh lây truyền qua đường máu; khoảng cách giữa 2 lần hiến máu toàn phần tối thiểu 12 tuần.', 1, 2),
(3, 2, 'Tuân thủ hướng dẫn an toàn lao động và sử dụng đầy đủ đồ bảo hộ do Ban tổ chức trang bị.', 1, 1),
(4, 3, 'Tham gia buổi phổ biến kế hoạch trực tuyến trước chuyến đi và có mặt đúng giờ tập kết.', 1, 1),
(5, 4, 'Đảm bảo quy định vệ sinh an toàn thực phẩm trong khu vực bếp.', 1, 1),
(6, 5, 'Có sức khỏe tốt để di chuyển và làm việc ngoài trời tại khu vực bãi bồi.', 1, 1),
(7, 7, 'Giao tiếp lịch sự, nhẹ nhàng và tôn trọng thời gian sinh hoạt của các cụ.', 1, 1);

-- -----------------------------------------------------------------------------
-- 10. SEED: registrations
-- -----------------------------------------------------------------------------
INSERT INTO `registrations` (
    `id`, `event_id`, `user_id`, `registration_code`, `qr_token`, `qr_token_hash`,
    `status`, `motivation_note`, `emergency_contact`, `registered_at`, `confirmed_at`
) VALUES
-- Event 4 (Completed): 4 Attended
(1, 4, 5, 'REG-202609-0001', 'ggqr_ev4_u5_9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d', SHA2('ggqr_ev4_u5_9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d', 256), 'Attended', 'Đăng ký phụ trách sơ chế nguyên liệu và đóng hộp suất ăn.', 'Mẹ: 0909111222', '2026-09-05 09:00:00', '2026-09-05 09:00:00'),
(2, 4, 6, 'REG-202609-0002', 'ggqr_ev4_u6_1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e', SHA2('ggqr_ev4_u6_1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e', 256), 'Attended', 'Đã có kinh nghiệm hỗ trợ bếp ăn từ thiện.', 'Bố: 0909222333', '2026-09-05 10:15:00', '2026-09-05 10:15:00'),
(3, 4, 7, 'REG-202609-0003', 'ggqr_ev4_u7_2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f', SHA2('ggqr_ev4_u7_2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f', 256), 'Attended', 'Phụ trách phân chia phần cơm và trao tại bàn phát.', 'Chị gái: 0909333444', '2026-09-06 08:30:00', '2026-09-06 08:30:00'),
(4, 4, 8, 'REG-202609-0004', 'ggqr_ev4_u8_3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a', SHA2('ggqr_ev4_u8_3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a', 256), 'Attended', 'Hỗ trợ vận chuyển thùng cơm và nước uống.', 'Anh trai: 0909444555', '2026-09-06 14:20:00', '2026-09-06 14:20:00'),

-- Event 5 (Completed): 4 Attended
(5, 5, 5, 'REG-202610-0005', 'ggqr_ev5_u5_4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b', SHA2('ggqr_ev5_u5_4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b', 256), 'Attended', 'Tham gia trồng cây đước và thu gom rác ven rừng.', 'Mẹ: 0909111222', '2026-09-25 09:00:00', '2026-09-25 09:00:00'),
(6, 5, 6, 'REG-202610-0006', 'ggqr_ev5_u6_5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c', SHA2('ggqr_ev5_u6_5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c', 256), 'Attended', 'Phụ trách vận chuyển bầu cây giống ra bãi bồi.', 'Bố: 0909222333', '2026-09-25 11:00:00', '2026-09-25 11:00:00'),
(7, 5, 9, 'REG-202610-0007', 'ggqr_ev5_u9_6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d', SHA2('ggqr_ev5_u9_6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d', 256), 'Attended', 'Tham gia cùng nhóm sinh viên Khoa học Tự nhiên.', 'Mẹ: 0909555666', '2026-09-26 15:00:00', '2026-09-26 15:00:00'),
(8, 5, 10, 'REG-202610-0008', 'ggqr_ev5_u10_7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e', SHA2('ggqr_ev5_u10_7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e', 256), 'Attended', 'Đăng ký tổ trồng cây khu vực 2.', 'Bố: 0909666777', '2026-09-27 10:00:00', '2026-09-27 10:00:00'),

-- Event 1 (Upcoming Blood Donation): 4 Confirmed
(9, 1, 5, 'REG-202611-0009', 'ggqr_ev1_u5_8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f', SHA2('ggqr_ev1_u5_8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f', 256), 'Confirmed', 'Đăng ký hiến máu 350ml và hỗ trợ bàn hướng dẫn.', 'Mẹ: 0909111222', '2026-10-22 08:30:00', '2026-10-22 08:30:00'),
(10, 1, 8, 'REG-202611-0010', 'ggqr_ev1_u8_9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a', SHA2('ggqr_ev1_u8_9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a', 256), 'Confirmed', 'Hiến máu định kỳ đủ điều kiện sau 12 tuần.', 'Anh trai: 0909444555', '2026-10-23 09:15:00', '2026-10-23 09:15:00'),
(11, 1, 9, 'REG-202611-0011', 'ggqr_ev1_u9_0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b', SHA2('ggqr_ev1_u9_0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b', 256), 'Confirmed', 'Đăng ký hiến máu ca sáng.', 'Mẹ: 0909555666', '2026-10-24 14:00:00', '2026-10-24 14:00:00'),
(12, 1, 10, 'REG-202611-0012', 'ggqr_ev1_u10_1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c', SHA2('ggqr_ev1_u10_1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c', 256), 'Confirmed', 'Tham gia hiến máu ca chiều.', 'Bố: 0909666777', '2026-10-25 16:00:00', '2026-10-25 16:00:00'),

-- Event 2 (Upcoming Environment): 3 Confirmed
(13, 2, 5, 'REG-202611-0013', 'ggqr_ev2_u5_2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d', SHA2('ggqr_ev2_u5_2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d', 256), 'Confirmed', 'Tham gia làm sạch bờ kênh Nhiêu Lộc.', 'Mẹ: 0909111222', '2026-10-25 10:00:00', '2026-10-25 10:00:00'),
(14, 2, 6, 'REG-202611-0014', 'ggqr_ev2_u6_3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e', SHA2('ggqr_ev2_u6_3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e', 256), 'Confirmed', 'Phụ trách nhóm thu gom khu vực cầu Công Lý.', 'Bố: 0909222333', '2026-10-25 11:00:00', '2026-10-25 11:00:00'),
(15, 2, 7, 'REG-202611-0015', 'ggqr_ev2_u7_4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f', SHA2('ggqr_ev2_u7_4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f', 256), 'Confirmed', 'Phụ trách phân loại chai nhựa tái chế.', 'Chị gái: 0909333444', '2026-10-26 09:00:00', '2026-10-26 09:00:00'),

-- Event 3 (Upcoming Education): 3 Confirmed
(16, 3, 5, 'REG-202611-0016', 'ggqr_ev3_u5_5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a', SHA2('ggqr_ev3_u5_5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a', 256), 'Confirmed', 'Phụ trách lớp học thực hành khoa học cho học sinh.', 'Mẹ: 0909111222', '2026-10-26 15:00:00', '2026-10-26 15:00:00'),
(17, 3, 7, 'REG-202611-0017', 'ggqr_ev3_u7_6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b', SHA2('ggqr_ev3_u7_6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b', 256), 'Confirmed', 'Hướng dẫn phân loại đầu sách thư viện.', 'Chị gái: 0909333444', '2026-10-27 08:30:00', '2026-10-27 08:30:00'),
(18, 3, 8, 'REG-202611-0018', 'ggqr_ev3_u8_7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2c', SHA2('ggqr_ev3_u8_7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2c', 256), 'Confirmed', 'Hỗ trợ hậu cần vận chuyển kệ sách.', 'Anh trai: 0909444555', '2026-10-27 10:00:00', '2026-10-27 10:00:00'),

-- Event 6 (Upcoming Blood Donation at VNU): 2 Confirmed
(19, 6, 6, 'REG-202612-0019', 'ggqr_ev6_u6_8a9b0c1d2e3f4a5b6c7d8e9f0a1b2c3d', SHA2('ggqr_ev6_u6_8a9b0c1d2e3f4a5b6c7d8e9f0a1b2c3d', 256), 'Confirmed', 'Đăng ký hiến máu ca sáng tại Nhà Điều hành.', 'Bố: 0909222333', '2026-10-28 09:00:00', '2026-10-28 09:00:00'),
(20, 6, 7, 'REG-202612-0020', 'ggqr_ev6_u7_9b0c1d2e3f4a5b6c7d8e9f0a1b2c3d4e', SHA2('ggqr_ev6_u7_9b0c1d2e3f4a5b6c7d8e9f0a1b2c3d4e', 256), 'Confirmed', 'Tình nguyện viên điều phối đón tiếp người hiến máu.', 'Chị gái: 0909333444', '2026-10-28 10:30:00', '2026-10-28 10:30:00'),

-- Event 7 (Full capacity max_volunteers=2, plus 1 Waitlisted):
(21, 7, 6, 'REG-202611-0021', 'ggqr_ev7_u6_0c1d2e3f4a5b6c7d8e9f0a1b2c3d4e5f', SHA2('ggqr_ev7_u6_0c1d2e3f4a5b6c7d8e9f0a1b2c3d4e5f', 256), 'Confirmed', 'Chăm sóc vườn hoa và trò chuyện cùng các cụ.', 'Bố: 0909222333', '2026-10-28 14:00:00', '2026-10-28 14:00:00'),
(22, 7, 7, 'REG-202611-0022', 'ggqr_ev7_u7_1d2e3f4a5b6c7d8e9f0a1b2c3d4e5f6a', SHA2('ggqr_ev7_u7_1d2e3f4a5b6c7d8e9f0a1b2c3d4e5f6a', 256), 'Confirmed', 'Đọc báo và hỗ trợ sinh hoạt tại trung tâm.', 'Chị gái: 0909333444', '2026-10-28 14:30:00', '2026-10-28 14:30:00'),
(23, 7, 9, 'REG-202611-0023', 'ggqr_ev7_u9_2e3f4a5b6c7d8e9f0a1b2c3d4e5f6a7b', SHA2('ggqr_ev7_u9_2e3f4a5b6c7d8e9f0a1b2c3d4e5f6a7b', 256), 'Waitlisted', 'Đăng ký danh sách chờ nếu có bạn bận đột xuất.', 'Mẹ: 0909555666', '2026-10-29 09:00:00', NULL);

-- -----------------------------------------------------------------------------
-- 11. SEED: waitlist
-- -----------------------------------------------------------------------------
INSERT INTO `waitlist` (`id`, `event_id`, `user_id`, `registration_id`, `position_number`, `status`) VALUES
(1, 7, 9, 23, 1, 'Waiting');

-- -----------------------------------------------------------------------------
-- 12. SEED: attendance (For Completed Events 4 & 5)
-- -----------------------------------------------------------------------------
INSERT INTO `attendance` (
    `id`, `registration_id`, `event_id`, `user_id`,
    `check_in_time`, `check_out_time`, `check_in_method`, `check_out_method`,
    `checked_in_by`, `checked_out_by`, `calculated_hours`, `status`, `notes`
) VALUES
(1, 1, 4, 5, '2026-09-12 06:00:00', '2026-09-12 12:00:00', 'QR', 'QR', 2, 2, 6.00, 'Verified', 'Điểm danh đầy đủ bằng QR Code'),
(2, 2, 4, 6, '2026-09-12 06:10:00', '2026-09-12 12:10:00', 'QR', 'QR', 2, 2, 6.00, 'Verified', 'Tham gia đầy đủ các khâu tại bếp'),
(3, 3, 4, 7, '2026-09-12 06:15:00', '2026-09-12 11:45:00', 'QR', 'QR', 2, 2, 5.50, 'Verified', 'Hoàn thành nhiệm vụ phân chia suất ăn'),
(4, 4, 4, 8, '2026-09-12 06:00:00', '2026-09-12 12:00:00', 'QR', 'QR', 2, 2, 6.00, 'Verified', 'Hỗ trợ vận chuyển nguyên liệu'),
(5, 5, 5, 5, '2026-10-04 06:30:00', '2026-10-04 14:00:00', 'QR', 'QR', 4, 4, 7.50, 'Verified', 'Trồng cây rừng ngập mặn Cần Giờ'),
(6, 6, 5, 6, '2026-10-04 06:30:00', '2026-10-04 14:00:00', 'QR', 'QR', 4, 4, 7.50, 'Verified', 'Điều phối nhóm trồng cây khu vực 1'),
(7, 7, 5, 9, '2026-10-04 06:45:00', '2026-10-04 13:45:00', 'QR', 'QR', 4, 4, 7.00, 'Verified', 'Tham gia đầy đủ'),
(8, 8, 5, 10, '2026-10-04 06:45:00', '2026-10-04 13:45:00', 'Manual', 'QR', 4, 4, 7.00, 'Verified', 'Điểm danh thủ công lúc vào do điện thoại hết pin');

-- -----------------------------------------------------------------------------
-- 13. SEED: volunteer_hours
-- -----------------------------------------------------------------------------
INSERT INTO `volunteer_hours` (
    `id`, `user_id`, `event_id`, `registration_id`, `attendance_id`,
    `hours`, `status`, `approved_by`, `approved_at`, `created_at`
) VALUES
(1, 5, 4, 1, 1, 6.00, 'Approved', 2, '2026-09-13 09:00:00', '2026-09-12 12:05:00'),
(2, 6, 4, 2, 2, 6.00, 'Approved', 2, '2026-09-13 09:05:00', '2026-09-12 12:15:00'),
(3, 7, 4, 3, 3, 5.50, 'Approved', 2, '2026-09-13 09:10:00', '2026-09-12 11:50:00'),
(4, 8, 4, 4, 4, 6.00, 'Approved', 2, '2026-09-13 09:15:00', '2026-09-12 12:05:00'),
(5, 5, 5, 5, 5, 7.50, 'Approved', 4, '2026-10-05 10:00:00', '2026-10-04 14:10:00'),
(6, 6, 5, 6, 6, 7.50, 'Approved', 4, '2026-10-05 10:05:00', '2026-10-04 14:10:00'),
(7, 9, 5, 7, 7, 7.00, 'Approved', 4, '2026-10-05 10:10:00', '2026-10-04 13:55:00'),
(8, 10, 5, 8, 8, 7.00, 'Approved', 4, '2026-10-05 10:15:00', '2026-10-04 13:55:00');

-- -----------------------------------------------------------------------------
-- 14. SEED: certificates
-- -----------------------------------------------------------------------------
INSERT INTO `certificates` (
    `id`, `certificate_code`, `verification_code`, `user_id`, `event_id`, `organization_id`,
    `registration_id`, `volunteer_hour_id`, `recipient_name`, `event_title`, `organization_name`,
    `verified_hours`, `issued_date`, `issued_by`, `pdf_path`, `status`
) VALUES
(1, 'GG-CERT-2026-0001', 'GGVERIFY20260001MINHANH4', 5, 4, 1, 1, 1, 'Nguyễn Ngọc Minh Anh', 'Bếp ăn thiện nguyện – Chuẩn bị 500 suất cơm trưa cho bệnh nhân nghèo', 'CLB Sinh Viên Tình Nguyện ĐHQG TP.HCM', 6.00, '2026-09-14', 2, NULL, 'Valid'),
(2, 'GG-CERT-2026-0002', 'GGVERIFY20260002HOANGNAM4', 6, 4, 1, 2, 2, 'Võ Hoàng Nam', 'Bếp ăn thiện nguyện – Chuẩn bị 500 suất cơm trưa cho bệnh nhân nghèo', 'CLB Sinh Viên Tình Nguyện ĐHQG TP.HCM', 6.00, '2026-09-14', 2, NULL, 'Valid'),
(3, 'GG-CERT-2026-0003', 'GGVERIFY20260003THUHA4', 7, 4, 1, 3, 3, 'Đặng Thu Hà', 'Bếp ăn thiện nguyện – Chuẩn bị 500 suất cơm trưa cho bệnh nhân nghèo', 'CLB Sinh Viên Tình Nguyện ĐHQG TP.HCM', 5.50, '2026-09-14', 2, NULL, 'Valid'),
(4, 'GG-CERT-2026-0004', 'GGVERIFY20260004QUOCBAO4', 8, 4, 1, 4, 4, 'Phan Quốc Bảo', 'Bếp ăn thiện nguyện – Chuẩn bị 500 suất cơm trưa cho bệnh nhân nghèo', 'CLB Sinh Viên Tình Nguyện ĐHQG TP.HCM', 6.00, '2026-09-14', 2, NULL, 'Valid'),
(5, 'GG-CERT-2026-0005', 'GGVERIFY20260005MINHANH5', 5, 5, 3, 5, 5, 'Nguyễn Ngọc Minh Anh', 'Ngày hội trồng 1.000 cây đước bảo vệ rừng ngập mặn Cần Giờ', 'Mạng Lưới Hành Động Môi Trường Sài Gòn Xanh', 7.50, '2026-10-06', 4, NULL, 'Valid'),
(6, 'GG-CERT-2026-0006', 'GGVERIFY20260006HOANGNAM5', 6, 5, 3, 6, 6, 'Võ Hoàng Nam', 'Ngày hội trồng 1.000 cây đước bảo vệ rừng ngập mặn Cần Giờ', 'Mạng Lưới Hành Động Môi Trường Sài Gòn Xanh', 7.50, '2026-10-06', 4, NULL, 'Valid'),
(7, 'GG-CERT-2026-0007', 'GGVERIFY20260007KHANHLINH5', 9, 5, 3, 7, 7, 'Bùi Khánh Linh', 'Ngày hội trồng 1.000 cây đước bảo vệ rừng ngập mặn Cần Giờ', 'Mạng Lưới Hành Động Môi Trường Sài Gòn Xanh', 7.00, '2026-10-06', 4, NULL, 'Valid');

-- -----------------------------------------------------------------------------
-- 15. SEED: notifications
-- -----------------------------------------------------------------------------
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `link_url`, `is_read`) VALUES
(1, 5, 'Đã cấp chứng nhận tình nguyện', 'Chứng nhận 7.5 giờ từ chương trình Ngày hội trồng 1.000 cây đước bảo vệ rừng ngập mặn Cần Giờ đã sẵn sàng để tải xuống.', 'Certificate', '/certificate/verify/GGVERIFY20260005MINHANH5', 0),
(2, 5, 'Xác nhận đăng ký tham gia sự kiện', 'Bạn đã đăng ký tham gia Ngày hội hiến máu Giọt Hồng Sẻ Chia – Đợt tháng 11/2026. Mã QR điểm danh đã được tạo trong trang cá nhân.', 'Registration', '/volunteer/dashboard', 0),
(3, 6, 'Đã xác nhận giờ tình nguyện', 'Ban tổ chức Sài Gòn Xanh đã xác nhận 7.5 giờ tình nguyện của bạn.', 'Hours', '/volunteer/dashboard', 1),
(4, 9, 'Ghi nhận vào Danh sách chờ (Waitlist)', 'Sự kiện Chăm sóc và trò chuyện cùng người cao tuổi tại Trung tâm Dưỡng lão Thị Nghè hiện đã đủ chỗ. Hệ thống sẽ tự động chuyển bạn sang Xác nhận khi có người hủy.', 'Registration', '/volunteer/dashboard', 0),
(5, 1, 'Sự kiện mới chờ phê duyệt', 'Tổ chức Sài Gòn Xanh vừa gửi sự kiện "Tập huấn kỹ năng sơ cấp cứu ban đầu cho đội ngũ tình nguyện viên cộng đồng" chờ Admin duyệt.', 'Event', '/admin/dashboard?tab=events', 0);

-- -----------------------------------------------------------------------------
-- 16. SEED: reviews
-- -----------------------------------------------------------------------------
INSERT INTO `reviews` (`id`, `event_id`, `organization_id`, `user_id`, `registration_id`, `rating`, `comment`, `status`) VALUES
(1, 4, 1, 5, 1, 5, 'Ban tổ chức phân công công việc rất rõ ràng ngay từ 6h sáng. Khu vực sơ chế sạch sẽ, hộp đựng cơm dùng bã mía thân thiện môi trường, cô chú bệnh nhân nhận cơm rất vui.', 'Visible'),
(2, 4, 1, 6, 2, 5, 'Khâu điểm danh bằng mã QR lúc đến và lúc về rất nhanh, không phải chờ ký tên trên danh sách giấy như các đợt trước.', 'Visible'),
(3, 5, 3, 5, 5, 5, 'Chuyến đi Cần Giờ được chuẩn bị chu đáo từ xe đưa đón, ủng lội bùn đến hướng dẫn kỹ thuật cắm cọc giữ cây đước con của các chú kiểm lâm.', 'Visible'),
(4, 5, 3, 9, 7, 4, 'Chương trình thực tế và ý nghĩa, mong CLB tổ chức thêm các đợt chăm sóc rừng định kỳ hàng quý.', 'Visible');

-- -----------------------------------------------------------------------------
-- 17. SEED: reports
-- -----------------------------------------------------------------------------
INSERT INTO `reports` (`id`, `reporter_id`, `target_type`, `target_id`, `reason`, `details`, `status`, `resolved_by`, `resolution_note`, `resolved_at`) VALUES
(1, 7, 'Event', 2, 'Cần bổ sung rõ vị trí gửi xe máy cho tình nguyện viên', 'Khu vực cầu Công Lý buổi sáng khá đông phương tiện, mong BTC ghi rõ địa chỉ bãi giữ xe trong phần hướng dẫn.', 'Resolved', 1, 'Đã liên hệ BTC Sài Gòn Xanh cập nhật vị trí gửi xe tại cổng Công viên bờ kênh.', '2026-10-25 16:00:00');

-- -----------------------------------------------------------------------------
-- 18. SEED: audit_logs
-- -----------------------------------------------------------------------------
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`) VALUES
(1, 1, 'VERIFY_ORGANIZATION', 'Organization', 1, '{"verification_status":"Pending"}', '{"verification_status":"Verified"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(2, 1, 'APPROVE_EVENT', 'Event', 1, '{"status":"Pending"}', '{"status":"Published"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(3, 4, 'MANUAL_CHECKIN', 'Attendance', 8, NULL, '{"registration_id":8,"method":"Manual","note":"Điểm danh thủ công lúc vào do điện thoại hết pin"}', '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)'),
(4, 2, 'ISSUE_CERTIFICATE', 'Certificate', 1, NULL, '{"certificate_code":"GG-CERT-2026-0001","hours":6.0}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

-- -----------------------------------------------------------------------------
-- 19. SEED: blood_donation_appointments
-- -----------------------------------------------------------------------------
INSERT INTO `blood_donation_appointments` (
    `id`, `event_id`, `user_id`, `registration_id`, `appointment_code`,
    `time_slot`, `blood_group_declared`, `donation_volume_ml`,
    `medical_unit_name`, `status`, `medical_staff_confirmed_by`, `medical_confirmation_note`
) VALUES
(1, 1, 5, 9, 'BLD-202611-0001', 'Ca 1: 07:30 - 09:00', 'O+', 350, 'Bệnh viện Truyền máu Huyết học TP.HCM', 'Scheduled', NULL, 'Đã đặt lịch hẹn khung giờ sáng sớm'),
(2, 1, 8, 10, 'BLD-202611-0002', 'Ca 2: 09:00 - 10:30', 'AB+', 350, 'Bệnh viện Truyền máu Huyết học TP.HCM', 'Scheduled', NULL, 'Người hiến máu nhắc lại'),
(3, 1, 9, 11, 'BLD-202611-0003', 'Ca 1: 07:30 - 09:00', 'O-', 250, 'Bệnh viện Truyền máu Huyết học TP.HCM', 'Scheduled', NULL, 'Nhóm máu Rh-'),
(4, 1, 10, 12, 'BLD-202611-0004', 'Ca 3: 13:30 - 15:00', 'A+', 350, 'Bệnh viện Truyền máu Huyết học TP.HCM', 'Scheduled', NULL, 'Đăng ký khung giờ buổi chiều'),
(5, 6, 6, 19, 'BLD-202612-0005', 'Ca 1: 07:30 - 09:00', 'A+', 350, 'Trung tâm Hiến Máu Nhân Đạo TP.HCM', 'Scheduled', NULL, 'Đăng ký tại điểm Nhà Điều hành ĐHQG');

-- -----------------------------------------------------------------------------
-- 20. SEED: system_settings
-- -----------------------------------------------------------------------------
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `description`, `updated_by`) VALUES
(1, 'site_name', 'Góp Gió Volunteer', 'general', 'Tên hiển thị của nền tảng', 1),
(2, 'site_slogan', 'Một hành động nhỏ – Ngàn giá trị lớn.', 'general', 'Khẩu hiệu của dự án', 1),
(3, 'support_email', 'hotro@gopgio.vn', 'contact', 'Email hỗ trợ tình nguyện viên và tổ chức', 1),
(4, 'support_hotline', '1900 6868', 'contact', 'Số điện thoại liên hệ điều phối', 1),
(5, 'cancel_deadline_hours', '12', 'policy', 'Số giờ tối thiểu trước khi sự kiện diễn ra được phép hủy đăng ký', 1),
(6, 'max_hours_per_event', '12', 'policy', 'Số giờ tình nguyện tối đa cho một sự kiện trong ngày', 1),
(7, 'auto_issue_certificate', '1', 'certificate', 'Tự động cấp chứng nhận ngay khi Ban tổ chức duyệt giờ tình nguyện', 1),
(8, 'blood_donation_disclaimer', 'Nền tảng Góp Gió Volunteer chỉ hỗ trợ đặt lịch hẹn và điều phối tình nguyện viên. Việc khám sàng lọc, xét nghiệm và kết luận đủ điều kiện hiến máu hoàn toàn do bác sĩ và nhân viên y tế chuyên môn thực hiện tại điểm hiến máu.', 'medical', 'Lưu ý y tế bắt buộc đối với sự kiện hiến máu', 1);

SET FOREIGN_KEY_CHECKS = 1;
