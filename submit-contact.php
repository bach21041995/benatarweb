<?php
/**
 * Xử lý form liên hệ từ frontend
 * Lưu thông tin khách hàng vào content.json
 */

header('Content-Type: application/json');

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Lấy data từ form
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate
$errors = [];
if (empty($name)) $errors[] = 'Vui lòng nhập họ tên';
if (empty($email)) $errors[] = 'Vui lòng nhập email';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ';
if (empty($message)) $errors[] = 'Vui lòng nhập nội dung';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Đọc data hiện tại
$dataFile = __DIR__ . '/data/content.json';
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

// Khởi tạo mảng contacts nếu chưa có
if (!isset($data['contacts'])) {
    $data['contacts'] = [];
}

// Tạo ID mới
$maxId = 0;
foreach ($data['contacts'] as $contact) {
    if (isset($contact['id']) && $contact['id'] > $maxId) {
        $maxId = $contact['id'];
    }
}

// Thêm liên hệ mới
$newContact = [
    'id' => $maxId + 1,
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'message' => $message,
    'read' => false,
    'created_at' => date('Y-m-d H:i:s')
];

// Thêm vào đầu mảng (mới nhất trước)
array_unshift($data['contacts'], $newContact);

// Lưu file
if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode([
        'success' => true, 
        'message' => 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại']);
}
