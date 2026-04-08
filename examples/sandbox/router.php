<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

const IXOPAY_SANDBOX_STORAGE = __DIR__ . '/storage/transactions.json';

if (!is_dir(dirname(IXOPAY_SANDBOX_STORAGE))) {
    mkdir(dirname(IXOPAY_SANDBOX_STORAGE), 0777, true);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($method === 'POST' && preg_match('#^/api/v3/transaction/[^/]+/(debit|preauthorize)$#', $path, $matches)) {
    $transactionType = strtoupper($matches[1]);
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
    $scenario = strtolower((string) getHeaderValue('X-Sandbox-Scenario', 'redirect'));

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
        'paymentMethod' => 'mock-card',
    ];

    persistTransaction($merchantTransactionId, $record);

    sendJson(buildTransactionResponse($record));
}

if ($method === 'GET' && preg_match('#^/api/v3/status/[^/]+/getByMerchantTransactionId/(.+)$#', $path, $matches)) {
    $merchantTransactionId = rawurldecode($matches[1]);
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

    sendJson(buildCallbackPayload($record));
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
        'POST /api/v3/transaction/{apiKey}/debit',
        'POST /api/v3/transaction/{apiKey}/preauthorize',
        'GET /api/v3/status/{apiKey}/getByMerchantTransactionId/{merchantTransactionId}',
        'GET /sandbox/callback/{merchantTransactionId}',
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
        case 'pending':
            return $base + [
                'returnType' => 'PENDING',
            ];
        case 'finished':
            return $base + [
                'returnType' => 'FINISHED',
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
        'pending', 'redirect' => 'PENDING',
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
    $result = $record['scenario'] === 'error' ? 'ERROR' : 'OK';

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
        'errors' => $record['scenario'] === 'error' ? [[
            'code' => 'SANDBOX_DECLINED',
            'message' => 'The sandbox issuer declined the transaction.',
        ]] : [],
    ];
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
