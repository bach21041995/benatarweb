<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();
$gallery = isset($data['gallery']) ? $data['gallery'] : array();

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add' || $action === 'edit') {
        $id = isset($_POST['id']) ? $_POST['id'] : uniqid();
        $type = isset($_POST['type']) ? $_POST['type'] : 'image';
        $category = isset($_POST['category']) ? trim($_POST['category']) : '';
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $videoUrl = isset($_POST['video_url']) ? trim($_POST['video_url']) : '';
        $order = isset($_POST['order']) ? (int)$_POST['order'] : 0;
        
        // Upload thumbnail
        $thumbnail = isset($_POST['existing_thumbnail']) ? $_POST['existing_thumbnail'] : '';
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
            $uploaded = uploadFile($_FILES['thumbnail'], 'uploads');
            if ($uploaded) $thumbnail = $uploaded;
        }
        
        // Upload file (for images)
        $file = isset($_POST['existing_file']) ? $_POST['existing_file'] : '';
        if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
            $uploaded = uploadFile($_FILES['file'], 'uploads');
            if ($uploaded) $file = $uploaded;
        }
        
        $item = array(
            'id' => $id,
            'type' => $type,
            'category' => $category,
            'title' => $title,
            'description' => $description,
            'thumbnail' => $thumbnail,
            'video_url' => $videoUrl,
            'file' => $file,
            'order' => $order
        );
        
        if ($action === 'edit') {
            foreach ($gallery as $i => $g) {
                if ($g['id'] === $id) {
                    $data['gallery'][$i] = $item;
                    break;
                }
            }
        } else {
            $data['gallery'][] = $item;
        }
        
        saveData($data);
        setFlash('success', $action === 'edit' ? 'Đã cập nhật!' : 'Đã thêm mới!');
        header('Location: gallery.php');
        exit;
    }
    
    if ($action === 'delete') {
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        foreach ($gallery as $i => $g) {
            if ($g['id'] === $id) {
                if (!empty($g['thumbnail']) && strpos($g['thumbnail'], 'uploads/') === 0) {
                    deleteFile('../' . $g['thumbnail']);
                }
                if (!empty($g['file']) && strpos($g['file'], 'uploads/') === 0) {
                    deleteFile('../' . $g['file']);
                }
                array_splice($data['gallery'], $i, 1);
                break;
            }
        }
        saveData($data);
        setFlash('success', 'Đã xóa!');
        header('Location: gallery.php');
        exit;
    }
    
    if ($action === 'reorder') {
        $orders = isset($_POST['orders']) ? $_POST['orders'] : array();
        foreach ($orders as $id => $ord) {
            foreach ($data['gallery'] as $i => $g) {
                if ($g['id'] === $id) {
                    $data['gallery'][$i]['order'] = (int)$ord;
                    break;
                }
            }
        }
        saveData($data);
        setFlash('success', 'Đã cập nhật thứ tự!');
        header('Location: gallery.php');
        exit;
    }
}

// Sắp xếp theo order
usort($gallery, function($a, $b) {
    $orderA = isset($a['order']) ? (int)$a['order'] : 999999;
    $orderB = isset($b['order']) ? (int)$b['order'] : 999999;
    return $orderA - $orderB;
});

$flash = getFlash();

// Video categories
$categories = array('TVC', 'MV', 'Film');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tư liệu (Ảnh/Video) - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; margin-top: 25px; }
        .gallery-card { background: var(--bg-secondary); border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); }
        .gallery-thumb { position: relative; height: 180px; overflow: hidden; }
        .gallery-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .gallery-badge { position: absolute; top: 10px; left: 10px; background: var(--primary-color); color: #fff; padding: 4px 10px; font-size: 11px; border-radius: 4px; text-transform: uppercase; }
        .gallery-cat-badge { position: absolute; top: 10px; right: 10px; background: #C9A24A; color: #111; padding: 4px 10px; font-size: 11px; border-radius: 4px; font-weight: 600; }
        .gallery-play { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 50px; height: 50px; background: rgba(139,30,45,0.9); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .gallery-play svg { width: 20px; height: 20px; fill: #fff; margin-left: 3px; }
        .gallery-info { padding: 15px; }
        .gallery-title { font-size: 15px; font-weight: 600; color: var(--text-primary); margin-bottom: 5px; }
        .gallery-desc { font-size: 13px; color: var(--text-secondary); line-height: 1.5; }
        .gallery-order { font-size: 12px; color: var(--text-secondary); margin-top: 8px; }
        .gallery-actions { display: flex; gap: 10px; padding: 15px; border-top: 1px solid var(--border-color); }
        .btn-xs { padding: 6px 12px; font-size: 12px; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: rgba(40, 167, 69, 0.15); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.3); }
        
        /* Modal */
        .gal-modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; overflow-y: auto; padding: 20px; }
        .gal-modal-overlay.active { display: flex; }
        .gal-modal-box { background: #1a1a2e; border-radius: 12px; width: 600px; max-width: 100%; max-height: 90vh; overflow-y: auto; }
        .gal-modal-header { padding: 20px 25px; border-bottom: 1px solid #333; display: flex; justify-content: space-between; align-items: center; }
        .gal-modal-header h3 { margin: 0; font-size: 18px; color: #fff; }
        .gal-modal-close { background: none; border: none; font-size: 28px; color: #888; cursor: pointer; }
        .gal-modal-body { padding: 25px; }
        .gal-form-group { margin-bottom: 20px; }
        .gal-form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #ccc; font-size: 14px; }
        .gal-form-group input, .gal-form-group select, .gal-form-group textarea { width: 100%; padding: 12px 15px; border: 1px solid #444; border-radius: 6px; background: #0d0d1a; color: #fff; font-size: 14px; box-sizing: border-box; }
        .gal-form-group textarea { min-height: 80px; resize: vertical; }
        .gal-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .gal-form-hint { font-size: 12px; color: #666; margin-top: 5px; }
        .gal-modal-footer { padding: 20px 25px; border-top: 1px solid #333; display: flex; gap: 10px; justify-content: flex-end; }
        .current-thumb { width: 100px; height: 70px; object-fit: cover; border-radius: 6px; margin-top: 8px; }
        
        .filter-tabs { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-tab { padding: 8px 20px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-secondary); cursor: pointer; font-size: 14px; }
        .filter-tab:hover, .filter-tab.active { background: var(--primary-color); border-color: var(--primary-color); color: #fff; }
        
        @media(max-width: 600px) {
            .gal-form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Tư liệu (Ảnh/Video)</h1>
            <button type="button" class="btn btn-primary" onclick="openAddModal()">+ Thêm mới</button>
        </div>
        
        <?php if ($flash): ?>
        <div class="alert alert-success"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>
        
        <!-- Filter tabs -->
        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterItems('all')">Tất cả (<?php echo count($gallery); ?>)</button>
            <?php 
            $tvcCount = 0; $mvCount = 0; $filmCount = 0; $otherCount = 0;
            foreach ($gallery as $g) {
                $cat = isset($g['category']) ? $g['category'] : '';
                if ($cat === 'TVC') $tvcCount++;
                elseif ($cat === 'MV') $mvCount++;
                elseif ($cat === 'Film') $filmCount++;
                else $otherCount++;
            }
            ?>
            <button class="filter-tab" onclick="filterItems('TVC')">TVC (<?php echo $tvcCount; ?>)</button>
            <button class="filter-tab" onclick="filterItems('MV')">MV (<?php echo $mvCount; ?>)</button>
            <button class="filter-tab" onclick="filterItems('Film')">Film (<?php echo $filmCount; ?>)</button>
            <button class="filter-tab" onclick="filterItems('other')">Khác (<?php echo $otherCount; ?>)</button>
        </div>
        
        <div class="gallery-grid" id="galleryList">
            <?php foreach ($gallery as $item): 
                $thumb = !empty($item['thumbnail']) ? '../' . $item['thumbnail'] : 'https://placehold.co/400x300/222/C9A24A?text=' . urlencode($item['title'] ?: 'Item');
                $cat = isset($item['category']) ? $item['category'] : '';
            ?>
            <div class="gallery-card" data-category="<?php echo e($cat); ?>">
                <div class="gallery-thumb">
                    <img src="<?php echo $thumb; ?>" alt="<?php echo e($item['title']); ?>">
                    <span class="gallery-badge"><?php echo ($item['type'] === 'video') ? 'Video' : 'Ảnh'; ?></span>
                    <?php if (!empty($cat)): ?>
                    <span class="gallery-cat-badge"><?php echo e($cat); ?></span>
                    <?php endif; ?>
                    <?php if ($item['type'] === 'video'): ?>
                    <div class="gallery-play">
                        <svg viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="gallery-info">
                    <div class="gallery-title"><?php echo e($item['title']); ?></div>
                    <?php if (!empty($item['description'])): ?>
                    <div class="gallery-desc"><?php echo e(mb_substr($item['description'], 0, 60)); ?>...</div>
                    <?php endif; ?>
                    <div class="gallery-order">Thứ tự: <?php echo isset($item['order']) ? $item['order'] : 0; ?></div>
                </div>
                <div class="gallery-actions">
                    <button type="button" class="btn btn-secondary btn-xs" onclick='openEditModal(<?php echo json_encode($item); ?>)'>Sửa</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa item này?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo e($item['id']); ?>">
                        <button type="submit" class="btn btn-danger btn-xs">Xóa</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($gallery)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
            <p>Chưa có tư liệu nào. Nhấn "Thêm mới" để bắt đầu.</p>
        </div>
        <?php endif; ?>
    </main>
    
    <!-- Modal Add/Edit -->
    <div class="gal-modal-overlay" id="galModal">
        <div class="gal-modal-box">
            <div class="gal-modal-header">
                <h3 id="galModalTitle">Thêm mới</h3>
                <button type="button" class="gal-modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="galForm">
                <input type="hidden" name="action" id="galAction" value="add">
                <input type="hidden" name="id" id="galId">
                <input type="hidden" name="existing_thumbnail" id="galExistingThumb">
                <input type="hidden" name="existing_file" id="galExistingFile">
                
                <div class="gal-modal-body">
                    <div class="gal-form-row">
                        <div class="gal-form-group">
                            <label>Loại</label>
                            <select name="type" id="galType" onchange="toggleVideoFields()">
                                <option value="image">Ảnh</option>
                                <option value="video">Video</option>
                            </select>
                        </div>
                        <div class="gal-form-group">
                            <label>Phân loại Video</label>
                            <select name="category" id="galCategory">
                                <option value="">-- Không phân loại --</option>
                                <option value="TVC">TVC</option>
                                <option value="MV">MV</option>
                                <option value="Film">Film</option>
                            </select>
                            <p class="gal-form-hint">Chọn TVC/MV/Film để hiển thị trong bộ lọc</p>
                        </div>
                    </div>
                    
                    <div class="gal-form-group">
                        <label>Tiêu đề</label>
                        <input type="text" name="title" id="galTitle" placeholder="VD: MV Hồ Ngọc Hà - Cả Một Trời Thương Nhớ">
                    </div>
                    
                    <div class="gal-form-group">
                        <label>Mô tả</label>
                        <textarea name="description" id="galDesc" placeholder="Mô tả ngắn về tư liệu này..."></textarea>
                    </div>
                    
                    <div class="gal-form-group" id="videoUrlGroup">
                        <label>Link Video (YouTube/Google Drive/Vimeo)</label>
                        <input type="text" name="video_url" id="galVideoUrl" placeholder="https://youtube.com/watch?v=... hoặc https://drive.google.com/file/d/...">
                        <p class="gal-form-hint">Dán link video từ YouTube, Google Drive hoặc Vimeo</p>
                    </div>
                    
                    <div class="gal-form-row">
                        <div class="gal-form-group">
                            <label>Ảnh thumbnail</label>
                            <input type="file" name="thumbnail" accept="image/*">
                            <p class="gal-form-hint">Ảnh đại diện hiển thị trong gallery</p>
                            <img id="galThumbPreview" class="current-thumb" style="display:none;">
                        </div>
                        <div class="gal-form-group" id="fileUploadGroup">
                            <label>Upload ảnh/video</label>
                            <input type="file" name="file" accept="image/*,video/*">
                            <p class="gal-form-hint">Upload file nếu không dùng link</p>
                        </div>
                    </div>
                    
                    <div class="gal-form-group">
                        <label>Thứ tự hiển thị</label>
                        <input type="number" name="order" id="galOrder" value="0" min="0">
                        <p class="gal-form-hint">Số nhỏ hơn hiển thị trước</p>
                    </div>
                </div>
                
                <div class="gal-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function openAddModal() {
        document.getElementById('galModalTitle').textContent = 'Thêm tư liệu mới';
        document.getElementById('galAction').value = 'add';
        document.getElementById('galId').value = '';
        document.getElementById('galType').value = 'video';
        document.getElementById('galCategory').value = '';
        document.getElementById('galTitle').value = '';
        document.getElementById('galDesc').value = '';
        document.getElementById('galVideoUrl').value = '';
        document.getElementById('galOrder').value = '0';
        document.getElementById('galExistingThumb').value = '';
        document.getElementById('galExistingFile').value = '';
        document.getElementById('galThumbPreview').style.display = 'none';
        toggleVideoFields();
        document.getElementById('galModal').classList.add('active');
    }
    
    function openEditModal(item) {
        document.getElementById('galModalTitle').textContent = 'Sửa tư liệu';
        document.getElementById('galAction').value = 'edit';
        document.getElementById('galId').value = item.id || '';
        document.getElementById('galType').value = item.type || 'image';
        document.getElementById('galCategory').value = item.category || '';
        document.getElementById('galTitle').value = item.title || '';
        document.getElementById('galDesc').value = item.description || '';
        document.getElementById('galVideoUrl').value = item.video_url || '';
        document.getElementById('galOrder').value = item.order || 0;
        document.getElementById('galExistingThumb').value = item.thumbnail || '';
        document.getElementById('galExistingFile').value = item.file || '';
        
        if (item.thumbnail) {
            document.getElementById('galThumbPreview').src = '../' + item.thumbnail;
            document.getElementById('galThumbPreview').style.display = 'block';
        } else {
            document.getElementById('galThumbPreview').style.display = 'none';
        }
        
        toggleVideoFields();
        document.getElementById('galModal').classList.add('active');
    }
    
    function closeModal() {
        document.getElementById('galModal').classList.remove('active');
    }
    
    function toggleVideoFields() {
        var type = document.getElementById('galType').value;
        var videoGroup = document.getElementById('videoUrlGroup');
        if (type === 'video') {
            videoGroup.style.display = 'block';
        } else {
            videoGroup.style.display = 'none';
        }
    }
    
    function filterItems(category) {
        var cards = document.querySelectorAll('.gallery-card');
        var tabs = document.querySelectorAll('.filter-tab');
        
        tabs.forEach(function(tab) {
            tab.classList.remove('active');
            if (tab.textContent.toLowerCase().indexOf(category.toLowerCase()) !== -1 || (category === 'all' && tab.textContent.indexOf('Tất cả') !== -1)) {
                tab.classList.add('active');
            }
        });
        
        cards.forEach(function(card) {
            var cardCat = card.dataset.category || '';
            if (category === 'all') {
                card.style.display = '';
            } else if (category === 'other') {
                card.style.display = (cardCat === '' || (cardCat !== 'TVC' && cardCat !== 'MV' && cardCat !== 'Film')) ? '' : 'none';
            } else {
                card.style.display = (cardCat === category) ? '' : 'none';
            }
        });
    }
    
    document.getElementById('galModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
    </script>
</body>
</html>