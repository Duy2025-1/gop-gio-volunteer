# GÓP GIÓ VOLUNTEER

**Hệ thống Quản lý Hoạt động Tình nguyện & Hiến máu Nhân đạo tích hợp Trợ lý Gió AI**  
*Thông điệp: "Một hành động nhỏ – Ngàn giá trị lớn."*

---

## 1. Giới thiệu đề tài

**Góp Gió Volunteer** là nền tảng web kết nối sinh viên, tình nguyện viên với các câu lạc bộ thiện nguyện, tổ chức xã hội và các điểm tiếp nhận hiến máu nhân đạo. Dự án được phát triển phục vụ học phần **Lập trình Web** theo kiến trúc **MVC thuần hướng đối tượng (PHP 8.2+ OOP)**, sử dụng **PDO Prepared Statements** kết nối hệ quản trị cơ sở dữ liệu **MySQL 8.0**, tích hợp trợ lý ảo **Gió AI (Google Gemini API)** và đóng gói môi trường bằng **Docker Compose**.

### Các chức năng chính của hệ thống
- **Kiến trúc Core MVC & Bảo mật nền tảng:** Tự xây dựng bộ định tuyến `Router`, `Controller`, `BaseModel`, quản lý phiên `Session`, kiểm tra dữ liệu `Validator`, chống giả mạo truy vấn `CSRF Token` và ghi nhật ký kiểm toán `AuditLog`.
- **Xác thực & Phân quyền RBAC 4 vai trò:** Phân tách quyền hạn chặt chẽ giữa Khách (`guest`), Tình nguyện viên (`volunteer`), Đơn vị tổ chức (`organizer`) và Quản trị viên (`admin`); mật khẩu băm chuẩn `Bcrypt` kèm cơ chế khóa tạm thời khi đăng nhập sai nhiều lần.
- **Khám phá Sự kiện, Hiến máu & Bảng xếp hạng:** Tìm kiếm, lọc đa tiêu chí (theo danh mục, khu vực, loại hình thiện nguyện/hiến máu, trạng thái), xem chi tiết chiến dịch, hồ sơ đơn vị tổ chức và bảng vinh danh tình nguyện viên tiêu biểu.
- **Đăng ký tham gia & Hàng chờ tự động (Waitlist):** Kiểm tra chống đăng ký trùng lặp; tự động xếp vào danh sách chờ (`Waitlist`) khi sự kiện đủ chỉ tiêu và tự động đôn người chờ #1 lên chính thức (`Confirmed`) trong `PDO Transaction` ngay khi có tình nguyện viên hủy đăng ký.
- **Điểm danh QR Code bằng Camera:** Quét mã QR trực tiếp qua trình duyệt bằng `html5-qrcode` (hoặc nhập mã dự phòng), chống quét trùng lặp và tự động tính toán thời lượng tham gia `(Check-out - Check-in) / 3600`.
- **Cấp Chứng nhận PDF tiếng Việt & Xác minh QR công khai:** Tự động tạo file chứng nhận PDF chuẩn Unicode (`Dompdf`) kèm mã QR (`Endroid QR Code`) và mã tra cứu công khai tại `/certificate/verify/{verification_code}`.
- **Hệ thống Dashboard chuyên biệt theo vai trò:**
  - **Dashboard Tình nguyện viên (`/volunteer/dashboard`):** Theo dõi tổng giờ đã duyệt, biểu đồ đóng góp, lấy mã QR điểm danh cá nhân, tải chứng nhận PDF và đánh giá sự kiện.
  - **Dashboard Đơn vị tổ chức (`/organizer/dashboard`):** Tạo và quản lý chiến dịch, duyệt tình nguyện viên, quét QR điểm danh, xác nhận giờ tham gia, cấp chứng nhận và xuất danh sách CSV (UTF-8 BOM).
  - **Trung tâm Quản trị viên (`/admin/dashboard`):** Kiểm duyệt sự kiện, xác minh tổ chức, quản lý người dùng, điều chỉnh giờ, thu hồi chứng nhận, xem 6 biểu đồ thống kê (`Chart.js`) và tra cứu `Audit Logs`.
- **Trợ lý thông minh Gió AI – Trợ lý tình nguyện viên:** Chatbot AI tích hợp trực tiếp ở góc dưới bên phải màn hình, kết nối **Google Gemini API** và dữ liệu thực tế từ MySQL để tư vấn sự kiện, lịch hiến máu, hướng dẫn nghiệp vụ và tra cứu hồ sơ cá nhân theo quyền đăng nhập.

---

## 2. Thành viên nhóm & Phân công phụ trách

Nhóm gồm **04 thành viên** làm việc theo quy trình **Feature Branch Workflow** trên Git trong 4 tuần. Mỗi thành viên phát triển tính năng trên nhánh riêng (`feat/...`), kiểm thử, tối ưu mã nguồn (`refactor`) và gộp vào nhánh chính `main`.

| STT | Thành viên | Tài khoản GitHub | Email Git | Phụ trách chính |
| :---: | :--- | :--- | :--- | :--- |
| **1** | **Cao Mạnh Duy** *(Nhóm trưởng)* | `Duy2025-1` | `duyduy7102005@gmail.com` | Core MVC OOP, PDO Database, Router, Bảo mật CSRF, AuditLog, Định tuyến, Điểm danh QR Camera, Chứng nhận PDF & Tích hợp Gió AI Chatbot |
| **2** | **Nguyễn Quốc Hậu** | `NguyenQuocHau2531` | `nguyenquochauqk@gmail.com` | Thiết kế CSDL MySQL 20 bảng (3NF), Dữ liệu mẫu, BaseModel, Xác thực & Phân quyền RBAC 4 vai trò, Thông báo, Đánh giá sao & Trung tâm Quản trị Admin |
| **3** | **Phan Thơm Mỹ** | `phanthommy26-cell` | `phanthommy26@gmail.com` | Model & Repository Sự kiện/Đơn vị tổ chức, Bộ lọc đa tiêu chí, Nghiệp vụ Đăng ký tham gia, Hàng chờ Waitlist tự động & Bộ kiểm thử tự động (Automated Tests) |
| **4** | **Đào Phát** | `Sakana0107-phat` | `phatdao0107@gmail.com` | Hệ thống Design System UI/UX, Layout dùng chung, Trang chủ, Chuyên trang Hiến máu, Bảng xếp hạng, Dashboard Đơn vị tổ chức & Dashboard Tình nguyện viên |

---

## 3. Lộ trình phát triển 4 tuần trên Git

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
| **TV3 (`phanthommy26-cell`)** | `feat/registration-waitlist` | Xây dựng `app/Models/Registration.php`, `app/Repositories/RegistrationRepository.php`, `app/Services/RegistrationService.php`, `app/Controllers/RegistrationController.php`; tối ưu kiểm tra trùng lịch, sinh mã QR Token và tự động đôn danh sách chờ `Waitlist`. |
| **TV1 (`Duy2025-1`)** | `feat/qr-attendance` | Xây dựng `app/Models/Attendance.php`, `app/Services/AttendanceService.php`, `app/Controllers/AttendanceController.php` và giao diện quét mã QR bằng Camera `app/Views/attendance/scanner.php`; tối ưu chống quét trùng lặp và tự động tính giờ tham gia. |
| **TV2 (`NguyenQuocHau2531`)** | `feat/notifications-reviews` | Xây dựng hệ thống thông báo người dùng (`app/Models/Notification.php`, `app/Controllers/NotificationController.php`), tối ưu đánh dấu đã đọc hàng loạt và xây dựng `app/Models/Review.php` hỗ trợ đánh giá sao & nhận xét sự kiện. |
| **TV4 (`Sakana0107-phat`)** | `feat/organizer-dashboard` | Xây dựng `app/Controllers/OrganizerController.php` và giao diện Bảng điều khiển Đơn vị tổ chức `app/Views/organizer/dashboard.php` (quản lý chiến dịch, duyệt tình nguyện viên, xác nhận giờ tham gia và xuất danh sách CSV). |

### Tuần 4 (`15/11/2026 – 21/11/2026`): Chứng nhận PDF, Dashboard Tình nguyện viên, Trung tâm Admin, Kiểm thử & Gió AI Chatbot
| Thành viên | Nhánh (Branch) | Nội dung công việc & Các file thực hiện |
| :--- | :--- | :--- |
| **TV1 (`Duy2025-1`)** | `feat/pdf-certificates` & `feat/gio-ai-chatbot` | Xây dựng phân hệ cấp Chứng nhận PDF tiếng Việt & Xác minh QR (`app/Models/Certificate.php`, `app/Services/CertificateService.php`, `app/Controllers/CertificateController.php`, `app/Views/certificates/verify.php`); tích hợp hệ thống **Gió AI – Trợ lý tình nguyện viên** (`AiContextService.php`, `AiPromptBuilder.php`, `AiChatService.php`, `ChatController.php`, `chatbot.php`, `chatbot.css`, `chatbot.js`). |
| **TV4 (`Sakana0107-phat`)** | `feat/volunteer-dashboard-stats` | Xây dựng `app/Services/StatisticsService.php`, `app/Controllers/VolunteerController.php` và giao diện Bảng điều khiển Tình nguyện viên `app/Views/volunteer/dashboard.php` (thống kê giờ tình nguyện, thẻ QR cá nhân, danh sách chứng nhận). |
| **TV2 (`NguyenQuocHau2531`)** | `feat/admin-center` | Xây dựng `app/Controllers/AdminController.php` và giao diện Trung tâm Quản trị viên `app/Views/admin/dashboard.php` (duyệt tổ chức, kiểm duyệt sự kiện, quản lý tài khoản, điều chỉnh giờ, 6 biểu đồ `Chart.js` và nhật ký `AuditLog`). |
| **TV3 (`phanthommy26-cell`)** | `test/automated-suite` | Xây dựng bộ kiểm thử tự động toàn diện `tests/BusinessLogicTest.php` và `tests/run_tests.php` kiểm tra 26 kịch bản nghiệp vụ, bảo mật RBAC, Waitlist, QR Attendance, Chứng nhận PDF và Gió AI Chatbot. |

---

## 4. Công nghệ sử dụng & Cấu trúc thư mục

| Phân lớp | Công nghệ & Thư viện |
| :--- | :--- |
| **Frontend** | HTML5, CSS3, JavaScript ES6+, Bootstrap 5.3, Fetch API / AJAX, Chart.js 4, `html5-qrcode`, Google Fonts (*Be Vietnam Pro*) |
| **Backend** | PHP 8.2+ thuần theo mô hình **MVC + Service + Repository + Middleware**, PDO Prepared Statements |
| **AI & Thư viện** | Google Gemini API (`gemini-2.5-flash`) / OpenAI API, Composer, `dompdf/dompdf` (xuất PDF tiếng Việt), `endroid/qr-code` |
| **Cơ sở dữ liệu** | MySQL 8.0 (20 bảng InnoDB chuẩn 3NF, Foreign Keys, Unique Constraints, Indexes, Transactions) + SQLite Fallback |
| **Môi trường chạy** | Docker & Docker Compose (Nginx 1.25, PHP 8.2-FPM, MySQL 8.0, phpMyAdmin) |

```text
gop-gio/
├── app/
│   ├── Controllers/
│   │   ├── AdminController.php
│   │   ├── AttendanceController.php
│   │   ├── AuthController.php
│   │   ├── CertificateController.php
│   │   ├── ChatController.php
│   │   ├── EventController.php
│   │   ├── HomeController.php
│   │   ├── NotificationController.php
│   │   ├── OrganizationController.php
│   │   ├── OrganizerController.php
│   │   ├── RegistrationController.php
│   │   └── VolunteerController.php
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
│   │   ├── Certificate.php
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
│   │   ├── AiChatService.php
│   │   ├── AiContextService.php
│   │   ├── AiPromptBuilder.php
│   │   ├── AttendanceService.php
│   │   ├── AuthService.php
│   │   ├── CertificateService.php
│   │   ├── EventService.php
│   │   ├── RegistrationService.php
│   │   └── StatisticsService.php
│   └── Views/
│       ├── admin/
│       ├── attendance/
│       ├── auth/
│       ├── blood/
│       ├── certificates/
│       ├── events/
│       ├── home/
│       ├── layouts/
│       │   ├── 404.php
│       │   ├── chatbot.php
│       │   └── main.php
│       ├── leaderboard/
│       ├── organizations/
│       ├── organizer/
│       └── volunteer/
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
│   │   ├── css/
│   │   │   ├── chatbot.css
│   │   │   └── style.css
│   │   ├── images/
│   │   └── js/
│   │       ├── app.js
│   │       └── chatbot.js
│   ├── uploads/
│   └── index.php
├── routes/
│   └── web.php
├── storage/
│   ├── cache/
│   ├── certificates/
│   └── logs/
├── tests/
│   ├── BusinessLogicTest.php
│   └── run_tests.php
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
# 1. Sao chép file cấu hình môi trường (nếu chưa có file .env)
cp .env.example .env

# 2. Khởi chạy hệ thống bằng Docker Compose
docker compose up -d

# 3. Khi muốn dừng hệ thống
docker compose down
```
- **Địa chỉ truy cập Website:** http://localhost:8080

### Cách 2: Khởi chạy nhanh bằng PHP Built-in Server
```bash
composer install
php -S localhost:8080 -t public public/index.php
```

---

## 6. Cấu hình & Sử dụng Trợ lý Gió AI Chatbot

**Gió AI – Trợ lý tình nguyện viên** được tích hợp ở góc dưới bên phải toàn bộ các trang của website.

1. **Lấy khóa API miễn phí:** Truy cập **Google AI Studio** (`https://aistudio.google.com/app/apikey`) để tạo `GEMINI_API_KEY`.
2. **Cấu hình trong file `.env`:**
   ```env
   AI_PROVIDER=gemini
   GEMINI_API_KEY=your_gemini_api_key_here
   GEMINI_MODEL=gemini-2.5-flash
   AI_TIMEOUT_SECONDS=15
   AI_MAX_MESSAGE_LENGTH=500
   AI_RATE_LIMIT_PER_MINUTE=15
   ```
3. **Tính năng nổi bật của Gió AI:**
   - Truy xuất dữ liệu sự kiện, lịch hiến máu, đơn vị tổ chức và bảng xếp hạng trực tiếp từ MySQL theo thời gian thực.
   - Ghi nhớ ngữ cảnh hội thoại trong phiên làm việc để trả lời các câu hỏi nối tiếp.
   - Phân quyền dữ liệu cá nhân: Khách chưa đăng nhập chỉ tra cứu dữ liệu công khai; thành viên đã đăng nhập có thể hỏi về tổng giờ tình nguyện, trạng thái đăng ký và chứng nhận PDF của chính mình.
   - Tự động chuyển sang bộ suy luận ngữ cảnh CSDL nội bộ nếu chạy trong môi trường không có kết nối mạng ngoài.

---

## 7. Danh sách tài khoản kiểm thử (Seed Data)

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

---

## 8. Kiểm thử tự động (Automated Testing)

Dự án đi kèm bộ kiểm thử tự động kiểm tra **26 kịch bản nghiệp vụ & bảo mật cốt lõi** (Đăng ký/Đăng nhập, RBAC 4 vai trò, Duyệt sự kiện, Waitlist tự động, Điểm danh QR Check-in/Check-out, Tính giờ, Chứng nhận PDF Dompdf và Gió AI Chatbot):

```bash
php tests/run_tests.php
```
