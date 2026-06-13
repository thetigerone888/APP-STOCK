<?php
// MARBOHUB POS — Shared Backend API
// อัปโหลดไฟล์นี้ไว้ที่ public_html/api.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Pin');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ===== PIN รักษาความปลอดภัย (เปลี่ยนได้) =====
define('PIN', '8888');
$pin = $_SERVER['HTTP_X_PIN'] ?? ($_GET['pin'] ?? '');
if ($pin !== PIN) { http_response_code(401); echo json_encode(['error'=>'PIN ไม่ถูกต้อง']); exit; }

// ===== ไฟล์เก็บข้อมูล =====
define('DATA_FILE', __DIR__ . '/marbohub_data.json');
define('LOCK_FILE', __DIR__ . '/marbohub_data.lock');

function loadData() {
    if (!file_exists(DATA_FILE)) return ['orders'=>[], 'stockOv'=>[], 'costOv'=>[], 'v'=>0];
    $raw = file_get_contents(DATA_FILE);
    return $raw ? json_decode($raw, true) : ['orders'=>[], 'stockOv'=>[], 'costOv'=>[], 'v'=>0];
}

function saveData($data) {
    $data['v'] = ($data['v'] ?? 0) + 1;
    $data['ts'] = round(microtime(true) * 1000);
    $fp = fopen(LOCK_FILE, 'w');
    if (flock($fp, LOCK_EX)) {
        file_put_contents(DATA_FILE, json_encode($data, JSON_UNESCAPED_UNICODE));
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    return $data;
}

$action = $_GET['action'] ?? '';

// ===== โหลดข้อมูลทั้งหมด =====
if ($action === 'load') {
    echo json_encode(loadData(), JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== เช็คว่ามีอัปเดตใหม่ไหม (polling) =====
if ($action === 'poll') {
    $clientV = intval($_GET['v'] ?? 0);
    $data = loadData();
    if ($data['v'] > $clientV) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['v' => $data['v'], 'noChange' => true]);
    }
    exit;
}

// ===== บันทึกทั้งหมด (full sync) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) { http_response_code(400); echo json_encode(['error'=>'invalid body']); exit; }
    $data = saveData($body);
    echo json_encode(['ok'=>true, 'v'=>$data['v']]);
    exit;
}

// ===== เพิ่มออเดอร์ใหม่ (atomic) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'addOrder') {
    $body = json_decode(file_get_contents('php://input'), true);
    $fp = fopen(LOCK_FILE, 'w');
    flock($fp, LOCK_EX);
    $data = loadData();
    $order = $body['order'];
    // เพิ่มออเดอร์
    array_unshift($data['orders'], $order);
    // ตัดสต๊อก
    if (isset($body['stockCuts'])) {
        foreach ($body['stockCuts'] as $pid => $qty) {
            $cur = $data['stockOv'][$pid] ?? null;
            // ถ้ายังไม่เคยตั้งค่า ไม่ต้องตัด (ใช้ค่า default จาก PRODUCTS)
            if ($cur !== null) $data['stockOv'][$pid] = max(0, $cur - $qty);
        }
    }
    $data['v'] = ($data['v'] ?? 0) + 1;
    $data['ts'] = round(microtime(true) * 1000);
    file_put_contents(DATA_FILE, json_encode($data, JSON_UNESCAPED_UNICODE));
    flock($fp, LOCK_UN); fclose($fp);
    echo json_encode(['ok'=>true, 'v'=>$data['v']]);
    exit;
}

http_response_code(400);
echo json_encode(['error'=>'unknown action: ' . $action]);
