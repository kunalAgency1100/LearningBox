<?php
function commerce_config(): array {
    static $config;
    if ($config === null) $config = require dirname(__DIR__) . '/config/payments.php';
    return $config;
}
function esc($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function money($amount): string { return 'INR ' . number_format($amount / 100, 2); }
function commerce_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start(['cookie_httponly' => true, 'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'cookie_samesite' => 'Lax']);
    }
    if (empty($_SESSION['commerce_csrf'])) $_SESSION['commerce_csrf'] = bin2hex(random_bytes(32));
}
function require_admin(): void {
    commerce_session();
    if (($_SESSION['admin_auth'] ?? false) !== true) { header('Location: login.php'); exit; }
    header('Cache-Control: no-store');
}
function check_csrf($token): void {
    if (!is_string($token) || !hash_equals($_SESSION['commerce_csrf'] ?? '', $token) || $token === '') {
        throw new InvalidArgumentException('Your session expired. Refresh the page and try again.');
    }
}
function price_to_paise(string $price): int {
    if (!preg_match('/^[0-9]{1,7}(?:\.[0-9]{1,2})?$/D', $price)) throw new InvalidArgumentException('Enter a valid price with at most two decimal places.');
    $parts = explode('.', $price);
    $amount = (int)$parts[0] * 100 + (int)str_pad($parts[1] ?? '', 2, '0');
    if ($amount < 100 || $amount > 100000000) throw new InvalidArgumentException('Price must be between INR 1 and INR 10,00,000.');
    return $amount;
}
function buyer_details(array $data): array {
    $values = [];
    foreach (['name', 'email', 'phone'] as $key) {
        if (!is_string($data[$key] ?? null)) throw new InvalidArgumentException('Name, email and contact number are required.');
        $values[$key] = trim($data[$key]);
    }
    if ($values['name'] === '' || strlen($values['name']) > 120 || preg_match('/[\x00-\x1f\x7f]/', $values['name'])) throw new InvalidArgumentException('Enter a valid name (up to 120 characters).');
    if (strlen($values['email']) > 254 || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid email address.');
    if (!preg_match('/^\+?[0-9 ()-]{7,25}$/D', $values['phone']) || strlen(preg_replace('/\D/', '', $values['phone'])) < 7 || strlen(preg_replace('/\D/', '', $values['phone'])) > 15) throw new InvalidArgumentException('Enter a valid contact number including country code.');
    return $values;
}
function valid_signature(string $payload, string $signature, string $secret): bool {
    return $secret !== '' && preg_match('/^[a-f0-9]{64}$/D', $signature) && hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
}
class PaymentGatewayException extends RuntimeException {}
function payment_setup_issues(): array {
    $config = commerce_config();
    $issues = [];
    if (!preg_match('/^rzp_(test|live)_[A-Za-z0-9]+$/D', $config['key_id']) || stripos($config['key_id'], 'REPLACE') !== false || !$config['key_secret'] || stripos($config['key_secret'], 'REPLACE') !== false) {
        $issues[] = 'Configure a valid Razorpay key ID and matching secret in the private payment configuration.';
    }
    if (!function_exists('curl_init')) $issues[] = 'Enable the PHP cURL extension in cPanel.';
    return $issues;
}
function require_payment_setup(): void {
    if (payment_setup_issues()) throw new PaymentGatewayException('Online payments are not configured yet. Please contact LearningBox to complete your purchase.');
}
function razorpay(string $method, string $path, ?array $body = null): array {
    require_payment_setup();
    $config = commerce_config();
    $ch = curl_init('https://api.razorpay.com/v1/' . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_USERPWD => $config['key_id'] . ':' . $config['key_secret'], CURLOPT_CUSTOMREQUEST => $method, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 25, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_errno($ch);
    curl_close($ch);
    $result = json_decode($raw ?: '', true);
    if ($code < 200 || $code >= 300 || !is_array($result)) {
        // Log diagnostic codes only: never gateway bodies, credentials or buyer data.
        error_log('Razorpay request failed: HTTP ' . $code . ', cURL ' . $curlError);
        if ($code === 401 || $code === 403) throw new PaymentGatewayException('The payment provider could not authenticate this store. Please contact LearningBox.');
        throw new PaymentGatewayException('Unable to connect to the payment provider. Please try again shortly.');
    }
    return $result;
}
function payment_matches(array $purchase, array $payment): bool {
    return ($payment['order_id'] ?? '') === $purchase['razorpay_order_id']
        && (int)($payment['amount'] ?? 0) === (int)$purchase['amount']
        && ($payment['currency'] ?? '') === $purchase['currency']
        && is_string($payment['id'] ?? null) && preg_match('/^pay_[A-Za-z0-9]+$/D', $payment['id']);
}
// A row lock serializes browser verification and repeated / out-of-order webhooks.
function apply_payment(PDO $pdo, array $payment): void {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT * FROM purchases WHERE razorpay_order_id = ? FOR UPDATE');
        $stmt->execute([$payment['order_id'] ?? '']);
        $purchase = $stmt->fetch();
        if (!$purchase) { $pdo->commit(); return; }
        if (!payment_matches($purchase, $payment)) throw new RuntimeException('Payment details do not match the purchase.');
        if ($purchase['status'] !== 'paid') {
            $state = $payment['status'] ?? '';
            if ($state === 'captured') {
                $pdo->prepare("UPDATE purchases SET status='paid', razorpay_payment_id=?, failure_reason=NULL, paid_at=UTC_TIMESTAMP() WHERE id=?")->execute([$payment['id'], $purchase['id']]);
                foreach (['buyer', 'admin'] as $recipient) $pdo->prepare('INSERT IGNORE INTO payment_emails (purchase_id, recipient_type) VALUES (?, ?)')->execute([$purchase['id'], $recipient]);
            } elseif ($state === 'failed' && $purchase['status'] !== 'authorized') {
                $pdo->prepare("UPDATE purchases SET status='failed', razorpay_payment_id=?, failure_reason=? WHERE id=?")->execute([$payment['id'], substr((string)($payment['error_description'] ?? 'Payment attempt failed.'), 0, 255), $purchase['id']]);
            } elseif ($state === 'authorized') {
                $pdo->prepare("UPDATE purchases SET status='authorized', razorpay_payment_id=?, failure_reason=NULL WHERE id=?")->execute([$payment['id'], $purchase['id']]);
            }
        }
        $pdo->commit();
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
function purchase_by_token(PDO $pdo, string $token): array {
    if (!preg_match('/^[a-f0-9]{64}$/D', $token)) throw new InvalidArgumentException('Purchase not found.');
    $stmt = $pdo->prepare('SELECT * FROM purchases WHERE token=?');
    $stmt->execute([$token]);
    $purchase = $stmt->fetch();
    if (!$purchase) throw new InvalidArgumentException('Purchase not found.');
    return $purchase;
}
