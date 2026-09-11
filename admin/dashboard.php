<?php
// admin/dashboard.php
session_start();

// Enforce authentication
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header('Location: login.php');
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

// Fetch all submissions
try {
    $stmt = $pdo->query("SELECT * FROM contact_submissions ORDER BY created_at DESC");
    $submissions = $stmt->fetchAll();
} catch (PDOException $e) {
    $dbError = "Failed to retrieve submissions.";
    $submissions = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LearningBox</title>
    <!-- Prevent indexing -->
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: "#F4BD18",
              "brand-navy": "#000976",
            },
            fontFamily: {
              display: ["Montserrat", "sans-serif"],
            }
          }
        }
      };
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Navbar -->
    <?php require dirname(__DIR__) . '/includes/admin-navbar.php'; ?>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Navigation Tabs -->
        <?php require dirname(__DIR__) . '/includes/admin-tabs.php'; ?>
        
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-display font-bold text-gray-900">Contact Submissions</h1>
                <p class="text-gray-500 mt-2">View and manage inquiries received from the contact page.</p>
            </div>
            <div class="bg-primary/20 text-brand-navy px-4 py-2 rounded-lg font-bold border border-primary/30">
                Total: <?= count($submissions) ?>
            </div>
        </div>

        <?php if (isset($dbError)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 shadow-sm border border-red-100">
                <?= htmlspecialchars($dbError) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider text-xs border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Date & Time</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Contact Info</th>
                            <th class="px-6 py-4 w-full">Message</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($submissions) > 0): ?>
                            <?php foreach ($submissions as $sub): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-gray-500">#<?= htmlspecialchars($sub['id']) ?></td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <?= date('M j, Y g:i A', strtotime($sub['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900 border-r border-gray-50">
                                        <?= htmlspecialchars($sub['name']) ?>
                                    </td>
                                    <td class="px-6 py-4 border-r border-gray-50">
                                        <div class="flex flex-col gap-1">
                                            <a href="mailto:<?= htmlspecialchars($sub['email']) ?>" class="text-blue-600 hover:underline flex items-center gap-1">
                                                <span class="material-symbols-outlined" style="font-size:12px;">mail</span>
                                                <?= htmlspecialchars($sub['email']) ?>
                                            </a>
                                            <?php if (!empty($sub['phone'])): ?>
                                                <div class="text-gray-500 flex items-center gap-1">
                                                    <span class="material-symbols-outlined" style="font-size:12px;">call</span>
                                                    <?= htmlspecialchars($sub['phone']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400 italic text-xs">No phone provided</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-normal min-w-[300px]">
                                        <p class="text-gray-700 text-sm leading-relaxed">
                                            <?= nl2br(htmlspecialchars($sub['message'])) ?>
                                        </p>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">inbox</span>
                                    <p>No submissions found yet.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
