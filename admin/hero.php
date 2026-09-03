<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['hero'] = [
        'label' => $_POST['label'] ?? '',
        'title' => $_POST['title'] ?? '',
        'subtitle' => $_POST['subtitle'] ?? '',
        'background_image' => $_POST['current_bg_image'] ?? '',
        'background_video' => $_POST['current_bg_video'] ?? ''
    ];
    
    // Upload background image
    if (!empty($_FILES['background_image']['name'])) {
        $uploaded = uploadFile($_FILES['background_image']);
        if ($uploaded) {
            $data['hero']['background_image'] = $uploaded;
        }
    }
    
    // Upload background video
    if (!empty($_FILES['background_video']['name'])) {
        $uploaded = uploadFile($_FILES['background_video']);
        if ($uploaded) {
            $data['hero']['background_video'] = $uploaded;
        }
    }
    
    if (saveData($data)) {
        setFlash('success', 'Đã cập nhật banner thành công!');
    } else {
        setFlash('danger', 'Có lỗi xảy ra!');
    }
    
    header('Location: hero.php');
    exit;
}

$hero = $data['hero'] ?? [];
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Banner trang chủ - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Banner trang chủ</h1>
          <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span>/</span>
            <span>Banner</span>
          </div>
        </div>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="current_bg_image" value="<?= e($hero['background_image'] ?? '') ?>">
        <input type="hidden" name="current_bg_video" value="<?= e($hero['background_video'] ?? '') ?>">
        
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Nội dung Banner</h3>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label class="form-label">Label nhỏ (phía trên tiêu đề)</label>
              <input type="text" name="label" class="form-input" value="<?= e($hero['label'] ?? '') ?>" placeholder="VD: Moments To Memories">
            </div>
            
            <div class="form-group">
              <label class="form-label">Tiêu đề chính</label>
              <input type="text" name="title" class="form-input" value="<?= e($hero['title'] ?? '') ?>" placeholder="VD: Kể câu chuyện bằng cảm xúc">
              <p class="form-hint">Dùng &lt;span class="text-accent"&gt;text&lt;/span&gt; để tô màu chữ</p>
            </div>
            
            <div class="form-group">
              <label class="form-label">Mô tả</label>
              <textarea name="subtitle" class="form-input" rows="3"><?= e($hero['subtitle'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
        
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Hình nền / Video nền</h3>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label class="form-label">Hình nền</label>
              <label class="file-upload">
                <input type="file" name="background_image" accept="image/*">
                <div class="file-upload-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                  </svg>
                </div>
                <div class="file-upload-text">Chọn hình nền</div>
                <div class="file-upload-hint">PNG, JPG - Kích thước khuyến nghị: 1920x1080</div>
              </label>
              <?php if (!empty($hero['background_image'])): ?>
                <div class="file-preview">
                  <img src="<?= imageUrl($hero['background_image']) ?>" alt="">
                  <div class="file-preview-info">
                    <div class="file-preview-name">Hình nền hiện tại</div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
            
            <div class="form-group">
              <label class="form-label">Video nền (tùy chọn)</label>
              <label class="file-upload">
                <input type="file" name="background_video" accept="video/mp4">
                <div class="file-upload-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="23 7 16 12 23 17 23 7"/>
                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                  </svg>
                </div>
                <div class="file-upload-text">Chọn video nền</div>
                <div class="file-upload-hint">MP4 - Tối đa 20MB</div>
              </label>
              <?php if (!empty($hero['background_video'])): ?>
                <div class="file-preview">
                  <video width="100" height="60" style="border-radius: 6px;">
                    <source src="<?= imageUrl($hero['background_video']) ?>" type="video/mp4">
                  </video>
                  <div class="file-preview-info">
                    <div class="file-preview-name">Video nền hiện tại</div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <div style="display: flex; gap: 10px;">
          <button type="submit" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/>
              <polyline points="7 3 7 8 15 8"/>
            </svg>
            Lưu thay đổi
          </button>
          <a href="index.php" class="btn btn-secondary">Hủy</a>
        </div>
      </form>
    </main>
  </div>
</body>
</html>
