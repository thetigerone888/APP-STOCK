<?php
// ========================================================
// sync_lib.php — ดึงข้อมูลจาก Paypers Sheet มาใส่ฐานข้อมูล
// เรียกใช้จาก sync.php (cron) หรือ api.php (ปุ่ม sync มือ)
// สำคัญ: เฉพาะ "เพิ่ม" แถวใหม่ที่ยังไม่เคยมี (ตาม hash) เท่านั้น
//        จะไม่ overwrite/ลบ รายการที่มีอยู่แล้วในฐานข้อมูล
//        ดังนั้นการแก้ไขข้อมูลผ่านเว็บจะไม่หายแม้ sync ใหม่
// ========================================================
require_once __DIR__ . '/xlsx_reader.php';

function do_paypers_sync($pdo) {
    $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');

    $ch = curl_init(PAYPERS_SHEET_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($body === false || $httpCode !== 200) {
        return ['success' => false, 'error' => "ดึงไฟล์ไม่สำเร็จ (HTTP $httpCode) $curlErr"];
    }
    file_put_contents($tmpFile, $body);

    try {
        $reader = new MiniXLSXReader($tmpFile);
        $rows = $reader->readSheet('รวม');
        $reader->close();
    } catch (Exception $e) {
        @unlink($tmpFile);
        return ['success' => false, 'error' => 'อ่านไฟล์ล้มเหลว: ' . $e->getMessage()];
    }
    @unlink($tmpFile);

    $MONTH_MAP = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                  7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
    $PAYER_MAP = [
        'ROMNAPHAT CHAIMONGKON' => 'รมณ',
        'ฝรั่ง  36' => 'ฝรั่ง',
        'พ่อจ่าย' => 'พ่อ',
        'พี่คิวจ่าย' => 'พี่คิว',
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO expenses
        (entry_date, description, qty, price, amount, main_category, sub_category, vendor, month_name, payer, source, sheet_hash)
        VALUES (?,?,?,?,?,?,?,?,?,?,'sheet',?)");

    $inserted = 0; $skipped = 0;
    foreach ($rows as $rowNum => $row) {
        if ($rowNum < 3) continue; // ข้าม header 2 แถวแรก
        $dateRaw = trim($row[0] ?? '');
        if (!$dateRaw) continue;

        $amount = floatval($row[16] ?? 0);
        if ($amount <= 0) { $skipped++; continue; }

        if (!preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $dateRaw, $m)) { $skipped++; continue; }
        $dateStr = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        $monthNum = (int)$m[2];

        $desc = trim($row[6] ?? '');
        $qty = floatval($row[7] ?? 1) ?: 1;
        $price = floatval($row[8] ?? $amount);
        $cat = trim($row[18] ?? '') ?: 'รายจ่ายอื่นๆ';
        $subcat = trim($row[19] ?? '');
        $vendor = trim($row[20] ?? '');
        $payerRaw = trim($row[25] ?? '');
        $payer = $PAYER_MAP[$payerRaw] ?? $payerRaw;
        $month = $MONTH_MAP[$monthNum] ?? '';

        // ใช้เลขแถวในชีตเป็น hash โดยตรง (ไม่ใช่เนื้อหา) เพื่อไม่ให้รายการที่
        // วันที่/รายการ/จำนวนเงิน/ผู้ขาย เหมือนกันโดยบังเอิญ (เช่น ซื้อของราคาเท่ากันคนละวัน)
        // ถูกเข้าใจผิดว่าเป็นรายการซ้ำแล้วถูกข้ามไป
        $hash = hash('sha256', 'sheetrow:' . $rowNum);

        $stmt->execute([$dateStr, $desc, $qty, $price, $amount, $cat, $subcat, $vendor, $month, $payer, $hash]);
        if ($stmt->rowCount() > 0) { $inserted++; } else { $skipped++; }
    }

    $pdo->prepare("REPLACE INTO app_settings (setting_key, setting_value) VALUES ('last_sync', ?)")
        ->execute([date('Y-m-d H:i:s') . " (+{$inserted} รายการใหม่, ข้าม {$skipped})"]);

    return ['success' => true, 'inserted' => $inserted, 'skipped' => $skipped];
}
