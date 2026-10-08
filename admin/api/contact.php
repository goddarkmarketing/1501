<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once dirname(__DIR__) . '/includes/db.php';

function contactHeaderSafe(string $value): string {
    return trim(str_replace(["\r", "\n"], '', $value));
}

function contactNotifyAddress(): string {
    try {
        $row = fetchOne('SELECT setting_value FROM site_settings WHERE setting_key = ?', ['email']);
        $email = trim((string) ($row['setting_value'] ?? ''));
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
    } catch (Throwable $e) {
        // settings table may be unavailable
    }
    return 'me.agentthailand@gmail.com';
}

function contactCallbackLabel(string $value): string {
    $map = [
        'now' => 'ติดต่อทันที',
        '09-12' => '09:00 - 12:00',
        '13-16' => '13:00 - 16:00',
        '16-19' => '16:00 - 19:00',
    ];
    return $map[$value] ?? ($value !== '' ? $value : '-');
}

function contactSendNotification(array $lead): bool {
    $to = contactNotifyAddress();
    $name = (string) ($lead['name'] ?? '');
    $customerEmail = (string) ($lead['email'] ?? '');
    $rows = [
        'ชื่อ' => $name,
        'เบอร์โทร' => (string) ($lead['phone'] ?? ''),
        'อีเมล' => $customerEmail,
        'ประกันที่สนใจ' => (string) ($lead['insurance_type'] ?? ''),
        'จังหวัด' => (string) ($lead['province'] ?? ''),
        'อายุ' => $lead['age'] ? (string) $lead['age'] : '',
        'เวลาที่สะดวกติดต่อกลับ' => contactCallbackLabel((string) ($lead['callback_time'] ?? '')),
        'ข้อความ' => (string) ($lead['message'] ?? ''),
    ];

    $html = '<p>มีลูกค้ากรอกแบบฟอร์มบนเว็บไซต์ Agent Thailand</p><table cellpadding="6" cellspacing="0" border="1" style="border-collapse:collapse">';
    foreach ($rows as $label => $value) {
        $shown = $value !== '' ? $value : '-';
        $html .= '<tr><th align="left">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . '</th><td>' . nl2br(htmlspecialchars($shown, ENT_QUOTES, 'UTF-8')) . '</td></tr>';
    }
    $html .= '</table><p>ดูรายการทั้งหมดได้ในหลังบ้าน เมนูข้อความติดต่อ</p>';

    $subject = 'มีลูกค้าใหม่สนใจประกัน' . ($name !== '' ? ' — ' . $name : '');
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $host = preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'prakandd-thailand.com'));
    $host = preg_replace('/^www\./i', '', $host);
    if (!preg_match('/^[a-z0-9.-]+$/i', $host)) {
        $host = 'prakandd-thailand.com';
    }
    $from = 'noreply@' . $host;
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Agent Thailand <' . contactHeaderSafe($from) . '>',
    ];
    if (filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . contactHeaderSafe($customerEmail);
    }

    return @mail($to, $encodedSubject, $html, implode("\r\n", $headers));
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$name = trim($payload['name'] ?? '');
$phone = trim($payload['phone'] ?? '');
$email = trim($payload['email'] ?? '');

if ($name === '' && $phone === '' && $email === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'กรุณากรอกชื่อหรือเบอร์โทร']);
    exit;
}

try {
    $lead = [
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'insurance_type' => trim($payload['insurance_type'] ?? ''),
        'province' => trim($payload['province'] ?? ''),
        'age' => (int) ($payload['age'] ?? 0) ?: null,
        'callback_time' => trim($payload['callback_time'] ?? ''),
        'message' => trim($payload['message'] ?? ''),
    ];
    query(
        'INSERT INTO contact_submissions (name, phone, email, insurance_type, province, age, callback_time, message, status) VALUES (?,?,?,?,?,?,?,?,\'new\')',
        [
            $lead['name'],
            $lead['phone'],
            $lead['email'],
            $lead['insurance_type'],
            $lead['province'],
            $lead['age'],
            $lead['callback_time'],
            $lead['message'],
        ]
    );
    $mailed = contactSendNotification($lead);
    echo json_encode(['ok' => true, 'mailed' => $mailed]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'บันทึกไม่สำเร็จ']);
}
