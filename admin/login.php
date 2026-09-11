<?php
// admin/login.php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once dirname(__DIR__) . '/config/db.php';
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Success
                session_regenerate_id(true);
                $_SESSION['admin_auth'] = true;
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_id'] = $user['id'];
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Internal database error. Please contact a server administrator.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - LearningBox</title>
    <!-- Prevent indexing -->
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: "#F4BD18",        // Brand Yellow
              "brand-navy": "#000976",   // Brand Navy
            },
            fontFamily: {
              display: ["Montserrat", "sans-serif"],
            }
          }
        }
      };
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-10 rounded-2xl shadow-xl w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <h1 class="font-display text-2xl font-bold text-brand-navy">LearningBox Admin</h1>
            <p class="text-sm text-gray-500 mt-2">Sign in to view contact responses</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 border border-red-200 rounded-lg p-3 text-sm mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" id="username" name="username" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
            </div>
            
            <button type="submit" 
                    class="w-full bg-primary hover:bg-yellow-400 text-brand-navy font-bold py-3 px-4 rounded-lg shadow-md transition-all">
                Login
            </button>
        </form>
    </div>

</body>
</html>
