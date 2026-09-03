<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($action === 'delete' && $id) {
    foreach ($data['clients'] as $key => $item) {
        if ($item['id'] == $id) {
            if (!empty($item['logo']) && strpos($item['logo'], 'uploads/') === 0) {
                deleteFile($item['logo']);
            }
            unset($data['clients'][$key]);
            break;
        }
    }
    $data['clients'] = array_values($data['clients']);
    saveData($data);
    setFlash('success', 'Đã xóa!');
    header('Location: clients.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemData = [
        'id' => $_POST['id'] ?: time(),
        'name' => $_POST['name'] ?? '',
        'logo' => $_POST['current_logo'] ?? ''
    ];
    
    if (!empty($_FILES['logo']['name'])) {
        $uploaded = uploadFile($_FILES['logo']);
        if ($uploaded) {
            if (!empty($itemData['logo']) && strpos($itemData['logo'], 'uploads/') === 0) {
                deleteFile($itemData['logo']);
            }
            $itemData['logo'] = $uploaded;
        }
    }
    
    $found = false;
    foreach ($data['clients'] as $key => $item) {
        if ($item['id'] == $itemData['id']) {
            $data['clients'][$key] = $itemData;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $data['clients'][] = $itemData;
    }
    
    saveData($data);
    setFlash('success', $found ? 'Đã cập nhật!' : 'Đã thêm mới!');
    header('Location: clients.php');
    exit;
}

$editItem = null;
if ($action === 'edit' && $id) {
    foreach ($data['clients'] as $item) {
        if ($item['id'] == $id) {
            $editItem = $item;
            break;
        }
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Khách hàng - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Khách hàng tiêu biểu</h1>
          <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span>/</span>
            <span>Khách hàng</span>
          </div>
        </div>
        <?php if ($action === 'list'): ?>
          <a href="?action=add" class="btn btn-primary">+ Thêm khách hàng</a>
        <?php endif; ?>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <?php if ($action === 'list'): ?>
        <div class="card">
          <div class="card-body">
            <?php if (empty($data['clients'])): ?>
              <p style="text-align: center; color: #64748b; padding: 40px;">Chưa có khách hàng nào.</p>
            <?php else: ?>
              <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                <?php foreach ($data['clients'] as $item): ?>
                  <div style="background: #f8fafc; border-radius: 8px; padding: 20px; text-align: center;">
                    <img src="<?= imageUrl($item['logo'] ?? '', '150x60') ?>" alt="<?= e($item['name']) ?>" style="max-height: 60px; margin-bottom: 10px;">
                    <div style="font-size: 14px; color: #64748b;"><?= e($item['name']) ?></div>
                    <div style="margin-top: 10px; display: flex; gap: 5px; justify-content: center;">
                      <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">Sửa</a>
                      <a href="?action=delete&id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa?')">Xóa</a>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= e($editItem['id'] ?? '') ?>">
          <input type="hidden" name="current_logo" value="<?= e($editItem['logo'] ?? '') ?>">
          
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><?= $editItem ? 'Sửa khách hàng' : 'Thêm khách hàng mới' ?></h3>
            </div>
            <div class="card-body">
              <div class="form-group">
                <label class="form-label">Tên khách hàng <span class="required">*</span></label>
                <input type="text" name="name" class="form-input" value="<?= e($editItem['name'] ?? '') ?>" required>
              </div>
              
              <div class="form-group">
                <label class="form-label">Logo</label>
                <label class="file-upload">
                  <input type="file" name="logo" accept="image/*">
                  <div class="file-upload-text">Chọn logo</div>
                  <div class="file-upload-hint">PNG trong suốt khuyến nghị</div>
                </label>
                <?php if (!empty($editItem['logo'])): ?>
                  <div class="file-preview">
                    <img src="<?= imageUrl($editItem['logo']) ?>" alt="">
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          
          <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><?= $editItem ? 'Cập nhật' : 'Thêm mới' ?></button>
            <a href="clients.php" class="btn btn-secondary">Hủy</a>
          </div>
        </form>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
