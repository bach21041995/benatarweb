# Website Creative Studio - Hướng dẫn cài đặt

## 📁 Cấu trúc thư mục

```
m2m-website/
├── index.php              # Trang chủ website
├── contact.php            # Xử lý form liên hệ
├── assets/
│   └── css/
│       └── style.css      # CSS cho website
├── data/
│   ├── content.json       # Nội dung website (sửa ở đây hoặc qua admin)
│   └── users.json         # Tài khoản admin
├── uploads/               # Thư mục chứa hình ảnh upload
├── admin/
│   ├── index.php          # Dashboard admin
│   ├── login.php          # Trang đăng nhập
│   ├── company.php        # Quản lý thông tin công ty
│   ├── team.php           # Quản lý đội ngũ
│   └── ...                # Các trang quản lý khác
└── README.md              # File này
```

---

## 🚀 CÁCH 1: Chạy trên máy tính (để test)

### Yêu cầu
- PHP 7.4 trở lên (đã cài sẵn trên Mac/Linux, Windows cần cài XAMPP)

### Bước 1: Mở Terminal/Command Prompt

### Bước 2: Di chuyển vào thư mục website
```bash
cd đường-dẫn-tới-thư-mục/m2m-website
```

### Bước 3: Chạy PHP server
```bash
php -S localhost:8000
```

### Bước 4: Mở trình duyệt
- Website: http://localhost:8000
- Admin: http://localhost:8000/admin

### Đăng nhập Admin
- Username: `admin`
- Password: `admin123`

---

## 🌐 CÁCH 2: Upload lên Hosting (để chạy thật)

### Yêu cầu hosting
- PHP 7.4 trở lên
- Không cần MySQL (dùng file JSON)

### Các bước thực hiện

#### Bước 1: Chuẩn bị file
1. Giải nén file `m2m-website.zip`
2. Mở file `data/users.json` và đổi mật khẩu admin

#### Bước 2: Upload lên hosting
1. Đăng nhập vào cPanel hoặc DirectAdmin
2. Mở **File Manager**
3. Vào thư mục `public_html` (hoặc `htdocs`)
4. Upload toàn bộ file trong thư mục `m2m-website`

#### Bước 3: Phân quyền thư mục
Đặt quyền **755** hoặc **775** cho các thư mục:
- `data/`
- `uploads/`

Cách làm trong File Manager:
1. Click chuột phải vào thư mục
2. Chọn "Change Permissions" hoặc "Permissions"
3. Đặt giá trị 755

#### Bước 4: Truy cập website
- Website: `https://yourdomain.com`
- Admin: `https://yourdomain.com/admin`

---

## ✏️ CÁCH CHỈNH SỬA NỘI DUNG

### Qua Admin Panel (Dễ - Khuyên dùng)
1. Truy cập `/admin`
2. Đăng nhập với tài khoản admin
3. Chỉnh sửa từng mục trong menu bên trái
4. Nhấn "Lưu" để cập nhật

### Trực tiếp file JSON (Nâng cao)
1. Mở file `data/content.json`
2. Chỉnh sửa nội dung theo cấu trúc JSON
3. Lưu file

---

## 🔒 BẢO MẬT

### Đổi mật khẩu admin
1. Mở file `data/users.json`
2. Sửa giá trị `password_plain`
3. Lưu file

### Bảo vệ thư mục data
Tạo file `.htaccess` trong thư mục `data/` với nội dung:
```
Deny from all
```

---

## ❓ CÂU HỎI THƯỜNG GẶP

### Q: Website không hiển thị hình ảnh?
A: Kiểm tra:
- Đã upload hình vào thư mục `uploads/`
- Đường dẫn hình trong admin đúng
- Thư mục `uploads/` có quyền ghi (755)

### Q: Không lưu được nội dung trong admin?
A: Đặt quyền 755 hoặc 775 cho thư mục `data/`

### Q: Quên mật khẩu admin?
A: Mở file `data/users.json` và xem/sửa `password_plain`

### Q: Muốn đổi màu chủ đạo?
A: Vào Admin > Cài đặt > Màu chủ đạo

---

## 📞 HỖ TRỢ

Nếu cần hỗ trợ thêm, vui lòng liên hệ người phát triển.

---

*Phiên bản 1.0 - 2024*
