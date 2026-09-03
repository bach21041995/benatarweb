<?php
session_start();
require_once 'functions.php';

// Nếu đã đăng nhập thì chuyển về dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = authenticate($username, $password);
    
    if ($user) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập - Admin Panel</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body class="login-page">
  <div class="login-box">
    <div class="login-logo">
      <h1>Admin<span>Panel</span></h1>
    </div>
    
    <h2 class="login-title">Đăng nhập</h2>
    
    <?php if ($error): ?>
      <div class="alert alert-danger">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="15" y1="9" x2="9" y2="15"/>
          <line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
        <?= e($error) ?>
      </div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Tên đăng nhập</label>
        <input type="text" name="username" class="form-input" placeholder="Nhập tên đăng nhập" required autofocus>
      </div>
      
      <div class="form-group">
        <label class="form-label">Mật khẩu</label>
        <input type="password" name="password" class="form-input" placeholder="Nhập mật khẩu" required>
      </div>
      
      <button type="submit" class="btn btn-primary" style="width: 100%;">
        Đăng nhập
      </button>
    </form>
    
    <!-- <p style="text-align: center; margin-top: 20px; font-size: 14px; color: #64748b;">
      Mặc định: admin / admin123
    </p> -->
  </div>
</body>
</html>
