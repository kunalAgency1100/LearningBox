<?php
$adminPage = basename($_SERVER['SCRIPT_NAME']);
$adminTabs = ['dashboard.php' => 'Contact Submissions', 'registrations.php' => 'Registrations', 'consultations.php' => 'Consultations', 'products.php' => 'Products', 'payments.php' => 'Payments'];
?>
<div class="mb-8 border-b border-gray-200">
    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
        <?php foreach ($adminTabs as $href => $label): ?>
        <a href="<?= $href ?>" <?= $adminPage === $href ? 'aria-current="page"' : '' ?> class="<?= $adminPage === $href ? 'border-primary text-brand-navy' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
</div>
