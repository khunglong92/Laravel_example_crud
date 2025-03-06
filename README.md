# Laravel CRUD API với JWT Authentication và Swagger

Dự án Laravel CRUD API được container hóa bằng Docker, sử dụng PostgreSQL làm cơ sở dữ liệu, JWT Authentication cho xác thực và tích hợp Swagger để tạo tài liệu API.

## Tính năng chính

- **RESTful API**: Xây dựng trên Laravel framework
- **JWT Authentication**: Xác thực người dùng với access token và refresh token
- **PostgreSQL Database**: Lưu trữ dữ liệu
- **Docker**: Container hóa ứng dụng và cơ sở dữ liệu
- **Swagger Documentation**: Tài liệu API tự động

## Yêu cầu hệ thống

- Docker
- Docker Compose
- Git

## Cài đặt và Khởi động

### 1. Clone dự án

```bash
git clone <repository-url>
cd Laravel_CRUD
```

### 2. Cấu hình môi trường

Tạo file `.env` từ file mẫu:

```bash
cp .env.example .env
```

Cập nhật các thông số trong file `.env`:

```
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=postgres_laravel
DB_USERNAME=postgres
DB_PASSWORD=postgres

L5_SWAGGER_CONST_HOST=http://127.0.0.1:8000
```

### 3. Khởi động Docker

```bash
docker-compose up -d
```

### 4. Cài đặt dependencies

```bash
docker-compose exec app composer install
```

### 5. Tạo key cho ứng dụng

```bash
docker-compose exec app php artisan key:generate
```

### 6. Tạo JWT secret key

```bash
docker-compose exec app php artisan jwt:secret
```

### 7. Chạy migration để tạo cơ sở dữ liệu

```bash
docker-compose exec app php artisan migrate
```

### 8. Tạo tài liệu Swagger

```bash
docker-compose exec app php artisan l5-swagger:generate
```

## Kiểm tra kết nối PostgreSQL

Để kiểm tra kết nối với PostgreSQL, bạn có thể thực hiện các bước sau:

### 1. Kiểm tra container PostgreSQL

```bash
docker-compose ps
```

Đảm bảo container `db` đang chạy.

### 2. Truy cập vào container PostgreSQL

```bash
docker-compose exec db psql -U postgres
```

### 3. Liệt kê các database

```sql
\l
```

Bạn sẽ thấy database `postgres_laravel` trong danh sách.

### 4. Kết nối với database

```sql
\c postgres_laravel
```

### 5. Liệt kê các bảng

```sql
\dt
```

Bạn sẽ thấy các bảng như `users`, `migrations`, v.v.

### 6. Thoát khỏi PostgreSQL

```sql
\q
```

## Cấu trúc API

### Authentication API

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | /api/register | Đăng ký người dùng mới |
| POST | /api/login | Đăng nhập và nhận JWT token |
| POST | /api/refresh | Làm mới token |

### Cấu trúc Response

Tất cả các API đều trả về response theo định dạng:

```json
{
  "message": "success/error",
  "data": { ... }
}
```

## Cập nhật Swagger khi thêm API mới

Khi bạn thêm một API mới hoặc thay đổi API hiện có, bạn cần cập nhật tài liệu Swagger:

### 1. Thêm annotation Swagger trong controller

```php
/**
 * @OA\Post(
 *     path="/api/endpoint",
 *     summary="Mô tả endpoint",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="field1", type="string", example="value1"),
 *             @OA\Property(property="field2", type="string", example="value2")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="success"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="result", type="string", example="data")
 *             )
 *         )
 *     )
 * )
 */
public function method(Request $request)
{
    // Implementation
}
```

### 2. Tạo lại tài liệu Swagger

```bash
docker-compose exec app php artisan l5-swagger:generate
```

### 3. Truy cập tài liệu Swagger

Mở trình duyệt và truy cập: http://127.0.0.1:8000/api/documentation

## Các lệnh thường dùng

### Docker

```bash
# Khởi động các container
docker-compose up -d

# Dừng các container
docker-compose down

# Xem logs
docker-compose logs app
docker-compose logs db

# Khởi động lại container
docker-compose restart app
```

### Laravel

```bash
# Truy cập shell của container
docker-compose exec app bash

# Xóa cache
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear

# Tạo controller mới
docker-compose exec app php artisan make:controller Api/ControllerName

# Tạo migration mới
docker-compose exec app php artisan make:migration create_table_name

# Chạy migration
docker-compose exec app php artisan migrate

# Xem log Laravel
docker-compose exec app tail -f storage/logs/laravel.log
```

## Xử lý sự cố

### 1. Lỗi kết nối database

- Kiểm tra container PostgreSQL có đang chạy không:
  ```bash
  docker-compose ps
  ```
- Kiểm tra logs của container:
  ```bash
  docker-compose logs db
  ```
- Đảm bảo thông số kết nối trong file `.env` chính xác
- Kiểm tra cấu hình trong `docker-compose.yml`

### 2. Lỗi JWT Authentication

- Đảm bảo đã tạo JWT secret key:
  ```bash
  docker-compose exec app php artisan jwt:secret
  ```
- Kiểm tra cấu hình JWT trong `config/jwt.php`
- Xem logs để kiểm tra lỗi:
  ```bash
  docker-compose exec app tail -f storage/logs/laravel.log
  ```

### 3. Lỗi Swagger

- Đảm bảo cấu hình Swagger trong `config/l5-swagger.php` chính xác
- Xóa cache và tạo lại tài liệu:
  ```bash
  docker-compose exec app php artisan config:clear
  docker-compose exec app php artisan l5-swagger:generate
  ```

### 4. Reset database

Nếu cần reset toàn bộ database:

```bash
docker-compose down -v
docker-compose up -d
docker-compose exec app php artisan migrate
```

## Truy cập các dịch vụ

- API: http://127.0.0.1:8000/api
- Swagger Documentation: http://127.0.0.1:8000/api/documentation
- PostgreSQL: localhost:5432

## License

Dự án được phân phối dưới giấy phép MIT.
