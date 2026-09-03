<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($action === 'delete' && $id) {
    foreach ($data['process'] as $key => $item) {
        if ($item['id'] == $id) {
            unset($data['process'][$key]);
            break;
        }
    }
    $data['process'] = array_values($data['process']);
    saveData($data);
    setFlash('success', 'Đã xóa!');
    header('Location: process.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemData = [
        'id' => $_POST['id'] ?: time(),
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'image' => $_POST['current_image'] ?? ''
    ];
    
    if (!empty($_FILES['image']['name'])) {
        $uploaded = uploadFile($_FILES['image']);
        if ($uploaded) {
            $itemData['image'] = $uploaded;
        }
    }
    
    $found = false;
    foreach ($data['process'] as $key => $item) {
        if ($item['id'] == $itemData['id']) {
            $data['process'][$key] = $itemData;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $data['process'][] = $itemData;
    }
    
    saveData($data);
    setFlash('success', $found ? 'Đã cập nhật!' : 'Đã thêm mới!');
    header('Location: process.php');
    exit;
}

$editItem = null;
if ($action === 'edit' && $id) {
    foreach ($data['process'] as $item) {
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
  <title>Quy trình - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Quy trình làm việc</h1>
          <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span>/</span>
            <span>Quy trình</span>
          </div>
        </div>
        <?php if ($action === 'list'): ?>
          <a href="?action=add" class="btn btn-primary">+ Thêm bước</a>
        <?php endif; ?>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <?php if ($action === 'list'): ?>
        <div class="card">
          <div class="card-body">
            <?php if (empty($data['process'])): ?>
              <p style="text-align: center; color: #64748b; padding: 40px;">Chưa có bước nào.</p>
            <?php else: ?>
              <?php foreach ($data['process'] as $index => $item): ?>
                <div class="list-item">
                  <div style="width: 40px; height: 40px; background: #4f46e5; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    <?= $index + 1 ?>
                  </div>
                  <div class="list-item-content">
                    <div class="list-item-title"><?= e($item['title']) ?></div>
                    <div class="list-item-subtitle"><?= e(mb_substr($item['description'] ?? '', 0, 60)) ?>...</div>
                  </div>
                  <div class="list-item-actions">
                    <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">Sửa</a>
                    <a href="?action=delete&id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa?')">Xóa</a>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= e($editItem['id'] ?? '') ?>">
          <input type="hidden" name="current_image" value="<?= e($editItem['image'] ?? '') ?>">
          
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><?= $editItem ? 'Sửa bước' : 'Thêm bước mới' ?></h3>
            </div>
            <div class="card-body">
              <div class="form-group">
                <label class="form-label">Tiêu đề <span class="required">*</span></label>
                <input type="text" name="title" class="form-input" value="<?= e($editItem['title'] ?? '') ?>" required>
              </div>
              
              <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-input" rows="3"><?= e($editItem['description'] ?? '') ?></textarea>
              </div>
              
              <div class="form-group">
                <label class="form-label">Hình minh họa (tùy chọn)</label>
                <label class="file-upload">
                  <input type="file" name="image" accept="image/*">
                  <div class="file-upload-text">Chọn hình</div>
                </label>
                <?php if (!empty($editItem['image'])): ?>
                  <div class="file-preview">
                    <img src="<?= imageUrl($editItem['image']) ?>" alt="">
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          
          <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><?= $editItem ? 'Cập nhật' : 'Thêm mới' ?></button>
            <a href="process.php" class="btn btn-secondary">Hủy</a>
          </div>
        </form>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
