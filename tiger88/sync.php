<?php
// ========================================================
// sync.php — จุดที่ Cron Job เรียกทุกวัน (ไม่ต้อง login)
// ป้องกันด้วย SYNC_SECRET_KEY เพื่อไม่ให้คนนอกยิงเล่นได้
// URL ที่ใช้ตั้ง Cron: https://thetiger.one/sync.php?key=SYNC_SECRET_KEY
// ========================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/sync_lib.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['key']) || $_GET['key'] !== SYNC_SECRET_KEY) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

try {
    $pdo = getDB();
    $result = do_paypers_sync($pdo);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
