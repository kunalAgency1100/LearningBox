<?php
require_once dirname(__DIR__) . '/includes/commerce.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}
try {
    $raw = file_get_contents('php://input', false, null, 0, 262145);
    if (strlen($raw) > 262144) {
        http_response_code(413);
        exit;
    }
    if (!valid_signature($raw, $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '', commerce_config()['webhook_secret'])) {
        http_response_code(400);
        echo '{"success":false}';
        exit;
    }
    $event = json_decode($raw, true);
    if (!is_array($event)) {
        http_response_code(400);
        exit;
    }
    if (in_array($event['event'] ?? '', ['payment.captured', 'payment.authorized', 'payment.failed', 'order.paid'], true)) {
        $payment = $event['payload']['payment']['entity'] ?? null;
        if (!is_array($payment)) {
            http_response_code(400);
            exit;
        }
        require dirname(__DIR__) . '/config/db.php';
        apply_payment($pdo, $payment);
    }
    echo '{"success":true}';
} catch (Throwable $e) {
    error_log('Payment webhook: ' . $e->getMessage());
    http_response_code(500);
    echo '{"success":false}';
}
