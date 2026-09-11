<?php
// admin/setup_tool.php
// A simple utility to generate a password hash for the initial admin setup.
// MAKE SURE TO DELETE THIS FILE AFTER DEPLOYMENT.

$generated_hash = '';
$raw_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
    $raw_password = $_POST['password'];
    $generated_hash = password_hash($raw_password, PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-xl shadow-lg max-w-lg w-full">
        <h1 class="text-2xl font-bold mb-4 text-red-600">Password Hash Generator</h1>
        <p class="text-gray-600 text-sm mb-6">Type a password below to generate a secure standard hash. You can paste this hash directly into your `admin_users` table via cPanel/phpMyAdmin.</p>

        <form method="POST" class="mb-6">
            <input type="text" name="password" placeholder="Enter desired password" required class="w-full border p-2 rounded mb-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Generate Hash</button>
        </form>

        <?php if ($generated_hash): ?>
            <div class="bg-gray-50 border p-4 rounded mt-4">
                <p class="text-sm font-bold mb-2">Password: <span class="font-normal"><?= htmlspecialchars($raw_password) ?></span></p>
                <p class="text-sm font-bold mb-1">Hash:</p>
                <code class="block bg-gray-200 p-2 rounded break-all text-xs text-green-700"><?= htmlspecialchars($generated_hash) ?></code>
            </div>
            
            <p class="mt-4 text-xs text-red-500 font-bold">⚠️ IMPORTANT: Please DELETE this `setup_tool.php` file from your server after you have generated your hash!</p>
        <?php endif; ?>
    </div>
</body>
</html>
