<?php
require dirname(__DIR__) . '/includes/commerce-layout.php';
require_admin();
require dirname(__DIR__) . '/config/db.php';
$error = '';
$editing = ['id'=>'','name'=>'','slug'=>'','description'=>'','amount'=>100,'active'=>1];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id=?'); $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch() ?: $editing;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf($_POST['csrf'] ?? '');
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $amount = price_to_paise(trim((string)($_POST['price'] ?? '')));
        if ($name === '' || strlen($name) > 180) throw new InvalidArgumentException('Name is required and must be at most 180 characters.');
        if (strlen($slug) > 180 || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) throw new InvalidArgumentException('Use lowercase letters, numbers and single hyphens for the slug.');
        if (strlen($description) > 10000) throw new InvalidArgumentException('Description must be at most 10,000 characters.');
        $values = [$name,$slug,$description,$amount,isset($_POST['active']) ? 1 : 0];
        if (!empty($_POST['id'])) {
            $values[] = (int)$_POST['id'];
            $pdo->prepare('UPDATE products SET name=?,slug=?,description=?,amount=?,active=? WHERE id=?')->execute($values);
        } else $pdo->prepare('INSERT INTO products (name,slug,description,amount,active) VALUES (?,?,?,?,?)')->execute($values);
        header('Location: products.php?saved=1'); exit;
    } catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
    catch (PDOException $e) { $error = $e->getCode() === '23000' ? 'That slug is already in use. Choose another.' : 'Unable to save the product.'; }
    $editing = ['id'=>$_POST['id'] ?? '', 'name'=>$_POST['name'] ?? '', 'slug'=>$_POST['slug'] ?? '', 'description'=>$_POST['description'] ?? '', 'amount'=>100, 'active'=>isset($_POST['active'])];
}
$products = $pdo->query('SELECT * FROM products ORDER BY created_at DESC')->fetchAll();
page_start('Products & services', true); ?>
<?php if ($error): ?><p role="alert" class="bg-primary/20 p-4 rounded-lg mb-6"><?= esc($error) ?></p><?php endif; ?>
<?php if (isset($_GET['saved'])): ?><p role="status" class="bg-primary/20 p-4 rounded-lg mb-6">Product saved.</p><?php endif; ?>
<div class="grid lg:grid-cols-3 gap-8"><form method="post" class="bg-white p-6 rounded-xl border border-gray-200 space-y-4 h-fit">
<h2 class="text-xl font-bold text-brand-navy"><?= $editing['id'] ? 'Edit product' : 'Add product' ?></h2>
<input type="hidden" name="csrf" value="<?= esc($_SESSION['commerce_csrf']) ?>"><input type="hidden" name="id" value="<?= esc($editing['id']) ?>">
<div><label for="name">Product / service name</label><input id="name" name="name" maxlength="180" required value="<?= esc($editing['name']) ?>"></div>
<div><label for="slug">Slug</label><input id="slug" name="slug" maxlength="180" pattern="[a-z0-9]+(-[a-z0-9]+)*" placeholder="leadership-workshop" required value="<?= esc($editing['slug']) ?>"></div>
<div><label for="price">Price (INR)</label><input id="price" name="price" type="number" min="1" max="1000000" step="0.01" required value="<?= esc($_POST['price'] ?? number_format($editing['amount']/100,2,'.','')) ?>"></div>
<div><label for="description">Description</label><textarea id="description" name="description" rows="4" maxlength="10000"><?= esc($editing['description']) ?></textarea></div>
<label class="flex gap-2 items-center"><input type="checkbox" name="active" <?= $editing['active'] ? 'checked' : '' ?>> Available for purchase</label>
<button class="bg-primary text-brand-navy font-bold px-5 py-3 rounded-lg">Save product</button><?php if ($editing['id']): ?> <a class="underline" href="products.php">Cancel</a><?php endif; ?>
</form><section class="lg:col-span-2 space-y-4">
<?php foreach ($products as $product): ?><article class="bg-white border border-gray-200 p-6 rounded-xl"><div class="flex flex-wrap justify-between gap-4"><h2 class="font-bold text-xl"><?= esc($product['name']) ?></h2><span><?= money($product['amount']) ?></span></div><p class="text-gray-500 my-3"><?= $product['active'] ? 'Active' : 'Inactive' ?> · <?= esc($product['slug']) ?></p><a class="underline mr-4" href="?edit=<?= (int)$product['id'] ?>">Edit</a><a class="underline" href="../checkout.php?slug=<?= esc(rawurlencode($product['slug'])) ?>">Purchase link</a></article><?php endforeach; ?>
<?php if (!$products): ?><p class="p-8 bg-white rounded-xl">No products yet. Add your first product or service.</p><?php endif; ?></section></div><?php page_end(); ?>
