<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();

if (!isset($data['roadmap'])) {
    $data['roadmap'] = array('phases' => array());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add_phase') {
        $newPhase = array(
            'id' => uniqid(),
            'title' => isset($_POST['title']) ? $_POST['title'] : '',
            'title_en' => isset($_POST['title_en']) ? $_POST['title_en'] : '',
            'active' => true,
            'items' => array()
        );
        $data['roadmap']['phases'][] = $newPhase;
        saveData($data);
        setFlash('success', 'Đã thêm giai đoạn mới!');
    }
    elseif ($action === 'edit_phase') {
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        foreach ($data['roadmap']['phases'] as $i => $phase) {
            if ($phase['id'] == $id) {
                $data['roadmap']['phases'][$i]['title'] = isset($_POST['title']) ? $_POST['title'] : '';
                $data['roadmap']['phases'][$i]['title_en'] = isset($_POST['title_en']) ? $_POST['title_en'] : '';
                $data['roadmap']['phases'][$i]['active'] = isset($_POST['active']);
                break;
            }
        }
        saveData($data);
        setFlash('success', 'Đã cập nhật giai đoạn!');
    }
    elseif ($action === 'delete_phase') {
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        foreach ($data['roadmap']['phases'] as $i => $phase) {
            if ($phase['id'] == $id) {
                array_splice($data['roadmap']['phases'], $i, 1);
                break;
            }
        }
        saveData($data);
        setFlash('success', 'Đã xóa giai đoạn!');
    }
    elseif ($action === 'add_item') {
        $phaseId = isset($_POST['phase_id']) ? $_POST['phase_id'] : '';
        foreach ($data['roadmap']['phases'] as $i => $phase) {
            if ($phase['id'] == $phaseId) {
                $newItem = array(
                    'id' => uniqid(),
                    'icon' => isset($_POST['icon']) ? $_POST['icon'] : 'film',
                    'month' => isset($_POST['month']) ? $_POST['month'] : '',
                    'title' => isset($_POST['title']) ? $_POST['title'] : '',
                    'title_en' => isset($_POST['title_en']) ? $_POST['title_en'] : '',
                    'description' => isset($_POST['description']) ? $_POST['description'] : '',
                    'description_en' => isset($_POST['description_en']) ? $_POST['description_en'] : '',
                    'status' => isset($_POST['status']) ? $_POST['status'] : 'upcoming'
                );
                $data['roadmap']['phases'][$i]['items'][] = $newItem;
                break;
            }
        }
        saveData($data);
        setFlash('success', 'Đã thêm mục mới!');
    }
    elseif ($action === 'edit_item') {
        $phaseId = isset($_POST['phase_id']) ? $_POST['phase_id'] : '';
        $itemId = isset($_POST['item_id']) ? $_POST['item_id'] : '';
        foreach ($data['roadmap']['phases'] as $i => $phase) {
            if ($phase['id'] == $phaseId) {
                foreach ($phase['items'] as $j => $item) {
                    if ($item['id'] == $itemId) {
                        $data['roadmap']['phases'][$i]['items'][$j]['icon'] = isset($_POST['icon']) ? $_POST['icon'] : 'film';
                        $data['roadmap']['phases'][$i]['items'][$j]['month'] = isset($_POST['month']) ? $_POST['month'] : '';
                        $data['roadmap']['phases'][$i]['items'][$j]['title'] = isset($_POST['title']) ? $_POST['title'] : '';
                        $data['roadmap']['phases'][$i]['items'][$j]['title_en'] = isset($_POST['title_en']) ? $_POST['title_en'] : '';
                        $data['roadmap']['phases'][$i]['items'][$j]['description'] = isset($_POST['description']) ? $_POST['description'] : '';
                        $data['roadmap']['phases'][$i]['items'][$j]['description_en'] = isset($_POST['description_en']) ? $_POST['description_en'] : '';
                        $data['roadmap']['phases'][$i]['items'][$j]['status'] = isset($_POST['status']) ? $_POST['status'] : 'upcoming';
                        break 2;
                    }
                }
            }
        }
        saveData($data);
        setFlash('success', 'Đã cập nhật!');
    }
    elseif ($action === 'delete_item') {
        $phaseId = isset($_POST['phase_id']) ? $_POST['phase_id'] : '';
        $itemId = isset($_POST['item_id']) ? $_POST['item_id'] : '';
        foreach ($data['roadmap']['phases'] as $i => $phase) {
            if ($phase['id'] == $phaseId) {
                foreach ($phase['items'] as $j => $item) {
                    if ($item['id'] == $itemId) {
                        array_splice($data['roadmap']['phases'][$i]['items'], $j, 1);
                        break 2;
                    }
                }
            }
        }
        saveData($data);
        setFlash('success', 'Đã xóa!');
    }
    
    header('Location: roadmap.php');
    exit;
}

$phases = $data['roadmap']['phases'];
$icons = array('film', 'camera', 'globe', 'music', 'brush', 'award', 'star', 'users', 'heart', 'book', 'tv', 'play');
$statuses = array('completed' => 'Hoàn thành', 'in_progress' => 'Đang thực hiện', 'upcoming' => 'Sắp tới');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lộ trình nội dung - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
    .phase-card { background: #1a1a2e; border: 1px solid #333; border-radius: 12px; margin-bottom: 20px; overflow: hidden; }
    .phase-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #0f0f1a; border-bottom: 1px solid #333; flex-wrap: wrap; gap: 10px; }
    .phase-header h3 { margin: 0; display: flex; align-items: center; gap: 10px; color: #fff; }
    .phase-badge { background: #8B1E2D; color: white; padding: 3px 10px; border-radius: 20px; font-size: 12px; }
    .phase-badge.inactive { background: #666; }
    .phase-body { padding: 20px; }
    .timeline-item { display: flex; gap: 15px; padding: 15px; background: #0f0f1a; border-radius: 8px; margin-bottom: 10px; position: relative; }
    .timeline-icon { width: 40px; height: 40px; background: #8B1E2D; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; }
    .timeline-content { flex: 1; }
    .timeline-month { font-size: 12px; color: #888; margin-bottom: 5px; }
    .timeline-title { font-weight: 600; margin-bottom: 5px; color: #fff; }
    .timeline-desc { font-size: 14px; color: #aaa; }
    .timeline-actions { display: flex; gap: 5px; margin-top: 10px; }
    .status-badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; }
    .status-completed { background: #28a745; color: white; }
    .status-in_progress { background: #ffc107; color: #000; }
    .status-upcoming { background: #6c757d; color: white; }
    .rm-modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; z-index: 9999; }
    .rm-modal-overlay.show { display: flex; }
    .rm-modal-box { background: #1a1a2e; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
    .rm-modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #333; }
    .rm-modal-header h2 { margin: 0; color: #fff; }
    .rm-close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #888; }
    .rm-modal-box form { padding: 20px; }
    .rm-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .rm-form-group { margin-bottom: 15px; }
    .rm-form-group label { display: block; margin-bottom: 5px; color: #aaa; }
    .rm-form-group input, .rm-form-group textarea, .rm-form-group select { width: 100%; padding: 10px; border: 1px solid #333; border-radius: 6px; background: #0f0f1a; color: #fff; }
    .rm-form-actions { display: flex; gap: 10px; margin-top: 20px; }
    .btn-danger { background: #dc3545 !important; }
    .btn-sm { padding: 6px 12px; font-size: 13px; }
    .empty-state { text-align: center; padding: 40px; color: #888; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Lộ trình nội dung</h1>
            <button class="btn btn-primary" onclick="openPhaseModal('add')">+ Thêm giai đoạn</button>
        </div>
        
        <?php $flash = getFlash('success'); if ($flash): ?>
            <div class="alert alert-success"><?php echo e($flash); ?></div>
        <?php endif; ?>
        
        <?php if (empty($phases)): ?>
        <div class="card empty-state">
            <p>Chưa có giai đoạn nào. Hãy thêm giai đoạn đầu tiên (vd: 2026-2027)</p>
        </div>
        <?php else: ?>
        
        <?php foreach ($phases as $phase): ?>
        <div class="phase-card">
            <div class="phase-header">
                <h3>
                    <?php echo e($phase['title']); ?>
                    <?php if (!empty($phase['title_en'])): ?>
                    <small style="color:#888;font-weight:normal">/ <?php echo e($phase['title_en']); ?></small>
                    <?php endif; ?>
                    <span class="phase-badge <?php echo empty($phase['active']) ? 'inactive' : ''; ?>">
                        <?php echo empty($phase['active']) ? 'Ẩn' : 'Hiện'; ?>
                    </span>
                </h3>
                <div style="display:flex;gap:10px">
                    <button class="btn btn-sm" onclick="openItemModal('add', '<?php echo $phase['id']; ?>')">+ Thêm mục</button>
                    <button class="btn btn-sm" onclick='openPhaseModal("edit", <?php echo json_encode($phase, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Sửa</button>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Xóa giai đoạn này?')">
                        <input type="hidden" name="action" value="delete_phase">
                        <input type="hidden" name="id" value="<?php echo e($phase['id']); ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
            <div class="phase-body">
                <?php if (empty($phase['items'])): ?>
                <div class="empty-state">Chưa có mục nào trong giai đoạn này</div>
                <?php else: ?>
                <?php foreach ($phase['items'] as $item): ?>
                <div class="timeline-item">
                    <div class="timeline-icon"><?php echo strtoupper(substr($item['icon'], 0, 1)); ?></div>
                    <div class="timeline-content">
                        <div class="timeline-month"><?php echo e($item['month']); ?></div>
                        <div class="timeline-title">
                            <?php echo e($item['title']); ?>
                            <span class="status-badge status-<?php echo $item['status']; ?>"><?php echo $statuses[$item['status']]; ?></span>
                        </div>
                        <div class="timeline-desc"><?php echo e($item['description']); ?></div>
                        <div class="timeline-actions">
                            <button class="btn btn-sm" onclick='openItemModal("edit", "<?php echo $phase['id']; ?>", <?php echo json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Sửa</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Xóa mục này?')">
                                <input type="hidden" name="action" value="delete_item">
                                <input type="hidden" name="phase_id" value="<?php echo e($phase['id']); ?>">
                                <input type="hidden" name="item_id" value="<?php echo e($item['id']); ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </main>
    
    <!-- Modal Phase -->
    <div id="phaseModal" class="rm-modal-overlay">
        <div class="rm-modal-box">
            <div class="rm-modal-header">
                <h2 id="phaseModalTitle">Thêm giai đoạn</h2>
                <button class="rm-close-btn" onclick="closePhaseModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="phaseAction" value="add_phase">
                <input type="hidden" name="id" id="phaseId" value="">
                
                <div class="rm-form-row">
                    <div class="rm-form-group">
                        <label>Tên giai đoạn (VN)</label>
                        <input type="text" name="title" id="phaseTitle" required placeholder="vd: 2026-2027">
                    </div>
                    <div class="rm-form-group">
                        <label>Phase Name (EN)</label>
                        <input type="text" name="title_en" id="phaseTitleEn" placeholder="e.g: 2026-2027">
                    </div>
                </div>
                
                <div class="rm-form-group" id="phaseActiveGroup" style="display:none">
                    <label>
                        <input type="checkbox" name="active" id="phaseActive" checked>
                        Hiển thị giai đoạn này
                    </label>
                </div>
                
                <div class="rm-form-actions">
                    <button type="submit" class="btn btn-primary">Lưu</button>
                    <button type="button" class="btn btn-secondary" onclick="closePhaseModal()">Hủy</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Item -->
    <div id="itemModal" class="rm-modal-overlay">
        <div class="rm-modal-box">
            <div class="rm-modal-header">
                <h2 id="itemModalTitle">Thêm mục</h2>
                <button class="rm-close-btn" onclick="closeItemModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="itemAction" value="add_item">
                <input type="hidden" name="phase_id" id="itemPhaseId" value="">
                <input type="hidden" name="item_id" id="itemId" value="">
                
                <div class="rm-form-row">
                    <div class="rm-form-group">
                        <label>Icon</label>
                        <select name="icon" id="itemIcon">
                            <?php foreach ($icons as $icon): ?>
                            <option value="<?php echo $icon; ?>"><?php echo $icon; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="rm-form-group">
                        <label>Tháng/Thời gian</label>
                        <input type="text" name="month" id="itemMonth" placeholder="vd: Tháng 8/2026">
                    </div>
                </div>
                
                <div class="rm-form-row">
                    <div class="rm-form-group">
                        <label>Tên dự án (VN)</label>
                        <input type="text" name="title" id="itemTitle" required>
                    </div>
                    <div class="rm-form-group">
                        <label>Project Name (EN)</label>
                        <input type="text" name="title_en" id="itemTitleEn">
                    </div>
                </div>
                
                <div class="rm-form-row">
                    <div class="rm-form-group">
                        <label>Mô tả (VN)</label>
                        <textarea name="description" id="itemDesc" rows="3"></textarea>
                    </div>
                    <div class="rm-form-group">
                        <label>Description (EN)</label>
                        <textarea name="description_en" id="itemDescEn" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="rm-form-group">
                    <label>Trạng thái</label>
                    <select name="status" id="itemStatus">
                        <option value="upcoming">Sắp tới</option>
                        <option value="in_progress">Đang thực hiện</option>
                        <option value="completed">Hoàn thành</option>
                    </select>
                </div>
                
                <div class="rm-form-actions">
                    <button type="submit" class="btn btn-primary">Lưu</button>
                    <button type="button" class="btn btn-secondary" onclick="closeItemModal()">Hủy</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function openPhaseModal(action, phase) {
        document.getElementById('phaseModal').classList.add('show');
        document.getElementById('phaseAction').value = action === 'add' ? 'add_phase' : 'edit_phase';
        document.getElementById('phaseModalTitle').textContent = action === 'add' ? 'Thêm giai đoạn' : 'Sửa giai đoạn';
        document.getElementById('phaseActiveGroup').style.display = action === 'edit' ? 'block' : 'none';
        
        if (action === 'edit' && phase) {
            document.getElementById('phaseId').value = phase.id;
            document.getElementById('phaseTitle').value = phase.title || '';
            document.getElementById('phaseTitleEn').value = phase.title_en || '';
            document.getElementById('phaseActive').checked = phase.active !== false;
        } else {
            document.getElementById('phaseId').value = '';
            document.getElementById('phaseTitle').value = '';
            document.getElementById('phaseTitleEn').value = '';
            document.getElementById('phaseActive').checked = true;
        }
    }
    
    function closePhaseModal() {
        document.getElementById('phaseModal').classList.remove('show');
    }
    
    function openItemModal(action, phaseId, item) {
        document.getElementById('itemModal').classList.add('show');
        document.getElementById('itemAction').value = action === 'add' ? 'add_item' : 'edit_item';
        document.getElementById('itemModalTitle').textContent = action === 'add' ? 'Thêm mục' : 'Sửa mục';
        document.getElementById('itemPhaseId').value = phaseId;
        
        if (action === 'edit' && item) {
            document.getElementById('itemId').value = item.id;
            document.getElementById('itemIcon').value = item.icon || 'film';
            document.getElementById('itemMonth').value = item.month || '';
            document.getElementById('itemTitle').value = item.title || '';
            document.getElementById('itemTitleEn').value = item.title_en || '';
            document.getElementById('itemDesc').value = item.description || '';
            document.getElementById('itemDescEn').value = item.description_en || '';
            document.getElementById('itemStatus').value = item.status || 'upcoming';
        } else {
            document.getElementById('itemId').value = '';
            document.getElementById('itemIcon').value = 'film';
            document.getElementById('itemMonth').value = '';
            document.getElementById('itemTitle').value = '';
            document.getElementById('itemTitleEn').value = '';
            document.getElementById('itemDesc').value = '';
            document.getElementById('itemDescEn').value = '';
            document.getElementById('itemStatus').value = 'upcoming';
        }
    }
    
    function closeItemModal() {
        document.getElementById('itemModal').classList.remove('show');
    }
    
    document.getElementById('phaseModal').addEventListener('click', function(e) { if (e.target === this) closePhaseModal(); });
    document.getElementById('itemModal').addEventListener('click', function(e) { if (e.target === this) closeItemModal(); });
    </script>
</body>
</html>
