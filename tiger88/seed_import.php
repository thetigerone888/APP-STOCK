<?php
// ========================================================
// seed_import.php — รันครั้งเดียวหลังสร้างตารางเสร็จ
// เพื่อนำเข้าข้อมูล 902 รายการที่มีอยู่แล้วเข้าฐานข้อมูล
// เปิดผ่านเบราว์เซอร์ครั้งเดียว: https://thetiger.one/seed_import.php
// แล้ว "ลบไฟล์นี้ทิ้ง" หลังรันเสร็จ เพื่อความปลอดภัย (ป้องกันรันซ้ำ)
// ========================================================
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

$jsonPath = __DIR__ . '/seed_data.json';
if (!file_exists($jsonPath)) {
    die("ไม่พบไฟล์ seed_data.json — ต้องอัปโหลดไฟล์นี้ไว้โฟลเดอร์เดียวกันก่อนรัน");
}

$entries = json_decode(file_get_contents($jsonPath), true);
if (!$entries) {
    die("อ่าน seed_data.json ไม่ได้ หรือไฟล์ว่างเปล่า");
}

$pdo = getDB();

// ป้องกันรันซ้ำ: ถ้ามีข้อมูลอยู่แล้วในตาราง ให้เตือนแล้วหยุด
$count = $pdo->query("SELECT COUNT(*) FROM expenses")->fetchColumn();
if ($count > 0) {
    die("ตาราง expenses มีข้อมูลอยู่แล้ว ($count รายการ) — ยกเลิกการ seed ซ้ำเพื่อความปลอดภัย\nถ้าต้องการ seed ใหม่ ให้ล้างตารางก่อน (TRUNCATE TABLE expenses) แล้วรันใหม่");
}

// สำคัญ: sheet_hash ต้องมาจากไฟล์ seed_data.json โดยตรง (คำนวณจากเลขแถวในชีตต้นฉบับ)
// เพื่อให้ตรงกับระบบ sync ในอนาคต — ไม่คำนวณจากเนื้อหาใหม่ที่นี่
$stmt = $pdo->prepare("INSERT IGNORE INTO expenses
    (entry_date, description, qty, price, amount, main_category, sub_category, vendor, month_name, payer, source, sheet_hash)
    VALUES (?,?,?,?,?,?,?,?,?,?, 'sheet', ?)");

$inserted = 0; $skipped = 0;
foreach ($entries as $e) {
    $parts = explode('/', $e['d']);
    if (count($parts) !== 3) { $skipped++; continue; }
    $dateStr = sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);

    $hash = $e['sheet_hash'] ?? null;
    if (!$hash) { $skipped++; continue; }

    $ok = $stmt->execute([
        $dateStr, $e['r'] ?? '', (float)($e['qty'] ?? 1), (float)($e['price'] ?? $e['a']), (float)($e['a'] ?? 0),
        $e['c'] ?? '', $e['sc'] ?? '', $e['v'] ?? '', $e['m'] ?? '', $e['payer'] ?? '', $hash
    ]);
    if ($stmt->rowCount() > 0) { $inserted++; } else { $skipped++; }
}

echo "นำเข้าข้อมูลสำเร็จ!\n";
echo "เพิ่มใหม่: $inserted รายการ\n";
echo "ข้าม (ซ้ำ): $skipped รายการ\n";
echo "\n=== สำคัญ: ลบไฟล์ seed_import.php และ seed_data.json ทิ้งตอนนี้เลย ===\n";
