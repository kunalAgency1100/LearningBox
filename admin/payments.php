<?php
require dirname(__DIR__) . '/includes/commerce-layout.php';
require_admin();
require_once dirname(__DIR__) . '/includes/payment-emails.php';
require dirname(__DIR__) . '/config/db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf($_POST['csrf'] ?? '');
        $id = (int)($_POST['id'] ?? 0);
        if (($_POST['action'] ?? '') === 'retry_email') {
            $delivery = send_payment_emails($pdo, $id, true);
            if ($delivery['error'] !== '') $message = $delivery['error'];
            elseif ($delivery['busy']) $message = 'Email delivery is already running. Please check again shortly.';
            else $message = $delivery['sent'] . ' email(s) accepted by the mail server; ' . $delivery['failed'] . ' failed. If accepted emails do not arrive, check cPanel Track Delivery.';
        } elseif (($_POST['action'] ?? '') === 'reconcile') {
            $stmt = $pdo->prepare('SELECT * FROM purchases WHERE id=?'); $stmt->execute([$id]); $purchase = $stmt->fetch();
            if (!$purchase || !$purchase['razorpay_order_id']) throw new InvalidArgumentException('No gateway order is available for this purchase.');
            $payments = razorpay('GET', 'orders/' . rawurlencode($purchase['razorpay_order_id']) . '/payments');
            foreach ($payments['items'] ?? [] as $payment) apply_payment($pdo, $payment);
            $message = 'Purchase status refreshed from Razorpay.';
        }
    } catch (InvalidArgumentException $e) { $message = $e->getMessage(); }
    catch (Throwable $e) { error_log('Admin payments: ' . $e->getMessage()); $message = 'Unable to complete the action. Please try again.'; }
}
$states = ['creating','creation_failed','pending','authorized','failed','paid'];
$status = in_array($_GET['status'] ?? '', $states, true) ? $_GET['status'] : '';
$search = is_string($_GET['q'] ?? null) ? substr(trim($_GET['q']),0,180) : '';
$where = []; $params = [];
if ($status) { $where[] = 'p.status=?'; $params[] = $status; }
if ($search !== '') { $where[] = '(p.buyer_name LIKE ? OR p.buyer_email LIKE ? OR p.buyer_phone LIKE ? OR p.razorpay_order_id=? OR p.razorpay_payment_id=?)'; $params = array_merge($params, ['%'.$search.'%','%'.$search.'%','%'.$search.'%',$search,$search]); }
$sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$stmt = $pdo->prepare('SELECT COUNT(*) FROM purchases p' . $sqlWhere); $stmt->execute($params); $total = (int)$stmt->fetchColumn();
$page = max(1,min(max(1,(int)ceil($total/25)),(int)($_GET['page'] ?? 1))); $offset = ($page-1)*25;
$stmt = $pdo->prepare("SELECT p.*, (SELECT GROUP_CONCAT(CONCAT(recipient_type, ': ', status, ' (', attempts, ')') SEPARATOR ' / ') FROM payment_emails e WHERE e.purchase_id=p.id) AS emails FROM purchases p" . $sqlWhere . " ORDER BY p.created_at DESC LIMIT 25 OFFSET $offset"); $stmt->execute($params); $purchases=$stmt->fetchAll();
page_start('Payment records', true); ?>
<?php $mailIssues = payment_mail_issues(commerce_config()); if ($mailIssues): ?>
<div role="alert" class="bg-primary/20 border border-primary/30 p-4 rounded-lg mb-6"><p class="font-bold text-brand-navy">Email setup needs attention</p><ul class="list-disc pl-5 mt-2"><?php foreach ($mailIssues as $issue): ?><li><?= esc($issue) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<?php $setupIssues = payment_setup_issues(); if ($setupIssues): ?>
<div role="alert" class="bg-primary/20 border border-primary/30 p-4 rounded-lg mb-6">
    <p class="font-bold text-brand-navy">Payment setup needs attention</p>
    <ul class="list-disc pl-5 mt-2"><?php foreach ($setupIssues as $issue): ?><li><?= esc($issue) ?></li><?php endforeach; ?></ul>
    <p class="mt-2 text-sm">See PAYMENTS_SETUP.md for the private configuration path. The checkout popup requires a successfully created Razorpay order.</p>
</div>
<?php endif; ?>

<p class="text-gray-600 mb-6">Buyer details, payment status and email delivery for every purchase. Dates are shown in UTC. Email “sent” means accepted by the hosting mail server.</p>
<?php if ($message): ?><p role="status" class="bg-primary/20 p-4 mb-6 rounded-lg"><?= esc($message) ?></p><?php endif; ?>
<form class="grid md:grid-cols-3 gap-4 mb-8"><div><label for="q">Search buyers or gateway IDs</label><input id="q" name="q" value="<?= esc($search) ?>"></div><div><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option><?php foreach($states as $state): ?><option <?= $status===$state?'selected':'' ?>><?= esc($state) ?></option><?php endforeach; ?></select></div><button class="bg-primary text-brand-navy font-bold rounded-lg self-end px-4 py-2">Filter</button></form>
<p class="mb-4"><?= $total ?> records</p><div class="overflow-x-auto bg-white border border-gray-200 rounded-xl"><table class="w-full text-left text-sm"><thead class="bg-gray-100"><tr><th class="p-4">Purchase</th><th class="p-4">Buyer</th><th class="p-4">Payment</th><th class="p-4">Emails / actions</th></tr></thead><tbody>
<?php foreach($purchases as $purchase): ?><tr class="border-t border-gray-200 align-top"><td class="p-4 min-w-48"><strong>LB-<?= (int)$purchase['id'] ?></strong><p><?= esc($purchase['product_name']) ?></p><p><?= money($purchase['amount']) ?></p><p class="text-gray-500 mt-2"><?= esc($purchase['created_at']) ?></p></td><td class="p-4"><strong><?= esc($purchase['buyer_name']) ?></strong><p><?= esc($purchase['buyer_email']) ?></p><p><?= esc($purchase['buyer_phone']) ?></p></td><td class="p-4"><strong><?= esc($purchase['status']) ?></strong><p class="break-all"><?= esc($purchase['razorpay_order_id']) ?></p><p class="break-all"><?= esc($purchase['razorpay_payment_id']) ?></p><p class="text-gray-500"><?= esc($purchase['failure_reason']) ?></p><?php if ($purchase['paid_at']): ?><p>Paid: <?= esc($purchase['paid_at']) ?></p><?php endif; ?></td><td class="p-4 min-w-48"><p><?= esc($purchase['emails'] ?: 'No confirmation emails queued') ?></p><form method="post" class="mt-3 space-y-2"><input type="hidden" name="csrf" value="<?= esc($_SESSION['commerce_csrf']) ?>"><input type="hidden" name="id" value="<?= (int)$purchase['id'] ?>"><?php if ($purchase['razorpay_order_id']): ?><button name="action" value="reconcile" class="block underline text-brand-navy">Refresh from Razorpay</button><?php endif; ?><?php if ($purchase['status'] === 'paid' && (!$purchase['emails'] || strpos($purchase['emails'], 'pending') !== false || strpos($purchase['emails'], 'failed') !== false)): ?><button name="action" value="retry_email" class="block underline text-brand-navy">Send unsent emails</button><?php endif; ?></form></td></tr><?php endforeach; ?>
<?php if (!$purchases): ?><tr><td colspan="4" class="p-10 text-center text-gray-500">No payment records found.</td></tr><?php endif; ?></tbody></table></div>
<div class="flex gap-6 mt-6"><?php if ($page>1): ?><a class="underline" href="?<?= esc(http_build_query(['status'=>$status,'q'=>$search,'page'=>$page-1])) ?>">Previous</a><?php endif; ?><span>Page <?= $page ?></span><?php if ($page*25<$total): ?><a class="underline" href="?<?= esc(http_build_query(['status'=>$status,'q'=>$search,'page'=>$page+1])) ?>">Next</a><?php endif; ?></div><?php page_end(); ?>
