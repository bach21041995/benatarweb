<?php
session_start();
require_once 'functions.php';
requireLogin();

$data = getData();
$contacts = $data['contacts'] ?? [];

// Xử lý các action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $contacts = array_filter($contacts, fn($c) => ($c['id'] ?? 0) !== $id);
        $contacts = array_values($contacts);
        $data['contacts'] = $contacts;
        
        if (saveData($data)) {
            setFlash('success', 'Đã xóa liên hệ!');
        }
    }
    
    if ($action === 'mark_read') {
        $id = (int)$_POST['id'];
        foreach ($contacts as &$contact) {
            if (($contact['id'] ?? 0) === $id) {
                $contact['read'] = true;
                break;
            }
        }
        $data['contacts'] = $contacts;
        saveData($data);
    }
    
    if ($action === 'mark_unread') {
        $id = (int)$_POST['id'];
        foreach ($contacts as &$contact) {
            if (($contact['id'] ?? 0) === $id) {
                $contact['read'] = false;
                break;
            }
        }
        $data['contacts'] = $contacts;
        saveData($data);
    }
    
    if ($action === 'delete_all_read') {
        $contacts = array_filter($contacts, fn($c) => empty($c['read']));
        $contacts = array_values($contacts);
        $data['contacts'] = $contacts;
        
        if (saveData($data)) {
            setFlash('success', 'Đã xóa tất cả liên hệ đã đọc!');
        }
    }
    
    header('Location: contacts.php');
    exit;
}

// Đếm số chưa đọc
$unreadCount = count(array_filter($contacts, fn($c) => empty($c['read'])));

// Sắp xếp mới nhất lên đầu
usort($contacts, fn($a, $b) => strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0'));

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Liên hệ khách hàng - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .contact-stats {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }
    .stat-card {
      background: #fff;
      padding: 20px 30px;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
      text-align: center;
    }
    .stat-number {
      font-size: 32px;
      font-weight: 700;
      color: #8B1E2D;
    }
    .stat-label {
      font-size: 13px;
      color: #666;
      margin-top: 5px;
    }
    .contact-table {
      width: 100%;
      border-collapse: collapse;
    }
    .contact-table th,
    .contact-table td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
    }
    .contact-table th {
      background: #f8f9fa;
      font-weight: 600;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #666;
    }
    .contact-table tr:hover {
      background: #fafafa;
    }
    .contact-table tr.unread {
      background: #fff9e6;
    }
    .contact-table tr.unread:hover {
      background: #fff3cc;
    }
    .contact-name {
      font-weight: 600;
      color: #333;
    }
    .contact-email {
      font-size: 13px;
      color: #666;
    }
    .contact-phone {
      font-size: 13px;
      color: #888;
    }
    .contact-message {
      max-width: 300px;
      font-size: 13px;
      color: #555;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .contact-date {
      font-size: 12px;
      color: #999;
    }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
    }
    .badge-new {
      background: #8B1E2D;
      color: #fff;
    }
    .badge-read {
      background: #e0e0e0;
      color: #666;
    }
    .actions-cell {
      white-space: nowrap;
    }
    .btn-icon {
      padding: 6px 10px;
      font-size: 12px;
    }
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #999;
    }
    .empty-state svg {
      width: 60px;
      height: 60px;
      stroke: #ddd;
      margin-bottom: 15px;
    }
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.active {
      display: flex;
    }
    .modal-content {
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      max-width: 500px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .modal-title {
      font-size: 18px;
      font-weight: 600;
    }
    .modal-close {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #999;
    }
    .modal-body p {
      margin-bottom: 15px;
    }
    .modal-body strong {
      color: #333;
    }
    .message-full {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      margin-top: 15px;
      white-space: pre-wrap;
      line-height: 1.6;
    }
  </style>
</head>
<body>
  <div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">📬 Liên hệ khách hàng</h1>
          <div class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span>/</span>
            <span>Liên hệ</span>
          </div>
        </div>
        <?php if (count(array_filter($contacts, fn($c) => !empty($c['read']))) > 0): ?>
        <form method="POST" onsubmit="return confirm('Xóa tất cả liên hệ đã đọc?')">
          <input type="hidden" name="action" value="delete_all_read">
          <button type="submit" class="btn btn-secondary">🗑️ Xóa đã đọc</button>
        </form>
        <?php endif; ?>
      </div>
      
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      
      <div class="contact-stats">
        <div class="stat-card">
          <div class="stat-number"><?= count($contacts) ?></div>
          <div class="stat-label">Tổng liên hệ</div>
        </div>
        <div class="stat-card">
          <div class="stat-number" style="color: #e74c3c;"><?= $unreadCount ?></div>
          <div class="stat-label">Chưa đọc</div>
        </div>
        <div class="stat-card">
          <div class="stat-number" style="color: #27ae60;"><?= count($contacts) - $unreadCount ?></div>
          <div class="stat-label">Đã đọc</div>
        </div>
      </div>
      
      <div class="card">
        <div class="card-body" style="padding: 0;">
          <?php if (empty($contacts)): ?>
            <div class="empty-state">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
              </svg>
              <p>Chưa có liên hệ nào từ khách hàng</p>
            </div>
          <?php else: ?>
            <table class="contact-table">
              <thead>
                <tr>
                  <th>Trạng thái</th>
                  <th>Khách hàng</th>
                  <th>Nội dung</th>
                  <th>Thời gian</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($contacts as $contact): ?>
                  <tr class="<?= empty($contact['read']) ? 'unread' : '' ?>">
                    <td>
                      <?php if (empty($contact['read'])): ?>
                        <span class="badge badge-new">Mới</span>
                      <?php else: ?>
                        <span class="badge badge-read">Đã đọc</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="contact-name"><?= e($contact['name'] ?? 'N/A') ?></div>
                      <div class="contact-email"><?= e($contact['email'] ?? '') ?></div>
                      <div class="contact-phone"><?= e($contact['phone'] ?? '') ?></div>
                    </td>
                    <td>
                      <div class="contact-message"><?= e($contact['message'] ?? '') ?></div>
                    </td>
                    <td>
                      <div class="contact-date"><?= date('d/m/Y H:i', strtotime($contact['created_at'] ?? 'now')) ?></div>
                    </td>
                    <td class="actions-cell">
                      <button type="button" class="btn btn-secondary btn-icon" onclick="viewContact(<?= htmlspecialchars(json_encode($contact), ENT_QUOTES) ?>)">👁️</button>
                      
                      <?php if (empty($contact['read'])): ?>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="action" value="mark_read">
                          <input type="hidden" name="id" value="<?= $contact['id'] ?? 0 ?>">
                          <button type="submit" class="btn btn-secondary btn-icon" title="Đánh dấu đã đọc">✓</button>
                        </form>
                      <?php else: ?>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="action" value="mark_unread">
                          <input type="hidden" name="id" value="<?= $contact['id'] ?? 0 ?>">
                          <button type="submit" class="btn btn-secondary btn-icon" title="Đánh dấu chưa đọc">↩️</button>
                        </form>
                      <?php endif; ?>
                      
                      <form method="POST" style="display: inline;" onsubmit="return confirm('Xóa liên hệ này?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $contact['id'] ?? 0 ?>">
                        <button type="submit" class="btn btn-danger btn-icon">🗑️</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>
  
  <!-- Modal xem chi tiết -->
  <div class="modal-overlay" id="viewModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">📧 Chi tiết liên hệ</h3>
        <button class="modal-close" onclick="closeModal()">&times;</button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Content will be inserted here -->
      </div>
    </div>
  </div>
  
  <script>
    function viewContact(contact) {
      const modal = document.getElementById('viewModal');
      const body = document.getElementById('modalBody');
      
      body.innerHTML = `
        <p><strong>Họ tên:</strong> ${contact.name || 'N/A'}</p>
        <p><strong>Email:</strong> <a href="mailto:${contact.email || ''}">${contact.email || 'N/A'}</a></p>
        <p><strong>Điện thoại:</strong> <a href="tel:${contact.phone || ''}">${contact.phone || 'N/A'}</a></p>
        <p><strong>Thời gian:</strong> ${contact.created_at || 'N/A'}</p>
        <p><strong>Nội dung:</strong></p>
        <div class="message-full">${contact.message || 'Không có nội dung'}</div>
      `;
      
      modal.classList.add('active');
      
      // Mark as read via AJAX
      if (!contact.read) {
        fetch('contacts.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'action=mark_read&id=' + contact.id
        }).then(() => {
          // Update UI
          document.querySelector(`tr.unread input[value="${contact.id}"]`)?.closest('tr')?.classList.remove('unread');
        });
      }
    }
    
    function closeModal() {
      document.getElementById('viewModal').classList.remove('active');
    }
    
    // Close modal on outside click
    document.getElementById('viewModal').addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });
    
    // Close modal on ESC
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeModal();
    });
  </script>
</body>
</html>
