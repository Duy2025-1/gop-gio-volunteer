# GÓP GIÓ VOLUNTEER

**Hệ thống Quản lý Hoạt động Tình nguyện & Hiến máu Nhân đạo**  
*Thông điệp: "Một hành động nhỏ – Ngàn giá trị lớn."*

---

## 1. Giới thiệu đề tài

**Góp Gió Volunteer** là nền tảng web kết nối sinh viên, tình nguyện viên với các câu lạc bộ thiện nguyện, tổ chức xã hội và các điểm tiếp nhận hiến máu nhân đạo. Dự án được phát triển phục vụ học phần **Lập trình Web** theo kiến trúc **MVC thuần hướng đối tượng (PHP 8.2+ OOP)**, sử dụng **PDO Prepared Statements** kết nối hệ quản trị cơ sở dữ liệu **MySQL 8.0** và đóng gói môi trường bằng **Docker Compose**.

### Các chức năng cốt lõi đã hoàn thành (Giai đoạn Tuần 1 – Tuần 3)
- **Kiến trúc Core MVC & Bảo mật nền tảng:** Tự xây dựng bộ định tuyến `Router`, `Controller`, `BaseModel`, quản lý phiên `Session`, kiểm tra dữ liệu `Validator`, chống giả mạo truy vấn `CSRF Token` và ghi nhật ký kiểm toán `AuditLog`.
- **Xác thực & Phân quyền RBAC 4 vai trò:** Phân tách quyền hạn rõ ràng giữa Khách (`guest`), Tình nguyện viên (`volunteer`), Đơn vị tổ chức (`organizer`) và Quản trị viên (`admin`); mật khẩu băm chuẩn `Bcrypt` kèm cơ chế khóa tạm thời khi đăng nhập sai nhiều lần.
- **Khám phá Sự kiện, Hiến máu & Bảng xếp hạng:** Tìm kiếm, lọc đa tiêu chí (theo danh mục, khu vực, loại hình thiện nguyện/hiến máu, trạng thái), xem chi tiết chiến dịch, hồ sơ đơn vị tổ chức và bảng vinh danh tình nguyện viên tiêu biểu.
- **Đăng ký tham gia & Hàng chờ tự động (Waitlist):** Kiểm tra chống trùng lịch thời gian thực; tự động xếp vào danh sách chờ (`Waitlist`) khi sự kiện đủ chỉ tiêu và tự động đôn người chờ #1 lên chính thức (`Confirmed`) ngay khi có tình nguyện viên hủy đăng ký.
- **Điểm danh QR Code bằng Camera:** Quét mã QR trực tiếp qua trình duyệt bằng `html5-qrcode` (hoặc nhập mã dự phòng), chống quét trùng lặp và tự động tính toán thời lượng tham gia `(Check-out - Check-in) / 3600` trong `PDO Transaction`.
- **Thông báo & Đánh giá sự kiện:** Hệ thống thông báo nội bộ theo thời gian thực (AJAX Polling) và đánh giá sao/nhận xét dành cho tình nguyện viên đã hoàn thành điểm danh.
- **Bảng điều khiển Đơn vị tổ chức (Organizer Dashboard):** Quản lý danh sách chiến dịch, khởi tạo sự kiện mới, theo dõi danh sách tình nguyện viên, xác nhận giờ tham gia và xuất báo cáo danh sách ra file Excel/CSV chuẩn tiếng Việt (UTF-8 BOM).

---

## 2. Thành viên nhóm & Phân công phụ trách

Nhóm gồm **04 thành viên** làm việc theo quy trình **Feature Branch Workflow** trên Git. Mỗi thành viên phát triển tính năng trên nhánh riêng (`feat/...`), kiểm thử, tối ưu mã nguồn (`refactor`) và gộp vào nhánh chính `main`.

| STT | Thành viên | Tài khoản GitHub | Email Git | Phụ trách chính |
| :---: | :--- | :--- | :--- | :--- |
| **1** | **Cao Mạnh Duy** *(Nhóm trưởng)* | `Duy2025-1` | `duyduy7102005@gmail.com` | Core MVC OOP, PDO Database, Router, Bảo mật CSRF, AuditLog, Định tuyến hệ thống & Điểm danh QR Code bằng Camera |
| **2** | **Nguyễn Quốc Hậu** | `NguyenQuocHau2531` | `nguyenquochauqk@gmail.com` | Thiết kế CSDL MySQL 20 bảng (3NF), Dữ liệu mẫu, BaseModel, Xác thực & Phân quyền RBAC 4 vai trò, Thông báo & Đánh giá sao |
| **3** | **Phan Thơm Mỹ** | `phanthommy26-cell` | `phanthommy26@gmail.com` | Model & Repository Sự kiện/Đơn vị tổ chức, Bộ lọc đa tiêu chí, Nghiệp vụ Đăng ký tham gia, Chống trùng lịch & Hàng chờ Waitlist tự động |
| **4** | **Đào Phát** | `Sakana0107-phat` | `phatdao0107@gmail.com` | Hệ thống Design System UI/UX, Layout dùng chung, Trang chủ, Chuyên trang Hiến máu, Bảng xếp hạng & Bảng điều khiển Đơn vị tổ chức |

---

## 3. Tiến độ thực hiện trong 3 tuần qua trên Git

### Tuần 1 (`25/10/2026 – 31/10/2026`): Khởi tạo Kiến trúc MVC, Cơ sở dữ liệu & Giao diện nền tảng
| Thành viên | Nhánh (Branch) | Nội dung công việc & Các file thực hiện |
| :--- | :--- | :--- |
| **TV1 (`Duy2025-1`)** | `feat/core-mvc` | Khởi tạo cấu trúc dự án, môi trường Docker (`Dockerfile`, `docker-compose.yml`, `composer.json`) và xây dựng bộ khung Core MVC OOP (`app/Core/Database.php`, `Router.php`, `Controller.php`, `Session.php`, `Csrf.php`, `Validator.php`, `Logger.php`, `public/index.php`). |
| **TV2 (`NguyenQuocHau2531`)** | `feat/database-schema` | Thiết kế CSDL MySQL 8.0 gồm 20 bảng chuẩn hóa 3NF (`database/schema.sql`), xây dựng lớp `app/Models/BaseModel.php` và biên soạn dữ liệu mẫu cho 4 vai trò (`database/seed.sql`). |
| **TV3 (`phanthommy26-cell`)** | `feat/event-models` | Xây dựng Model quản lý Đơn vị tổ chức (`app/Models/Organization.php`), Sự kiện (`app/Models/Event.php`) và tầng truy vấn `app/Repositories/EventRepository.php` hỗ trợ tìm kiếm, lọc đa tiêu chí và phân trang. |
| **TV4 (`Sakana0107-phat`)** | `feat/ui-foundation` | Thiết kế hệ thống giao diện `public/assets/css/style.css` (font *Be Vietnam Pro*), khung Layout dùng chung Header/Footer (`app/Views/layouts/main.php`, `404.php`), hiệu ứng tương tác `public/assets/js/app.js` và bộ ảnh hoạt động thực tế. |

### Tuần 2 (`01/11/2026 – 07/11/2026`): Xác thực RBAC, Module Sự kiện, Chuyên trang Công khai & Định tuyến
| Thành viên | Nhánh (Branch) | Nội dung công việc & Các file thực hiện |
| :--- | :--- | :--- |
| **TV2 (`NguyenQuocHau2531`)** | `feat/auth-rbac` | Xây dựng phân hệ Xác thực & Phân quyền 4 vai trò (`app/Models/User.php`, `app/Middleware/AuthMiddleware.php`, `app/Services/AuthService.php`, `app/Controllers/AuthController.php`) và giao diện Đăng nhập, Đăng ký, Quên mật khẩu (`app/Views/auth/*`). |
| **TV3 (`phanthommy26-cell`)** | `feat/events-organizations` | Xây dựng `app/Services/EventService.php`, `app/Controllers/EventController.php`, `app/Controllers/OrganizationController.php` và hoàn thiện giao diện danh sách/chi tiết sự kiện (`app/Views/events/*`) cùng hồ sơ đơn vị tổ chức (`app/Views/organizations/*`). |
| **TV4 (`Sakana0107-phat`)** | `feat/public-pages` | Xây dựng `app/Controllers/HomeController.php`, giao diện Trang chủ (`app/Views/home/index.php`), trang Giới thiệu (`app/Views/home/about.php`), chuyên trang Lịch Hiến máu nhân đạo (`app/Views/blood/index.php`) và Bảng xếp hạng công khai (`app/Views/leaderboard/index.php`). |
| **TV1 (`Duy2025-1`)** | `feat/audit-routing` | Xây dựng `app/Models/AuditLog.php` ghi nhận nhật ký thao tác quan trọng (kèm IP, User-Agent) và cấu hình bảng định tuyến trung tâm `routes/web.php` kết nối các Controller với Middleware. |

### Tuần 3 (`08/11/2026 – 14/11/2026`): Đăng ký & Hàng chờ Waitlist, Điểm danh QR Camera, Thông báo & Dashboard Tổ chức
| Thành viên | Nhánh (Branch) | Nội dung công việc & Các file thực hiện |
| :--- | :--- | :--- |
| **TV3 (`phanthommy26-cell`)** | `feat/registration-waitlist` | Xây dựng `app/Models/Registration.php`, `app/Repositories/RegistrationRepository.php`, `app/Services/RegistrationService.php`, `app/Controllers/RegistrationController.php`; tối ưu thuật toán kiểm tra trùng lịch, sinh mã QR Token và tự động đôn danh sách chờ `Waitlist`. |
| **TV1 (`Duy2025-1`)** | `feat/qr-attendance` | Xây dựng `app/Models/Attendance.php`, `app/Services/AttendanceService.php`, `app/Controllers/AttendanceController.php` và giao diện quét mã QR bằng Camera `app/Views/attendance/scanner.php`; tối ưu chống quét trùng lặp và tự động tính giờ tham gia. |
| **TV2 (`NguyenQuocHau2531`)** | `feat/notifications-reviews` | Xây dựng hệ thống thông báo người dùng (`app/Models/Notification.php`, `app/Controllers/NotificationController.php`), tối ưu đánh dấu đã đọc hàng loạt và xây dựng `app/Models/Review.php` hỗ trợ đánh giá sao & nhận xét sự kiện. |
| **TV4 (`Sakana0107-phat`)** | `feat/organizer-dashboard` | Xây dựng `app/Controllers/OrganizerController.php` và giao diện Bảng điều khiển Đơn vị tổ chức `app/Views/organizer/dashboard.php` (quản lý chiến dịch, duyệt tình nguyện viên, xác nhận giờ tham gia và xuất danh sách CSV). |

---

## 4. Công nghệ sử dụng & Cấu trúc thư mục

| Phân lớp | Công nghệ & Thư viện |
| :--- | :--- |
| **Frontend** | HTML5, CSS3, JavaScript ES6+, Bootstrap 5.3, Fetch API / AJAX, `html5-qrcode`, Google Fonts (*Be Vietnam Pro*) |
| **Backend** | PHP 8.2+ thuần theo mô hình **MVC + Service + Repository + Middleware**, PDO Prepared Statements |
| **Cơ sở dữ liệu** | MySQL 8.0 (20 bảng InnoDB chuẩn 3NF, Foreign Keys, Unique Constraints, Indexes, Transactions) |
| **Môi trường chạy** | Docker & Docker Compose (Nginx 1.25, PHP 8.2-FPM, MySQL 8.0, phpMyAdmin) |

```text
gop-gio/
├── app/
│   ├── Controllers/
│   │   ├── AttendanceController.php
│   │   ├── AuthController.php
│   │   ├── EventController.php
│   │   ├── HomeController.php
│   │   ├── NotificationController.php
│   │   ├── OrganizationController.php
│   │   ├── OrganizerController.php
│   │   └── RegistrationController.php
│   ├── Core/
│   │   ├── Controller.php
│   │   ├── Csrf.php
│   │   ├── Database.php
│   │   ├── Logger.php
│   │   ├── Router.php
│   │   ├── Session.php
│   │   └── Validator.php
│   ├── Helpers/
│   │   └── functions.php
│   ├── Middleware/
│   │   └── AuthMiddleware.php
│   ├── Models/
│   │   ├── Attendance.php
│   │   ├── AuditLog.php
│   │   ├── BaseModel.php
│   │   ├── Event.php
│   │   ├── Notification.php
│   │   ├── Organization.php
│   │   ├── Registration.php
│   │   ├── Review.php
│   │   └── User.php
│   ├── Repositories/
│   │   ├── EventRepository.php
│   │   └── RegistrationRepository.php
│   ├── Services/
│   │   ├── AttendanceService.php
│   │   ├── AuthService.php
│   │   ├── EventService.php
│   │   └── RegistrationService.php
│   └── Views/
│       ├── attendance/
│       ├── auth/
│       ├── blood/
│       ├── events/
│       ├── home/
│       ├── layouts/
│       ├── leaderboard/
│       ├── organizations/
│       └── organizer/
├── config/
│   └── config.php
├── database/
│   ├── schema.sql
│   └── seed.sql
├── docker/
│   ├── nginx/default.conf
│   └── php/php.ini
├── public/
│   ├── assets/
│   │   ├── css/style.css
│   │   ├── images/
│   │   └── js/app.js
│   ├── uploads/
│   └── index.php
├── routes/
│   └── web.php
├── storage/
├── .env.example
├── composer.json
├── Dockerfile
├── docker-compose.yml
└── README.md
```

---

## 5. Hướng dẫn cài đặt và khởi chạy

### Cách 1: Khởi chạy bằng Docker Compose (Khuyên dùng)
Hệ thống tự động khởi tạo các container (`gopgio_nginx`, `gopgio_app`, `gopgio_mysql`) và tự động nạp cấu trúc 20 bảng `database/schema.sql` cùng dữ liệu mẫu `database/seed.sql`.

```bash
# 1. Khởi chạy hệ thống bằng Docker Compose
docker compose up -d

# 2. Khi muốn dừng hệ thống
docker compose down
```
- **Địa chỉ truy cập Website:** http://localhost:8080

### Cách 2: Khởi chạy nhanh bằng PHP Built-in Server
```bash
composer install
php -S localhost:8080 -t public public/index.php
```

---

## 6. Danh sách tài khoản kiểm thử (Seed Data)

Hệ thống đã nạp sẵn dữ liệu mẫu trong `database/seed.sql` cho các nhóm quyền:

| Vai trò | Họ tên / Đơn vị | Email đăng nhập | Mật khẩu |
| :--- | :--- | :--- | :--- |
| **Quản trị viên (Admin)** | Trần Quản Trị Hệ Thống | `admin@gopgio.vn` | `Admin@123456` |
| **Đơn vị tổ chức 1** | CLB Sinh Viên Tình Nguyện ĐHQG TP.HCM | `org.dhqg@gopgio.vn` | `Org@123456` |
| **Đơn vị tổ chức 2** | Trung Tâm Kết Nối Hiến Máu Nhân Đạo Sài Gòn | `org.hienmau@gopgio.vn` | `Org@123456` |
| **Đơn vị tổ chức 3** | Mạng Lưới Hành Động Môi Trường Sài Gòn Xanh | `org.xanh@gopgio.vn` | `Org@123456` |
| **Tình nguyện viên 1** | Nguyễn Ngọc Minh Anh | `minhanh.tnv@gopgio.vn` | `Volunteer@123` |
| **Tình nguyện viên 2** | Võ Hoàng Nam | `hoangnam.tnv@gopgio.vn` | `Volunteer@123` |
| **Tình nguyện viên 3** | Đặng Thu Hà | `thuha.tnv@gopgio.vn` | `Volunteer@123` |
| **Tình nguyện viên 4** | Phan Quốc Bảo | `quocbao.tnv@gopgio.vn` | `Volunteer@123` |
| **Tình nguyện viên 5** | Bùi Khánh Linh *(Đang ở danh sách chờ Waitlist)* | `khanhlinh.tnv@gopgio.vn` | `Volunteer@123` |
| **Tình nguyện viên 6** | Hoàng Đức Trọng | `ductrong.tnv@gopgio.vn` | `Volunteer@123` |
