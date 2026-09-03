<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Xử lý xóa
if ($action === 'delete' && $id) {
    foreach ($data['team'] as $key => $member) {
        if ($member['id'] == $id) {
            // Xóa ảnh cũ
            if (!empty($member['image']) && strpos($member['image'], 'uploads/') === 0) {
                deleteFile($member['image']);
            }
            unset($data['team'][$key]);
            break;
        }
    }
    $data['team'] = array_values($data['team']);
    saveData($data);
    setFlash('success', 'Đã xóa thành viên thành công!');
    header('Location: team.php');
    exit;
}

// Xử lý form thêm/sửa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberData = [
        'id' => $_POST['id'] ?: time(),
        'name' => $_POST['name'] ?? '',
        'role' => $_POST['role'] ?? '',
        'quote' => $_POST['quote'] ?? '',
        'image' => $_POST['current_image'] ?? ''
    ];
    
    // Upload ảnh mới
    if (!empty($_FILES['image']['name'])) {
        $uploaded = uploadFile($_FILES['image']);
        if ($uploaded) {
            // Xóa ảnh cũ
            if (!empty($memberData['image']) && strpos($memberData['image'], 'uploads/') === 0) {
                deleteFile($memberData['image']);
            }
            $memberData['image'] = $uploaded;
        }
    }
    
    // Cập nhật hoặc thêm mới
    $found = false;
    foreach ($data['team'] as $key => $member) {
        if ($member['id'] == $memberData['id']) {
            $data['team'][$key] = $memberData;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $data['team'][] = $memberData;
    }
    
    saveData($data);
    setFlash('success', $found ? 'Đã cập nhật thành viên!' : 'Đã thêm thành viên mới!');
    header('Location: team.php');
    exit;
}

// Lấy thông tin thành viên cần sửa
$editMember = null;
if ($action === 'edit' && $id) {
    foreach ($data['team'] as $member) {
        if ($member['id'] == $id) {
            $editMember = $member;
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
  <title>Đội ngũ - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Quản lý đội ngũ</h1>
          <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span>/</span>
            <span>Đội ngũ</span>
          </div>
        </div>
        <?php if ($action === 'list'): ?>
          <a href="?action=add" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Thêm thành viên
          </a>
        <?php endif; ?>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <?php if ($action === 'list'): ?>
        <!-- Danh sách -->
        <div class="card">
          <div class="card-body">
            <?php if (empty($data['team'])): ?>
              <p style="text-align: center; color: #64748b; padding: 40px;">Chưa có thành viên nào. <a href="?action=add">Thêm ngay</a></p>
            <?php else: ?>
              <?php foreach ($data['team'] as $member): ?>
                <div class="list-item">
                  <img src="<?= imageUrl($member['image'] ?? '') ?>" alt="" class="list-item-image">
                  <div class="list-item-content">
                    <div class="list-item-title"><?= e($member['name']) ?></div>
                    <div class="list-item-subtitle"><?= e($member['role'] ?? '') ?></div>
                  </div>
                  <div class="list-item-actions">
                    <a href="?action=edit&id=<?= $member['id'] ?>" class="btn btn-secondary btn-sm">Sửa</a>
                    <a href="?action=delete&id=<?= $member['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
        
      <?php else: ?>
        <!-- Form thêm/sửa -->
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= e($editMember['id'] ?? '') ?>">
          <input type="hidden" name="current_image" value="<?= e($editMember['image'] ?? '') ?>">
          
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><?= $editMember ? 'Sửa thành viên' : 'Thêm thành viên mới' ?></h3>
            </div>
            <div class="card-body">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Họ tên <span class="required">*</span></label>
                  <input type="text" name="name" class="form-input" value="<?= e($editMember['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Chức vụ</label>
                  <input type="text" name="role" class="form-input" value="<?= e($editMember['role'] ?? '') ?>">
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">Câu nói / Châm ngôn</label>
                <textarea name="quote" class="form-input" rows="3"><?= e($editMember['quote'] ?? '') ?></textarea>
              </div>
              
              <div class="form-group">
                <label class="form-label">Ảnh đại diện</label>
                <label class="file-upload">
                  <input type="file" name="image" accept="image/*">
                  <div class="file-upload-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                      <polyline points="17 8 12 3 7 8"/>
                      <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                  </div>
                  <div class="file-upload-text">Nhấn để chọn ảnh hoặc kéo thả vào đây</div>
                  <div class="file-upload-hint">PNG, JPG tối đa 5MB</div>
                </label>
                
                <?php if (!empty($editMember['image'])): ?>
                  <div class="file-preview">
                    <img src="<?= imageUrl($editMember['image']) ?>" alt="">
                    <div class="file-preview-info">
                      <div class="file-preview-name">Ảnh hiện tại</div>
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
                <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
              </svg>
              <?= $editMember ? 'Cập nhật' : 'Thêm mới' ?>
            </button>
            <a href="team.php" class="btn btn-secondary">Hủy</a>
          </div>
        </form>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
