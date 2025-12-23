# SE2025-16.1
Xây dựng Ứng dụng với VueJS và PHP

### Hosting web: [Click vào đây](http://34.9.250.1/)
### Hướng dẫn cài đặt: [Click vào đây](./INSTALLATION_GUIDE.md)

### Technologies
- **Frontend:** Vue.js, Inertia.js, TailwindCSS
- **Backend:** Laravel, MySQL
- **AI Service:** Google Gemini API
- **Real-time:** Pusher (Attendance & Notifications)

# Video demo sản phẩm
Link truy cập video demo sản phẩm: [Điền link video tại đây]


# Goal và Business Objective
## Goal
Phát triển nền tảng giáo dục số "Smart Classroom", giải quyết các bài toán về quản lý lớp học thủ công và thiếu công cụ hỗ trợ tự học cho học sinh.

- **Tự động hóa quản lý lớp học:** Thay thế quy trình điểm danh giấy và tính điểm thủ công bằng hệ thống QR Code và Sổ điểm điện tử tự động, giúp tiết kiệm 80% thời gian hành chính cho giáo viên.

- **Cá nhân hóa kho tri thức:** Xây dựng không gian lưu trữ riêng biệt cho mỗi học sinh, cho phép tải lên tài liệu, tạo ghi chú và quản lý kiến thức theo cách riêng.

- **Ứng dụng AI vào ôn luyện:** Tích hợp trí tuệ nhân tạo (Gemini) để tự động phân tích tài liệu học tập và sinh ra bộ thẻ ghi nhớ (Flashcards) và phân tích các lỗi sai trong các bài tập của học sinh, hỏi đáp các tài liệu trực tiếp cùng AI, giúp học sinh ôn bài nhanh chóng.

- **Minh bạch hóa đánh giá năng lực:** Cung cấp hệ thống biểu đồ phân tích dữ liệu trực quan để đánh giá chính xác năng lực thực tế của học sinh.

- **Tăng cường tương tác thời gian thực:** Xây dựng môi trường lớp học kết nối liên tục thông qua bảng tin, bình luận và hệ thống thông báo tức thì (Real-time notifications).

- **Đảm bảo tính ổn định và bảo mật:** Xây dựng hệ thống phân quyền chặt chẽ giữa Giáo viên/Học sinh và cơ chế bảo vệ dữ liệu bài thi, điểm số.


## Business Objective
**Hiệu suất xử lý AI:**
- **Tốc độ sinh Flashcard:** Với tài liệu văn bản dưới 2000 từ, hệ thống phải phân tích và tạo ra bộ 20 thẻ ghi nhớ (Câu hỏi - Đáp án) trong thời gian **dưới 15 giây**.
- **Độ chính xác:** Đảm bảo 90% nội dung Flashcard được tạo ra bám sát với ngữ cảnh tài liệu gốc.

**Hiệu suất Real-time (Điểm danh & Thông báo):**
- **Độ trễ điểm danh:** Khi học sinh quét mã QR thành công, trạng thái "Có mặt" phải hiển thị trên màn hình giáo viên trong vòng **dưới 2 giây**.
- **Khả năng chịu tải:** Hệ thống điểm danh phải chịu tải được **50 học sinh quét mã đồng thời** mà không bị treo hoặc mất dữ liệu.

**Hiệu suất lưu trữ & Hệ thống:**
- **Dung lượng cá nhân:** Cung cấp cho mỗi học sinh **50MB** dung lượng lưu trữ tài liệu (PDF, Ảnh) trong module Góc học tập, khi vượt quá lượng lưu trữ phải xóa những file cũ đi.
- **Tốc độ tải trang:** Dashboard chính (chứa biểu đồ thống kê) phải tải xong trong vòng **2 giây** với dữ liệu lớp học tiêu chuẩn (50 học sinh, 10 bài tập).

# Mô tả Use Case
Dưới đây là mô tả chi tiết các chức năng của hệ thống, phân loại theo trạng thái thực hiện.

### 1. Các Use Case đã hoàn thành

#### Nhóm chức năng chung (Authentication & User)
- **Đăng ký & Đăng nhập:** Người dùng đăng ký tài khoản mới, hệ thống tự động xác thực và đăng nhập.
- **Phân quyền người dùng:** Hệ thống tự động phân chia giao diện và quyền hạn dựa trên vai trò (Giáo viên hoặc Học sinh).
- **Quản lý hồ sơ:** Cập nhật thông tin cá nhân, ảnh đại diện.

#### Nhóm chức năng cho Giáo viên (Teacher Hub)
- **Quản lý Lớp học (Teams):**
  - Tạo lớp học mới với tên và mô tả.
  - Xem danh sách thành viên, mời hoặc xóa học sinh khỏi lớp.
- **Điểm danh thông minh (QR Attendance):**
  - **Tạo phiên điểm danh:** Sinh mã QR động (thay đổi theo thời gian/token) để chống gian lận.
  - **Theo dõi trực tiếp (Real-time):** Xem danh sách học sinh vừa quét mã hiện lên màn hình ngay lập tức.
  - **Chốt điểm danh:** Kết thúc phiên và lưu lịch sử.
- **Sổ điểm điện tử (Gradebook):**
  - **Cài đặt trọng số:** Cấu hình % điểm cho Chuyên cần, Bài tập, Giữa kỳ, Cuối kỳ.
  - **Xem bảng điểm:** Xem điểm chi tiết của toàn bộ lớp dưới dạng bảng tính.
  - **Tính điểm tự động:** Hệ thống tự động tính điểm tổng kết dựa trên điểm thành phần.
- **Phân tích dữ liệu (Analytics):**
  - **Biểu đồ phân phối điểm:** Xem phổ điểm của lớp (0-2, 2-4, ...).
- **Trợ lý chấm bài AI:**
  - Giáo viên sử dụng AI để chấm sơ bộ các bài tự luận, gợi ý điểm số và lời phê.
- **Ngân hàng câu hỏi (Question Bank):**
  - **Quản lý kho câu hỏi:** Tạo, sửa, xóa câu hỏi trắc nghiệm/tự luận; phân loại theo Môn học và Chủ đề (Topics/Tags).
  - **Nhập liệu thông minh (Import):** Hỗ trợ tải lên hàng loạt câu hỏi từ file Excel/CSV theo mẫu có sẵn.
  - **AI Question Generator:** Giáo viên nhập chủ đề hoặc đoạn văn bản, AI (Gemini) tự động sinh ra các câu hỏi trắc nghiệm tương ứng.
  - **Soạn đề thi (Quiz Builder):** Tạo bài kiểm tra bằng cách chọn thủ công các câu hỏi từ ngân hàng hoặc cấu hình để hệ thống lấy ngẫu nhiên.
  - **Hỗ trợ giáo viên đăng câu hỏi với hình ảnh và công thức toán học**
- **Hệ thống chống gian lận cho các bài kiểm tra**: Khi người dùng Alt + Tab quá 3 lần hoặc mở ứng dụng khác quá 3 lần hệ thống sẽ tự động nộp bài

#### Nhóm chức năng cho Học sinh (Student Space)
- **Dashboard cá nhân:**
  - Xem biểu đồ năng lực học tập của bản thân.
  - Xem danh sách bài tập sắp đến hạn (To-do list) và thông báo mới nhất.
- **Góc học tập (Memory Shards):**
  - **Quản lý tài liệu:** Tải lên và lưu trữ các file bài giảng, hình ảnh liên quan đến môn học, học sinh có thể vẽ trực tiếp lên tài liệu để ghi chú một cách nhanh chóng, dễ dàng.
  - **Sổ tay điện tử (Notebook):** Tạo ghi chú dạng văn bản (Rich Text) hoặc bảng tính (Spreadsheet) ngay trên web.
  - **Flashcards (Thẻ ghi nhớ):**
    - Tạo bộ thẻ thủ công.
    - **AI Generate:** Chọn tài liệu đã upload, yêu cầu AI tạo tự động bộ câu hỏi ôn tập.
    - **Ôn tập:** Chế độ lật thẻ 3D, trộn thẻ ngẫu nhiên để tự kiểm tra kiến thức.
  - **Phòng luyện đề (Simulation Gym):**
    - Luyện tập lại những câu hỏi học sinh đã sai trong các bài trắc nghiệm.
    - Random 10 câu hỏi bất kỳ trong kho câu hỏi trắc nghiệm của lớp học để học sinh luyện tập 
    - Thử thách luyện tất cả các câu hỏi có trong lớp học cho đến khi học sinh trả lời sai 3 câu sẽ dừng lại
- **Nộp bài & Xem điểm:**
  - Nộp bài tập vào các bài đăng của giáo viên.
  - Xem điểm số và nhận xét chi tiết từ giáo viên.
- **Hệ thống Gamification (Trò chơi hóa)**:
  - **Hệ thống Huy hiệu (Badges):** Tự động cấp huy hiệu khi đạt thành tích (Ví dụ: "Chuyên cần", "Top 1 Server", "Thánh Flashcard").

### 2. Các Use Case dự kiến thực hiện (Future)
- **Mobile App:**
  - Phát triển phiên bản ứng dụng di động để phụ huynh nhận thông báo điểm danh và điểm số tức thì.

