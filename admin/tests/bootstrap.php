<?php
/**
 * Admin integration test helpers.
 * Requires XAMPP Apache + MySQL (agent1501) running.
 */

declare(strict_types=1);

define('ATEST_BASE', getenv('ATEST_BASE') ?: 'http://localhost/1501');
define('ATEST_ADMIN', ATEST_BASE . '/admin');
define('ATEST_COOKIE', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'agent1501_atest_cookies.txt');
define('ATEST_MARKER', 'ATEST_' . date('YmdHis') . '_' . substr(bin2hex(random_bytes(3)), 0, 6));
define('ATEST_SITE_ROOT', dirname(__DIR__, 2));

final class ATest {
    public static int $passed = 0;
    public static int $failed = 0;
    public static int $skipped = 0;
    /** @var list<string> */
    public static array $failures = [];
    /** @var list<string> */
    public static array $cleanup = [];

    public static function section(string $title): void {
        echo "\n=== {$title} ===\n";
    }

    public static function ok(string $name): void {
        self::$passed++;
        echo "  PASS  {$name}\n";
    }

    public static function fail(string $name, string $detail = ''): void {
        self::$failed++;
        $msg = $detail !== '' ? "{$name} — {$detail}" : $name;
        self::$failures[] = $msg;
        echo "  FAIL  {$msg}\n";
    }

    public static function skip(string $name, string $reason = ''): void {
        self::$skipped++;
        echo "  SKIP  {$name}" . ($reason !== '' ? " ({$reason})" : '') . "\n";
    }

    public static function assertTrue(bool $cond, string $name, string $detail = ''): void {
        if ($cond) {
            self::ok($name);
        } else {
            self::fail($name, $detail);
        }
    }

    public static function assertEquals(mixed $expected, mixed $actual, string $name): void {
        if ($expected == $actual) {
            self::ok($name);
        } else {
            self::fail($name, 'expected ' . json_encode($expected, JSON_UNESCAPED_UNICODE) . ' got ' . json_encode($actual, JSON_UNESCAPED_UNICODE));
        }
    }

    public static function assertContains(string $needle, string $haystack, string $name): void {
        if (str_contains($haystack, $needle)) {
            self::ok($name);
        } else {
            $snip = mb_substr($haystack, 0, 200);
            self::fail($name, "missing \"{$needle}\" in: {$snip}");
        }
    }
}

final class ATestDb {
    private static ?PDO $pdo = null;

    public static function pdo(): PDO {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                'mysql:host=127.0.0.1;dbname=agent1501;charset=utf8mb4',
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }
        return self::$pdo;
    }

    public static function one(string $sql, array $params = []): ?array {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function all(string $sql, array $params = []): array {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function exec(string $sql, array $params = []): void {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
    }

    public static function value(string $sql, array $params = []): mixed {
        $row = self::one($sql, $params);
        if (!$row) {
            return null;
        }
        return array_values($row)[0];
    }
}

final class ATestHttp {
    /** @return array{status:int,headers:string,body:string,json:?array} */
    public static function request(
        string $method,
        string $url,
        array $opts = []
    ): array {
        $ch = curl_init();
        $headers = $opts['headers'] ?? [];
        $follow = $opts['follow'] ?? true;

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_COOKIEJAR => ATEST_COOKIE,
            CURLOPT_COOKIEFILE => ATEST_COOKIE,
            CURLOPT_FOLLOWLOCATION => $follow,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        if (isset($opts['form'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($opts['form']));
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        } elseif (isset($opts['json'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($opts['json'], JSON_UNESCAPED_UNICODE));
            $headers[] = 'Content-Type: application/json';
        } elseif (isset($opts['multipart'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $opts['multipart']);
        } elseif (isset($opts['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $opts['body']);
        }

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('HTTP error: ' . $err . ' for ' . $url);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $headerStr = substr($raw, 0, $headerSize);
        $body = substr($raw, $headerSize);
        $json = null;
        $trimmed = ltrim($body);
        if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                $json = $decoded;
            }
        }

        return [
            'status' => $status,
            'headers' => $headerStr,
            'body' => $body,
            'json' => $json,
        ];
    }

    public static function get(string $path, array $opts = []): array {
        return self::request('GET', self::url($path), $opts);
    }

    public static function postForm(string $path, array $form, array $opts = []): array {
        $opts['form'] = $form;
        return self::request('POST', self::url($path), $opts);
    }

    public static function postJson(string $path, array $json, array $opts = []): array {
        $opts['json'] = $json;
        return self::request('POST', self::url($path), $opts);
    }

    public static function url(string $path): string {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, '/1501/')) {
            return ATEST_BASE . substr($path, strlen('/1501'));
        }
        if (str_starts_with($path, '/admin')) {
            return ATEST_BASE . $path;
        }
        if (str_starts_with($path, 'admin/')) {
            return ATEST_BASE . '/' . $path;
        }
        return ATEST_ADMIN . '/' . ltrim($path, '/');
    }

    public static function resetCookies(): void {
        if (is_file(ATEST_COOKIE)) {
            @unlink(ATEST_COOKIE);
        }
    }
}

function atest_login(string $user = 'admin', string $pass = 'admin123'): array {
    ATestHttp::resetCookies();
    return ATestHttp::postForm('login.php', [
        'username' => $user,
        'password' => $pass,
    ], ['follow' => true]);
}

function atest_require_login(): void {
    $res = atest_login();
    $ok = $res['status'] >= 200 && $res['status'] < 400
        && (str_contains($res['body'], 'แดชบอร์ด') || str_contains($res['body'], 'ระบบจัดการ') || str_contains($res['headers'], 'admin'));
    // Confirm session by hitting a protected page
    $dash = ATestHttp::get('index.php', ['follow' => false]);
    if ($dash['status'] === 302 && str_contains($dash['headers'], 'login.php')) {
        throw new RuntimeException('Login failed — cannot access dashboard (check admin/admin123)');
    }
    if ($dash['status'] === 200 && str_contains($dash['body'], 'เข้าสู่ระบบ')) {
        throw new RuntimeException('Login failed — still on login page');
    }
}

function atest_png_bytes(): string {
    // Minimal 1x1 PNG
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
}
