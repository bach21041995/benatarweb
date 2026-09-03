<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();

if (!isset($data['projects'])) {
    $data['projects'] = array();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add' || $action === 'edit') {
        $item = array(
            'id' => ($action === 'edit' && isset($_POST['id'])) ? $_POST['id'] : uniqid(),
            'title' => isset($_POST['title']) ? $_POST['title'] : '',
            'title_en' => isset($_POST['title_en']) ? $_POST['title_en'] : '',
            'description' => isset($_POST['description']) ? $_POST['description'] : '',
            'description_en' => isset($_POST['description_en']) ? $_POST['description_en'] : '',
            'director' => isset($_POST['director']) ? $_POST['director'] : '',
            'cast' => isset($_POST['cast']) ? $_POST['cast'] : '',
            'year' => isset($_POST['year']) ? $_POST['year'] : '',
            'link' => isset($_POST['link']) ? $_POST['link'] : '#',
            'image' => ''
        );
        
        // Upload ảnh mới
        if (!empty($_FILES['image']['name'])) {
            $uploaded = uploadFile($_FILES['image'], 'uploads');
            if ($uploaded) {
                $item['image'] = $uploaded;
            }
        } elseif ($action === 'edit') {
            // Giữ ảnh cũ
            foreach ($data['projects'] as $p) {
                if ($p['id'] == $_POST['id']) {
                    $item['image'] = isset($p['image']) ? $p['image'] : '';
                    break;
                }
            }
        }
        
        if ($action === 'add') {
            $data['projects'][] = $item;
            setFlash('success', 'Đã thêm dự án mới!');
        } else {
            foreach ($data['projects'] as $i => $p) {
                if ($p['id'] == $_POST['id']) {
                    $data['projects'][$i] = $item;
                    break;
                }
            }
            setFlash('success', 'Đã cập nhật!');
        }
        saveData($data);
    }
    elseif ($action === 'delete') {
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        foreach ($data['projects'] as $i => $p) {
            if ($p['id'] == $id) {
                if (!empty($p['image']) && file_exists('../' . $p['image'])) {
                    unlink('../' . $p['image']);
                }
                array_splice($data['projects'], $i, 1);
                break;
            }
        }
        saveData($data);
        setFlash('success', 'Đã xóa!');
    }
    
    header('Location: projects.php');
    exit;
}

$projects = $data['projects'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dự án phim - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
    .project-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
    .project-card { background: #1a1a2e; border: 1px solid #333; border-radius: 12px; overflow: hidden; }
    .project-image { width: 100%; height: 180px; object-fit: cover; background: #0f0f1a; }
    .project-body { padding: 20px; }
    .project-title { font-size: 18px; font-weight: 600; margin-bottom: 5px; color: #fff; }
    .project-meta { font-size: 13px; color: #888; margin-bottom: 10px; }
    .project-desc { font-size: 14px; color: #aaa; margin-bottom: 15px; }
    .project-actions { display: flex; gap: 10px; }
    .pj-modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; z-index: 9999; }
    .pj-modal-overlay.show { display: flex; }
    .pj-modal-box { background: #1a1a2e; border-radius: 12px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto; }
    .pj-modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #333; }
    .pj-modal-header h2 { margin: 0; color: #fff; }
    .pj-close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #888; }
    .pj-modal-box form { padding: 20px; }
    .pj-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .pj-form-group { margin-bottom: 15px; }
    .pj-form-group label { display: block; margin-bottom: 5px; color: #aaa; }
    .pj-form-group input, .pj-form-group textarea { width: 100%; padding: 10px; border: 1px solid #333; border-radius: 6px; background: #0f0f1a; color: #fff; }
    .pj-form-actions { display: flex; gap: 10px; margin-top: 20px; }
    .btn-danger { background: #dc3545 !important; }
    .btn-sm { padding: 6px 12px; font-size: 13px; }
    .empty-state { text-align: center; padding: 60px; color: #888; }
    .preview-img { max-width: 150px; border-radius: 8px; margin-top: 10px; }
    .no-image { display: flex; align-items: center; justify-content: center; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Dự án phim đã hoàn thành</h1>
            <button class="btn btn-primary" onclick="openProjectModal('add')">+ Thêm dự án</button>
        </div>
        
        <?php $flash = getFlash('success'); if ($flash): ?>
            <div class="alert alert-success"><?php echo e($flash); ?></div>
        <?php endif; ?>
        
        <?php if (empty($projects)): ?>
        <div class="card empty-state">
            <p>Chưa có dự án nào</p>
        </div>
        <?php else: ?>
        
        <div class="project-grid">
            <?php foreach ($projects as $project): ?>
            <div class="project-card">
                <?php if (!empty($project['image'])): ?>
                <img src="../<?php echo e($project['image']); ?>" class="project-image" alt="">
                <?php else: ?>
                <div class="project-image no-image">Chưa có ảnh</div>
                <?php endif; ?>
                <div class="project-body">
                    <div class="project-title"><?php echo e($project['title']); ?></div>
                    <div class="project-meta">
                        <?php if (!empty($project['director'])): ?>
                        <div>Đạo diễn: <?php echo e($project['director']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($project['cast'])): ?>
                        <div>Diễn viên: <?php echo e($project['cast']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($project['year'])): ?>
                        <div>Năm: <?php echo e($project['year']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($project['description'])): ?>
                    <div class="project-desc"><?php echo e(mb_substr($project['description'], 0, 100)); ?>...</div>
                    <?php endif; ?>
                    <div class="project-actions">
                        <button class="btn btn-sm" onclick='openProjectModal("edit", <?php echo json_encode($project, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Sửa</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Xóa dự án này?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo e($project['id']); ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
    
    <!-- Modal -->
    <div id="projectModal" class="pj-modal-overlay">
        <div class="pj-modal-box">
            <div class="pj-modal-header">
                <h2 id="projectModalTitle">Thêm dự án</h2>
                <button class="pj-close-btn" onclick="closeProjectModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="projectAction" value="add">
                <input type="hidden" name="id" id="projectId" value="">
                
                <div class="pj-form-group">
                    <label>Hình ảnh</label>
                    <input type="file" name="image" accept="image/*">
                    <img id="previewImage" class="preview-img" style="display:none">
                </div>
                
                <div class="pj-form-row">
                    <div class="pj-form-group">
                        <label>Tên phim (VN)</label>
                        <input type="text" name="title" id="projectTitle" required>
                    </div>
                    <div class="pj-form-group">
                        <label>Film Title (EN)</label>
                        <input type="text" name="title_en" id="projectTitleEn">
                    </div>
                </div>
                
                <div class="pj-form-row">
                    <div class="pj-form-group">
                        <label>Đạo diễn</label>
                        <input type="text" name="director" id="projectDirector">
                    </div>
                    <div class="pj-form-group">
                        <label>Diễn viên</label>
                        <input type="text" name="cast" id="projectCast">
                    </div>
                </div>
                
                <div class="pj-form-row">
                    <div class="pj-form-group">
                        <label>Năm sản xuất</label>
                        <input type="text" name="year" id="projectYear" placeholder="2026">
                    </div>
                    <div class="pj-form-group">
                        <label>Link xem (YouTube/Vimeo)</label>
                        <input type="text" name="link" id="projectLink" placeholder="https://...">
                    </div>
                </div>
                
                <div class="pj-form-row">
                    <div class="pj-form-group">
                        <label>Mô tả (VN)</label>
                        <textarea name="description" id="projectDesc" rows="3"></textarea>
                    </div>
                    <div class="pj-form-group">
                        <label>Description (EN)</label>
                        <textarea name="description_en" id="projectDescEn" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="pj-form-actions">
                    <button type="submit" class="btn btn-primary">Lưu</button>
                    <button type="button" class="btn btn-secondary" onclick="closeProjectModal()">Hủy</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function openProjectModal(action, item) {
        document.getElementById('projectModal').classList.add('show');
        document.getElementById('projectAction').value = action;
        document.getElementById('projectModalTitle').textContent = action === 'add' ? 'Thêm dự án' : 'Sửa dự án';
        
        var preview = document.getElementById('previewImage');
        
        if (action === 'edit' && item) {
            document.getElementById('projectId').value = item.id || '';
            document.getElementById('projectTitle').value = item.title || '';
            document.getElementById('projectTitleEn').value = item.title_en || '';
            document.getElementById('projectDirector').value = item.director || '';
            document.getElementById('projectCast').value = item.cast || '';
            document.getElementById('projectYear').value = item.year || '';
            document.getElementById('projectLink').value = item.link || '';
            document.getElementById('projectDesc').value = item.description || '';
            document.getElementById('projectDescEn').value = item.description_en || '';
            
            if (item.image) {
                preview.src = '../' + item.image;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        } else {
            document.getElementById('projectId').value = '';
            document.getElementById('projectTitle').value = '';
            document.getElementById('projectTitleEn').value = '';
            document.getElementById('projectDirector').value = '';
            document.getElementById('projectCast').value = '';
            document.getElementById('projectYear').value = '';
            document.getElementById('projectLink').value = '';
            document.getElementById('projectDesc').value = '';
            document.getElementById('projectDescEn').value = '';
            preview.style.display = 'none';
        }
    }
    
    function closeProjectModal() {
        document.getElementById('projectModal').classList.remove('show');
    }
    
    document.getElementById('projectModal').addEventListener('click', function(e) {
        if (e.target === this) closeProjectModal();
    });
    </script>
</body>
</html>
