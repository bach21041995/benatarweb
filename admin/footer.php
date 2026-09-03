<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();

// Khởi tạo footer mặc định nếu chưa có
if (!isset($data['footer'])) {
    $data['footer'] = [
        'tagline_vi' => 'Kể chuyện thương hiệu bằng hình ảnh',
        'tagline_en' => 'Brand storytelling through visuals',
        'description_vi' => 'Benatar Corporation là đơn vị tiên phong trong lĩnh vực sản xuất phim quảng cáo và doanh nghiệp, mang đến những câu chuyện thương hiệu đầy cảm xúc.',
        'description_en' => 'Benatar Corporation is a pioneer in advertising and corporate film production, delivering emotionally compelling brand stories.',
        'copyright_vi' => '© 2024 Benatar Corporation. Đã đăng ký bản quyền.',
        'copyright_en' => '© 2024 Benatar Corporation. All rights reserved.',
        'nav_links' => [
            ['title_vi' => 'Về chúng tôi', 'title_en' => 'About Us', 'url' => '#about'],
            ['title_vi' => 'Dịch vụ', 'title_en' => 'Services', 'url' => '#services'],
            ['title_vi' => 'Tư liệu', 'title_en' => 'Gallery', 'url' => '#gallery'],
            ['title_vi' => 'Liên hệ', 'title_en' => 'Contact', 'url' => '#contact'],
        ],
    ];
    saveData($data);
}

$footer = $data['footer'];
$company = $data['company'] ?? [];

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update';
    
    if ($action === 'update') {
        $data['footer'] = [
            'tagline_vi' => $_POST['tagline_vi'] ?? '',
            'tagline_en' => $_POST['tagline_en'] ?? '',
            'description_vi' => $_POST['description_vi'] ?? '',
            'description_en' => $_POST['description_en'] ?? '',
            'copyright_vi' => $_POST['copyright_vi'] ?? '',
            'copyright_en' => $_POST['copyright_en'] ?? '',
            'nav_links' => $footer['nav_links'] ?? [],
        ];
        setFlash('success', 'Đã cập nhật Footer!');
    }
    
    // Thêm link mới
    if ($action === 'add_link') {
        $data['footer']['nav_links'][] = [
            'title_vi' => $_POST['link_title_vi'] ?? '',
            'title_en' => $_POST['link_title_en'] ?? '',
            'url' => $_POST['link_url'] ?? '#',
        ];
        setFlash('success', 'Đã thêm link!');
    }
    
    // Xóa link
    if ($action === 'delete_link' && isset($_POST['link_index'])) {
        $index = (int)$_POST['link_index'];
        if (isset($data['footer']['nav_links'][$index])) {
            array_splice($data['footer']['nav_links'], $index, 1);
            setFlash('success', 'Đã xóa link!');
        }
    }
    
    // Cập nhật link
    if ($action === 'update_links') {
        $newLinks = [];
        $titles_vi = $_POST['link_titles_vi'] ?? [];
        $titles_en = $_POST['link_titles_en'] ?? [];
        $urls = $_POST['link_urls'] ?? [];
        
        foreach ($titles_vi as $i => $title_vi) {
            if (!empty($title_vi) || !empty($titles_en[$i])) {
                $newLinks[] = [
                    'title_vi' => $title_vi,
                    'title_en' => $titles_en[$i] ?? '',
                    'url' => $urls[$i] ?? '#',
                ];
            }
        }
        $data['footer']['nav_links'] = $newLinks;
        setFlash('success', 'Đã cập nhật links!');
    }
    
    saveData($data);
    header('Location: footer.php');
    exit;
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chỉnh sửa Footer - Admin</title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .footer-preview { background: #0a0a0a; color: #fff; border-radius: 12px; padding: 40px 30px 20px; }
    .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 40px; margin-bottom: 30px; }
    .footer-brand h3 { font-size: 24px; margin-bottom: 5px; }
    .footer-brand .tagline { color: var(--secondary, #C9A24A); font-size: 14px; margin-bottom: 15px; }
    .footer-brand p { color: #9ca3af; font-size: 14px; line-height: 1.6; }
    .footer-links h4 { font-size: 16px; margin-bottom: 15px; color: #fff; }
    .footer-links a { display: block; color: #9ca3af; font-size: 14px; margin-bottom: 10px; text-decoration: none; }
    .footer-contact a { display: flex; align-items: center; gap: 10px; color: #9ca3af; font-size: 14px; margin-bottom: 10px; text-decoration: none; }
    .footer-bottom { border-top: 1px solid #1f2937; padding-top: 20px; text-align: center; color: #6b7280; font-size: 13px; }
    
    .lang-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
    .lang-tab { padding: 10px 20px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.2s; }
    .lang-tab.active { background: var(--primary, #8B1E2D); color: #fff; border-color: var(--primary, #8B1E2D); }
    .lang-content { display: none; }
    .lang-content.active { display: block; }
    
    .link-item { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; padding: 15px; background: #f8fafc; border-radius: 8px; }
    .link-item input { flex: 1; }
    .link-item .btn-danger { padding: 8px 12px; }
    
    @media (max-width: 768px) {
      .footer-grid { grid-template-columns: 1fr; gap: 30px; }
    }
  </style>
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <h1 class="page-title">Chỉnh sửa Footer</h1>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <!-- Preview -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Xem trước</h3>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="footer-preview">
            <div class="footer-grid">
              <div class="footer-brand">
                <h3><?= e($company['name'] ?? 'Benatar Corporation') ?></h3>
                <p class="tagline"><?= e($footer['tagline_vi'] ?? '') ?></p>
                <p><?= e($footer['description_vi'] ?? '') ?></p>
              </div>
              <div class="footer-links">
                <h4>Liên kết</h4>
                <?php foreach ($footer['nav_links'] ?? [] as $link): ?>
                  <a href="<?= e($link['url']) ?>"><?= e($link['title_vi']) ?></a>
                <?php endforeach; ?>
              </div>
              <div class="footer-contact">
                <h4>Liên hệ</h4>
                <a href="#">📍 <?= e($company['address'] ?? '') ?></a>
                <a href="#">📞 <?= e($company['phone'] ?? '') ?></a>
                <a href="#">✉️ <?= e($company['email'] ?? '') ?></a>
              </div>
            </div>
            <div class="footer-bottom">
              <?= e($footer['copyright_vi'] ?? '') ?>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Content Form -->
      <form method="POST">
        <input type="hidden" name="action" value="update">
        
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Nội dung Footer</h3>
          </div>
          <div class="card-body">
            <div class="lang-tabs">
              <div class="lang-tab active" onclick="switchTab('vi')">🇻🇳 Tiếng Việt</div>
              <div class="lang-tab" onclick="switchTab('en')">🇬🇧 English</div>
            </div>
            
            <!-- Vietnamese -->
            <div class="lang-content active" id="tab-vi">
              <div class="form-group">
                <label class="form-label">Tagline (slogan ngắn)</label>
                <input type="text" name="tagline_vi" class="form-control" 
                       value="<?= e($footer['tagline_vi'] ?? '') ?>" 
                       placeholder="Kể chuyện thương hiệu bằng hình ảnh">
              </div>
              
              <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description_vi" class="form-control" rows="3" 
                          placeholder="Mô tả ngắn về công ty..."><?= e($footer['description_vi'] ?? '') ?></textarea>
              </div>
              
              <div class="form-group">
                <label class="form-label">Bản quyền</label>
                <input type="text" name="copyright_vi" class="form-control" 
                       value="<?= e($footer['copyright_vi'] ?? '') ?>" 
                       placeholder="© 2024 Company. Đã đăng ký bản quyền.">
              </div>
            </div>
            
            <!-- English -->
            <div class="lang-content" id="tab-en">
              <div class="form-group">
                <label class="form-label">Tagline</label>
                <input type="text" name="tagline_en" class="form-control" 
                       value="<?= e($footer['tagline_en'] ?? '') ?>" 
                       placeholder="Brand storytelling through visuals">
              </div>
              
              <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description_en" class="form-control" rows="3" 
                          placeholder="Short company description..."><?= e($footer['description_en'] ?? '') ?></textarea>
              </div>
              
              <div class="form-group">
                <label class="form-label">Copyright</label>
                <input type="text" name="copyright_en" class="form-control" 
                       value="<?= e($footer['copyright_en'] ?? '') ?>" 
                       placeholder="© 2024 Company. All rights reserved.">
              </div>
            </div>
          </div>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Lưu nội dung</button>
        </div>
      </form>
      
      <!-- Navigation Links -->
      <form method="POST">
        <input type="hidden" name="action" value="update_links">
        
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Links điều hướng</h3>
          </div>
          <div class="card-body">
            <div id="linksList">
              <?php foreach ($footer['nav_links'] ?? [] as $index => $link): ?>
                <div class="link-item">
                  <input type="text" name="link_titles_vi[]" class="form-control" 
                         value="<?= e($link['title_vi']) ?>" placeholder="Tiêu đề VI">
                  <input type="text" name="link_titles_en[]" class="form-control" 
                         value="<?= e($link['title_en']) ?>" placeholder="Title EN">
                  <input type="text" name="link_urls[]" class="form-control" 
                         value="<?= e($link['url']) ?>" placeholder="URL (#about)">
                  <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">×</button>
                </div>
              <?php endforeach; ?>
            </div>
            
            <button type="button" class="btn btn-secondary" onclick="addLink()" style="margin-top:15px;">
              + Thêm link
            </button>
          </div>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Lưu links</button>
        </div>
      </form>
      
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Thông tin liên hệ</h3>
        </div>
        <div class="card-body">
          <p style="color:#64748b;">Thông tin liên hệ (địa chỉ, điện thoại, email) được lấy từ <strong>Thông tin công ty</strong>.</p>
          <a href="company.php" class="btn btn-secondary">Chỉnh sửa thông tin công ty →</a>
        </div>
      </div>
    </main>
  </div>
  
  <script>
  function switchTab(lang) {
    document.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.lang-content').forEach(c => c.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById('tab-' + lang).classList.add('active');
  }
  
  function addLink() {
    const html = `
      <div class="link-item">
        <input type="text" name="link_titles_vi[]" class="form-control" placeholder="Tiêu đề VI">
        <input type="text" name="link_titles_en[]" class="form-control" placeholder="Title EN">
        <input type="text" name="link_urls[]" class="form-control" placeholder="URL (#about)">
        <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">×</button>
      </div>
    `;
    document.getElementById('linksList').insertAdjacentHTML('beforeend', html);
  }
  </script>
</body>
</html>
