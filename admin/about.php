<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paragraphsVi = array_filter(array_map('trim', explode("\n", isset($_POST['paragraphs_vi']) ? $_POST['paragraphs_vi'] : '')));
    $paragraphsEn = array_filter(array_map('trim', explode("\n", isset($_POST['paragraphs_en']) ? $_POST['paragraphs_en'] : '')));
    
    $data['about'] = array(
        'image' => isset($_POST['current_image']) ? $_POST['current_image'] : '',
        'title' => isset($_POST['title']) ? $_POST['title'] : '',
        'title_en' => isset($_POST['title_en']) ? $_POST['title_en'] : '',
        'paragraphs' => $paragraphsVi,
        'paragraphs_vi' => $paragraphsVi,
        'paragraphs_en' => $paragraphsEn,
        'quote' => isset($_POST['quote']) ? $_POST['quote'] : '',
        'quote_vi' => isset($_POST['quote']) ? $_POST['quote'] : '',
        'quote_en' => isset($_POST['quote_en']) ? $_POST['quote_en'] : ''
    );
    
    if (!empty($_FILES['image']['name'])) {
        $uploaded = uploadFile($_FILES['image']);
        if ($uploaded) {
            $data['about']['image'] = $uploaded;
        }
    }
    
    if (saveData($data)) {
        setFlash('success', 'Đã cập nhật thành công!');
    } else {
        setFlash('danger', 'Có lỗi xảy ra!');
    }
    
    header('Location: about.php');
    exit;
}

$about = isset($data['about']) ? $data['about'] : array();
$flash = getFlash();

// Lấy paragraphs
$paragraphsVi = isset($about['paragraphs_vi']) ? $about['paragraphs_vi'] : (isset($about['paragraphs']) ? $about['paragraphs'] : array());
$paragraphsEn = isset($about['paragraphs_en']) ? $about['paragraphs_en'] : array();
if (!is_array($paragraphsVi)) $paragraphsVi = array($paragraphsVi);
if (!is_array($paragraphsEn)) $paragraphsEn = array($paragraphsEn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Về chúng tôi - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .lang-tabs { display: flex; gap: 0; margin-bottom: 20px; }
    .lang-tab { padding: 12px 24px; border: 1px solid var(--border-color); background: var(--bg-tertiary); cursor: pointer; font-weight: 500; }
    .lang-tab:first-child { border-radius: 6px 0 0 6px; }
    .lang-tab:last-child { border-radius: 0 6px 6px 0; }
    .lang-tab.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
    .lang-panel { display: none; }
    .lang-panel.active { display: block; }
  </style>
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Về chúng tôi</h1>
          <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span>/</span>
            <span>Về chúng tôi</span>
          </div>
        </div>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="current_image" value="<?php echo e(isset($about['image']) ? $about['image'] : ''); ?>">
        
        <!-- Hình ảnh -->
        <div class="card" style="margin-bottom: 20px;">
          <div class="card-header">
            <h3 class="card-title">Hình ảnh</h3>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label class="file-upload">
                <input type="file" name="image" accept="image/*">
                <div class="file-upload-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                  </svg>
                </div>
                <div class="file-upload-text">Chọn hình ảnh</div>
              </label>
              <?php if (!empty($about['image'])): ?>
                <div class="file-preview">
                  <img src="<?php echo imageUrl($about['image']); ?>" alt="">
                  <div class="file-preview-info">
                    <div class="file-preview-name">Hình hiện tại</div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <!-- Nội dung song ngữ -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Nội dung</h3>
          </div>
          <div class="card-body">
            <div class="lang-tabs">
              <div class="lang-tab active" onclick="switchTab('vi')">Tiếng Việt</div>
              <div class="lang-tab" onclick="switchTab('en')">English</div>
            </div>
            
            <!-- Tiếng Việt -->
            <div class="lang-panel active" id="panel-vi">
              <div class="form-group">
                <label class="form-label">Tiêu đề (VN)</label>
                <input type="text" name="title" class="form-input" value="<?php echo e(isset($about['title']) ? $about['title'] : ''); ?>">
                <p class="form-hint">Dùng &lt;span class="text-accent"&gt;text&lt;/span&gt; để tô màu</p>
              </div>
              
              <div class="form-group">
                <label class="form-label">Nội dung (VN) - mỗi dòng là 1 đoạn văn</label>
                <textarea name="paragraphs_vi" class="form-input" rows="6"><?php echo e(implode("\n", $paragraphsVi)); ?></textarea>
              </div>
              
              <div class="form-group">
                <label class="form-label">Câu trích dẫn (VN)</label>
                <input type="text" name="quote" class="form-input" value="<?php echo e(isset($about['quote']) ? $about['quote'] : ''); ?>">
              </div>
            </div>
            
            <!-- English -->
            <div class="lang-panel" id="panel-en">
              <div class="form-group">
                <label class="form-label">Title (EN)</label>
                <input type="text" name="title_en" class="form-input" value="<?php echo e(isset($about['title_en']) ? $about['title_en'] : ''); ?>">
              </div>
              
              <div class="form-group">
                <label class="form-label">Content (EN) - each line is a paragraph</label>
                <textarea name="paragraphs_en" class="form-input" rows="6"><?php echo e(implode("\n", $paragraphsEn)); ?></textarea>
              </div>
              
              <div class="form-group">
                <label class="form-label">Quote (EN)</label>
                <input type="text" name="quote_en" class="form-input" value="<?php echo e(isset($about['quote_en']) ? $about['quote_en'] : ''); ?>">
              </div>
            </div>
          </div>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
          <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
          <a href="index.php" class="btn btn-secondary">Hủy</a>
        </div>
      </form>
    </main>
  </div>
  
  <script>
  function switchTab(lang) {
    // Tabs
    document.querySelectorAll('.lang-tab').forEach(function(tab) {
      tab.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Panels
    document.querySelectorAll('.lang-panel').forEach(function(panel) {
      panel.classList.remove('active');
    });
    document.getElementById('panel-' + lang).classList.add('active');
  }
  </script>
</body>
</html>
