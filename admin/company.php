<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['company'] = [
        'name' => $_POST['name'] ?? '',
        'tagline' => $_POST['tagline'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'address' => $_POST['address'] ?? '',
        'ceo' => $_POST['ceo'] ?? '',
        'tax_code' => $_POST['tax_code'] ?? '',
        'facebook' => $_POST['facebook'] ?? '',
        'youtube' => $_POST['youtube'] ?? '',
        'tiktok' => $_POST['tiktok'] ?? '',
        'zalo' => $_POST['zalo'] ?? ''
    ];
    
    if (saveData($data)) {
        setFlash('success', 'Đã cập nhật thông tin công ty thành công!');
    } else {
        setFlash('danger', 'Có lỗi xảy ra, vui lòng thử lại!');
    }
    
    header('Location: company.php');
    exit;
}

$company = $data['company'] ?? [];
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thông tin công ty - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Thông tin công ty</h1>
          <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span>/</span>
            <span>Thông tin công ty</span>
          </div>
        </div>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <form method="POST">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin cơ bản</h3>
          </div>
          <div class="card-body">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Tên công ty <span class="required">*</span></label>
                <input type="text" name="name" class="form-input" value="<?= e($company['name'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Slogan / Tagline</label>
                <input type="text" name="tagline" class="form-input" value="<?= e($company['tagline'] ?? '') ?>">
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="phone" class="form-input" value="<?= e($company['phone'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="<?= e($company['email'] ?? '') ?>">
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label">Địa chỉ</label>
              <input type="text" name="address" class="form-input" value="<?= e($company['address'] ?? '') ?>">
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Giám đốc / CEO</label>
                <input type="text" name="ceo" class="form-input" value="<?= e($company['ceo'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Mã số thuế</label>
                <input type="text" name="tax_code" class="form-input" value="<?= e($company['tax_code'] ?? '') ?>">
              </div>
            </div>
          </div>
        </div>
        
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Mạng xã hội</h3>
          </div>
          <div class="card-body">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Facebook</label>
                <input type="url" name="facebook" class="form-input" value="<?= e($company['facebook'] ?? '') ?>" placeholder="https://facebook.com/...">
              </div>
              <div class="form-group">
                <label class="form-label">YouTube</label>
                <input type="url" name="youtube" class="form-input" value="<?= e($company['youtube'] ?? '') ?>" placeholder="https://youtube.com/...">
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">TikTok</label>
                <input type="url" name="tiktok" class="form-input" value="<?= e($company['tiktok'] ?? '') ?>" placeholder="https://tiktok.com/...">
              </div>
              <div class="form-group">
                <label class="form-label">Zalo (số điện thoại)</label>
                <input type="text" name="zalo" class="form-input" value="<?= e($company['zalo'] ?? '') ?>" placeholder="0912345678">
              </div>
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
