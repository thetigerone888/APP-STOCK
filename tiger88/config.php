<?php
// ========================================================
// Tiger88 Expense System — Configuration
// ไฟล์นี้ใน git เป็นเวอร์ชัน placeholder (repo นี้เป็น public)
// ก่อน deploy ต้องแก้ค่า CHANGE_ME_* ทุกตัวให้เป็นค่าจริง
// และห้าม commit ค่าจริงกลับเข้า git
// ========================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'CHANGE_ME_DB_NAME');      // ชื่อ DB จริงที่สร้างใน cPanel
define('DB_USER', 'CHANGE_ME_DB_USER');      // ชื่อ DB user จริงที่สร้าง
define('DB_PASS', 'CHANGE_ME_DB_PASSWORD');  // รหัสผ่าน DB user

// รหัสผ่านเข้าเว็บ — สร้าง hash จากรหัสผ่านที่ต้องการด้วยคำสั่ง:
//   php -r "echo password_hash('รหัสผ่านของคุณ', PASSWORD_DEFAULT), PHP_EOL;"
define('LOGIN_PASSWORD_HASH', 'CHANGE_ME_PASSWORD_HASH');

// Key ลับสำหรับ cron sync — สร้างค่าสุ่มด้วยคำสั่ง:
//   php -r "echo bin2hex(random_bytes(16)), PHP_EOL;"
define('SYNC_SECRET_KEY', 'CHANGE_ME_SYNC_SECRET_KEY');

// URL ของ Paypers Sheet (Publish to web แบบ XLSX)
define('PAYPERS_SHEET_URL', 'CHANGE_ME_PAYPERS_SHEET_XLSX_URL');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}
