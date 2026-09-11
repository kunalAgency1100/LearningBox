<?php
// Prefer an absolute path outside public_html, configured in the hosting environment.
$privateConfig = getenv('LEARNINGBOX_PAYMENT_CONFIG') ?: dirname(__DIR__, 2) . '/learningbox-payments.php';
$settings = is_file($privateConfig) ? require $privateConfig : [];
return array_merge([
    'key_id' => getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_TZb21hKYtx026s',
    'key_secret' => getenv('RAZORPAY_KEY_SECRET') ?: 'J12e1GI7EFMujiFf8qoySOI1',
    'webhook_secret' => getenv('RAZORPAY_WEBHOOK_SECRET') ?: 'zXLzdFYXgcQ21oiGh48GjN1q9QOfE1JR',
    'base_url' => getenv('LEARNINGBOX_URL') ?: 'https://www.learningbox.in',
    'admin_email' => getenv('LEARNINGBOX_ADMIN_EMAIL') ?: 'info@learningbox.in',
    'from_email' => getenv('LEARNINGBOX_FROM_EMAIL') ?: 'info@learningbox.in',
    'mail_envelope_from' => getenv('LEARNINGBOX_MAIL_ENVELOPE_FROM') ?: 'info@learningbox.in',
], $settings);
