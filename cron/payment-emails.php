<?php
// Retry unsent receipts once per minute from cPanel cron. HTTP access is denied.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/includes/payment-emails.php';
require dirname(__DIR__) . '/config/db.php';
$result = send_payment_emails($pdo);
echo json_encode($result) . PHP_EOL;
exit($result['error'] !== '' || $result['failed'] > 0 ? 1 : 0);
