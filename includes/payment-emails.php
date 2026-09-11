<?php
require_once __DIR__ . '/commerce.php';

function payment_mail_issues(array $config): array {
    $issues = [];
    if (!function_exists('mail')) $issues[] = 'PHP mail() is disabled. Ask your hosting provider to enable outgoing mail.';
    foreach (['from_email', 'admin_email'] as $field) {
        if (!is_string($config[$field] ?? null) || !filter_var($config[$field], FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $config[$field])) {
            $issues[] = 'Configure a valid ' . $field . ' in your payment configuration.';
        }
    }
    $envelopeFrom = $config['mail_envelope_from'] ?? '';
    if (!is_string($envelopeFrom) || ($envelopeFrom !== '' && (!filter_var($envelopeFrom, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+$/D', $envelopeFrom)))) {
        $issues[] = 'Leave mail_envelope_from empty for cPanel defaults, or enter a valid mailbox address.';
    }
    $url = is_string($config['base_url'] ?? null) ? parse_url($config['base_url']) : false;
    if (($url['scheme'] ?? '') !== 'https' || empty($url['host'])) $issues[] = 'Configure a valid HTTPS base_url in your payment configuration.';
    return $issues;
}

function payment_email_message(array $job, array $config): array {
    $buyer = $job['recipient_type'] === 'buyer';
    $to = $buyer ? $job['buyer_email'] : $config['admin_email'];
    $subject = ($buyer ? 'Your LearningBox payment receipt' : 'New paid LearningBox purchase') . ' - LB-' . $job['id'];
    $intro = $buyer ? 'Hi ' . esc($job['buyer_name']) . ', thank you for your purchase. Your payment has been received. Our team will contact you with the next steps for your product or service.' : 'A new purchase has been paid successfully. Please contact the buyer with the next steps.';
    $details = ['Purchase reference'=>'LB-'.$job['id'],'Product / service'=>$job['product_name'],'Amount paid'=>money($job['amount']),'Payment ID'=>$job['razorpay_payment_id'],'Paid at (UTC)'=>$job['paid_at'],'Buyer'=>$job['buyer_name'],'Email'=>$job['buyer_email'],'Contact number'=>$job['buyer_phone']];
    $rows = '';
    foreach ($details as $label=>$value) $rows .= '<tr><th align="left" style="padding:10px;border-bottom:1px solid #e5e7eb">'.esc($label).'</th><td style="padding:10px;border-bottom:1px solid #e5e7eb">'.esc($value).'</td></tr>';
    $link = rtrim($config['base_url'],'/') . ($buyer ? '/payment-success.php?token='.$job['token'] : '/admin/payments.php');
    $html = '<!DOCTYPE html><html><body style="margin:0;background:#F3F4F6;font-family:Arial,sans-serif;color:#1F2937"><div style="max-width:600px;margin:24px auto;background:white"><div style="background:#000976;color:white;padding:24px;border-bottom:4px solid #F4BD18"><h1>LearningBox</h1><p>Payment confirmed</p></div><div style="padding:24px"><p>'.$intro.'</p><table style="width:100%;border-collapse:collapse">'.$rows.'</table><p><a href="'.esc($link).'">View purchase details</a></p><p>Questions? Reply to this email or contact '.esc($config['admin_email']).'.</p><p>Regards,<br>LearningBox Team</p></div></div></body></html>';
    $headers = implode("\r\n", [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
        'From: LearningBox <' . $config['from_email'] . '>',
        'Reply-To: ' . $config['admin_email'],
        'Date: ' . gmdate('D, d M Y H:i:s') . ' +0000',
    ]);
    $envelopeFrom = $config['mail_envelope_from'] ?? '';
    return [
        'to' => $to, 'subject' => $subject, 'html' => $html,
        'body' => chunk_split(base64_encode($html), 76, "\r\n"),
        'headers' => $headers,
        'envelope' => $envelopeFrom === '' ? '' : '-f' . escapeshellarg($envelopeFrom),
    ];
}

// Use cPanel's PHP/sendmail configuration. No SMTP login or external mail API.
// The optional callable lets tests inspect arguments without sending any email.
function submit_payment_email(array $message, ?callable $transport = null): bool {
    if (!filter_var($message['to'], FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $message['to'])) return false;
    $transport = $transport ?? 'mail';
    if ($message['envelope'] === '') {
        return @$transport($message['to'], $message['subject'], $message['body'], $message['headers']);
    }
    return @$transport($message['to'], $message['subject'], $message['body'], $message['headers'], $message['envelope']);
}

// A shared lock covers immediate sends, manual retries and cron. Paid state is
// committed before entering this function; a mail failure never undoes payment.
function send_payment_emails(PDO $pdo, ?int $purchaseId = null, bool $retry = false): array {
    $result = ['sent' => 0, 'failed' => 0, 'busy' => false, 'error' => ''];
    $locked = false;
    try {
        $config = commerce_config();
        $issues = payment_mail_issues($config);
        if ($issues) { $result['error'] = implode(' ', $issues); return $result; }
        $locked = (bool)$pdo->query("SELECT GET_LOCK('learningbox_payment_emails', 0)")->fetchColumn();
        if (!$locked) { $result['busy'] = true; return $result; }
        if ($purchaseId !== null) {
            // Recover missing queue records for an existing paid purchase.
            foreach (['buyer', 'admin'] as $recipient) {
                $pdo->prepare("INSERT IGNORE INTO payment_emails (purchase_id, recipient_type) SELECT id, ? FROM purchases WHERE id=? AND status='paid'")->execute([$recipient, $purchaseId]);
            }
            if ($retry) {
                $pdo->prepare("UPDATE payment_emails SET status='pending', attempts=0, next_attempt_at=UTC_TIMESTAMP() WHERE purchase_id=? AND status IN ('pending','failed')")->execute([$purchaseId]);
            }
        }
        $sql = "SELECT e.id AS email_id,e.recipient_type,e.attempts,p.* FROM payment_emails e JOIN purchases p ON p.id=e.purchase_id WHERE e.status IN ('pending','failed') AND e.attempts<5 AND e.next_attempt_at<=UTC_TIMESTAMP() AND p.status='paid'";
        $params = [];
        if ($purchaseId !== null) { $sql .= ' AND p.id=?'; $params[] = $purchaseId; }
        $stmt = $pdo->prepare($sql . ' ORDER BY e.id LIMIT 30');
        $stmt->execute($params);
        foreach ($stmt->fetchAll() as $job) {
            $pdo->prepare("UPDATE payment_emails SET status='failed',attempts=attempts+1,next_attempt_at=DATE_ADD(UTC_TIMESTAMP(),INTERVAL 10 MINUTE) WHERE id=?")->execute([$job['email_id']]);
            $sent = false;
            try {
                $mail = payment_email_message($job, $config);
                $sent = submit_payment_email($mail);
            } catch (Throwable $e) {
                // Continue to the other recipient even if one submission fails.
                error_log('Payment email submission error for queue ID ' . $job['email_id']);
            }
            $pdo->prepare("UPDATE payment_emails SET status=?,sent_at=" . ($sent ? 'UTC_TIMESTAMP()' : 'NULL') . ' WHERE id=?')->execute([$sent ? 'sent' : 'failed', $job['email_id']]);
            $result[$sent ? 'sent' : 'failed']++;
            if (!$sent) error_log('Payment email rejected by mail transport for queue ID ' . $job['email_id']);
        }
    } catch (Throwable $e) {
        $result['error'] = 'Email processing could not finish. Check the hosting PHP error log and retry from Payments.';
        error_log('Payment email queue processing failed (' . get_class($e) . ').');
    } finally {
        if ($locked) {
            try { $pdo->query("SELECT RELEASE_LOCK('learningbox_payment_emails')"); }
            catch (Throwable $e) { error_log('Payment email queue lock release failed.'); }
        }
    }
    return $result;
}
