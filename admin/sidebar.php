<?php
// Đọc menu từ JSON
$menuData = getData();
$adminMenus = $menuData['admin_menus'] ?? [];
$menuSections = $menuData['menu_sections'] ?? [];

// Nếu chưa có menu, dùng mặc định
if (empty($adminMenus)) {
    $adminMenus = [
        ['id' => 'dashboard', 'title' => 'Dashboard', 'page' => 'index', 'icon' => 'home', 'section' => 'overview', 'order' => 0, 'active' => true, 'system' => true],
        ['id' => 'company', 'title' => 'Thông tin công ty', 'page' => 'company', 'icon' => 'building', 'section' => 'content', 'order' => 1, 'active' => true],
        ['id' => 'hero', 'title' => 'Banner trang chủ', 'page' => 'hero', 'icon' => 'image', 'section' => 'content', 'order' => 2, 'active' => true],
        ['id' => 'about', 'title' => 'Về chúng tôi', 'page' => 'about', 'icon' => 'info', 'section' => 'content', 'order' => 3, 'active' => true],
        ['id' => 'services', 'title' => 'Dịch vụ', 'page' => 'services', 'icon' => 'briefcase', 'section' => 'content', 'order' => 4, 'active' => true],
        ['id' => 'team', 'title' => 'Đội ngũ', 'page' => 'team', 'icon' => 'users', 'section' => 'content', 'order' => 5, 'active' => true],
        ['id' => 'features', 'title' => 'Điểm nổi bật', 'page' => 'features', 'icon' => 'star', 'section' => 'content', 'order' => 6, 'active' => true],
        ['id' => 'process', 'title' => 'Quy trình', 'page' => 'process', 'icon' => 'list', 'section' => 'content', 'order' => 7, 'active' => true],
        ['id' => 'clients', 'title' => 'Khách hàng', 'page' => 'clients', 'icon' => 'user', 'section' => 'content', 'order' => 8, 'active' => true],
        ['id' => 'gallery', 'title' => 'Tư liệu (Ảnh/Video)', 'page' => 'gallery', 'icon' => 'camera', 'section' => 'content', 'order' => 9, 'active' => true],
        ['id' => 'contacts', 'title' => 'Liên hệ nhận được', 'page' => 'contacts', 'icon' => 'mail', 'section' => 'customers', 'order' => 10, 'active' => true],
        ['id' => 'menus', 'title' => 'Quản lý Menu', 'page' => 'menus', 'icon' => 'menu', 'section' => 'settings', 'order' => 11, 'active' => true, 'system' => true],
        ['id' => 'settings', 'title' => 'Cài đặt chung', 'page' => 'settings', 'icon' => 'settings', 'section' => 'settings', 'order' => 12, 'active' => true],
    ];
    $menuSections = [
        ['id' => 'overview', 'title' => 'Tổng quan', 'order' => 0],
        ['id' => 'content', 'title' => 'Nội dung', 'order' => 1],
        ['id' => 'customers', 'title' => 'Khách hàng', 'order' => 2],
        ['id' => 'settings', 'title' => 'Cài đặt', 'order' => 3],
    ];
}

// Sắp xếp
usort($adminMenus, fn($a, $b) => ($a['order'] ?? 0) - ($b['order'] ?? 0));
usort($menuSections, fn($a, $b) => ($a['order'] ?? 0) - ($b['order'] ?? 0));

// Lấy trang hiện tại
$currentPage = basename($_SERVER['PHP_SELF']);

// Đếm liên hệ chưa đọc
$unreadContacts = count(array_filter($menuData['contacts'] ?? [], fn($c) => empty($c['read'])));

// Hàm render icon - ĐẦY ĐỦ TẤT CẢ ICONS
function sidebarIcon($name) {
    $icons = [
        'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'building' => '<path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16"/><path d="M1 21h22"/><path d="M9 7h1M9 11h1M9 15h1M14 7h1M14 11h1M14 15h1"/>',
        'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'box' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>',
        'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'heart' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
        'list' => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
        'gallery' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
        'camera' => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
        'film' => '<rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'message' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'menu' => '<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        'zap' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'layers' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        'edit' => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'plus-square' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>',
    ];
    $path = $icons[$name] ?? $icons['file'];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' . $path . '</svg>';
}
?>
<!-- Sidebar Navigation -->
<aside class="sidebar">
  <div class="sidebar-header">
    <a href="index.php" class="sidebar-logo">
      Admin<span>Panel</span>
    </a>
  </div>
  
  <nav class="sidebar-nav">
    <?php foreach ($menuSections as $section): ?>
      <?php 
      $sectionMenus = array_filter($adminMenus, fn($m) => 
          ($m['section'] ?? '') === $section['id'] && 
          ($m['active'] ?? true)
      );
      if (empty($sectionMenus)) continue;
      ?>
      <div class="nav-section">
        <div class="nav-section-title"><?= htmlspecialchars($section['title']) ?></div>
        
        <?php foreach ($sectionMenus as $menu): 
          $pageFile = ($menu['page'] ?? 'index') . '.php';
          $isActive = $currentPage === $pageFile;
          $url = !empty($menu['custom_url']) ? $menu['custom_url'] : $pageFile;
        ?>
          <a href="<?= htmlspecialchars($url) ?>" class="nav-link <?= $isActive ? 'active' : '' ?>">
            <?= sidebarIcon($menu['icon'] ?? 'file') ?>
            <?= htmlspecialchars($menu['title']) ?>
            <?php if ($menu['page'] === 'contacts' && $unreadContacts > 0): ?>
              <span style="background:#e74c3c;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px;margin-left:auto;">
                <?= $unreadContacts ?>
              </span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    
    <!-- Logout luôn hiển thị -->
    <div class="nav-section">
      <a href="logout.php" class="nav-link">
        <?= sidebarIcon('logout') ?>
        Đăng xuất
      </a>
    </div>
  </nav>
</aside>
