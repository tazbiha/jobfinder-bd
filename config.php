
<?php

// ========================================
// 1. DATABASE SETTINGS
// ========================================

define('DB_HOST', getenv('DB_HOST') ?: 'database-jobfinder.k.aivencloud.com');
define('DB_PORT', (int) (getenv('DB_PORT') ?: 12361));
define('DB_USER', getenv('DB_USER') ?: 'avnadmin');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'defaultdb');
define('DB_SSL_CA', getenv('DB_SSL_CA') ?: '/etc/secrets/ca.pem');

// ========================================
// 2. GEMINI API SETTINGS
// ========================================

define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
define('GEMINI_MODEL', getenv('GEMINI_MODEL') ?: 'gemini-3.8-flash');
define(
    'GEMINI_API_URL',
    'https://generativelanguage.googleapis.com/v1beta/interactions'
);

// ========================================
// 3. SESSION SETTINGS
// ========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ========================================
// 4. DATABASE CONNECTION
// ========================================

mysqli_report(MYSQLI_REPORT_OFF);

$db = mysqli_init();

if (!$db) {
    error_log('Could not initialize MySQL connection.');
    http_response_code(500);
    exit('Could not initialize database connection.');
}

if (DB_PASS === '') {
    error_log('Database password is missing.');
    http_response_code(500);
    exit('Database settings are incomplete.');
}

if (!is_readable(DB_SSL_CA)) {
    error_log('Aiven SSL CA certificate is missing or unreadable.');
    http_response_code(500);
    exit('Database SSL certificate is not configured.');
}

$db->ssl_set(null, null, DB_SSL_CA, null, null);

$connected = @$db->real_connect(
    DB_HOST,
    DB_USER,
    DB_PASS,
    DB_NAME,
    DB_PORT,
    null,
    MYSQLI_CLIENT_SSL
);

if (!$connected) {
    error_log('Database connection failed: ' . $db->connect_error);
    http_response_code(500);
    exit('Database connection failed. Check database settings.');
}

if (!$db->set_charset('utf8mb4')) {
    error_log('Could not set database charset: ' . $db->error);
}

// ========================================
// 5. HTML ESCAPING HELPER
// ========================================

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// ========================================
// 6. COMPANY LOGO HELPER
// ========================================

function company_logo(array $job): string
{
    $company = e($job['company'] ?? 'Company');

    if (empty($job['company_logo'])) {
        return '<span class="company-logo company-logo-placeholder" role="img" aria-label="' .
            $company . ' logo">CO</span>';
    }

    return '<img class="company-logo" src="' .
        e($job['company_logo']) .
        '" alt="' . $company . ' logo" loading="lazy">';
}

// ========================================
// 7. JOB CATEGORIES
// ========================================

function job_categories(): array
{
    return [
        'Accounting/Finance',
        'Bank/Non-Bank Fin. Institution',
        'NGO/Development',
        'Education/Training',
        'Marketing/Sales',
        'HR/Org. Development',
        'Pharmaceutical',
        'Healthcare/Medical',
        'E-commerce/Digital Marketing',
        'IT/Telecommunication',
        'Administration',
        'Other',
    ];
}

// ========================================
// 8. CURRENT USER
// ========================================

function user()
{
    return $_SESSION['user'] ?? null;
}

// ========================================
// 9. FLASH MESSAGE
// ========================================

function flash($message = null)
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }

    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $message;
}

// ========================================
// 10. AUTHENTICATION / ROLE CHECK
// ========================================

function need($role = null): void
{
    $currentUser = user();

    if (
        !$currentUser ||
        ($role !== null && ($currentUser['role'] ?? null) !== $role)
    ) {
        header('Location: login.php');
        exit;
    }
}

// ========================================
// 11. SELECT QUERY HELPER
// Returns an array of rows
// ========================================

function q(string $sql, array $params = []): array
{
    global $db;

    $stmt = $db->prepare($sql);

    if (!$stmt) {
        error_log('SQL prepare error: ' . $db->error);
        throw new RuntimeException('Database query failed.');
    }

    try {
        if ($params) {
            $types = '';
            $values = [];

            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $types .= 'i';
                } elseif (is_float($value)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }

                $values[$key] = $value;
            }

            $references = [];

            foreach ($values as $key => &$value) {
                $references[$key] = &$value;
            }
            unset($value);

            if (!$stmt->bind_param($types, ...$references)) {
                throw new RuntimeException(
                    'Database parameter binding failed.'
                );
            }
        }

        if (!$stmt->execute()) {
            error_log('SQL execute error: ' . $stmt->error);
            throw new RuntimeException('Database query failed.');
        }

        $result = $stmt->get_result();

        if ($result === false) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    } finally {
        $stmt->close();
    }
}

// ========================================
// 12. INSERT / UPDATE / DELETE HELPER
// Returns the database insert ID
// ========================================

function x(string $sql, array $params = []): int
{
    global $db;

    $stmt = $db->prepare($sql);

    if (!$stmt) {
        error_log('SQL prepare error: ' . $db->error);
        throw new RuntimeException('Database query failed.');
    }

    try {
        if ($params) {
            $types = '';
            $values = [];

            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $types .= 'i';
                } elseif (is_float($value)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }

                $values[$key] = $value;
            }

            $references = [];

            foreach ($values as $key => &$value) {
                $references[$key] = &$value;
            }
            unset($value);

            if (!$stmt->bind_param($types, ...$references)) {
                throw new RuntimeException(
                    'Database parameter binding failed.'
                );
            }
        }

        if (!$stmt->execute()) {
            error_log('SQL execute error: ' . $stmt->error);
            throw new RuntimeException('Database query failed.');
        }

        return (int) $db->insert_id;
    } finally {
        $stmt->close();
    }
}

// ========================================
// 13. GEMINI API HELPER
// Returns [result, error]
// ========================================

function gemini(string $prompt, bool $json = true): array
{
    if (GEMINI_API_KEY === '') {
        return [null, 'Gemini API key is missing from environment.'];
    }

    if (!function_exists('curl_init')) {
        return [null, 'PHP cURL extension is not enabled.'];
    }

    $body = [
        'model' => GEMINI_MODEL,
        'input' => $prompt,
    ];

    if ($json) {
        $body['response_format'] = [
            'type' => 'text',
            'mime_type' => 'application/json',
        ];
    }

    $payload = json_encode(
        $body,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    if ($payload === false) {
        return [null, 'Could not encode Gemini request.'];
    }

    $ch = curl_init(GEMINI_API_URL);

    if ($ch === false) {
        return [null, 'Could not initialize Gemini connection.'];
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 90,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-goog-api-key: ' . GEMINI_API_KEY,
        ],
        CURLOPT_POSTFIELDS => $payload,
    ]);

    $maxAttempts = 3;
    $response = false;
    $curlError = '';
    $status = 0;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $retryAfter = null;

        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            static function ($handle, string $headerLine) use (&$retryAfter): int {
                if (stripos($headerLine, 'Retry-After:') === 0) {
                    $retryAfter = trim(
                        substr($headerLine, strlen('Retry-After:'))
                    );
                }

                return strlen($headerLine);
            }
        );

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $shouldRetry = $status === 503;

        if ($status === 429 && $response !== false) {
            $errorData = json_decode($response, true);

            $errorMessage = strtolower(
                is_array($errorData)
                    ? ($errorData['error']['message'] ?? '')
                    : ''
            );

            $dailyQuotaExceeded =
                str_contains($errorMessage, 'per day') ||
                str_contains($errorMessage, 'daily') ||
                str_contains($errorMessage, 'free tier');

            $shouldRetry = !$dailyQuotaExceeded;
        }

        if (
            $response === false ||
            !$shouldRetry ||
            $attempt === $maxAttempts
        ) {
            break;
        }

        $retryDelay = min($attempt, 5);

        if ($retryAfter !== null) {
            if (ctype_digit($retryAfter)) {
                $retryDelay = min((int) $retryAfter, 5);
            } else {
                $retryAt = strtotime($retryAfter);

                if ($retryAt !== false) {
                    $retryDelay = min(
                        max(0, $retryAt - time()),
                        5
                    );
                }
            }
        }

        if ($retryDelay > 0) {
            usleep($retryDelay * 1000000);
        }
    }

    curl_close($ch);

    if ($response === false) {
        error_log('Gemini cURL error: ' . $curlError);

        return [
            null,
            'Could not connect to Gemini. Check network and SSL settings.',
        ];
    }

    $data = json_decode($response, true);

    if ($status < 200 || $status >= 300) {
        $message = is_array($data)
            ? ($data['error']['message'] ?? 'Gemini API request failed.')
            : 'Gemini API returned an invalid error response.';

        error_log('Gemini HTTP ' . $status . ': ' . $message);

        return [
            null,
            'Gemini API error (HTTP ' . $status . '): ' . $message,
        ];
    }

    if (!is_array($data)) {
        error_log('Gemini returned invalid response JSON.');

        return [null, 'Invalid response received from Gemini.'];
    }

    $text = '';

    if (
        isset($data['output_text']) &&
        is_string($data['output_text'])
    ) {
        $text = $data['output_text'];
    }

    if (
        $text === '' &&
        isset($data['steps']) &&
        is_array($data['steps'])
    ) {
        foreach ($data['steps'] as $step) {
            if (($step['type'] ?? '') !== 'model_output') {
                continue;
            }

            foreach (($step['content'] ?? []) as $part) {
                if (
                    ($part['type'] ?? '') === 'text' &&
                    isset($part['text']) &&
                    is_string($part['text'])
                ) {
                    $text .= $part['text'];
                }
            }
        }
    }

    if (trim($text) === '') {
        error_log('Gemini response did not contain output text.');

        return [null, 'Gemini returned an empty response.'];
    }

    $text = trim($text);

    if (!$json) {
        return [$text, null];
    }

    // Remove optional Markdown code fences from AI-generated JSON.
    $text = preg_replace('/^\s*```(?:json)?\s*/i', '', $text);
    $text = preg_replace('/\s*```\s*$/', '', $text);
    $text = trim($text);

    $result = json_decode($text, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Gemini JSON parse error: ' . json_last_error_msg());

        return [null, 'Could not parse AI JSON response.'];
    }

    return [$result, null];
}

// End of config.php — do not add a closing PHP tag.
