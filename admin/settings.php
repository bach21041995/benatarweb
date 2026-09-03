<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['settings'] = [
        'primary_color' => $_POST['primary_color'] ?? '#8B1E2D',
        'secondary_color' => $_POST['secondary_color'] ?? '#C9A24A',
        'logo' => $_POST['current_logo'] ?? '',
        'favicon' => $_POST['current_favicon'] ?? ''
    ];
    
    $data['cta'] = [
        'title' => $_POST['cta_title'] ?? '',
        'subtitle' => $_POST['cta_subtitle'] ?? ''
    ];
    
    if (!empty($_FILES['logo']['name'])) {
        $uploaded = uploadFile($_FILES['logo']);
        if ($uploaded) {
            $data['settings']['logo'] = $uploaded;
        }
    }
    
    if (!empty($_FILES['favicon']['name'])) {
        $uploaded = uploadFile($_FILES['favicon']);
        if ($uploaded) {
            $data['settings']['favicon'] = $uploaded;
        }
    }
    
    if (saveData($data)) {
        setFlash('success', 'Đã lưu cài đặt!');
    } else {
        setFlash('danger', 'Có lỗi xảy ra!');
    }
    
    header('Location: settings.php');
    exit;
}

$settings = $data['settings'] ?? [];
$cta = $data['cta'] ?? [];
$flash = getFlash();

// Màu mặc định
$primaryColor = $settings['primary_color'] ?? '#8B1E2D';
$secondaryColor = $settings['secondary_color'] ?? '#C9A24A';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cài đặt - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .color-group {
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
    }
    .color-item {
      flex: 1;
      min-width: 200px;
    }
    .color-picker-box {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
    }
    .color-picker-box input[type="color"] {
      width: 60px;
      height: 60px;
      border: none;
      cursor: pointer;
      border-radius: 8px;
      overflow: hidden;
    }
    .color-picker-box input[type="color"]::-webkit-color-swatch-wrapper {
      padding: 0;
    }
    .color-picker-box input[type="color"]::-webkit-color-swatch {
      border: 2px solid #ddd;
      border-radius: 6px;
    }
    .color-info {
      flex: 1;
    }
    .color-info .color-label {
      font-weight: 600;
      margin-bottom: 5px;
      color: #333;
    }
    .color-info .color-hex {
      font-family: monospace;
      font-size: 14px;
      padding: 5px 10px;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 4px;
      color: #666;
    }
    .color-preview {
      display: flex;
      gap: 10px;
      margin-top: 20px;
      padding: 20px;
      background: #1a1a1a;
      border-radius: 8px;
    }
    .preview-btn {
      padding: 12px 24px;
      border: none;
      border-radius: 4px;
      font-weight: 600;
      font-size: 12px;
      letter-spacing: 1px;
      text-transform: uppercase;
      cursor: pointer;
    }
    .preview-primary {
      background: var(--preview-primary);
      color: #fff;
    }
    .preview-secondary {
      background: transparent;
      border: 2px solid var(--preview-secondary);
      color: var(--preview-secondary);
    }
    .preview-text {
      color: var(--preview-secondary);
      font-size: 14px;
      display: flex;
      align-items: center;
      margin-left: 20px;
    }
  </style>
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Cài đặt chung</h1>
          <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span>/</span>
            <span>Cài đặt</span>
          </div>
        </div>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="current_logo" value="<?= e($settings['logo'] ?? '') ?>">
        <input type="hidden" name="current_favicon" value="<?= e($settings['favicon'] ?? '') ?>">
        
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">🎨 Màu sắc giao diện</h3>
          </div>
          <div class="card-body">
            <div class="color-group">
              <div class="color-item">
                <div class="color-picker-box">
                  <input type="color" name="primary_color" id="primaryColor" value="<?= e($primaryColor) ?>">
                  <div class="color-info">
                    <div class="color-label">Primary Color</div>
                    <div class="color-hex" id="primaryHex"><?= e($primaryColor) ?></div>
                  </div>
                </div>
                <p class="form-hint" style="margin-top: 10px;">Màu chính: nút bấm, tiêu đề highlight, CTA</p>
              </div>
              
              <div class="color-item">
                <div class="color-picker-box">
                  <input type="color" name="secondary_color" id="secondaryColor" value="<?= e($secondaryColor) ?>">
                  <div class="color-info">
                    <div class="color-label">Secondary Color</div>
                    <div class="color-hex" id="secondaryHex"><?= e($secondaryColor) ?></div>
                  </div>
                </div>
                <p class="form-hint" style="margin-top: 10px;">Màu phụ: label, border, icon, accent text</p>
              </div>
            </div>
            
            <div class="color-preview" id="colorPreview" style="--preview-primary: <?= e($primaryColor) ?>; --preview-secondary: <?= e($secondaryColor) ?>;">
              <button type="button" class="preview-btn preview-primary">Kết nối ngay</button>
              <button type="button" class="preview-btn preview-secondary">Khám phá</button>
              <span class="preview-text">★ Documentary Cinematic</span>
            </div>
          </div>
        </div>
        
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">🖼️ Logo & Favicon</h3>
          </div>
          <div class="card-body">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Logo</label>
                <label class="file-upload">
                  <input type="file" name="logo" accept="image/*">
                  <div class="file-upload-text">Chọn logo</div>
                  <div class="file-upload-hint">PNG trong suốt, cao ~40px</div>
                </label>
                <?php if (!empty($settings['logo'])): ?>
                  <div class="file-preview">
                    <img src="<?= imageUrl($settings['logo']) ?>" alt="" style="max-height: 40px; width: auto;">
                    <div class="file-preview-info">
                      <div class="file-preview-name">Logo hiện tại</div>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="form-group">
                <label class="form-label">Favicon</label>
                <label class="file-upload">
                  <input type="file" name="favicon" accept="image/*">
                  <div class="file-upload-text">Chọn favicon</div>
                  <div class="file-upload-hint">ICO hoặc PNG 32x32</div>
                </label>
                <?php if (!empty($settings['favicon'])): ?>
                  <div class="file-preview">
                    <img src="<?= imageUrl($settings['favicon']) ?>" alt="" style="width: 32px; height: 32px;">
                    <div class="file-preview-info">
                      <div class="file-preview-name">Favicon hiện tại</div>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">📢 Phần kêu gọi hành động (CTA)</h3>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label class="form-label">Tiêu đề CTA</label>
              <input type="text" name="cta_title" class="form-input" value="<?= e($cta['title'] ?? '') ?>" placeholder="Sẵn sàng kể câu chuyện của bạn?">
              <p class="form-hint">Dùng &lt;span class="text-accent"&gt;text&lt;/span&gt; để tô màu primary</p>
            </div>
            
            <div class="form-group">
              <label class="form-label">Mô tả CTA</label>
              <input type="text" name="cta_subtitle" class="form-input" value="<?= e($cta['subtitle'] ?? '') ?>" placeholder="Hãy liên hệ với chúng tôi để bắt đầu hành trình sáng tạo">
            </div>
          </div>
        </div>
        
        <div style="display: flex; gap: 10px;">
          <button type="submit" class="btn btn-primary">💾 Lưu cài đặt</button>
          <a href="index.php" class="btn btn-secondary">Hủy</a>
        </div>
      </form>
    </main>
  </div>
  
  <script>
    // Sync color pickers with hex display and preview
    const primaryInput = document.getElementById('primaryColor');
    const secondaryInput = document.getElementById('secondaryColor');
    const primaryHex = document.getElementById('primaryHex');
    const secondaryHex = document.getElementById('secondaryHex');
    const preview = document.getElementById('colorPreview');
    
    primaryInput.addEventListener('input', function() {
      primaryHex.textContent = this.value.toUpperCase();
      preview.style.setProperty('--preview-primary', this.value);
    });
    
    secondaryInput.addEventListener('input', function() {
      secondaryHex.textContent = this.value.toUpperCase();
      preview.style.setProperty('--preview-secondary', this.value);
    });
  </script>
</body>
</html>
