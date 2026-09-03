<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();
$flash = getFlash();

$stats = [
    'services' => count($data['services'] ?? []),
    'team' => count($data['team'] ?? []),
    'clients' => count($data['clients'] ?? []),
    'features' => count($data['features'] ?? [])
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Dashboard</h1>
          <div class="breadcrumb"><span>Trang chủ</span></div>
        </div>
        <a href="../" target="_blank" class="btn btn-secondary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
            <polyline points="15 3 21 3 21 9"/>
            <line x1="10" y1="14" x2="21" y2="3"/>
          </svg>
          Xem website
        </a>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
          </div>
          <div class="stat-value"><?= $stats['services'] ?></div>
          <div class="stat-label">Dịch vụ</div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div class="stat-value"><?= $stats['team'] ?></div>
          <div class="stat-label">Thành viên</div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <div class="stat-value"><?= $stats['clients'] ?></div>
          <div class="stat-label">Khách hàng</div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          </div>
          <div class="stat-value"><?= $stats['features'] ?></div>
          <div class="stat-label">Điểm nổi bật</div>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Hướng dẫn sử dụng</h3>
        </div>
        <div class="card-body">
          <p style="margin-bottom: 15px;">Chào mừng đến với trang quản trị website!</p>
          <ul style="margin-left: 20px; line-height: 2;">
            <li><strong>Thông tin công ty:</strong> Cập nhật tên, địa chỉ, SĐT, email, mạng xã hội</li>
            <li><strong>Banner trang chủ:</strong> Thay đổi tiêu đề, mô tả, hình nền/video</li>
            <li><strong>Dịch vụ:</strong> Thêm/sửa/xóa các dịch vụ của công ty</li>
            <li><strong>Đội ngũ:</strong> Quản lý thông tin nhân viên</li>
            <li><strong>Khách hàng:</strong> Thêm logo khách hàng tiêu biểu</li>
            <li><strong>Cài đặt:</strong> Đổi logo, favicon, màu chủ đạo</li>
          </ul>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
