<?php
/**
 * Cấu hình chung cho website
 * Dễ dàng thay đổi các thiết lập tại đây
 */

// Đường dẫn
define('ROOT_PATH', dirname(__DIR__) . '/');
define('DATA_PATH', ROOT_PATH . 'data/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('ADMIN_PATH', ROOT_PATH . 'admin/');

// File dữ liệu
define('CONTENT_FILE', DATA_PATH . 'content.json');
define('USERS_FILE', DATA_PATH . 'users.json');

// Cài đặt Gallery
define('GALLERY_PER_PAGE_OPTIONS', [6, 12, 24, 48]); // Các tùy chọn số item/trang
define('GALLERY_DEFAULT_PER_PAGE', 12);

// Cài đặt upload
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'webm', 'mov']);

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
