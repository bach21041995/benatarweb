<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();

if (!isset($data['partners'])) {
    $data['partners'] = array();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add') {
        $newItem = array(
            'id' => uniqid(),
            'icon' => isset($_POST['icon']) ? $_POST['icon'] : 'star',
            'title' => isset($_POST['title']) ? $_POST['title'] : '',
            'title_en' => isset($_POST['title_en']) ? $_POST['title_en'] : '',
            'description' => isset($_POST['description']) ? $_POST['description'] : '',
            'description_en' => isset($_POST['description_en']) ? $_POST['description_en'] : '',
        );
        $data['partners'][] = $newItem;
        saveData($data);
        setFlash('success', 'Đã thêm đối tác mới!');
    }
    elseif ($action === 'edit') {
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        foreach ($data['partners'] as $i => $item) {
            if ($item['id'] == $id) {
                $data['partners'][$i]['icon'] = isset($_POST['icon']) ? $_POST['icon'] : 'star';
                $data['partners'][$i]['title'] = isset($_POST['title']) ? $_POST['title'] : '';
                $data['partners'][$i]['title_en'] = isset($_POST['title_en']) ? $_POST['title_en'] : '';
                $data['partners'][$i]['description'] = isset($_POST['description']) ? $_POST['description'] : '';
                $data['partners'][$i]['description_en'] = isset($_POST['description_en']) ? $_POST['description_en'] : '';
                break;
            }
        }
        saveData($data);
        setFlash('success', 'Đã cập nhật!');
    }
    elseif ($action === 'delete') {
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        foreach ($data['partners'] as $i => $item) {
            if ($item['id'] == $id) {
                array_splice($data['partners'], $i, 1);
                break;
            }
        }
        saveData($data);
        setFlash('success', 'Đã xóa!');
    }
    
    header('Location: partners.php');
    exit;
}

$partners = $data['partners'];
$icons = array('film', 'camera', 'lightbulb', 'users', 'star', 'heart', 'globe', 'code', 'settings', 'zap', 'award', 'briefcase');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đối tác chiến lược - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
    .partner-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .partner-modal-overlay.show {
        display: flex;
    }
    .partner-modal-box {
        background: #1a1a2e;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .partner-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #333;
    }
    .partner-modal-header h2 {
        margin: 0;
        color: #fff;
    }
    .partner-close-btn {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #888;
        line-height: 1;
    }
    .partner-close-btn:hover {
        color: #fff;
    }
    .partner-modal-box form {
        padding: 20px;
    }
    .partner-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    .partner-form-group {
        margin-bottom: 15px;
    }
    .partner-form-group label {
        display: block;
        margin-bottom: 5px;
        color: #aaa;
        font-size: 14px;
    }
    .partner-form-group input,
    .partner-form-group textarea,
    .partner-form-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #333;
        border-radius: 6px;
        background: #0f0f1a;
        color: #fff;
        font-size: 14px;
    }
    .partner-form-group input:focus,
    .partner-form-group textarea:focus,
    .partner-form-group select:focus {
        outline: none;
        border-color: #8B1E2D;
    }
    .partner-form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #333;
    }
    .icon-preview {
        background: #8B1E2D;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
    }
    .btn-danger {
        background: #dc3545 !important;
        border-color: #dc3545 !important;
    }
    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }
    @media (max-width: 600px) {
        .partner-form-row {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Đối tác chiến lược</h1>
            <button class="btn btn-primary" onclick="openPartnerModal('add')">+ Thêm đối tác</button>
        </div>
        
        <?php $flash = getFlash('success'); if ($flash): ?>
            <div class="alert alert-success"><?php echo e($flash); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Tên đối tác</th>
                        <th>Mô tả</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($partners)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:40px;color:#888;">Chưa có đối tác nào</td></tr>
                    <?php else: ?>
                    <?php foreach ($partners as $item): ?>
                    <tr>
                        <td><span class="icon-preview"><?php echo e($item['icon']); ?></span></td>
                        <td>
                            <strong><?php echo e($item['title']); ?></strong>
                            <?php if (!empty($item['title_en'])): ?>
                            <br><small style="color:#888"><?php echo e($item['title_en']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:300px"><?php echo e(mb_substr($item['description'], 0, 80)); ?>...</td>
                        <td>
                            <button class="btn btn-sm" onclick='openPartnerModal("edit", <?php echo json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Sửa</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Xóa đối tác này?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo e($item['id']); ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <!-- Modal -->
    <div id="partnerModal" class="partner-modal-overlay">
        <div class="partner-modal-box">
            <div class="partner-modal-header">
                <h2 id="partnerModalTitle">Thêm đối tác</h2>
                <button class="partner-close-btn" onclick="closePartnerModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="partnerAction" value="add">
                <input type="hidden" name="id" id="partnerId" value="">
                
                <div class="partner-form-group">
                    <label>Icon</label>
                    <select name="icon" id="partnerIcon">
                        <?php foreach ($icons as $icon): ?>
                        <option value="<?php echo $icon; ?>"><?php echo $icon; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="partner-form-row">
                    <div class="partner-form-group">
                        <label>Tên đối tác (VN)</label>
                        <input type="text" name="title" id="partnerTitle" required>
                    </div>
                    <div class="partner-form-group">
                        <label>Partner Name (EN)</label>
                        <input type="text" name="title_en" id="partnerTitleEn">
                    </div>
                </div>
                
                <div class="partner-form-row">
                    <div class="partner-form-group">
                        <label>Mô tả (VN)</label>
                        <textarea name="description" id="partnerDesc" rows="3"></textarea>
                    </div>
                    <div class="partner-form-group">
                        <label>Description (EN)</label>
                        <textarea name="description_en" id="partnerDescEn" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="partner-form-actions">
                    <button type="submit" class="btn btn-primary">Lưu</button>
                    <button type="button" class="btn btn-secondary" onclick="closePartnerModal()">Hủy</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function openPartnerModal(action, item) {
        document.getElementById('partnerModal').classList.add('show');
        document.getElementById('partnerAction').value = action;
        document.getElementById('partnerModalTitle').textContent = action === 'add' ? 'Thêm đối tác' : 'Sửa đối tác';
        
        if (action === 'edit' && item) {
            document.getElementById('partnerId').value = item.id || '';
            document.getElementById('partnerIcon').value = item.icon || 'star';
            document.getElementById('partnerTitle').value = item.title || '';
            document.getElementById('partnerTitleEn').value = item.title_en || '';
            document.getElementById('partnerDesc').value = item.description || '';
            document.getElementById('partnerDescEn').value = item.description_en || '';
        } else {
            document.getElementById('partnerId').value = '';
            document.getElementById('partnerIcon').value = 'star';
            document.getElementById('partnerTitle').value = '';
            document.getElementById('partnerTitleEn').value = '';
            document.getElementById('partnerDesc').value = '';
            document.getElementById('partnerDescEn').value = '';
        }
    }
    
    function closePartnerModal() {
        document.getElementById('partnerModal').classList.remove('show');
    }
    
    // Đóng modal khi click bên ngoài
    document.getElementById('partnerModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePartnerModal();
        }
    });
    </script>
</body>
</html>
