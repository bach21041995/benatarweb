<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$icons = ['lightbulb', 'film', 'settings', 'users', 'camera', 'play'];

if ($action === 'delete' && $id) {
    foreach ($data['features'] as $key => $item) {
        if ($item['id'] == $id) {
            unset($data['features'][$key]);
            break;
        }
    }
    $data['features'] = array_values($data['features']);
    saveData($data);
    setFlash('success', 'Đã xóa!');
    header('Location: features.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemData = [
        'id' => $_POST['id'] ?: time(),
        'icon' => $_POST['icon'] ?? 'lightbulb',
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? ''
    ];
    
    $found = false;
    foreach ($data['features'] as $key => $item) {
        if ($item['id'] == $itemData['id']) {
            $data['features'][$key] = $itemData;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $data['features'][] = $itemData;
    }
    
    saveData($data);
    setFlash('success', $found ? 'Đã cập nhật!' : 'Đã thêm mới!');
    header('Location: features.php');
    exit;
}

$editItem = null;
if ($action === 'edit' && $id) {
    foreach ($data['features'] as $item) {
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
  <title>Điểm nổi bật - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Điểm nổi bật</h1>
          <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span>/</span>
            <span>Điểm nổi bật</span>
          </div>
        </div>
        <?php if ($action === 'list'): ?>
          <a href="?action=add" class="btn btn-primary">+ Thêm mới</a>
        <?php endif; ?>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <?php if ($action === 'list'): ?>
        <div class="card">
          <div class="card-body">
            <?php if (empty($data['features'])): ?>
              <p style="text-align: center; color: #64748b; padding: 40px;">Chưa có mục nào.</p>
            <?php else: ?>
              <?php foreach ($data['features'] as $item): ?>
                <div class="list-item">
                  <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <?= renderIcon($item['icon'] ?? 'lightbulb') ?>
                  </div>
                  <div class="list-item-content">
                    <div class="list-item-title"><?= e($item['title']) ?></div>
                    <div class="list-item-subtitle"><?= e(mb_substr($item['description'] ?? '', 0, 50)) ?>...</div>
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
        <form method="POST">
          <input type="hidden" name="id" value="<?= e($editItem['id'] ?? '') ?>">
          
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><?= $editItem ? 'Sửa' : 'Thêm mới' ?></h3>
            </div>
            <div class="card-body">
              <div class="form-group">
                <label class="form-label">Icon</label>
                <select name="icon" class="form-input">
                  <?php foreach ($icons as $icon): ?>
                    <option value="<?= $icon ?>" <?= ($editItem['icon'] ?? '') === $icon ? 'selected' : '' ?>><?= ucfirst($icon) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              
              <div class="form-group">
                <label class="form-label">Tiêu đề <span class="required">*</span></label>
                <input type="text" name="title" class="form-input" value="<?= e($editItem['title'] ?? '') ?>" required>
              </div>
              
              <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-input" rows="3"><?= e($editItem['description'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
          
          <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><?= $editItem ? 'Cập nhật' : 'Thêm mới' ?></button>
            <a href="features.php" class="btn btn-secondary">Hủy</a>
          </div>
        </form>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
