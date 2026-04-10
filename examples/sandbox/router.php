<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

const IXOPAY_SANDBOX_STORAGE = __DIR__ . '/storage/transactions.json';
const IXOPAY_SANDBOX_API_KEY = 'sandbox-api-key';
const IXOPAY_SANDBOX_USERNAME = 'sandbox-user';
const IXOPAY_SANDBOX_PASSWORD = 'sandbox-password';
const IXOPAY_SANDBOX_SHARED_SECRET = 'sandbox-shared-secret';

if (!is_dir(dirname(IXOPAY_SANDBOX_STORAGE))) {
    mkdir(dirname(IXOPAY_SANDBOX_STORAGE), 0777, true);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($method === 'GET' && $path === '/sandbox/test-credentials') {
    sendJson([
        'apiKey' => IXOPAY_SANDBOX_API_KEY,
        'username' => IXOPAY_SANDBOX_USERNAME,
        'password' => IXOPAY_SANDBOX_PASSWORD,
        'sharedSecret' => IXOPAY_SANDBOX_SHARED_SECRET,
        'baseUrl' => getBaseUrl() . '/',
    ]);
}

if ($method === 'GET' && $path === '/sandbox/payment-methods') {
    sendJson([
        'paymentMethods' => [
            [
                'id' => 'card',
                'label' => 'Card',
                'testValues' => [
                    'success' => '4111111111111111',
                    'decline' => '4000000000000002',
                    'threeDSecure' => '4000000000003220',
                ],
                'scenarios' => ['redirect', 'finished', 'error', 'card-3ds'],
            ],
            [
                'id' => 'mpesa',
                'label' => 'M-Pesa',
                'testValues' => [
                    'success' => '+254700000001',
                    'pending' => '+254700000002',
                    'timeout' => '+254700000003',
                ],
                'scenarios' => ['mpesa-success', 'mpesa-pending', 'mpesa-timeout'],
            ],
        ],
    ]);
}

if ($method === 'POST' && preg_match('#^/api/v3/transaction/([^/]+)/(debit|preauthorize)$#', $path, $matches)) {
    assertSandboxCredentials($matches[1]);

    $transactionType = strtoupper($matches[2]);
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
    $scenario = strtolower((string) getHeaderValue('X-Sandbox-Scenario', 'redirect'));
    $paymentMethod = strtolower((string) getHeaderValue('X-Sandbox-Payment-Method', inferPaymentMethod($scenario)));

    $merchantTransactionId = (string) ($payload['merchantTransactionId'] ?? uniqid('sandbox-', true));
    $amount = (float) ($payload['amount'] ?? 0);
    $currency = (string) ($payload['currency'] ?? 'EUR');
    $uuid = 'sandbox-' . substr(sha1($merchantTransactionId), 0, 18);
    $purchaseId = 'purchase-' . substr(md5($merchantTransactionId), 0, 10);

    $record = [
        'uuid' => $uuid,
        'purchaseId' => $purchaseId,
        'merchantTransactionId' => $merchantTransactionId,
        'transactionType' => $transactionType,
        'scenario' => $scenario,
        'amount' => $amount,
        'currency' => $currency,
        'paymentMethod' => $paymentMethod,
        'createdAt' => gmdate(DATE_ATOM),
    ];

    persistTransaction($merchantTransactionId, $record);

    sendJson(buildTransactionResponse($record));
}

if ($method === 'GET' && preg_match('#^/api/v3/status/([^/]+)/getByMerchantTransactionId/(.+)$#', $path, $matches)) {
    assertSandboxCredentials($matches[1], false);

    $merchantTransactionId = rawurldecode($matches[2]);
    $record = loadTransaction($merchantTransactionId);

    if ($record === null) {
        sendJson([
            'success' => false,
            'errorCode' => 'NOT_FOUND',
            'errorMessage' => 'Sandbox transaction not found.',
        ], 404);
    }

    sendJson(buildStatusResponse($record));
}

if ($method === 'GET' && preg_match('#^/sandbox/callback/(.+)$#', $path, $matches)) {
    $merchantTransactionId = rawurldecode($matches[1]);
    $record = loadTransaction($merchantTransactionId);

    if ($record === null) {
        sendJson([
            'result' => 'INVALID_REQUEST',
            'merchantTransactionId' => $merchantTransactionId,
        ], 404);
    }

    $payload = buildCallbackPayload($record);

    sendJson([
        'payload' => $payload,
        'signature' => signPayload($payload),
        'headers' => [
            'X-Signature' => signPayload($payload),
            'Content-Type' => 'application/json',
        ],
    ]);
}

if ($method === 'POST' && preg_match('#^/sandbox/webhooks/(.+)/replay$#', $path, $matches)) {
    $merchantTransactionId = rawurldecode($matches[1]);
    $record = loadTransaction($merchantTransactionId);

    if ($record === null) {
        sendJson([
            'result' => 'INVALID_REQUEST',
            'merchantTransactionId' => $merchantTransactionId,
        ], 404);
    }

    $payload = buildCallbackPayload($record);

    sendJson([
        'delivered' => true,
        'payload' => $payload,
        'signature' => signPayload($payload),
        'message' => 'Sandbox replay generated. Deliver this payload to your local callback URL.',
    ]);
}

if ($method === 'GET' && preg_match('#^/sandbox/redirect/(.+)$#', $path, $matches)) {
    $merchantTransactionId = rawurldecode($matches[1]);
    $record = loadTransaction($merchantTransactionId);

    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>IXOPAY Sandbox Redirect</title>';
    echo '<style>body{font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;padding:32px}';
    echo '.card{max-width:720px;margin:0 auto;background:#111827;border-radius:24px;padding:24px;border:1px solid #334155}';
    echo 'code{background:#0b1220;padding:2px 6px;border-radius:8px}</style></head><body><div class="card">';
    echo '<p>IXOPAY Sandbox Redirect</p><h1>Simulated hosted-payment redirect</h1>';
    echo '<p>Merchant transaction: <code>' . htmlspecialchars($merchantTransactionId, ENT_QUOTES, 'UTF-8') . '</code></p>';
    if ($record !== null) {
        echo '<p>Scenario: <code>' . htmlspecialchars($record['scenario'], ENT_QUOTES, 'UTF-8') . '</code></p>';
        echo '<p>Callback JSON: <a href="/sandbox/callback/' . rawurlencode($merchantTransactionId) . '">/sandbox/callback/' . htmlspecialchars($merchantTransactionId, ENT_QUOTES, 'UTF-8') . '</a></p>';
    }
    echo '</div></body></html>';
    exit;
}

sendJson([
    'name' => 'IXOPAY Local Sandbox',
    'routes' => [
        'GET /sandbox/test-credentials',
        'GET /sandbox/payment-methods',
        'POST /api/v3/transaction/{apiKey}/debit',
        'POST /api/v3/transaction/{apiKey}/preauthorize',
        'GET /api/v3/status/{apiKey}/getByMerchantTransactionId/{merchantTransactionId}',
        'GET /sandbox/callback/{merchantTransactionId}',
        'POST /sandbox/webhooks/{merchantTransactionId}/replay',
    ],
]);

function buildTransactionResponse(array $record): array
{
    $base = [
        'success' => true,
        'uuid' => $record['uuid'],
        'purchaseId' => $record['purchaseId'],
        'paymentMethod' => $record['paymentMethod'],
        'extraData' => [
            'sandboxScenario' => $record['scenario'],
        ],
    ];

    switch ($record['scenario']) {
        case 'mpesa-pending':
        case 'pending':
            return $base + [
                'returnType' => 'PENDING',
            ];
        case 'mpesa-success':
        case 'finished':
            return $base + [
                'returnType' => 'FINISHED',
            ];
        case 'mpesa-timeout':
            return $base + [
                'returnType' => 'PENDING',
                'extraData' => [
                    'sandboxScenario' => $record['scenario'],
                    'providerMessage' => 'M-Pesa STK push timed out. Await callback or poll status.',
                ],
            ];
        case 'card-3ds':
            return $base + [
                'returnType' => 'REDIRECT',
                'redirectType' => 'fullpage',
                'redirectUrl' => getBaseUrl() . '/sandbox/redirect/' . rawurlencode($record['merchantTransactionId']) . '?challenge=3ds',
                'extraData' => [
                    'sandboxScenario' => $record['scenario'],
                    'threeDSecure' => 'challenge-required',
                ],
            ];
        case 'error':
            return $base + [
                'returnType' => 'ERROR',
                'errors' => [[
                    'code' => 'SANDBOX_DECLINED',
                    'message' => 'Sandbox decline triggered by X-Sandbox-Scenario=error',
                    'adapterCode' => 'MOCK-DECLINE',
                    'adapterMessage' => 'The sandbox issuer declined the transaction.',
                ]],
            ];
        case 'redirect':
        default:
            return $base + [
                'returnType' => 'REDIRECT',
                'redirectType' => 'fullpage',
                'redirectUrl' => getBaseUrl() . '/sandbox/redirect/' . rawurlencode($record['merchantTransactionId']),
            ];
    }
}

function buildStatusResponse(array $record): array
{
    $status = match ($record['scenario']) {
        'pending', 'redirect', 'mpesa-pending', 'mpesa-timeout', 'card-3ds' => 'PENDING',
        'error' => 'ERROR',
        default => 'SUCCESS',
    };

    return [
        'success' => true,
        'transactionStatus' => $status,
        'uuid' => $record['uuid'],
        'merchantTransactionId' => $record['merchantTransactionId'],
        'purchaseId' => $record['purchaseId'],
        'transactionType' => $record['transactionType'],
        'paymentMethod' => $record['paymentMethod'],
        'amount' => $record['amount'],
        'currency' => $record['currency'],
        'extraData' => [
            'sandboxScenario' => $record['scenario'],
        ],
    ];
}

function buildCallbackPayload(array $record): array
{
    $result = in_array($record['scenario'], ['error', 'mpesa-timeout'], true) ? 'ERROR' : 'OK';

    return [
        'result' => $result,
        'uuid' => $record['uuid'],
        'merchantTransactionId' => $record['merchantTransactionId'],
        'purchaseId' => $record['purchaseId'],
        'transactionType' => $record['transactionType'],
        'paymentMethod' => $record['paymentMethod'],
        'amount' => $record['amount'],
        'currency' => $record['currency'],
        'extraData' => [
            'sandboxScenario' => $record['scenario'],
        ],
        'errors' => in_array($record['scenario'], ['error', 'mpesa-timeout'], true) ? [[
            'code' => $record['scenario'] === 'mpesa-timeout' ? 'SANDBOX_MPESA_TIMEOUT' : 'SANDBOX_DECLINED',
            'message' => $record['scenario'] === 'mpesa-timeout'
                ? 'The sandbox M-Pesa confirmation timed out.'
                : 'The sandbox issuer declined the transaction.',
        ]] : [],
    ];
}

function inferPaymentMethod(string $scenario): string
{
    if (str_starts_with($scenario, 'mpesa')) {
        return 'mpesa';
    }

    return 'card';
}

function assertSandboxCredentials(string $apiKey, bool $requireBasicAuth = true): void
{
    if ($apiKey !== IXOPAY_SANDBOX_API_KEY) {
        sendJson([
            'success' => false,
            'errorCode' => 'SANDBOX_INVALID_API_KEY',
            'errorMessage' => 'Use sandbox-api-key for the local sandbox.',
        ], 401);
    }

    if (!$requireBasicAuth) {
        return;
    }

    [$username, $password] = getBasicAuthCredentials();

    if ($username !== IXOPAY_SANDBOX_USERNAME || $password !== IXOPAY_SANDBOX_PASSWORD) {
        sendJson([
            'success' => false,
            'errorCode' => 'SANDBOX_INVALID_CREDENTIALS',
            'errorMessage' => 'Use sandbox-user / sandbox-password for the local sandbox.',
        ], 401);
    }
}

function signPayload(array $payload): string
{
    return hash_hmac('sha512', json_encode($payload, JSON_UNESCAPED_SLASHES), IXOPAY_SANDBOX_SHARED_SECRET);
}

function getBasicAuthCredentials(): array
{
    if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
        return [(string) $_SERVER['PHP_AUTH_USER'], (string) $_SERVER['PHP_AUTH_PW']];
    }

    $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

    if (str_starts_with($authorization, 'Basic ')) {
        $decoded = base64_decode(substr($authorization, 6), true);

        if (is_string($decoded) && str_contains($decoded, ':')) {
            return explode(':', $decoded, 2);
        }
    }

    return ['', ''];
}

function persistTransaction(string $merchantTransactionId, array $record): void
{
    $transactions = loadAllTransactions();
    $transactions[$merchantTransactionId] = $record;
    file_put_contents(IXOPAY_SANDBOX_STORAGE, json_encode($transactions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function loadTransaction(string $merchantTransactionId): ?array
{
    $transactions = loadAllTransactions();

    return $transactions[$merchantTransactionId] ?? null;
}

function loadAllTransactions(): array
{
    if (!file_exists(IXOPAY_SANDBOX_STORAGE)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents(IXOPAY_SANDBOX_STORAGE), true);

    return is_array($decoded) ? $decoded : [];
}

function getHeaderValue(string $name, string $default = ''): string
{
    $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

    return isset($_SERVER[$serverKey]) ? (string) $_SERVER[$serverKey] : $default;
}

function getBaseUrl(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8089';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

    return $scheme . '://' . $host;
}

function sendJson(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}
