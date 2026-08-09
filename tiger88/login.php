<?php
require_once 'auth.php';

if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (attempt_login($_POST['password'] ?? '')) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'รหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ — เสือกินเส้น 88</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap');
* { box-sizing: border-box; margin:0; padding:0; font-family:'Sarabun',sans-serif; }
body {
  background: linear-gradient(135deg, #92400e 0%, #d97706 60%, #f59e0b 100%);
  min-height: 100vh; display:flex; align-items:center; justify-content:center;
}
.box {
  background: #fff; padding: 36px 32px; border-radius: 18px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25); width: 320px;
}
h1 { color:#92400e; font-size:22px; margin-bottom:6px; text-align:center; }
p.sub { color:#78716c; font-size:13px; text-align:center; margin-bottom:22px; }
input {
  width:100%; padding:12px 14px; border:1.5px solid #e8dfd0; border-radius:10px;
  margin-bottom:14px; font-size:15px; outline:none; transition: border-color .2s;
}
input:focus { border-color:#d97706; }
button {
  width:100%; padding:12px; background:#d97706; color:white; border:none;
  border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; transition: background .2s;
}
button:hover { background:#92400e; }
.err { color:#dc2626; font-size:13px; margin-bottom:14px; text-align:center; }
</style>
</head>
<body>
<form class="box" method="POST">
  <h1>🐯 เสือกินเส้น 88</h1>
  <p class="sub">ระบบรายงานค่าใช้จ่าย</p>
  <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <input type="password" name="password" placeholder="รหัสผ่าน" autofocus required>
  <button type="submit">เข้าสู่ระบบ</button>
</form>
</body>
</html>
