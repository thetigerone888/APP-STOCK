<?php
require_once __DIR__ . '/auth.php';
require_login_api();
require_once __DIR__ . '/sync_lib.php';

header('Content-Type: application/json; charset=utf-8');
$pdo = getDB();
$action = $_GET['action'] ?? '';
if ($action === '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $input['action'] ?? '';
}

switch ($action) {

    case 'list': {
        $stmt = $pdo->query("SELECT id, entry_date, description AS r, qty, price, amount AS a,
                                     main_category AS c, sub_category AS sc, vendor AS v,
                                     month_name AS m, payer
                              FROM expenses ORDER BY entry_date ASC, id ASC");
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $d = DateTime::createFromFormat('Y-m-d', $row['entry_date']);
            $row['d'] = $d ? $d->format('d/m/Y') : $row['entry_date'];
            unset($row['entry_date']);
            $row['qty'] = (float)$row['qty'];
            $row['price'] = (float)$row['price'];
            $row['a'] = (float)$row['a'];
            $row['id'] = (int)$row['id'];
        }
        echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
        break;
    }

    case 'save': {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $d = DateTime::createFromFormat('d/m/Y', $input['d'] ?? '');
        $dateStr = $d ? $d->format('Y-m-d') : date('Y-m-d');
        $id = isset($input['id']) ? (int)$input['id'] : null;

        $params = [
            $dateStr,
            $input['r'] ?? '',
            (float)($input['qty'] ?? 1),
            (float)($input['price'] ?? 0),
            (float)($input['a'] ?? 0),
            $input['c'] ?? '',
            $input['sc'] ?? '',
            $input['v'] ?? '',
            $input['m'] ?? '',
            $input['payer'] ?? '',
        ];

        if ($id) {
            $stmt = $pdo->prepare("UPDATE expenses SET entry_date=?, description=?, qty=?, price=?, amount=?,
                                    main_category=?, sub_category=?, vendor=?, month_name=?, payer=?, source='manual'
                                    WHERE id=?");
            $stmt->execute(array_merge($params, [$id]));
        } else {
            $stmt = $pdo->prepare("INSERT INTO expenses
                (entry_date, description, qty, price, amount, main_category, sub_category, vendor, month_name, payer, source)
                VALUES (?,?,?,?,?,?,?,?,?,?,'manual')");
            $stmt->execute($params);
            $id = (int)$pdo->lastInsertId();
        }
        echo json_encode(['success' => true, 'id' => $id], JSON_UNESCAPED_UNICODE);
        break;
    }

    case 'delete': {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = (int)($input['id'] ?? 0);
        if ($id) {
            $pdo->prepare("DELETE FROM expenses WHERE id=?")->execute([$id]);
        }
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        break;
    }

    case 'trigger_sync': {
        $result = do_paypers_sync($pdo);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
    }

    case 'sync_status': {
        $stmt = $pdo->query("SELECT setting_value FROM app_settings WHERE setting_key='last_sync'");
        $val = $stmt->fetchColumn();
        echo json_encode(['success' => true, 'last_sync' => $val ?: 'ยังไม่เคย sync'], JSON_UNESCAPED_UNICODE);
        break;
    }

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
