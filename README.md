# Mini Training Session Registration App

Ứng dụng đăng ký buổi đào tạo nội bộ, được xây dựng theo hướng dẫn Lab02 bằng PHP thuần.

## 1. Mục tiêu bài làm

- Thiết lập project đúng cấu trúc cơ bản: `public`, `src`, `views`, `config`, `storage/logs`.
- Cấu hình Composer và PSR-4 autoload.
- Quản lý biến môi trường bằng `.env` và `.env.example`.
- Xây dựng API GET/POST và xử lý các lỗi phổ biến theo HTTP status code.
- Bổ sung chức năng nâng cao: lưu dữ liệu đăng ký, chống đăng ký trùng, cập nhật số chỗ còn lại.

## 2. Kiến trúc hệ thống

Ứng dụng chia thành 3 lớp:

1. Presentation
- `GET /` trả về trang chủ HTML.
- Các API endpoint trả dữ liệu JSON.

2. Application
- Router thủ công trong `public/index.php`.
- Controllers:
  - `DashboardController`: dữ liệu cho trang chủ.
  - `SessionController`: trả danh sách buổi đào tạo.
  - `EnrollmentController`: xử lý đăng ký.

3. Data & Support
- Dữ liệu mẫu ban đầu: `src/Data/training_sessions.php`.
- Dữ liệu runtime:
  - `storage/sessions.json`
  - `storage/enrollments.json`
- Lớp hỗ trợ:
  - `TrainingStorage`: đọc/ghi JSON và khóa file.
  - `ApiResponder`: chuẩn hóa response JSON.
  - `AppEnv`: đọc biến môi trường.

## 3. Cấu trúc thư mục

```text
php-mini-training-session-app/
├─ config/
├─ public/
├─ src/
│  ├─ Controllers/
│  ├─ Data/
│  └─ Support/
├─ storage/
│  └─ logs/
├─ views/
├─ composer.json
├─ .env
└─ .env.example
```

## 4. Thiết lập môi trường

### 4.1 Kiểm tra công cụ

```bash
php -v
composer -V
git --version
```

### 4.2 Cài dependency và autoload

```bash
composer install
composer dump-autoload
```

### 4.3 Tạo file môi trường

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

## 5. Cấu hình ứng dụng

Các biến quan trọng trong `.env`:

- `APP_NAME`
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `TRAINING_CENTER_NAME`
- `MAX_SEATS_PER_ENROLLMENT`

Ứng dụng sẽ validate biến môi trường khi khởi động.

## 6. Endpoints

1. Trang chủ
- `GET /`

2. Danh sách buổi đào tạo
- `GET /sessions`

3. Tạo đăng ký
- `POST /enrollments`
- Header bắt buộc: `Content-Type: application/json`

## 7. Quy tắc nghiệp vụ

Khi gọi `POST /enrollments`:

- Bắt buộc có `session_id`, `employee_name`, `email`, `seats`.
- `seats` phải lớn hơn `0`.
- `seats` không vượt quá `MAX_SEATS_PER_ENROLLMENT`.
- `session_id` phải tồn tại.
- Session phải còn đủ số chỗ.
- Không cho cùng email đăng ký trùng cùng một session.

## 8. Chức năng phát triển thêm

Ngoài yêu cầu cơ bản của Lab02, project đã có thêm:

1. Lưu dữ liệu đăng ký ra file JSON
- Mỗi đăng ký thành công được ghi vào `storage/enrollments.json`.

2. Cập nhật số chỗ còn lại theo thời gian thực
- Sau khi đăng ký thành công, `seats_available` được giảm và lưu vào `storage/sessions.json`.

3. Chống đăng ký trùng
- Nếu cùng email đăng ký lại cùng session, API trả về `422`.

4. Ghi file có khóa
- Tránh lỗi xung đột dữ liệu khi có nhiều request đồng thời.

## 9. Danh sách status code xử lý

- `200 OK`: lấy dữ liệu thành công.
- `201 Created`: đăng ký thành công.
- `400 Bad Request`: body JSON sai định dạng.
- `404 Not Found`: sai đường dẫn.
- `405 Method Not Allowed`: sai method.
- `415 Unsupported Media Type`: sai Content-Type.
- `422 Unprocessable Content`: dữ liệu không hợp lệ về nghiệp vụ.

## 10. Hướng dẫn chạy ứng dụng

```bash
php -S localhost:8000 -t public
```

Mở trình duyệt tại:

```text
http://localhost:8000/
```

## 11. Hướng dẫn kiểm tra nhanh

### 11.1 Kiểm tra lấy danh sách session

```bash
curl -i http://localhost:8000/sessions
```

Kỳ vọng: `200 OK`

### 11.2 Kiểm tra đăng ký thành công

```bash
curl -i -X POST http://localhost:8000/enrollments \
  -H "Content-Type: application/json" \
  -d "{\"session_id\":1,\"employee_name\":\"Nguyen Van A\",\"email\":\"a.unique@example.com\",\"seats\":1}"
```

Kỳ vọng: `201 Created`

### 11.3 Kiểm tra sai Content-Type

```bash
curl -i -X POST http://localhost:8000/enrollments \
  -H "Content-Type: text/plain" \
  -d "abc"
```

Kỳ vọng: `415 Unsupported Media Type`

### 11.4 Kiểm tra dữ liệu không hợp lệ

```bash
curl -i -X POST http://localhost:8000/enrollments \
  -H "Content-Type: application/json" \
  -d "{\"session_id\":999,\"employee_name\":\"\",\"email\":\"\",\"seats\":0}"
```

Kỳ vọng: `422 Unprocessable Content`

### 11.5 Kiểm tra sai method

```bash
curl -i http://localhost:8000/enrollments
```

Kỳ vọng: `405 Method Not Allowed`

### 11.6 Kiểm tra route không tồn tại

```bash
curl -i http://localhost:8000/not-found
```

Kỳ vọng: `404 Not Found`

### 11.7 Kiểm tra chống đăng ký trùng

Gửi cùng payload hợp lệ 2 lần liên tiếp:

- Lần 1: `201 Created`
- Lần 2: `422 Unprocessable Content`

## 12. Kiểm tra dữ liệu lưu file

Sau khi đăng ký thành công:

- `storage/sessions.json`: số chỗ còn lại giảm đúng.
- `storage/enrollments.json`: có bản ghi mới gồm `enrollment_id`, `email`, `session_id`, `created_at`.

## 13. Gợi ý commit message

1. `chore: init mini training session project structure`
2. `feat: add sessions listing endpoint`
3. `feat: add enrollment endpoint with validation`
4. `feat: persist enrollments and update available seats`
5. `feat: prevent duplicate enrollment by email and session`
6. `docs: complete vietnamese readme for lab submission`
