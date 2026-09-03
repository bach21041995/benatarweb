<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();

// Khởi tạo CTA mặc định nếu chưa có
if (!isset($data['cta']) || empty($data['cta'])) {
    $data['cta'] = [
        'title_vi' => 'Sẵn sàng kể <span class="text-accent">câu chuyện</span> của bạn?',
        'title_en' => 'Ready to tell <span class="text-accent">your story</span>?',
        'subtitle_vi' => 'Hãy liên hệ với chúng tôi để bắt đầu hành trình sáng tạo',
        'subtitle_en' => 'Contact us to start your creative journey',
        'button_vi' => 'Liên hệ ngay',
        'button_en' => 'Contact Now',
        'background_image' => '',
    ];
    saveData($data);
}

$cta = $data['cta'];

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['cta'] = [
        'title_vi' => $_POST['title_vi'] ?? '',
        'title_en' => $_POST['title_en'] ?? '',
        'subtitle_vi' => $_POST['subtitle_vi'] ?? '',
        'subtitle_en' => $_POST['subtitle_en'] ?? '',
        'button_vi' => $_POST['button_vi'] ?? '',
        'button_en' => $_POST['button_en'] ?? '',
        'background_image' => $cta['background_image'] ?? '',
    ];
    
    // Upload background image
    if (!empty($_FILES['background_image']['name'])) {
        $uploaded = uploadFile($_FILES['background_image'], 'uploads');
        if ($uploaded) {
            // Xóa ảnh cũ nếu có
            if (!empty($cta['background_image'])) {
                deleteFile($cta['background_image']);
            }
            $data['cta']['background_image'] = $uploaded;
        }
    }
    
    // Xóa ảnh nếu checkbox được chọn
    if (isset($_POST['remove_background'])) {
        if (!empty($data['cta']['background_image'])) {
            deleteFile($data['cta']['background_image']);
        }
        $data['cta']['background_image'] = '';
    }
    
    saveData($data);
    setFlash('success', 'Đã cập nhật CTA!');
    header('Location: cta.php');
    exit;
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chỉnh sửa CTA - Admin</title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .preview-box { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-radius: 12px; padding: 40px; text-align: center; color: #fff; margin-bottom: 20px; position: relative; overflow: hidden; }
    .preview-box.has-bg { background-size: cover; background-position: center; }
    .preview-box::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,0.6); }
    .preview-box * { position: relative; z-index: 1; }
    .preview-title { font-size: 28px; font-weight: 700; margin-bottom: 10px; }
    .preview-subtitle { font-size: 16px; opacity: 0.9; margin-bottom: 20px; }
    .preview-btn { display: inline-block; padding: 12px 30px; background: var(--primary, #8B1E2D); color: #fff; border-radius: 6px; font-weight: 600; }
    .text-accent { color: var(--secondary, #C9A24A); }
    
    .lang-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
    .lang-tab { padding: 10px 20px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.2s; }
    .lang-tab.active { background: var(--primary, #8B1E2D); color: #fff; border-color: var(--primary, #8B1E2D); }
    .lang-content { display: none; }
    .lang-content.active { display: block; }
    
    .image-upload-box { border: 2px dashed #e2e8f0; border-radius: 8px; padding: 30px; text-align: center; transition: all 0.2s; }
    .image-upload-box:hover { border-color: var(--primary, #8B1E2D); }
    .current-image { max-width: 100%; max-height: 200px; border-radius: 8px; margin-bottom: 15px; }
  </style>
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <h1 class="page-title">Chỉnh sửa CTA</h1>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <!-- Preview -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Xem trước</h3>
        </div>
        <div class="card-body">
          <div class="preview-box <?= !empty($cta['background_image']) ? 'has-bg' : '' ?>" 
               style="<?= !empty($cta['background_image']) ? 'background-image: url(' . imageUrl($cta['background_image']) . ')' : '' ?>">
            <h2 class="preview-title"><?= $cta['title_vi'] ?? '' ?></h2>
            <p class="preview-subtitle"><?= e($cta['subtitle_vi'] ?? '') ?></p>
            <span class="preview-btn"><?= e($cta['button_vi'] ?? 'Liên hệ ngay') ?></span>
          </div>
        </div>
      </div>
      
      <form method="POST" enctype="multipart/form-data">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Nội dung CTA</h3>
          </div>
          <div class="card-body">
            <div class="lang-tabs">
              <div class="lang-tab active" onclick="switchTab('vi')">🇻🇳 Tiếng Việt</div>
              <div class="lang-tab" onclick="switchTab('en')">🇬🇧 English</div>
            </div>
            
            <!-- Vietnamese -->
            <div class="lang-content active" id="tab-vi">
              <div class="form-group">
                <label class="form-label">Tiêu đề chính</label>
                <input type="text" name="title_vi" class="form-control" 
                       value="<?= e($cta['title_vi'] ?? '') ?>" 
                       placeholder="Sẵn sàng kể câu chuyện của bạn?">
                <small style="color:#64748b;">Dùng <code>&lt;span class="text-accent"&gt;...&lt;/span&gt;</code> để highlight chữ vàng</small>
              </div>
              
              <div class="form-group">
                <label class="form-label">Phụ đề</label>
                <input type="text" name="subtitle_vi" class="form-control" 
                       value="<?= e($cta['subtitle_vi'] ?? '') ?>" 
                       placeholder="Hãy liên hệ với chúng tôi...">
              </div>
              
              <div class="form-group">
                <label class="form-label">Nút bấm</label>
                <input type="text" name="button_vi" class="form-control" 
                       value="<?= e($cta['button_vi'] ?? '') ?>" 
                       placeholder="Liên hệ ngay">
              </div>
            </div>
            
            <!-- English -->
            <div class="lang-content" id="tab-en">
              <div class="form-group">
                <label class="form-label">Main Title</label>
                <input type="text" name="title_en" class="form-control" 
                       value="<?= e($cta['title_en'] ?? '') ?>" 
                       placeholder="Ready to tell your story?">
              </div>
              
              <div class="form-group">
                <label class="form-label">Subtitle</label>
                <input type="text" name="subtitle_en" class="form-control" 
                       value="<?= e($cta['subtitle_en'] ?? '') ?>" 
                       placeholder="Contact us to start...">
              </div>
              
              <div class="form-group">
                <label class="form-label">Button Text</label>
                <input type="text" name="button_en" class="form-control" 
                       value="<?= e($cta['button_en'] ?? '') ?>" 
                       placeholder="Contact Now">
              </div>
            </div>
          </div>
        </div>
        
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Hình nền (tùy chọn)</h3>
          </div>
          <div class="card-body">
            <div class="image-upload-box">
              <?php if (!empty($cta['background_image'])): ?>
                <img src="<?= imageUrl($cta['background_image']) ?>" alt="Background" class="current-image">
                <br>
                <label style="cursor:pointer;">
                  <input type="checkbox" name="remove_background"> Xóa hình nền
                </label>
                <br><br>
              <?php endif; ?>
              <input type="file" name="background_image" accept="image/*">
              <p style="color:#64748b;margin-top:10px;font-size:13px;">Khuyến nghị: 1920x600px, tối đa 2MB</p>
            </div>
          </div>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </div>
      </form>
    </main>
  </div>
  
  <script>
  function switchTab(lang) {
    document.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.lang-content').forEach(c => c.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById('tab-' + lang).classList.add('active');
  }
  </script>
</body>
</html>
