<?php
// Load content data
$dataFile = __DIR__ . '/data/content.json';
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

// Helper function for images
function img($path, $placeholder = '800x600') {
    if (empty($path)) return "https://placehold.co/{$placeholder}/111/C9A24A?text=Image";
    if (strpos($path, 'http') === 0) return $path;
    $fullPath = __DIR__ . '/' . $path;
    return file_exists($fullPath) ? $path : "https://placehold.co/{$placeholder}/111/C9A24A?text=Image";
}

// Get data sections
$company = $data['company'] ?? [];
$hero = $data['hero'] ?? [];
$about = $data['about'] ?? [];
$services = $data['services'] ?? [];
$team = $data['team'] ?? [];
$features = $data['features'] ?? [];
$process = $data['process'] ?? [];
$clients = $data['clients'] ?? [];
$cta = $data['cta'] ?? [];
$settings = $data['settings'] ?? [];

// New sections data
$partners = $data['partners'] ?? [];
$roadmap = $data['roadmap'] ?? [];
$projects = $data['projects'] ?? [];

// Admin menus - dùng để kiểm tra section nào hiển thị
$adminMenus = $data['admin_menus'] ?? [];

// Frontend sections - chứa tiêu đề tùy chỉnh
$frontendSections = $data['frontend_sections'] ?? [];

// CTA data
$cta = $data['cta'] ?? [];

// Footer data  
$footer = $data['footer'] ?? [];

// Helper: check if section is active (đọc từ admin_menus)
function isSectionActive($sectionId, $menus) {
    foreach ($menus as $menu) {
        if (($menu['frontend_section'] ?? '') === $sectionId) {
            return $menu['active'] ?? true;
        }
    }
    return true; // Default: show if not configured
}

// Helper: get section title/label from frontend_sections
function getSectionText($sectionId, $field, $sections) {
    foreach ($sections as $s) {
        if ($s['id'] === $sectionId && !empty($s[$field])) {
            return $s[$field];
        }
    }
    return '';
}

// Get colors
$primaryColor = $settings['primary_color'] ?? '#8B1E2D';
$secondaryColor = $settings['secondary_color'] ?? '#C9A24A';

// Icons SVG
$icons = [
    'lightbulb' => '<svg viewBox="0 0 24 24" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
    'film' => '<svg viewBox="0 0 24 24" stroke-width="1.5"><rect x="2" y="2" width="20" height="20" rx="2"/><path d="M7 2v20M17 2v20M2 12h20"/></svg>',
    'users' => '<svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'camera' => '<svg viewBox="0 0 24 24" stroke-width="1.5"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>',
    'settings' => '<svg viewBox="0 0 24 24" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    'layers' => '<svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($company['name'] ?? 'Creative Studio') ?> - Documentary Cinematic Production</title>
  <meta name="description" content="<?= htmlspecialchars($hero['subtitle'] ?? '') ?>">
  
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  
  <?php if (!empty($settings['favicon'])): ?>
  <link rel="icon" href="<?= img($settings['favicon']) ?>">
  <?php endif; ?>
  
  <!-- Dynamic Colors from Admin -->
  <style>
    :root {
      --color-primary: <?= htmlspecialchars($primaryColor) ?>;
      --color-secondary: <?= htmlspecialchars($secondaryColor) ?>;
      --color-accent: <?= htmlspecialchars($secondaryColor) ?>;
    }
    .lang-switcher{display:flex;align-items:center;gap:5px;margin-left:20px}
    .lang-btn{padding:6px 12px;font-size:11px;font-weight:600;letter-spacing:1px;background:transparent;border:1px solid rgba(245,243,238,.3);color:var(--color-text-secondary);cursor:pointer;transition:var(--transition)}
    .lang-btn:hover{border-color:var(--color-secondary);color:var(--color-secondary)}
    .lang-btn.active{background:var(--color-secondary);border-color:var(--color-secondary);color:var(--color-bg-dark)}
    .header-right{display:flex;align-items:center;gap:15px}
    .mobile-lang{display:flex;gap:10px;margin-top:30px}
    @media(max-width:768px){.lang-switcher{display:none}}
    
    /* Gallery Slider - giống team-slider */
    .gallery-slider{display:flex;gap:30px;overflow-x:auto;padding:40px 0;scroll-snap-type:x mandatory;justify-content:center;flex-wrap:wrap}
    .gallery-slider::-webkit-scrollbar{display:none}
    .gallery-slider{-ms-overflow-style:none;scrollbar-width:none}
    .gallery-item{flex:0 0 350px;height:280px;position:relative;overflow:hidden;cursor:pointer;scroll-snap-align:start;border:1px solid rgba(245,243,238,0.1);transition:all 0.5s ease}
    .gallery-item:hover{transform:scale(1.02);border-color:var(--color-primary)}
    .gallery-item img{width:100%;height:100%;object-fit:cover;transition:all 0.5s ease;filter:saturate(0.8)}
    .gallery-item:hover img{filter:saturate(1);transform:scale(1.05)}
    .gallery-play-btn{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:70px;height:70px;background:rgba(139,30,45,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center;transition:all 0.3s ease;z-index:2}
    .gallery-play-btn svg{width:28px;height:28px;fill:#fff;margin-left:4px}
    .gallery-item:hover .gallery-play-btn{background:var(--color-primary);transform:translate(-50%,-50%) scale(1.1)}
    .gallery-overlay{position:absolute;bottom:0;left:0;right:0;padding:25px;background:linear-gradient(to top,rgba(17,17,17,0.95),transparent);transform:translateY(100%);transition:all 0.4s ease}
    .gallery-item:hover .gallery-overlay{transform:translateY(0)}
    .gallery-overlay h4{font-family:var(--font-heading);font-size:18px;color:var(--color-secondary);margin-bottom:5px}
    .gallery-overlay p{font-size:13px;color:var(--color-text-secondary);line-height:1.5}
    .gallery-filter{display:flex;justify-content:center;gap:15px;margin-bottom:30px;flex-wrap:wrap}
    .filter-btn{padding:10px 25px;background:transparent;border:1px solid rgba(245,243,238,0.3);color:var(--color-text);font-size:14px;cursor:pointer;transition:all 0.3s ease;font-family:var(--font-body)}
    .filter-btn:hover,.filter-btn.active{background:var(--color-primary);border-color:var(--color-primary);color:#fff}
    .gallery-category{position:absolute;top:10px;left:10px;background:var(--color-primary);color:#fff;padding:4px 12px;font-size:11px;font-weight:600;z-index:3;text-transform:uppercase}
    .gallery-item.hidden{display:none}
    .service-clickable{cursor:pointer}
    .service-clickable:hover{transform:translateY(-8px)}
    .service-link{display:inline-block;margin-top:15px;color:var(--color-primary);font-size:14px;font-weight:500;transition:all 0.3s ease}
    .service-clickable:hover .service-link{color:var(--color-secondary);transform:translateX(5px)}
    
    /* Gallery Modal */
    .gallery-modal{position:fixed;inset:0;background:rgba(0,0,0,0.95);z-index:3000;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:all 0.3s ease}
    .gallery-modal.active{opacity:1;visibility:visible}
    .gallery-modal-content{max-width:90vw;max-height:90vh;position:relative}
    .gallery-modal-content img{max-width:100%;max-height:85vh;object-fit:contain}
    .gallery-modal-content iframe{width:80vw;height:45vw;max-height:80vh;border:none}
    .gallery-modal-close{position:absolute;top:-40px;right:0;background:none;border:none;color:#fff;font-size:32px;cursor:pointer;padding:10px}
    .gallery-modal-close:hover{color:var(--color-secondary)}
    
    @media(max-width:768px){
      .gallery-slider{flex-wrap:nowrap;justify-content:flex-start;overflow-x:auto;padding:20px}
      .gallery-item{flex:0 0 280px;height:200px}
      .gallery-modal-content iframe{width:95vw;height:55vw}
    }
  
    /* Partners Section */
    .partners-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:30px;margin-top:50px}
    .partner-card{background:rgba(139,30,45,0.05);border:1px solid rgba(139,30,45,0.2);padding:40px 30px;text-align:center;transition:all 0.4s ease}
    .partner-card:hover{background:rgba(139,30,45,0.1);border-color:var(--color-primary);transform:translateY(-5px)}
    .partner-icon{width:60px;height:60px;margin:0 auto 20px;display:flex;align-items:center;justify-content:center;background:var(--color-primary);border-radius:50%}
    .partner-icon svg{width:28px;height:28px;stroke:var(--color-secondary);fill:none}
    .partner-title{font-family:var(--font-heading);font-size:20px;color:var(--color-secondary);margin-bottom:15px}
    .partner-desc{font-size:14px;color:var(--color-text-secondary);line-height:1.7}
    
    /* Roadmap Timeline */
    .roadmap-container{max-width:900px;margin:50px auto 0}
    .roadmap-phase{margin-bottom:50px}
    .roadmap-phase-title{font-family:var(--font-heading);font-size:28px;color:var(--color-secondary);text-align:center;margin-bottom:40px;padding-bottom:15px;border-bottom:2px solid var(--color-primary)}
    .roadmap-timeline{position:relative;padding-left:50px}
    .roadmap-timeline::before{content:'';position:absolute;left:20px;top:0;bottom:0;width:2px;background:linear-gradient(to bottom,var(--color-primary),var(--color-secondary))}
    .roadmap-item{position:relative;padding:25px 30px;background:rgba(17,17,17,0.8);border:1px solid rgba(139,30,45,0.3);margin-bottom:25px;transition:all 0.3s ease}
    .roadmap-item:hover{border-color:var(--color-primary);background:rgba(139,30,45,0.1)}
    .roadmap-item::before{content:'';position:absolute;left:-38px;top:30px;width:16px;height:16px;background:var(--color-primary);border-radius:50%;border:3px solid var(--color-bg-dark)}
    .roadmap-month{font-size:12px;color:var(--color-secondary);letter-spacing:2px;text-transform:uppercase;margin-bottom:8px}
    .roadmap-title{font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:10px;display:flex;align-items:center;gap:15px;flex-wrap:wrap}
    .roadmap-status{font-size:11px;padding:4px 12px;border-radius:20px;font-weight:600}
    .roadmap-status.completed{background:#28a745;color:#fff}
    .roadmap-status.in_progress{background:#ffc107;color:#000}
    .roadmap-status.upcoming{background:#6c757d;color:#fff}
    .roadmap-desc{font-size:14px;color:var(--color-text-secondary);line-height:1.7}
    
    /* Projects Section */
    .projects-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:30px;margin-top:50px}
    .project-card{position:relative;overflow:hidden;aspect-ratio:4/3;cursor:pointer}
    .project-card img{width:100%;height:100%;object-fit:cover;transition:all 0.6s ease;filter:saturate(0.8)}
    .project-card:hover img{transform:scale(1.05);filter:saturate(1)}
    .project-content{position:absolute;bottom:0;left:0;right:0;padding:30px;background:linear-gradient(to top,rgba(17,17,17,0.95) 0%,rgba(17,17,17,0.8) 50%,transparent 100%);transform:translateY(30%);transition:all 0.4s ease}
    .project-card:hover .project-content{transform:translateY(0)}
    .project-meta{font-size:12px;color:var(--color-secondary);margin-bottom:8px;letter-spacing:1px}
    .project-title{font-family:var(--font-heading);font-size:22px;color:#fff;margin-bottom:10px}
    .project-desc{font-size:14px;color:var(--color-text-secondary);line-height:1.6;opacity:0;transition:opacity 0.3s ease}
    .project-card:hover .project-desc{opacity:1}

    @media(max-width:768px){
      .roadmap-timeline{padding-left:40px}
      .roadmap-item{padding:20px}
      .roadmap-timeline::before{left:15px}
      .roadmap-item::before{left:-33px;width:14px;height:14px}
    }

  </style>
</head>
<body>

<!-- Header -->
<header class="header" id="header">
  <div class="container header-inner">
    <?php if (!empty($settings['logo'])): ?>
      <a href="#" class="logo"><img src="<?= img($settings['logo']) ?>" alt="<?= htmlspecialchars($company['name'] ?? 'Logo') ?>"></a>
    <?php else: ?>
      <a href="#" class="logo"><?= htmlspecialchars($company['name'] ?? 'Creative') ?><span>.</span></a>
    <?php endif; ?>
    
    <nav class="nav">
      <?php if (isSectionActive('about', $adminMenus)): ?><a href="#about" data-lang-vi="Về chúng tôi" data-lang-en="About Us">Về chúng tôi</a><?php endif; ?>
      <?php if (isSectionActive('services', $adminMenus)): ?><a href="#services" data-lang-vi="Dịch vụ" data-lang-en="Services">Dịch vụ</a><?php endif; ?>
      <?php if (isSectionActive('gallery', $adminMenus)): ?><a href="#gallery" data-lang-vi="Tư liệu" data-lang-en="Gallery">Tư liệu</a><?php endif; ?>
      <?php if (isSectionActive('team', $adminMenus)): ?><a href="#team" data-lang-vi="Đội ngũ" data-lang-en="Team">Đội ngũ</a><?php endif; ?>
      <?php if (isSectionActive('features', $adminMenus)): ?><a href="#features" data-lang-vi="Thế mạnh" data-lang-en="Why Us">Thế mạnh</a><?php endif; ?>
      <?php if (isSectionActive('partners', $adminMenus)): ?><a href="#partners" data-lang-vi="Đối tác" data-lang-en="Partners">Đối tác</a><?php endif; ?>
      <?php if (isSectionActive('roadmap', $adminMenus)): ?><a href="#roadmap" data-lang-vi="Lộ trình" data-lang-en="Roadmap">Lộ trình</a><?php endif; ?>
      <?php if (isSectionActive('projects', $adminMenus)): ?><a href="#projects" data-lang-vi="Dự án" data-lang-en="Projects">Dự án</a><?php endif; ?>

      <?php if (isSectionActive('contact', $adminMenus)): ?><a href="#contact" data-lang-vi="Liên hệ" data-lang-en="Contact">Liên hệ</a><?php endif; ?>
    </nav>
    
    <div class="header-right">
      <div class="lang-switcher">
        <button class="lang-btn active" onclick="switchLang('vi')">VI</button>
        <button class="lang-btn" onclick="switchLang('en')">EN</button>
      </div>
      <a href="#contact" class="btn btn-primary" onclick="openPopup(); return false;" data-lang-vi="Kết nối ngay" data-lang-en="Get in Touch">Kết nối ngay</a>
    </div>
    
    <button class="menu-toggle" onclick="document.getElementById('mobileMenu').classList.toggle('active')">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
  <?php if (isSectionActive('about', $adminMenus)): ?><a href="#about" onclick="this.parentElement.classList.remove('active')" data-lang-vi="Về chúng tôi" data-lang-en="About Us">Về chúng tôi</a><?php endif; ?>
  <?php if (isSectionActive('services', $adminMenus)): ?><a href="#services" onclick="this.parentElement.classList.remove('active')" data-lang-vi="Dịch vụ" data-lang-en="Services">Dịch vụ</a><?php endif; ?>
  <?php if (isSectionActive('gallery', $adminMenus)): ?><a href="#gallery" onclick="this.parentElement.classList.remove('active')" data-lang-vi="Tư liệu" data-lang-en="Gallery">Tư liệu</a><?php endif; ?>
  <?php if (isSectionActive('team', $adminMenus)): ?><a href="#team" onclick="this.parentElement.classList.remove('active')" data-lang-vi="Đội ngũ" data-lang-en="Team">Đội ngũ</a><?php endif; ?>
  <?php if (isSectionActive('features', $adminMenus)): ?><a href="#features" onclick="this.parentElement.classList.remove('active')" data-lang-vi="Thế mạnh" data-lang-en="Why Us">Thế mạnh</a><?php endif; ?>
  <?php if (isSectionActive('partners', $adminMenus)): ?><a href="#partners" onclick="this.parentElement.classList.remove('active')" data-lang-vi="Đối tác" data-lang-en="Partners">Đối tác</a><?php endif; ?>
  <?php if (isSectionActive('roadmap', $adminMenus)): ?><a href="#roadmap" onclick="this.parentElement.classList.remove('active')" data-lang-vi="Lộ trình" data-lang-en="Roadmap">Lộ trình</a><?php endif; ?>
  <?php if (isSectionActive('projects', $adminMenus)): ?><a href="#projects" onclick="this.parentElement.classList.remove('active')" data-lang-vi="Dự án" data-lang-en="Projects">Dự án</a><?php endif; ?>
  <?php if (isSectionActive('contact', $adminMenus)): ?><a href="#" onclick="openPopup(); this.parentElement.classList.remove('active'); return false;" data-lang-vi="Liên hệ" data-lang-en="Contact">Liên hệ</a><?php endif; ?>
  <div class="mobile-lang">
    <button class="lang-btn active" onclick="switchLang('vi')">VI</button>
    <button class="lang-btn" onclick="switchLang('en')">EN</button>
  </div>
</div>

<?php if (isSectionActive('hero', $adminMenus)): ?>
<!-- Hero Section -->
<section class="hero" id="home">
  <div class="hero-bg"></div>
  
  <?php if (!empty($hero['background_video'])): ?>
    <video class="hero-bg-video" autoplay muted loop playsinline>
      <source src="<?= img($hero['background_video']) ?>" type="video/mp4">
    </video>
  <?php elseif (!empty($hero['background_image'])): ?>
    <div class="hero-bg-image" style="background-image: url('<?= img($hero['background_image'], '1920x1080') ?>')"></div>
  <?php else: ?>
    <div class="hero-bg-image" style="background-image: url('https://images.unsplash.com/photo-1485846234645-a62644f84728?w=1920&q=80')"></div>
  <?php endif; ?>
  
  <div class="hero-content">
    <p class="hero-label"><?= htmlspecialchars($hero['label'] ?? 'Documentary Cinematic Production') ?></p>
    <h1 class="hero-title">
      <span class="lang-vi">Kể câu chuyện<br>bằng <span class="text-accent">hình ảnh</span></span>
      <span class="lang-en" style="display:none">Telling stories<br>through <span class="text-accent">visuals</span></span>
    </h1>
    <p class="hero-subtitle">
      <span class="lang-vi">Chúng tôi kể những câu chuyện thật qua trải nghiệm phim tài liệu điện ảnh.</span>
      <span class="lang-en" style="display:none">We tell real stories through cinematic documentary experiences.</span>
    </p>
    <div class="hero-cta">
      <a href="#contact" class="btn btn-primary" onclick="openPopup(); return false;" data-lang-vi="Kết nối ngay" data-lang-en="Get in Touch">Kết nối ngay</a>
      <a href="#services" class="btn btn-outline" data-lang-vi="Khám phá" data-lang-en="Explore">Khám phá</a>
    </div>
  </div>
  
  <div class="scroll-indicator">
    <span data-lang-vi="Cuộn xuống" data-lang-en="Scroll down">Cuộn xuống</span>
    <div class="scroll-line"></div>
  </div>
</section>
<?php endif; ?>

<?php if (isSectionActive('about', $adminMenus)): ?>
<?php 
$aboutLabel = getSectionText('about', 'label_vi', $frontendSections) ?: 'Về chúng tôi';
$aboutLabelEn = getSectionText('about', 'label_en', $frontendSections) ?: 'About Us';
$aboutTitle = getSectionText('about', 'title_vi', $frontendSections) ?: (isset($about['title']) ? $about['title'] : '');
$aboutTitleEn = getSectionText('about', 'title_en', $frontendSections) ?: (isset($about['title_en']) ? $about['title_en'] : '');
// Đọc paragraphs (array hoặc string)
$paragraphsVi = isset($about['paragraphs_vi']) ? $about['paragraphs_vi'] : (isset($about['paragraphs']) ? $about['paragraphs'] : array());
$paragraphsEn = isset($about['paragraphs_en']) ? $about['paragraphs_en'] : array();
if (is_string($paragraphsVi)) $paragraphsVi = array($paragraphsVi);
if (is_string($paragraphsEn)) $paragraphsEn = array($paragraphsEn);
$aboutQuoteVi = isset($about['quote_vi']) ? $about['quote_vi'] : (isset($about['quote']) ? $about['quote'] : '');
$aboutQuoteEn = isset($about['quote_en']) ? $about['quote_en'] : '';
?>
<!-- About Section -->
<section class="section section-dark" id="about">
  <div class="container">
    <div class="about-grid">
      <div class="about-image">
        <img src="<?= img($about['image'] ?? '', '800x600') ?>" alt="About us">
      </div>
      <div class="about-content">
        <p class="section-label" data-lang-vi="<?= htmlspecialchars($aboutLabel) ?>" data-lang-en="<?= htmlspecialchars($aboutLabelEn) ?>"><?= htmlspecialchars($aboutLabel) ?></p>
        <?php if (!empty($aboutTitle) || !empty($aboutTitleEn)): ?>
        <h2 class="section-title">
          <span class="lang-vi"><?= $aboutTitle ?></span>
          <span class="lang-en" style="display:none"><?= $aboutTitleEn ?: $aboutTitle ?></span>
        </h2>
        <?php endif; ?>
        
        <?php foreach ($paragraphsVi as $i => $pVi): ?>
        <p class="about-text">
          <span class="lang-vi"><?= nl2br(htmlspecialchars($pVi)) ?></span>
          <span class="lang-en" style="display:none"><?= nl2br(htmlspecialchars(isset($paragraphsEn[$i]) ? $paragraphsEn[$i] : $pVi)) ?></span>
        </p>
        <?php endforeach; ?>
        
        <?php if (!empty($aboutQuoteVi) || !empty($aboutQuoteEn)): ?>
        <blockquote class="about-quote">
          <span class="lang-vi">"<?= htmlspecialchars($aboutQuoteVi) ?>"</span>
          <span class="lang-en" style="display:none">"<?= htmlspecialchars($aboutQuoteEn ?: $aboutQuoteVi) ?>"</span>
        </blockquote>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (isSectionActive('services', $adminMenus) && !empty($services)): ?>
<!-- Services Section -->
<?php 
$svcLabel = getSectionText('services', 'label_vi', $frontendSections) ?: 'Dịch vụ';
$svcLabelEn = getSectionText('services', 'label_en', $frontendSections) ?: 'Services';
$svcTitle = getSectionText('services', 'title_vi', $frontendSections) ?: 'Cách chúng tôi kể';
$svcTitleEn = getSectionText('services', 'title_en', $frontendSections) ?: 'How We Tell Stories';
?>
<section class="section" id="services">
  <div class="container">
    <div class="text-center">
      <p class="section-label" data-lang-vi="<?= htmlspecialchars($svcLabel) ?>" data-lang-en="<?= htmlspecialchars($svcLabelEn) ?>"><?= htmlspecialchars($svcLabel) ?></p>
      <h2 class="section-title">
        <span class="lang-vi"><?= $svcTitle ?></span>
        <span class="lang-en" style="display:none"><?= $svcTitleEn ?></span>
      </h2>
    </div>
    <div class="services-grid">
      <?php foreach ($services as $service): 
        $svcTitleLower = strtolower($service['title'] ?? '');
        $svcLabelLower = strtolower($service['label'] ?? '');
        $galleryLink = '';
        if (strpos($svcTitleLower, 'tvc') !== false || strpos($svcLabelLower, 'tvc') !== false) {
          $galleryLink = 'TVC';
        } elseif (strpos($svcTitleLower, 'mv') !== false || strpos($svcLabelLower, 'mv') !== false) {
          $galleryLink = 'MV';
        } elseif (strpos($svcTitleLower, 'film') !== false || strpos($svcLabelLower, 'film') !== false || strpos($svcTitleLower, 'phim') !== false) {
          $galleryLink = 'Film';
        }
      ?>
        <div class="service-card<?= $galleryLink ? ' service-clickable' : '' ?>" <?php if ($galleryLink): ?>onclick="scrollToGalleryFilter('<?= $galleryLink ?>')"<?php endif; ?>>
          <img src="<?= img($service['image'] ?? '', '800x600') ?>" alt="<?= htmlspecialchars($service['title'] ?? '') ?>">
          <div class="service-content">
            <p class="service-label"><?= htmlspecialchars($service['label'] ?? '') ?></p>
            <h3 class="service-title"><?= htmlspecialchars($service['title'] ?? '') ?></h3>
            <p class="service-desc"><?= htmlspecialchars($service['description'] ?? '') ?></p>
            <?php if ($galleryLink): ?>
            <span class="service-link">Xem <?= $galleryLink ?> →</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (isSectionActive('team', $adminMenus) && !empty($team)): ?>
<?php 
$teamLabel = getSectionText('team', 'label_vi', $frontendSections) ?: 'Đội ngũ';
$teamLabelEn = getSectionText('team', 'label_en', $frontendSections) ?: 'Our Team';
$teamTitle = getSectionText('team', 'title_vi', $frontendSections) ?: 'Đôi mắt sau ống kính';
$teamTitleEn = getSectionText('team', 'title_en', $frontendSections) ?: 'The Eyes Behind the Lens';
?>
<!-- Team Section -->
<section class="section section-dark" id="team">
  <div class="container">
    <div class="text-center">
      <p class="section-label" data-lang-vi="<?= htmlspecialchars($teamLabel) ?>" data-lang-en="<?= htmlspecialchars($teamLabelEn) ?>"><?= htmlspecialchars($teamLabel) ?></p>
      <h2 class="section-title">
        <span class="lang-vi"><?= $teamTitle ?></span>
        <span class="lang-en" style="display:none"><?= $teamTitleEn ?></span>
      </h2>
    </div>
    <div class="team-slider">
      <?php foreach ($team as $member): ?>
        <div class="team-card">
          <div class="team-image">
            <img src="<?= img($member['image'] ?? '', '400x400') ?>" alt="<?= htmlspecialchars($member['name'] ?? '') ?>">
          </div>
          <h4 class="team-name"><?= htmlspecialchars($member['name'] ?? '') ?></h4>
          <p class="team-role"><?= htmlspecialchars($member['role'] ?? '') ?></p>
          <?php if (!empty($member['quote'])): ?>
            <p class="team-quote">"<?= htmlspecialchars($member['quote']) ?>"</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (isSectionActive('features', $adminMenus) && !empty($features)): ?>
<?php 
$featLabel = getSectionText('features', 'label_vi', $frontendSections) ?: 'Thế mạnh';
$featLabelEn = getSectionText('features', 'label_en', $frontendSections) ?: 'Why Choose Us';
$featTitle = getSectionText('features', 'title_vi', $frontendSections) ?: 'Tại sao chọn chúng tôi';
$featTitleEn = getSectionText('features', 'title_en', $frontendSections) ?: 'Why Choose Us';
?>
<!-- Features Section -->
<section class="section" id="features">
  <div class="container">
    <div class="text-center">
      <p class="section-label" data-lang-vi="<?= htmlspecialchars($featLabel) ?>" data-lang-en="<?= htmlspecialchars($featLabelEn) ?>"><?= htmlspecialchars($featLabel) ?></p>
      <h2 class="section-title">
        <span class="lang-vi"><?= $featTitle ?></span>
        <span class="lang-en" style="display:none"><?= $featTitleEn ?></span>
      </h2>
    </div>
    <div class="features-grid">
      <?php foreach ($features as $feature): ?>
        <div class="feature-card">
          <div class="feature-icon">
            <?= $icons[$feature['icon'] ?? 'lightbulb'] ?? $icons['lightbulb'] ?>
          </div>
          <h4 class="feature-title"><?= htmlspecialchars($feature['title'] ?? '') ?></h4>
          <p class="feature-desc"><?= htmlspecialchars($feature['description'] ?? '') ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php if (isSectionActive('partners', $adminMenus) && !empty($partners)): ?>
<?php 
$partLabel = getSectionText('partners', 'label_vi', $frontendSections) ?: 'Đối tác';
$partLabelEn = getSectionText('partners', 'label_en', $frontendSections) ?: 'Partners';
$partTitle = getSectionText('partners', 'title_vi', $frontendSections) ?: 'Đối tác chiến lược';
$partTitleEn = getSectionText('partners', 'title_en', $frontendSections) ?: 'Strategic Partners';
$partnerIcons = [
    'film' => '<svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="2"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>',
    'camera' => '<svg viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>',
    'code' => '<svg viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
    'lightbulb' => '<svg viewBox="0 0 24 24"><path d="M9 18h6M10 22h4M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/></svg>',
    'star' => '<svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    'heart' => '<svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
    'globe' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
    'users' => '<svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
];
?>
<!-- Partners Section -->
<section class="section section-dark" id="partners">
  <div class="container">
    <div class="text-center">
      <p class="section-label" data-lang-vi="<?= htmlspecialchars($partLabel) ?>" data-lang-en="<?= htmlspecialchars($partLabelEn) ?>"><?= htmlspecialchars($partLabel) ?></p>
      <h2 class="section-title">
        <span class="lang-vi"><?= $partTitle ?></span>
        <span class="lang-en" style="display:none"><?= $partTitleEn ?></span>
      </h2>
    </div>
    <div class="partners-grid">
      <?php foreach ($partners as $partner): ?>
        <div class="partner-card">
          <div class="partner-icon">
            <?= $partnerIcons[$partner['icon'] ?? 'star'] ?? $partnerIcons['star'] ?>
          </div>
          <h4 class="partner-title">
            <span class="lang-vi"><?= htmlspecialchars($partner['title'] ?? '') ?></span>
            <span class="lang-en" style="display:none"><?= htmlspecialchars($partner['title_en'] ?? $partner['title'] ?? '') ?></span>
          </h4>
          <p class="partner-desc">
            <span class="lang-vi"><?= htmlspecialchars($partner['description'] ?? '') ?></span>
            <span class="lang-en" style="display:none"><?= htmlspecialchars($partner['description_en'] ?? $partner['description'] ?? '') ?></span>
          </p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php 
$roadmapPhases = isset($roadmap['phases']) ? $roadmap['phases'] : [];
$activePhases = array_filter($roadmapPhases, function($p) { return isset($p['active']) ? $p['active'] : true; });
?>
<?php if (isSectionActive('roadmap', $adminMenus) && !empty($activePhases)): ?>
<?php 
$rmLabel = getSectionText('roadmap', 'label_vi', $frontendSections) ?: 'Lộ trình';
$rmLabelEn = getSectionText('roadmap', 'label_en', $frontendSections) ?: 'Roadmap';
$rmTitle = getSectionText('roadmap', 'title_vi', $frontendSections) ?: 'Lộ trình nội dung';
$rmTitleEn = getSectionText('roadmap', 'title_en', $frontendSections) ?: 'Content Roadmap';
$statusLabels = ['completed' => 'Hoàn thành', 'in_progress' => 'Đang thực hiện', 'upcoming' => 'Sắp tới'];
$statusLabelsEn = ['completed' => 'Completed', 'in_progress' => 'In Progress', 'upcoming' => 'Upcoming'];
?>
<!-- Roadmap Section -->
<section class="section" id="roadmap">
  <div class="container">
    <div class="text-center">
      <p class="section-label" data-lang-vi="<?= htmlspecialchars($rmLabel) ?>" data-lang-en="<?= htmlspecialchars($rmLabelEn) ?>"><?= htmlspecialchars($rmLabel) ?></p>
      <h2 class="section-title">
        <span class="lang-vi"><?= $rmTitle ?></span>
        <span class="lang-en" style="display:none"><?= $rmTitleEn ?></span>
      </h2>
    </div>
    <div class="roadmap-container">
      <?php foreach ($activePhases as $phase): ?>
      <div class="roadmap-phase">
        <h3 class="roadmap-phase-title">
          <span class="lang-vi"><?= htmlspecialchars($phase['title'] ?? '') ?></span>
          <span class="lang-en" style="display:none"><?= htmlspecialchars($phase['title_en'] ?? $phase['title'] ?? '') ?></span>
        </h3>
        <div class="roadmap-timeline">
          <?php $items = isset($phase['items']) ? $phase['items'] : []; foreach ($items as $item): $status = isset($item['status']) ? $item['status'] : 'upcoming'; ?>
          <div class="roadmap-item">
            <div class="roadmap-month"><?= htmlspecialchars($item['month'] ?? '') ?></div>
            <h4 class="roadmap-title">
              <span class="lang-vi"><?= htmlspecialchars($item['title'] ?? '') ?></span>
              <span class="lang-en" style="display:none"><?= htmlspecialchars($item['title_en'] ?? $item['title'] ?? '') ?></span>
              <span class="roadmap-status <?= $status ?>">
                <span class="lang-vi"><?= $statusLabels[$status] ?? $status ?></span>
                <span class="lang-en" style="display:none"><?= $statusLabelsEn[$status] ?? $status ?></span>
              </span>
            </h4>
            <p class="roadmap-desc">
              <span class="lang-vi"><?= htmlspecialchars($item['description'] ?? '') ?></span>
              <span class="lang-en" style="display:none"><?= htmlspecialchars($item['description_en'] ?? $item['description'] ?? '') ?></span>
            </p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (isSectionActive('projects', $adminMenus) && !empty($projects)): ?>
<?php 
$pjLabel = getSectionText('projects', 'label_vi', $frontendSections) ?: 'Dự án';
$pjLabelEn = getSectionText('projects', 'label_en', $frontendSections) ?: 'Projects';
$pjTitle = getSectionText('projects', 'title_vi', $frontendSections) ?: 'Dự án phim đã hoàn thành';
$pjTitleEn = getSectionText('projects', 'title_en', $frontendSections) ?: 'Completed Film Projects';
?>
<!-- Projects Section -->
<section class="section section-dark" id="projects">
  <div class="container">
    <div class="text-center">
      <p class="section-label" data-lang-vi="<?= htmlspecialchars($pjLabel) ?>" data-lang-en="<?= htmlspecialchars($pjLabelEn) ?>"><?= htmlspecialchars($pjLabel) ?></p>
      <h2 class="section-title">
        <span class="lang-vi"><?= $pjTitle ?></span>
        <span class="lang-en" style="display:none"><?= $pjTitleEn ?></span>
      </h2>
    </div>
    <div class="projects-grid">
      <?php foreach ($projects as $project): ?>
        <div class="project-card">
          <?php if (!empty($project['image'])): ?>
          <img src="<?= img($project['image'], '800x600') ?>" alt="<?= htmlspecialchars($project['title'] ?? '') ?>">
          <?php else: ?>
          <img src="https://placehold.co/800x600/111/C9A24A?text=<?= urlencode($project['title'] ?? 'Film') ?>" alt="<?= htmlspecialchars($project['title'] ?? '') ?>">
          <?php endif; ?>
          <div class="project-content">
            <p class="project-meta">
              <?php if (!empty($project['director'])): ?>Đạo diễn: <?= htmlspecialchars($project['director']) ?><?php endif; ?>
              <?php if (!empty($project['year'])): ?> • <?= htmlspecialchars($project['year']) ?><?php endif; ?>
            </p>
            <h3 class="project-title">
              <span class="lang-vi"><?= htmlspecialchars($project['title'] ?? '') ?></span>
              <span class="lang-en" style="display:none"><?= htmlspecialchars($project['title_en'] ?? $project['title'] ?? '') ?></span>
            </h3>
            <?php if (!empty($project['cast'])): ?>
            <p class="project-desc">Diễn viên: <?= htmlspecialchars($project['cast']) ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>


<?php if (isSectionActive('process', $adminMenus) && !empty($process)): ?>
<?php 
$procLabel = getSectionText('process', 'label_vi', $frontendSections) ?: 'Quy trình';
$procLabelEn = getSectionText('process', 'label_en', $frontendSections) ?: 'Our Process';
$procTitle = getSectionText('process', 'title_vi', $frontendSections) ?: 'Hành trình sáng tạo';
$procTitleEn = getSectionText('process', 'title_en', $frontendSections) ?: 'Creative Journey';
?>
<!-- Process Section -->
<section class="section section-dark" id="process">
  <div class="container">
    <div class="text-center">
      <p class="section-label" data-lang-vi="<?= htmlspecialchars($procLabel) ?>" data-lang-en="<?= htmlspecialchars($procLabelEn) ?>"><?= htmlspecialchars($procLabel) ?></p>
      <h2 class="section-title">
        <span class="lang-vi"><?= $procTitle ?></span>
        <span class="lang-en" style="display:none"><?= $procTitleEn ?></span>
      </h2>
    </div>
    <div class="process-timeline">
      <?php foreach ($process as $index => $step): ?>
        <div class="process-item">
          <div class="process-content">
            <h4 class="process-title"><?= htmlspecialchars($step['title'] ?? '') ?></h4>
            <p class="process-desc"><?= htmlspecialchars($step['description'] ?? '') ?></p>
          </div>
          <div class="process-number"><?= $index + 1 ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (isSectionActive('clients', $adminMenus) && !empty($clients)): ?>
<?php 
$cliLabel = getSectionText('clients', 'label_vi', $frontendSections) ?: 'Đối tác';
$cliLabelEn = getSectionText('clients', 'label_en', $frontendSections) ?: 'Partners';
$cliTitle = getSectionText('clients', 'title_vi', $frontendSections) ?: 'Khách hàng tin tưởng';
$cliTitleEn = getSectionText('clients', 'title_en', $frontendSections) ?: 'Trusted Clients';
?>
<!-- Clients Section -->
<section class="section">
  <div class="container">
    <div class="text-center">
      <p class="section-label" data-lang-vi="<?= htmlspecialchars($cliLabel) ?>" data-lang-en="<?= htmlspecialchars($cliLabelEn) ?>"><?= htmlspecialchars($cliLabel) ?></p>
      <h2 class="section-title">
        <span class="lang-vi"><?= $cliTitle ?></span>
        <span class="lang-en" style="display:none"><?= $cliTitleEn ?></span>
      </h2>
    </div>
    <div class="clients-grid">
      <?php foreach ($clients as $client): ?>
        <div class="client-logo">
          <img src="<?= img($client['logo'] ?? '', '150x50') ?>" alt="<?= htmlspecialchars($client['name'] ?? 'Client') ?>">
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (isSectionActive('gallery', $adminMenus)): ?>
<?php 
$gallery = $data['gallery'] ?? [];
// Sắp xếp theo order
usort($gallery, function($a, $b) {
    $orderA = isset($a['order']) ? (int)$a['order'] : 999999;
    $orderB = isset($b['order']) ? (int)$b['order'] : 999999;
    return $orderA - $orderB;
});
$galLabel = getSectionText('gallery', 'label_vi', $frontendSections) ?: 'Tư liệu';
$galLabelEn = getSectionText('gallery', 'label_en', $frontendSections) ?: 'Gallery';
$galTitle = getSectionText('gallery', 'title_vi', $frontendSections) ?: 'Hình ảnh & Video';
$galTitleEn = getSectionText('gallery', 'title_en', $frontendSections) ?: 'Photos & Videos';

// Lấy danh sách categories có trong gallery
$categories = array();
foreach ($gallery as $item) {
    $cat = isset($item['category']) ? $item['category'] : '';
    if (!empty($cat) && !in_array($cat, $categories)) {
        $categories[] = $cat;
    }
}

if (!empty($gallery)): 
?>
<!-- Gallery Section -->
<section class="section section-dark" id="gallery">
  <div class="container">
    <div class="text-center">
      <p class="section-label" data-lang-vi="<?= htmlspecialchars($galLabel) ?>" data-lang-en="<?= htmlspecialchars($galLabelEn) ?>"><?= htmlspecialchars($galLabel) ?></p>
      <h2 class="section-title">
        <span class="lang-vi"><?= $galTitle ?></span>
        <span class="lang-en" style="display:none"><?= $galTitleEn ?></span>
      </h2>
    </div>
    
    <!-- Filter Tabs -->
    <?php if (!empty($categories)): ?>
    <div class="gallery-filter">
      <button class="filter-btn active" data-filter="all" data-lang-vi="Tất cả" data-lang-en="All">Tất cả</button>
      <?php if (in_array('TVC', $categories)): ?>
      <button class="filter-btn" data-filter="TVC">TVC</button>
      <?php endif; ?>
      <?php if (in_array('MV', $categories)): ?>
      <button class="filter-btn" data-filter="MV">MV</button>
      <?php endif; ?>
      <?php if (in_array('Film', $categories)): ?>
      <button class="filter-btn" data-filter="Film">Film</button>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="gallery-slider" id="galleryGrid">
      <?php foreach ($gallery as $item): 
        $thumb = $item['thumbnail'] ?? '';
        if (empty($thumb)) {
          $thumb = 'https://placehold.co/600x400/111/C9A24A?text=' . ($item['type'] === 'video' ? 'Video' : 'Image');
        } elseif (strpos($thumb, 'http') !== 0) {
          $thumb = $thumb;
        }
        
        $isVideo = ($item['type'] ?? 'image') === 'video';
        $videoUrl = $item['video_url'] ?? '';
        $fileUrl = $item['file'] ?? '';
        $category = isset($item['category']) ? $item['category'] : '';
      ?>
        <div class="gallery-item" data-type="<?= $item['type'] ?? 'image' ?>" 
             data-category="<?= htmlspecialchars($category) ?>"
             data-video="<?= htmlspecialchars($videoUrl ?: $fileUrl) ?>"
             data-image="<?= htmlspecialchars($thumb) ?>"
             onclick="openGalleryItem(this)">
          <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($item['title'] ?? '') ?>">
          <?php if ($isVideo): ?>
            <div class="gallery-play-btn">
              <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
          <?php endif; ?>
          <?php if (!empty($category)): ?>
          <span class="gallery-category"><?= htmlspecialchars($category) ?></span>
          <?php endif; ?>
          <div class="gallery-overlay">
            <h4><?= htmlspecialchars($item['title'] ?? '') ?></h4>
            <?php if (!empty($item['description'])): ?>
            <p><?= htmlspecialchars($item['description']) ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php endif; ?>

<?php
// Render Custom Sections
$customSections = $data['custom_sections'] ?? [];
foreach ($customSections as $cs):
    if (!($cs['active'] ?? true)) continue;
    if (!isSectionActive($cs['id'], $adminMenus)) continue;
    
    $csContent = $cs['content'] ?? [];
    $csBg = ($cs['background'] ?? 'light') === 'dark' ? 'section-dark' : '';
    $csId = $cs['id'];
    
    // Icons mapping
    $csIcons = [
        'star' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'heart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
        'check' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>',
        'award' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>',
        'target' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
        'lightbulb' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18h6M10 22h4M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'globe' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
        'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'rocket' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/></svg>',
        'clock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        'camera' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>',
        'film' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/></svg>',
        'music' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>',
        'brush' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.06 11.9l8.07-8.06a2.85 2.85 0 1 1 4.03 4.03l-8.06 8.08"/><path d="M7.07 14.94c-1.66 0-3 1.35-3 3.02 0 1.33-2.5 1.52-2 2.02 1.08 1.1 2.49 2.02 4 2.02 2.2 0 4-1.8 4-4.04a3.01 3.01 0 0 0-3-3.02z"/></svg>',
        'code' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
    ];
?>
<section class="section <?= $csBg ?>" id="<?= $csId ?>">
  <div class="container">
    <?php if (!empty($csContent['label_vi']) || !empty($csContent['title_vi'])): ?>
    <div class="text-center" style="margin-bottom: 40px;">
      <?php if (!empty($csContent['label_vi'])): ?>
        <p class="section-label">
          <span class="lang-vi"><?= htmlspecialchars($csContent['label_vi']) ?></span>
          <span class="lang-en" style="display:none"><?= htmlspecialchars($csContent['label_en'] ?? $csContent['label_vi']) ?></span>
        </p>
      <?php endif; ?>
      <?php if (!empty($csContent['title_vi'])): ?>
        <h2 class="section-title">
          <span class="lang-vi"><?= $csContent['title_vi'] ?></span>
          <span class="lang-en" style="display:none"><?= $csContent['title_en'] ?? $csContent['title_vi'] ?></span>
        </h2>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php // Layout: Text Block ?>
    <?php if ($cs['layout'] === 'text_block'): ?>
      <div class="custom-text-block" style="max-width:800px;margin:0 auto;">
        <p class="lang-vi"><?= nl2br(htmlspecialchars($csContent['content_vi'] ?? '')) ?></p>
        <p class="lang-en" style="display:none"><?= nl2br(htmlspecialchars($csContent['content_en'] ?? '')) ?></p>
      </div>
    
    <?php // Layout: 2 Columns ?>
    <?php elseif ($cs['layout'] === 'two_columns'): ?>
      <div class="custom-columns" style="display:grid;grid-template-columns:repeat(2,1fr);gap:40px;">
        <?php foreach ($csContent['columns'] ?? [] as $col): ?>
          <div class="custom-column">
            <?php if (!empty($col['title_vi'])): ?>
              <h3 style="font-size:22px;margin-bottom:15px;">
                <span class="lang-vi"><?= htmlspecialchars($col['title_vi']) ?></span>
                <span class="lang-en" style="display:none"><?= htmlspecialchars($col['title_en'] ?? '') ?></span>
              </h3>
            <?php endif; ?>
            <p class="lang-vi" style="color:var(--text-muted);line-height:1.7;"><?= nl2br(htmlspecialchars($col['content_vi'] ?? '')) ?></p>
            <p class="lang-en" style="display:none;color:var(--text-muted);line-height:1.7;"><?= nl2br(htmlspecialchars($col['content_en'] ?? '')) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    
    <?php // Layout: 3 Columns ?>
    <?php elseif ($cs['layout'] === 'three_columns'): ?>
      <div class="custom-columns" style="display:grid;grid-template-columns:repeat(3,1fr);gap:30px;">
        <?php foreach ($csContent['columns'] ?? [] as $col): ?>
          <div class="custom-column">
            <?php if (!empty($col['title_vi'])): ?>
              <h3 style="font-size:20px;margin-bottom:15px;">
                <span class="lang-vi"><?= htmlspecialchars($col['title_vi']) ?></span>
                <span class="lang-en" style="display:none"><?= htmlspecialchars($col['title_en'] ?? '') ?></span>
              </h3>
            <?php endif; ?>
            <p class="lang-vi" style="color:var(--text-muted);line-height:1.7;"><?= nl2br(htmlspecialchars($col['content_vi'] ?? '')) ?></p>
            <p class="lang-en" style="display:none;color:var(--text-muted);line-height:1.7;"><?= nl2br(htmlspecialchars($col['content_en'] ?? '')) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    
    <?php // Layout: Cards Grid ?>
    <?php elseif ($cs['layout'] === 'cards_grid'): ?>
      <div class="features-grid">
        <?php foreach ($csContent['cards'] ?? [] as $card): ?>
          <div class="feature-card">
            <div class="feature-icon"><?= $csIcons[$card['icon'] ?? 'star'] ?? $csIcons['star'] ?></div>
            <h4 class="feature-title">
              <span class="lang-vi"><?= htmlspecialchars($card['title_vi'] ?? '') ?></span>
              <span class="lang-en" style="display:none"><?= htmlspecialchars($card['title_en'] ?? '') ?></span>
            </h4>
            <p class="feature-desc">
              <span class="lang-vi"><?= htmlspecialchars($card['content_vi'] ?? '') ?></span>
              <span class="lang-en" style="display:none"><?= htmlspecialchars($card['content_en'] ?? '') ?></span>
            </p>
          </div>
        <?php endforeach; ?>
      </div>
    
    <?php // Layout: Image Left ?>
    <?php elseif ($cs['layout'] === 'image_left'): ?>
      <div class="about-grid">
        <div class="about-image">
          <?php if (!empty($csContent['image'])): ?>
            <img src="<?= img($csContent['image']) ?>" alt="">
          <?php endif; ?>
        </div>
        <div class="about-content">
          <p class="lang-vi" style="color:var(--text-muted);line-height:1.8;"><?= nl2br(htmlspecialchars($csContent['content_vi'] ?? '')) ?></p>
          <p class="lang-en" style="display:none;color:var(--text-muted);line-height:1.8;"><?= nl2br(htmlspecialchars($csContent['content_en'] ?? '')) ?></p>
        </div>
      </div>
    
    <?php // Layout: Image Right ?>
    <?php elseif ($cs['layout'] === 'image_right'): ?>
      <div class="about-grid" style="direction:rtl;">
        <div class="about-image" style="direction:ltr;">
          <?php if (!empty($csContent['image'])): ?>
            <img src="<?= img($csContent['image']) ?>" alt="">
          <?php endif; ?>
        </div>
        <div class="about-content" style="direction:ltr;">
          <p class="lang-vi" style="color:var(--text-muted);line-height:1.8;"><?= nl2br(htmlspecialchars($csContent['content_vi'] ?? '')) ?></p>
          <p class="lang-en" style="display:none;color:var(--text-muted);line-height:1.8;"><?= nl2br(htmlspecialchars($csContent['content_en'] ?? '')) ?></p>
        </div>
      </div>
    
    <?php // Layout: Stats ?>
    <?php elseif ($cs['layout'] === 'stats'): ?>
      <div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:30px;text-align:center;">
        <?php foreach ($csContent['items'] ?? [] as $stat): ?>
          <div class="stat-item">
            <div class="stat-number" style="font-size:48px;font-weight:700;color:var(--secondary);margin-bottom:10px;"><?= htmlspecialchars($stat['number'] ?? '') ?></div>
            <div class="stat-label">
              <span class="lang-vi"><?= htmlspecialchars($stat['label_vi'] ?? '') ?></span>
              <span class="lang-en" style="display:none"><?= htmlspecialchars($stat['label_en'] ?? '') ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    
    <?php // Layout: Testimonials ?>
    <?php elseif ($cs['layout'] === 'testimonials'): ?>
      <div class="testimonials-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:30px;">
        <?php foreach ($csContent['items'] ?? [] as $test): ?>
          <div class="testimonial-card" style="background:var(--card-bg);padding:30px;border-radius:12px;">
            <p class="testimonial-content" style="font-style:italic;margin-bottom:20px;line-height:1.7;">
              <span class="lang-vi">"<?= htmlspecialchars($test['content_vi'] ?? '') ?>"</span>
              <span class="lang-en" style="display:none">"<?= htmlspecialchars($test['content_en'] ?? '') ?>"</span>
            </p>
            <div class="testimonial-author">
              <strong><?= htmlspecialchars($test['name'] ?? '') ?></strong>
              <?php if (!empty($test['role'])): ?>
                <br><span style="color:var(--text-muted);font-size:14px;"><?= htmlspecialchars($test['role']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    
    <?php // Layout: FAQ ?>
    <?php elseif ($cs['layout'] === 'faq'): ?>
      <div class="faq-list" style="max-width:800px;margin:0 auto;">
        <?php foreach ($csContent['items'] ?? [] as $faq): ?>
          <div class="faq-item" style="border-bottom:1px solid var(--border-color);padding:20px 0;">
            <h4 class="faq-question" style="font-size:18px;margin-bottom:10px;cursor:pointer;" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'">
              <span class="lang-vi">❓ <?= htmlspecialchars($faq['question_vi'] ?? '') ?></span>
              <span class="lang-en" style="display:none">❓ <?= htmlspecialchars($faq['question_en'] ?? '') ?></span>
            </h4>
            <div class="faq-answer" style="color:var(--text-muted);line-height:1.7;padding-left:25px;">
              <span class="lang-vi"><?= nl2br(htmlspecialchars($faq['answer_vi'] ?? '')) ?></span>
              <span class="lang-en" style="display:none"><?= nl2br(htmlspecialchars($faq['answer_en'] ?? '')) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    
    <?php // Layout: CTA Banner ?>
    <?php elseif ($cs['layout'] === 'cta_banner'): ?>
      <div class="custom-cta" style="text-align:center;padding:40px;background:linear-gradient(135deg, var(--primary), var(--secondary));border-radius:16px;color:#fff;<?= !empty($csContent['background_image']) ? 'background-image:url(' . img($csContent['background_image']) . ');background-size:cover;background-position:center;' : '' ?>">
        <?php if (!empty($csContent['subtitle_vi'])): ?>
          <p style="margin-bottom:20px;opacity:0.9;">
            <span class="lang-vi"><?= htmlspecialchars($csContent['subtitle_vi']) ?></span>
            <span class="lang-en" style="display:none"><?= htmlspecialchars($csContent['subtitle_en'] ?? '') ?></span>
          </p>
        <?php endif; ?>
        <?php if (!empty($csContent['button_vi'])): ?>
          <a href="<?= htmlspecialchars($csContent['button_url'] ?? '#contact') ?>" class="btn btn-primary" style="background:#fff;color:var(--primary);">
            <span class="lang-vi"><?= htmlspecialchars($csContent['button_vi']) ?></span>
            <span class="lang-en" style="display:none"><?= htmlspecialchars($csContent['button_en'] ?? '') ?></span>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endforeach; ?>

<?php if (isSectionActive('contact', $adminMenus)): ?>
<?php
$ctaTitleVi = $cta['title_vi'] ?? 'Sẵn sàng kể <span class="text-accent">câu chuyện</span> của bạn?';
$ctaTitleEn = $cta['title_en'] ?? 'Ready to tell <span class="text-accent">your story</span>?';
$ctaSubVi = $cta['subtitle_vi'] ?? 'Hãy liên hệ với chúng tôi để bắt đầu hành trình sáng tạo';
$ctaSubEn = $cta['subtitle_en'] ?? 'Contact us to start your creative journey';
$ctaBtnVi = $cta['button_vi'] ?? 'Liên hệ ngay';
$ctaBtnEn = $cta['button_en'] ?? 'Contact Now';
$ctaBg = !empty($cta['background_image']) ? 'style="background-image: url(' . img($cta['background_image']) . ')"' : '';
?>
<!-- CTA Section -->
<section class="cta <?= !empty($cta['background_image']) ? 'has-bg' : '' ?>" id="contact" <?= $ctaBg ?>>
  <div class="cta-bg"></div>
  <div class="container cta-content">
    <h2 class="cta-title">
      <span class="lang-vi"><?= $ctaTitleVi ?></span>
      <span class="lang-en" style="display:none"><?= $ctaTitleEn ?></span>
    </h2>
    <p class="cta-subtitle">
      <span class="lang-vi"><?= htmlspecialchars($ctaSubVi) ?></span>
      <span class="lang-en" style="display:none"><?= htmlspecialchars($ctaSubEn) ?></span>
    </p>
    <a href="#" class="btn btn-primary" onclick="openPopup(); return false;">
      <span class="lang-vi"><?= htmlspecialchars($ctaBtnVi) ?></span>
      <span class="lang-en" style="display:none"><?= htmlspecialchars($ctaBtnEn) ?></span>
    </a>
  </div>
</section>
<?php endif; ?>

<?php
$footerTagVi = $footer['tagline_vi'] ?? ($company['tagline'] ?? 'Kể chuyện thương hiệu bằng hình ảnh');
$footerTagEn = $footer['tagline_en'] ?? 'Brand storytelling through visuals';
$footerDescVi = $footer['description_vi'] ?? '';
$footerDescEn = $footer['description_en'] ?? '';
?>
<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <?php if (!empty($settings['logo'])): ?>
          <a href="#" class="logo"><img src="<?= img($settings['logo']) ?>" alt="Logo"></a>
        <?php else: ?>
          <a href="#" class="logo"><?= htmlspecialchars($company['name'] ?? 'Creative') ?><span>.</span></a>
        <?php endif; ?>
        <p class="footer-tagline">
          <span class="lang-vi"><?= htmlspecialchars($footerTagVi) ?></span>
          <span class="lang-en" style="display:none"><?= htmlspecialchars($footerTagEn) ?></span>
        </p>
        <?php if (!empty($footerDescVi)): ?>
        <p class="footer-desc">
          <span class="lang-vi"><?= htmlspecialchars($footerDescVi) ?></span>
          <span class="lang-en" style="display:none"><?= htmlspecialchars($footerDescEn) ?></span>
        </p>
        <?php endif; ?>
      </div>
      
      <div>
        <h4 class="footer-title" data-lang-vi="Dịch vụ" data-lang-en="Services">Dịch vụ</h4>
        <ul class="footer-links">
          <?php if (!empty($services)): ?>
            <?php foreach (array_slice($services, 0, 4) as $s): ?>
              <li><a href="#services"><?= htmlspecialchars($s['title'] ?? '') ?></a></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li><a href="#services" data-lang-vi="Sản xuất phim" data-lang-en="Film Production">Sản xuất phim</a></li>
            <li><a href="#services" data-lang-vi="Nhiếp ảnh" data-lang-en="Photography">Nhiếp ảnh</a></li>
          <?php endif; ?>
        </ul>
      </div>
      
      <div>
        <h4 class="footer-title" data-lang-vi="Liên kết" data-lang-en="Links">Liên kết</h4>
        <ul class="footer-links">
          <?php if (!empty($footer['nav_links'])): ?>
            <?php foreach ($footer['nav_links'] as $link): ?>
              <li><a href="<?= htmlspecialchars($link['url'] ?? '#') ?>">
                <span class="lang-vi"><?= htmlspecialchars($link['title_vi'] ?? '') ?></span>
                <span class="lang-en" style="display:none"><?= htmlspecialchars($link['title_en'] ?? $link['title_vi'] ?? '') ?></span>
              </a></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li><a href="#about" data-lang-vi="Về chúng tôi" data-lang-en="About Us">Về chúng tôi</a></li>
            <li><a href="#team" data-lang-vi="Đội ngũ" data-lang-en="Team">Đội ngũ</a></li>
            <li><a href="#features" data-lang-vi="Thế mạnh" data-lang-en="Why Us">Thế mạnh</a></li>
            <li><a href="#contact" data-lang-vi="Liên hệ" data-lang-en="Contact">Liên hệ</a></li>
          <?php endif; ?>
        </ul>
      </div>
      
      <div class="footer-contact">
        <h4 class="footer-title" data-lang-vi="Liên hệ" data-lang-en="Contact">Liên hệ</h4>
        <?php if (!empty($company['address'])): ?>
          <p>📍 <?= htmlspecialchars($company['address']) ?></p>
        <?php endif; ?>
        <?php if (!empty($company['phone'])): ?>
          <p>📞 <a href="tel:<?= htmlspecialchars($company['phone']) ?>"><?= htmlspecialchars($company['phone']) ?></a></p>
        <?php endif; ?>
        <?php if (!empty($company['email'])): ?>
          <p>✉️ <a href="mailto:<?= htmlspecialchars($company['email']) ?>"><?= htmlspecialchars($company['email']) ?></a></p>
        <?php endif; ?>
        <?php if (!empty($company['ceo'])): ?>
          <p>👤 CEO: <?= htmlspecialchars($company['ceo']) ?></p>
        <?php endif; ?>
        <?php if (!empty($company['tax_code'])): ?>
          <p>🏢 MST: <?= htmlspecialchars($company['tax_code']) ?></p>
        <?php endif; ?>
        
        <div class="footer-social">
          <?php if (!empty($company['facebook'])): ?>
            <a href="<?= htmlspecialchars($company['facebook']) ?>" target="_blank">
              <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty($company['youtube'])): ?>
            <a href="<?= htmlspecialchars($company['youtube']) ?>" target="_blank">
              <svg viewBox="0 0 24 24" stroke-width="1.5"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty($company['tiktok'])): ?>
            <a href="<?= htmlspecialchars($company['tiktok']) ?>" target="_blank">
              <svg viewBox="0 0 24 24" stroke-width="1.5"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty($company['zalo'])): ?>
            <a href="https://zalo.me/<?= htmlspecialchars($company['zalo']) ?>" target="_blank">
              <svg viewBox="0 0 24 24" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <?php
$copyrightVi = $footer['copyright_vi'] ?? ('© ' . date('Y') . ' ' . ($company['name'] ?? 'Creative Studio') . '. Đã đăng ký bản quyền.');
$copyrightEn = $footer['copyright_en'] ?? ('© ' . date('Y') . ' ' . ($company['name'] ?? 'Creative Studio') . '. All rights reserved.');
?>
    <div class="footer-bottom">
      <p>
        <span class="lang-vi"><?= htmlspecialchars($copyrightVi) ?></span>
        <span class="lang-en" style="display:none"><?= htmlspecialchars($copyrightEn) ?></span>
      </p>
    </div>
  </div>
</footer>

<!-- Contact Popup -->
<div class="popup-overlay" id="contactPopup">
  <div class="popup-content">
    <button class="popup-close" onclick="closePopup()">
      <svg viewBox="0 0 24 24" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <p class="section-label" data-lang-vi="Liên hệ" data-lang-en="Contact">Liên hệ</p>
    <h3 class="section-title" style="font-size: 32px; margin-bottom: 30px;" data-lang-vi="Kết nối với chúng tôi" data-lang-en="Get in Touch">Kết nối với chúng tôi</h3>
    <form id="contactForm" onsubmit="submitForm(event)">
      <div class="form-group">
        <label class="form-label" data-lang-vi="Họ và tên" data-lang-en="Full Name">Họ và tên</label>
        <input type="text" class="form-input" name="name" id="inputName" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-input" name="email" id="inputEmail" required>
      </div>
      <div class="form-group">
        <label class="form-label" data-lang-vi="Số điện thoại" data-lang-en="Phone Number">Số điện thoại</label>
        <input type="tel" class="form-input" name="phone" id="inputPhone">
      </div>
      <div class="form-group">
        <label class="form-label" data-lang-vi="Nội dung" data-lang-en="Message">Nội dung</label>
        <textarea class="form-input" name="message" id="inputMessage" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary" id="submitBtn" style="width: 100%; justify-content: center;" data-lang-vi="Gửi tin nhắn" data-lang-en="Send Message">Gửi tin nhắn</button>
    </form>
  </div>
</div>

<!-- Gallery Modal -->
<div class="gallery-modal" id="galleryModal">
  <div class="gallery-modal-content" id="galleryModalContent">
    <button class="gallery-modal-close" onclick="closeGalleryModal()">&times;</button>
  </div>
</div>

<script>
// Gallery Filter
function filterGallery(category) {
  var items = document.querySelectorAll('.gallery-item');
  var btns = document.querySelectorAll('.filter-btn');
  
  btns.forEach(function(btn) {
    btn.classList.remove('active');
    if (btn.dataset.filter === category) {
      btn.classList.add('active');
    }
  });
  
  items.forEach(function(item) {
    if (category === 'all' || item.dataset.category === category) {
      item.classList.remove('hidden');
    } else {
      item.classList.add('hidden');
    }
  });
}

// Scroll to gallery with filter
function scrollToGalleryFilter(category) {
  var gallerySection = document.getElementById('gallery');
  if (gallerySection) {
    gallerySection.scrollIntoView({ behavior: 'smooth' });
    setTimeout(function() {
      filterGallery(category);
    }, 500);
  }
}

// Init filter buttons
document.addEventListener('DOMContentLoaded', function() {
  var filterBtns = document.querySelectorAll('.filter-btn');
  filterBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      filterGallery(this.dataset.filter);
    });
  });
  
  // Check URL hash for filter
  var hash = window.location.hash;
  if (hash === '#gallery-tvc') {
    setTimeout(function() { scrollToGalleryFilter('TVC'); }, 300);
  } else if (hash === '#gallery-mv') {
    setTimeout(function() { scrollToGalleryFilter('MV'); }, 300);
  } else if (hash === '#gallery-film') {
    setTimeout(function() { scrollToGalleryFilter('Film'); }, 300);
  }
});

// Gallery functions
function openGalleryItem(el) {
  const type = el.dataset.type;
  const video = el.dataset.video;
  const image = el.dataset.image;
  const modal = document.getElementById('galleryModal');
  const content = document.getElementById('galleryModalContent');
  
  // Clear previous content (keep close button)
  content.innerHTML = '<button class="gallery-modal-close" onclick="closeGalleryModal()">&times;</button>';
  
  if (type === 'video' && video) {
    // Check if YouTube
    const ytMatch = video.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
    // Check if Google Drive
    const driveMatch = video.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/);
    
    if (ytMatch) {
      // YouTube video
      const iframe = document.createElement('iframe');
      iframe.src = 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=1';
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      iframe.allowFullscreen = true;
      content.appendChild(iframe);
    } else if (driveMatch) {
      // Google Drive video
      const iframe = document.createElement('iframe');
      iframe.src = 'https://drive.google.com/file/d/' + driveMatch[1] + '/preview';
      iframe.allow = 'autoplay; encrypted-media';
      iframe.allowFullscreen = true;
      content.appendChild(iframe);
    } else if (video.includes('vimeo.com')) {
      // Vimeo video
      const vimeoMatch = video.match(/vimeo\.com\/(\d+)/);
      if (vimeoMatch) {
        const iframe = document.createElement('iframe');
        iframe.src = 'https://player.vimeo.com/video/' + vimeoMatch[1] + '?autoplay=1';
        iframe.allow = 'autoplay; fullscreen; picture-in-picture';
        iframe.allowFullscreen = true;
        content.appendChild(iframe);
      }
    } else {
      // Local video or direct URL
      const videoEl = document.createElement('video');
      videoEl.src = video;
      videoEl.controls = true;
      videoEl.autoplay = true;
      videoEl.style.maxWidth = '90vw';
      videoEl.style.maxHeight = '85vh';
      content.appendChild(videoEl);
    }
  } else {
    // Image
    const img = document.createElement('img');
    img.src = image;
    content.appendChild(img);
  }
  
  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeGalleryModal() {
  const modal = document.getElementById('galleryModal');
  const content = document.getElementById('galleryModalContent');
  modal.classList.remove('active');
  document.body.style.overflow = '';
  // Stop video when closing
  content.innerHTML = '<button class="gallery-modal-close" onclick="closeGalleryModal()">&times;</button>';
}

document.getElementById('galleryModal').addEventListener('click', function(e) {
  if (e.target === this) closeGalleryModal();
});

// Language system
let currentLang = localStorage.getItem('lang') || 'vi';

const placeholders = {
  vi: { name: 'Nhập họ và tên', email: 'Nhập email', phone: 'Nhập số điện thoại', message: 'Bạn cần hỗ trợ gì?' },
  en: { name: 'Enter your name', email: 'Enter your email', phone: 'Enter your phone', message: 'How can we help you?' }
};

const alerts = {
  vi: { thanks: 'Cảm ơn bạn! Chúng tôi sẽ liên hệ sớm nhất.', error: 'Có lỗi xảy ra, vui lòng thử lại.' },
  en: { thanks: 'Thank you! We will contact you soon.', error: 'An error occurred, please try again.' }
};

document.addEventListener('DOMContentLoaded', () => switchLang(currentLang));

function switchLang(lang) {
  currentLang = lang;
  localStorage.setItem('lang', lang);
  document.documentElement.lang = lang;
  
  // Update all elements with data-lang attributes
  document.querySelectorAll('[data-lang-vi][data-lang-en]').forEach(el => {
    el.innerHTML = el.getAttribute('data-lang-' + lang);
  });
  
  // Toggle visibility for lang-vi and lang-en spans
  document.querySelectorAll('.lang-vi').forEach(el => {
    el.style.display = lang === 'vi' ? '' : 'none';
  });
  document.querySelectorAll('.lang-en').forEach(el => {
    el.style.display = lang === 'en' ? '' : 'none';
  });
  
  // Update placeholders
  if (document.getElementById('inputName')) {
    document.getElementById('inputName').placeholder = placeholders[lang].name;
    document.getElementById('inputEmail').placeholder = placeholders[lang].email;
    document.getElementById('inputPhone').placeholder = placeholders[lang].phone;
    document.getElementById('inputMessage').placeholder = placeholders[lang].message;
  }
  
  // Update active button state
  document.querySelectorAll('.lang-btn').forEach(btn => {
    btn.classList.toggle('active', btn.textContent === lang.toUpperCase());
  });
}

// Header scroll effect
window.addEventListener('scroll', () => {
  document.getElementById('header').classList.toggle('scrolled', window.scrollY > 50);
});

// Popup functions
function openPopup() {
  document.getElementById('contactPopup').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closePopup() {
  document.getElementById('contactPopup').classList.remove('active');
  document.body.style.overflow = '';
}

document.getElementById('contactPopup').addEventListener('click', function(e) {
  if (e.target === this) closePopup();
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closePopup();
});

// Form submit - Save to JSON via AJAX
function submitForm(e) {
  e.preventDefault();
  const form = e.target;
  const btn = document.getElementById('submitBtn');
  const originalText = btn.textContent;
  
  btn.disabled = true;
  btn.textContent = currentLang === 'vi' ? 'Đang gửi...' : 'Sending...';
  
  fetch('submit-contact.php', {
    method: 'POST',
    body: new FormData(form)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert(alerts[currentLang].thanks);
      form.reset();
      closePopup();
    } else {
      alert(data.message || alerts[currentLang].error);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert(alerts[currentLang].error);
  })
  .finally(() => {
    btn.disabled = false;
    btn.textContent = originalText;
  });
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    const href = this.getAttribute('href');
    if (href === '#' || this.onclick) return;
    e.preventDefault();
    const target = document.querySelector(href);
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
});
</script>

</body>
</html>