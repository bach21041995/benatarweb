<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Xử lý xóa
if ($action === 'delete' && $id) {
    foreach ($data['services'] as $key => $item) {
        if ($item['id'] == $id) {
            if (!empty($item['image']) && strpos($item['image'], 'uploads/') === 0) {
                deleteFile($item['image']);
            }
            unset($data['services'][$key]);
            break;
        }
    }
    $data['services'] = array_values($data['services']);
    saveData($data);
    setFlash('success', 'Đã xóa dịch vụ!');
    header('Location: services.php');
    exit;
}

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemData = [
        'id' => $_POST['id'] ?: time(),
        'label' => $_POST['label'] ?? '',
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'link' => $_POST['link'] ?? '#',
        'image' => $_POST['current_image'] ?? ''
    ];
    
    if (!empty($_FILES['image']['name'])) {
        $uploaded = uploadFile($_FILES['image']);
        if ($uploaded) {
            if (!empty($itemData['image']) && strpos($itemData['image'], 'uploads/') === 0) {
                deleteFile($itemData['image']);
            }
            $itemData['image'] = $uploaded;
        }
    }
    
    $found = false;
    foreach ($data['services'] as $key => $item) {
        if ($item['id'] == $itemData['id']) {
            $data['services'][$key] = $itemData;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $data['services'][] = $itemData;
    }
    
    saveData($data);
    setFlash('success', $found ? 'Đã cập nhật!' : 'Đã thêm mới!');
    header('Location: services.php');
    exit;
}

$editItem = null;
if ($action === 'edit' && $id) {
    foreach ($data['services'] as $item) {
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
  <title>Dịch vụ - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Quản lý dịch vụ</h1>
          <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span>/</span>
            <span>Dịch vụ</span>
          </div>
        </div>
        <?php if ($action === 'list'): ?>
          <a href="?action=add" class="btn btn-primary">+ Thêm dịch vụ</a>
        <?php endif; ?>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <?php if ($action === 'list'): ?>
        <div class="card">
          <div class="card-body">
            <?php if (empty($data['services'])): ?>
              <p style="text-align: center; color: #64748b; padding: 40px;">Chưa có dịch vụ nào.</p>
            <?php else: ?>
              <?php foreach ($data['services'] as $item): ?>
                <div class="list-item">
                  <img src="<?= imageUrl($item['image'] ?? '', '60x60') ?>" alt="" class="list-item-image">
                  <div class="list-item-content">
                    <div class="list-item-title"><?= e($item['title']) ?></div>
                    <div class="list-item-subtitle"><?= e($item['label'] ?? '') ?></div>
                  </div>
                  <div class="list-item-actions">
                    <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">Sửa</a>
                    <a href="?action=delete&id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
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
              <h3 class="card-title"><?= $editItem ? 'Sửa dịch vụ' : 'Thêm dịch vụ mới' ?></h3>
            </div>
            <div class="card-body">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Label (nhãn nhỏ)</label>
                  <input type="text" name="label" class="form-input" value="<?= e($editItem['label'] ?? '') ?>" placeholder="VD: Films, Photography">
                </div>
                <div class="form-group">
                  <label class="form-label">Tên dịch vụ <span class="required">*</span></label>
                  <input type="text" name="title" class="form-input" value="<?= e($editItem['title'] ?? '') ?>" required>
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-input" rows="3"><?= e($editItem['description'] ?? '') ?></textarea>
              </div>
              
              <div class="form-group">
                <label class="form-label">Link (để # nếu không có)</label>
                <input type="text" name="link" class="form-input" value="<?= e($editItem['link'] ?? '#') ?>">
              </div>
              
              <div class="form-group">
                <label class="form-label">Hình ảnh</label>
                <label class="file-upload">
                  <input type="file" name="image" accept="image/*">
                  <div class="file-upload-text">Chọn hình ảnh</div>
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
            <a href="services.php" class="btn btn-secondary">Hủy</a>
          </div>
        </form>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
