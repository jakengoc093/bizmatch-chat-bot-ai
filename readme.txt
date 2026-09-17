=== Bizmatch Chat Bot AI ===
Contributors: ngocnguyen
Donate link: https://ngocnguyen.com.vn/
Tags: chatbot, gemini ai, wordpress chatbot, ai assistant, bizmatch
Requires at least: 5.8
Tested up to: 6.5
Stable tag: 2.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Chatbot kết nối Gemini API tùy chỉnh vị trí hiển thị, màu sắc thương hiệu và thiết lập kịch bản tư vấn tự động cho website.

== Description ==

Bizmatch Chat Bot AI là plugin tích hợp trợ lý ảo thông minh sử dụng mô hình Google Gemini API trực tiếp vào website WordPress. Plugin giúp tự động hóa quá trình tư vấn khách hàng, giải đáp thắc mắc và điều hướng người dùng dựa trên dữ liệu thực tế của website.

* **Kết nối đa Key (Failover):** Hỗ trợ nạp API Key dự phòng tự động chuyển đổi khi Key chính bị giới hạn ngạch (Error 429).
* **Tùy biến giao diện:** Cho phép chọn màu sắc thương hiệu (Brand Color) và vị trí hiển thị (Góc trái / Góc phải).
* **Đa nguồn dữ liệu:** Tự động truy xuất bài viết, trang, sản phẩm từ Site chính và Subdomain vệ tinh để nạp context cho AI.
* **Lưu lịch sử thông minh:** Lưu vết cuộc trò chuyện bằng LocalStorage trong 7 ngày, tự động khóa ô nhập khi đang xử lý để tránh spam.

== Installation ==

1. Upload thư mục `bizmatch-chat-bot-ai` vào đường dẫn `/wp-content/plugins/`.
2. Vào **Plugins > Installed Plugins** trong WordPress Admin và bấm **Activate**.
3. Truy cập menu **Cài đặt > Bizmatch Chatbot** để nhập API Key và thiết lập vai trò tư vấn cho AI.

== Frequently Asked Questions ==

= Lấy Gemini API Key ở đâu? =
Bạn truy cập vào Google AI Studio (https://aistudio.google.com/app/apikey), đăng nhập tài khoản Google và tạo mã API miễn phí.

= Chatbot có làm chậm website không? =
Không. Toàn bộ mã nguồn giao diện được tải bất đồng bộ (Async/REST API) và chỉ gọi API khi người dùng gửi tin nhắn.

== Changelog ==

= 1.2 =
* Đổi tên plugin thành Bizmatch Chat Bot AI.
* Tích hợp bộ chọn màu nhận diện thương hiệu (Brand Color Picker).
* Thêm tính năng API Key dự phòng và tự động quét dữ liệu Subdomain qua REST API.
* Tối ưu hóa UI/UX: Tự động cuộn tin nhắn, khóa ô nhập khi bot đang phản hồi.

= 1.0 =
* Khởi tạo phiên bản thử nghiệm đầu tiên kết nối Gemini API.
