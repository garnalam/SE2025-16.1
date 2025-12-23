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



3. Cấu hình Môi trường (.env)

Bạn cần cấu hình file môi trường để kết nối cơ sở dữ liệu và các dịch vụ khác.

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

Cấu hình Google Gemini API (AI): 

1. Truy cập https://aistudio.google.com
2. Đăng nhập bằng tài khoản Google
3. Chọn **Get API key**
4. Bấm **Create API key**
5. Sao chép API key vừa tạo

Lấy API key điền vào file .env :
GEMINI_API_KEY=your_gemini_api_key_here

Truy cập pusher.com và đăng ký tài khoản (Sign Up) miễn phí.

Sau khi đăng nhập, chọn Channels -> Bấm Create App.

Điền thông tin:

Name: Tên dự án (VD: LopHocTuongTac).

Cluster: Chọn ap1 (Singapore) (Quan trọng: Chọn cái này cho gần Việt Nam và khớp cấu hình).

Frontend chọn Vue.js , Backend chọn Laravel

Bấm Create App.

Trong trang quản lý App vừa tạo, tìm menu bên trái chọn App Keys.
<img width="1840" height="937" alt="image" src="https://github.com/user-attachments/assets/3d006da3-c36a-42ad-b896-8f468a0b4eeb" />


Chạy lệnh sau trong Terminal để cài đặt các gói cần thiết cho tính năng Real-time


php artisan install:broadcasting

Gặp các câu trả lời sau thì hãy trả lời như hướng dẫn dưới : 
Which broadcasting driver would you like to use? => pusher
Pusher App ID: => Chính là value của app_id Trong pusher.com ở phần App Keys 
Pusher App Key: => Chính là value của key Trong pusher.com ở phần App Keys 
Pusher App Secret: => Chính là value của secret Trong pusher.com ở phần App Keys 
Cuối cùng phần Pusher App Cluster: ta chọn ap1
và nếu terminal nói  Would you like to install and build the Node dependencies required for broadcasting? (yes/no) [yes] thì ta chọn yes
Đợi quá trình hoàn tất sẽ đến bước tiếp theo 

2. Sửa lỗi SSL trên máy cá nhân (BẮT BUỘC CHO WINDOWS) 
Vì chúng ta đang chạy trên Localhost (Windows), PHP sẽ chặn kết nối đến Pusher do không tin tưởng chứng chỉ bảo mật. Bạn cần làm bước này 1 lần duy nhất trên máy tính của bạn:

Tải file cacert.pem tại đây: https://curl.se/ca/cacert.pem

Lưu file vào ổ C, ví dụ: C:\cacert.pem.

Mở file cấu hình php.ini (Gõ php --ini trong terminal để biết đường dẫn).

Tìm và sửa dòng curl.cainfo thành:
curl.cainfo = "C:\cacert.pem"
openssl.cafile = "C:\cacert.pem"
(Nhớ xóa dấu chấm phẩy ; ở đầu dòng nếu có).


3. Cài đặt thư viện đi kèm
   
    Cài đặt Thư viện PHP (Composer):
    
    composer install
    
    
    Cài đặt Thư viện JavaScript (NPM):
    
    npm install

4. Cài đặt Gói Phụ thuộc

Tạo Khóa Ứng dụng:

php artisan key:generate

Tạo Symbolic Link (Storage):

Lệnh này rất quan trọng. Nó tạo một lối tắt từ public/storage đến storage/app/public, cho phép ứng dụng hiển thị các file đã được upload (như ảnh đại diện, file bài tập).

php artisan storage:link

Chạy lệnh sau để đồng bộ dữ liệu badge:

php artisan badges:sync

Chạy Database Migrations:

Lệnh này sẽ tạo tất cả các bảng cần thiết trong database của bạn.

🖥️ Khởi chạy Ứng dụng

Để chạy dự án, bạn cần mở ba cửa sổ terminal riêng biệt tại thư mục gốc của dự án (SE2025-16.1).

Terminal 1: Chạy Vite (Frontend)

Biên dịch assets (CSS/JS) và theo dõi thay đổi (hot-reload).

npm run dev


Terminal 2: Chạy Server (Backend)

php artisan serve

Terminal 3 : php artisan queue:work

Sau khi cả hai terminal đều chạy thành công, bạn có thể truy cập ứng dụng tại:

http://localhost:8000

