<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();
$adminMenus = isset($data['admin_menus']) ? $data['admin_menus'] : array();
$menuSections = isset($data['menu_sections']) ? $data['menu_sections'] : array();
$frontendSections = isset($data['frontend_sections']) ? $data['frontend_sections'] : array();

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'toggle_active') {
        $menuId = isset($_POST['menu_id']) ? $_POST['menu_id'] : '';
        foreach ($adminMenus as $i => $menu) {
            if ($menu['id'] === $menuId) {
                $data['admin_menus'][$i]['active'] = !$menu['active'];
                break;
            }
        }
        saveData($data);
        header('Location: menus.php?success=1');
        exit;
    }
    
    if ($action === 'update_frontend') {
        $menuId = isset($_POST['menu_id']) ? $_POST['menu_id'] : '';
        $frontendSection = isset($_POST['frontend_section']) ? trim($_POST['frontend_section']) : '';
        $labelVi = isset($_POST['label_vi']) ? trim($_POST['label_vi']) : '';
        $labelEn = isset($_POST['label_en']) ? trim($_POST['label_en']) : '';
        $titleVi = isset($_POST['title_vi']) ? trim($_POST['title_vi']) : '';
        $titleEn = isset($_POST['title_en']) ? trim($_POST['title_en']) : '';
        
        foreach ($adminMenus as $i => $menu) {
            if ($menu['id'] === $menuId) {
                $data['admin_menus'][$i]['frontend_section'] = $frontendSection;
                break;
            }
        }
        
        if (!empty($frontendSection)) {
            $found = false;
            foreach ($frontendSections as $j => $fs) {
                if ($fs['id'] === $frontendSection) {
                    $data['frontend_sections'][$j]['label_vi'] = $labelVi;
                    $data['frontend_sections'][$j]['label_en'] = $labelEn;
                    $data['frontend_sections'][$j]['title_vi'] = $titleVi;
                    $data['frontend_sections'][$j]['title_en'] = $titleEn;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $data['frontend_sections'][] = array(
                    'id' => $frontendSection,
                    'label_vi' => $labelVi,
                    'label_en' => $labelEn,
                    'title_vi' => $titleVi,
                    'title_en' => $titleEn,
                    'order' => count($frontendSections) + 1
                );
            }
        }
        
        saveData($data);
        header('Location: menus.php?success=2');
        exit;
    }
}

function getFrontendInfo($sectionId, $sections) {
    foreach ($sections as $s) {
        if ($s['id'] === $sectionId) {
            return $s;
        }
    }
    return null;
}

$groupedMenus = array();
foreach ($menuSections as $sec) {
    $groupedMenus[$sec['id']] = array(
        'title' => $sec['title'],
        'items' => array()
    );
}
foreach ($adminMenus as $menu) {
    $section = isset($menu['section']) ? $menu['section'] : 'content';
    if (!isset($groupedMenus[$section])) {
        $groupedMenus[$section] = array('title' => $section, 'items' => array());
    }
    $groupedMenus[$section]['items'][] = $menu;
}

$availableFrontendSections = array(
    'hero', 'about', 'services', 'team', 'features', 'partners', 
    'process', 'roadmap', 'projects', 'gallery', 'clients', 'cta', 'contact'
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Menu - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .menu-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .menu-table th, .menu-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .menu-table th { background: var(--bg-tertiary); font-weight: 600; color: var(--text-secondary); }
        .menu-table tr:hover { background: var(--bg-tertiary); }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 15px; font-size: 11px; font-weight: 600; }
        .badge-primary { background: rgba(139, 30, 45, 0.2); color: #8B1E2D; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .section-group { margin-bottom: 40px; }
        .section-header { font-size: 18px; color: var(--text-primary); margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid var(--primary-color); }
        .toggle-btn { cursor: pointer; padding: 6px 14px; border: none; border-radius: 4px; font-size: 12px; }
        .toggle-btn.active { background: #28a745; color: white; }
        .toggle-btn.inactive { background: #6c757d; color: white; }
        .frontend-badge { font-size: 11px; background: rgba(139, 30, 45, 0.1); color: #8B1E2D; padding: 3px 8px; border-radius: 4px; margin-left: 8px; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: rgba(40, 167, 69, 0.15); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.3); }
        
        /* Modal - Fixed */
        .mn-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .mn-modal-overlay.active {
            display: flex;
        }
        .mn-modal-box {
            background: #1a1a2e;
            border-radius: 12px;
            width: 500px;
            max-width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            border: 1px solid #333;
        }
        .mn-modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .mn-modal-header h3 {
            margin: 0;
            font-size: 18px;
            color: #fff;
        }
        .mn-modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #888;
            cursor: pointer;
            line-height: 1;
        }
        .mn-modal-close:hover {
            color: #fff;
        }
        .mn-modal-body {
            padding: 25px;
        }
        .mn-form-group {
            margin-bottom: 20px;
        }
        .mn-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #ccc;
            font-size: 14px;
        }
        .mn-form-group input,
        .mn-form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #444;
            border-radius: 6px;
            background: #0d0d1a;
            color: #fff;
            font-size: 14px;
            box-sizing: border-box;
        }
        .mn-form-group input:focus,
        .mn-form-group select:focus {
            outline: none;
            border-color: #8B1E2D;
        }
        .mn-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .mn-form-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .mn-modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #333;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .mn-modal-footer .btn {
            padding: 10px 20px;
        }
        
        @media(max-width: 600px) {
            .mn-form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Quản lý Menu</h1>
            <p style="color: var(--text-secondary);">Bật/tắt menu và liên kết với section trang chủ</p>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php if ($_GET['success'] == '1'): ?>Đã cập nhật trạng thái menu!<?php endif; ?>
            <?php if ($_GET['success'] == '2'): ?>Đã cập nhật liên kết frontend section!<?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php foreach ($groupedMenus as $sectionId => $group): ?>
        <?php if (!empty($group['items'])): ?>
        <div class="section-group">
            <h2 class="section-header"><?php echo e($group['title']); ?></h2>
            <table class="menu-table">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Trang Admin</th>
                        <th>Section Frontend</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($group['items'] as $menu): 
                        $frontendId = isset($menu['frontend_section']) ? $menu['frontend_section'] : '';
                        $frontendInfo = $frontendId ? getFrontendInfo($frontendId, $frontendSections) : null;
                        $infoJson = $frontendInfo ? json_encode($frontendInfo) : '{}';
                    ?>
                    <tr>
                        <td><strong><?php echo e($menu['title']); ?></strong></td>
                        <td><code><?php echo e($menu['page']); ?>.php</code></td>
                        <td>
                            <?php if ($frontendId): ?>
                                <span class="badge badge-primary">#<?php echo e($frontendId); ?></span>
                                <?php if ($frontendInfo && !empty($frontendInfo['label_vi'])): ?>
                                <span class="frontend-badge"><?php echo e($frontendInfo['label_vi']); ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #666;">— Không hiển thị —</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="menu_id" value="<?php echo e($menu['id']); ?>">
                                <button type="submit" class="toggle-btn <?php echo ($menu['active'] ? 'active' : 'inactive'); ?>">
                                    <?php echo ($menu['active'] ? 'Đang bật' : 'Đã tắt'); ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm" onclick='openEditModal("<?php echo e($menu['id']); ?>", "<?php echo e($menu['title']); ?>", "<?php echo e($frontendId); ?>", <?php echo htmlspecialchars($infoJson, ENT_QUOTES); ?>)'>
                                Sửa Section
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
    </main>
    
    <!-- Modal Edit -->
    <div class="mn-modal-overlay" id="editModal">
        <div class="mn-modal-box">
            <div class="mn-modal-header">
                <h3 id="modalTitle">Sửa Section Frontend</h3>
                <button type="button" class="mn-modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_frontend">
                <input type="hidden" name="menu_id" id="editMenuId">
                
                <div class="mn-modal-body">
                    <div class="mn-form-group">
                        <label>Section Frontend ID</label>
                        <select name="frontend_section" id="editFrontendSection">
                            <option value="">— Không liên kết —</option>
                            <?php foreach ($availableFrontendSections as $sec): ?>
                            <option value="<?php echo $sec; ?>"><?php echo $sec; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mn-form-hint">Chọn section trên trang chủ để liên kết</p>
                    </div>
                    
                    <div class="mn-form-row">
                        <div class="mn-form-group">
                            <label>Label Tiếng Việt</label>
                            <input type="text" name="label_vi" id="editLabelVi" placeholder="VD: Dịch vụ">
                        </div>
                        <div class="mn-form-group">
                            <label>Label English</label>
                            <input type="text" name="label_en" id="editLabelEn" placeholder="VD: Services">
                        </div>
                    </div>
                    
                    <div class="mn-form-row">
                        <div class="mn-form-group">
                            <label>Title Tiếng Việt</label>
                            <input type="text" name="title_vi" id="editTitleVi" placeholder="VD: Dịch vụ của chúng tôi">
                        </div>
                        <div class="mn-form-group">
                            <label>Title English</label>
                            <input type="text" name="title_en" id="editTitleEn" placeholder="VD: Our Services">
                        </div>
                    </div>
                </div>
                
                <div class="mn-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function openEditModal(menuId, menuTitle, frontendSection, frontendInfo) {
        document.getElementById('editMenuId').value = menuId;
        document.getElementById('modalTitle').textContent = 'Sửa Section: ' + menuTitle;
        document.getElementById('editFrontendSection').value = frontendSection || '';
        document.getElementById('editLabelVi').value = (frontendInfo && frontendInfo.label_vi) ? frontendInfo.label_vi : '';
        document.getElementById('editLabelEn').value = (frontendInfo && frontendInfo.label_en) ? frontendInfo.label_en : '';
        document.getElementById('editTitleVi').value = (frontendInfo && frontendInfo.title_vi) ? frontendInfo.title_vi : '';
        document.getElementById('editTitleEn').value = (frontendInfo && frontendInfo.title_en) ? frontendInfo.title_en : '';
        document.getElementById('editModal').classList.add('active');
    }
    
    function closeModal() {
        document.getElementById('editModal').classList.remove('active');
    }
    
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
    </script>
</body>
</html>
