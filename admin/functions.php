<?php
/**
 * Các hàm tiện ích cho website
 */

// Bật hiển thị lỗi khi debug
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Định nghĩa đường dẫn
define('ROOT_PATH', dirname(__DIR__) . '/');
define('DATA_PATH', ROOT_PATH . 'data/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');

/**
 * Đọc dữ liệu từ file JSON
 */
function getData($file = 'content.json') {
    $path = DATA_PATH . $file;
    if (file_exists($path)) {
        $json = file_get_contents($path);
        return json_decode($json, true);
    }
    return [];
}

/**
 * Lưu dữ liệu vào file JSON
 */
function saveData($data, $file = 'content.json') {
    $path = DATA_PATH . $file;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($path, $json);
}

/**
 * Lấy thông tin công ty
 */
function getCompany() {
    $data = getData();
    return $data['company'] ?? [];
}

/**
 * Lấy dữ liệu theo section
 */
function getSection($section) {
    $data = getData();
    return $data[$section] ?? [];
}

/**
 * Lấy cài đặt
 */
function getSettings() {
    $data = getData();
    return $data['settings'] ?? [];
}

/**
 * Tạo URL cho hình ảnh
 */
function imageUrl($path, $placeholder = '800x600') {
    if (empty($path)) {
        return 'https://placehold.co/' . $placeholder . '/1a1a1a/c9a962?text=No+Image';
    }
    if (strpos($path, 'http') === 0) {
        return $path;
    }
    // Kiểm tra file có tồn tại không
    $fullPath = ROOT_PATH . ltrim($path, '/');
    if (!file_exists($fullPath)) {
        return 'https://placehold.co/' . $placeholder . '/1a1a1a/c9a962?text=Upload+Image';
    }
    return '/' . ltrim($path, '/');
}

/**
 * Upload file
 */
function uploadFile($file, $folder = '') {
    $uploadDir = UPLOADS_PATH . ($folder ? $folder . '/' : '');
    
    // Tạo thư mục nếu chưa có
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Tạo tên file unique
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    // Di chuyển file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/' . ($folder ? $folder . '/' : '') . $filename;
    }
    
    return false;
}

/**
 * Xóa file
 */
function deleteFile($path) {
    $fullPath = ROOT_PATH . $path;
    if (file_exists($fullPath) && is_file($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Kiểm tra đăng nhập
 */
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Yêu cầu đăng nhập
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/login.php');
        exit;
    }
}

/**
 * Xác thực người dùng
 */
function authenticate($username, $password) {
    $users = getData('users.json');
    
    foreach ($users['users'] as $user) {
        if ($user['username'] === $username) {
            // So sánh mật khẩu (dùng plain text cho đơn giản, nên dùng password_hash trong production)
            if ($user['password_plain'] === $password || password_verify($password, $user['password'])) {
                return $user;
            }
        }
    }
    
    return false;
}

/**
 * Tạo thông báo flash
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Lấy và xóa thông báo flash
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Escape HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Render icon SVG
 */
function renderIcon($name) {
    $icons = [
        'lightbulb' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21h6M12 3a6 6 0 0 0-4 10.5V17h8v-3.5A6 6 0 0 0 12 3z"/></svg>',
        'film' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2"/><path d="M7 2v20M17 2v20M2 12h20M2 7h5M2 17h5M17 17h5M17 7h5"/></svg>',
        'settings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'camera' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>',
        'play' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>',
        'phone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
        'close' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    ];
    
    return $icons[$name] ?? '';
}

// ============================================
// CÁC HÀM MỚI CHO GALLERY
// ============================================

// Cài đặt Gallery
define('GALLERY_PER_PAGE_OPTIONS', [6, 12, 24, 48]);
define('GALLERY_DEFAULT_PER_PAGE', 12);

/**
 * Phân trang
 */
function paginate($items, $page = 1, $perPage = 12) {
    $total = count($items);
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages ?: 1));
    $offset = ($page - 1) * $perPage;
    
    return [
        'items' => array_slice($items, $offset, $perPage),
        'current_page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages
    ];
}

/**
 * Sắp xếp gallery theo order
 */
function sortGalleryByOrder($gallery) {
    usort($gallery, function($a, $b) {
        $orderA = isset($a['order']) ? (int)$a['order'] : 999999;
        $orderB = isset($b['order']) ? (int)$b['order'] : 999999;
        return $orderA - $orderB;
    });
    return $gallery;
}

/**
 * Lấy YouTube video ID
 */
function getYouTubeId($url) {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Lấy thumbnail tự động từ URL video
 */
function getVideoThumbnail($url) {
    if ($ytId = getYouTubeId($url)) {
        return "https://img.youtube.com/vi/{$ytId}/maxresdefault.jpg";
    }
    return null;
}
