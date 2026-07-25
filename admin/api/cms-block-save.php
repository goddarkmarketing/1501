<?php
/**
 * Save from visual editor: blocks, DOM overrides, FAQ items.
 */
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/cms.php';
requireLogin();

$raw = file_get_contents('php://input') ?: '';
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'JSON ไม่ถูกต้อง']);
    exit;
}

try {
    // FAQ by index
    if (($payload['kind'] ?? '') === 'faq' && isset($payload['itemIndex']) && is_array($payload['item'] ?? null)) {
        $index = (int) $payload['itemIndex'];
        $rows = fetchAll('SELECT id FROM faq_items WHERE is_active = 1 ORDER BY sort_order, id');
        $q = trim((string) ($payload['item']['q'] ?? ''));
        $a = trim((string) ($payload['item']['a'] ?? ''));
        if ($q === '' || $a === '') {
            throw new Exception('กรุณากรอกคำถามและคำตอบ');
        }
        if (isset($rows[$index])) {
            query('UPDATE faq_items SET question = ?, answer = ? WHERE id = ?', [$q, $a, $rows[$index]['id']]);
        } else {
            query('INSERT INTO faq_items (question, answer, sort_order, is_active) VALUES (?,?,?,1)', [$q, $a, $index]);
        }
        echo json_encode(['ok' => true, 'message' => 'บันทึก FAQ แล้ว'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // DOM text overrides
    if (($payload['kind'] ?? '') === 'dom' || ($payload['block'] ?? '') === 'visual_overrides') {
        $page = trim((string) ($payload['page'] ?? 'home'));
        $selector = trim((string) ($payload['selector'] ?? ''));
        if ($selector === '') {
            throw new Exception('ไม่พบ selector');
        }
        $data = getCmsBlock('visual_overrides');
        if (!is_array($data)) $data = [];
        if (!isset($data[$page]) || !is_array($data[$page])) $data[$page] = [];
        $entry = [];
        if (array_key_exists('value', $payload)) $entry['html'] = (string) $payload['value'];
        if (array_key_exists('text', $payload)) $entry['text'] = (string) $payload['text'];
        if (array_key_exists('href', $payload)) $entry['href'] = (string) $payload['href'];
        if (isset($payload['item']['href'])) $entry['href'] = (string) $payload['item']['href'];
        if (isset($payload['item']['label']) && !isset($entry['html'])) {
            $entry['text'] = (string) $payload['item']['label'];
        }
        $data[$page][$selector] = array_merge($data[$page][$selector] ?? [], $entry);
        saveCmsBlock('visual_overrides', $data);
        echo json_encode(['ok' => true, 'message' => 'บันทึกข้อความแล้ว', 'data' => $data[$page][$selector]], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $key = trim((string) ($payload['block'] ?? ''));
    if ($key === '' || !isset(getCmsBlockDefinitions()[$key])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'ไม่พบบล็อก']);
        exit;
    }

    $data = getCmsBlock($key);
    if (!is_array($data)) {
        $data = [];
    }

    if (isset($payload['itemIndex']) && is_array($payload['item'] ?? null)) {
        $index = (int) $payload['itemIndex'];
        if (!isset($data['items']) || !is_array($data['items'])) {
            $data['items'] = [];
        }
        $current = $data['items'][$index] ?? [];
        if (!is_array($current)) $current = [];
        $data['items'][$index] = array_merge($current, $payload['item']);
    } elseif (!empty($payload['field'])) {
        $field = (string) $payload['field'];
        if (isset($payload['item']) && is_array($payload['item'])) {
            $current = is_array($data[$field] ?? null) ? $data[$field] : [];
            $data[$field] = array_merge($current, $payload['item']);
        } elseif (array_key_exists('value', $payload)) {
            $data[$field] = $payload['value'];
        }
    } elseif (isset($payload['data']) && is_array($payload['data'])) {
        $data = $payload['data'];
    }

    saveCmsBlock($key, $data);
    echo json_encode(['ok' => true, 'message' => 'บันทึกบล็อกแล้ว', 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}
