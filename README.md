Project SE2025-16.1 



📋 Yêu cầu Hệ thống

Trước khi bắt đầu, hãy đảm bảo bạn đã cài đặt các công cụ sau trên máy của mình:

PHP (phiên bản mới nhất)

Composer (Trình quản lý gói cho PHP)

XAMPP (hoặc một môi trường server tương tự như Laragon, WAMP) để quản lý Apache và MySQL.

Node.js và npm (Trình quản lý gói cho JavaScript)

Git (Hệ thống quản lý phiên bản)

🚀 Hướng dẫn Cài đặt

Vui lòng làm theo các bước sau để cài đặt và chạy dự án local.

1. Clone Repository

Mở terminal, di chuyển đến thư mục bạn muốn lưu project và chạy lệnh sau:

git clone https://github.com/garnalam/SE2025-16.1.git
cd SE2025-16.1


2. Lấy Code Mới Nhất từ Branch dev

Toàn bộ code phát triển đang ở branch dev. Hãy đảm bảo bạn có phiên bản mới nhất:

git pull origin dev


3. Cấu hình Môi trường (.env)

Bạn cần cấu hình file môi trường để kết nối cơ sở dữ liệu và các dịch vụ khác.

Khởi động XAMPP: Bật module Apache và MySQL.

Tạo Database: Truy cập phpMyAdmin (thường là http://localhost/phpmyadmin) và tạo một database mới (ví dụ: laravel như trong file mẫu).

Sao chép file .env:

cp .env.example .env


Chỉnh sửa file .env:
Mở file .env vừa tạo và cập nhật các trường sau:

# Đặt URL ứng dụng để trỏ đến cổng của artisan serve
APP_URL=http://localhost:8000

# Cấu hình kết nối MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel  # <- Tên database bạn vừa tạo ở B2
DB_USERNAME=root     # <- User của MySQL (mặc định của XAMPP là 'root')
DB_PASSWORD=        # <- Mật khẩu của MySQL (mặc định của XAMPP là rỗng)


4. Cài đặt Gói Phụ thuộc

Tạo Khóa Ứng dụng:

php artisan key:generate


Cài đặt Thư viện PHP (Composer):

composer install


Cài đặt Thư viện JavaScript (NPM):

npm install


5. Khởi tạo Ứng dụng

Chạy các lệnh sau để hoàn tất việc thiết lập cơ sở dữ liệu và liên kết lưu trữ file.

Chạy Database Migrations:
Lệnh này sẽ tạo tất cả các bảng cần thiết trong database của bạn.

php artisan migrate


Tạo Symbolic Link (Storage):
Lệnh này rất quan trọng. Nó tạo một lối tắt từ public/storage đến storage/app/public, cho phép ứng dụng hiển thị các file đã được upload (như ảnh đại diện, file bài tập).

php artisan storage:link


🖥️ Khởi chạy Ứng dụng

Để chạy dự án, bạn cần mở hai cửa sổ terminal riêng biệt tại thư mục gốc của dự án (SE2025-16.1).

Terminal 1: Chạy Vite (Frontend)
Biên dịch assets (CSS/JS) và theo dõi thay đổi (hot-reload).

npm run dev


Terminal 2: Chạy Server (Backend)
Khởi động server Laravel (mặc định ở cổng 8000).

php artisan serve


Sau khi cả hai terminal đều chạy thành công, bạn có thể truy cập ứng dụng tại:
http://localhost:8000

🛠️ Công nghệ Sử dụng

Backend: Laravel

Frontend: Vue.js với Inertia.js

Database: MySQL

Build Tool: Vite

Styling: Tailwind CSS