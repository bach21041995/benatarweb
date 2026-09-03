<?php
/**
 * Các hàm tiện ích dùng chung
 */

require_once __DIR__ . '/config.php';

/**
 * Đọc dữ liệu từ file JSON
 */
function getData() {
    if (!file_exists(CONTENT_FILE)) {
        return [];
    }
    $json = file_get_contents(CONTENT_FILE);
    return json_decode($json, true) ?: [];
}

/**
 * Lưu dữ liệu vào file JSON
 */
function saveData($data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents(CONTENT_FILE, $json);
}

/**
 * Lấy users
 */
function getUsers() {
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $json = file_get_contents(USERS_FILE);
    return json_decode($json, true) ?: [];
}

/**
 * Escape HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Upload file
 */
function uploadFile($file, $subfolder = '') {
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $uploadDir = UPLOADS_PATH . ($subfolder ? trim($subfolder, '/') . '/' : '');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . ($subfolder ? trim($subfolder, '/') . '/' : '') . $filename;
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
 * Flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Kiểm tra đăng nhập
 */
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Xác thực user
 */
function authenticate($username, $password) {
    $data = getUsers();
    $users = $data['users'] ?? [];
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            // Kiểm tra password plain text hoặc hashed
            if (isset($user['password_plain']) && $user['password_plain'] === $password) {
                return $user;
            }
            if (isset($user['password']) && password_verify($password, $user['password'])) {
                return $user;
            }
        }
    }
    return false;
}

/**
 * Tạo URL hình ảnh placeholder
 */
function img($path, $size = '800x600') {
    if (empty($path)) {
        return "https://placehold.co/{$size}/111/C9A24A?text=Image";
    }
    if (strpos($path, 'http') === 0) {
        return $path;
    }
    return $path;
}

/**
 * Phân trang
 */
function paginate($items, $page = 1, $perPage = 12) {
    $total = count($items);
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
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
 * Lấy Google Drive file ID
 */
function getDriveId($url) {
    if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Lấy thumbnail tự động từ URL video
 */
function getVideoThumbnail($url) {
    // YouTube
    if ($ytId = getYouTubeId($url)) {
        return "https://img.youtube.com/vi/{$ytId}/maxresdefault.jpg";
    }
    return null;
}
