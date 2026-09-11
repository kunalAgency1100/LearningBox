<?php
// admin/registrations.php
session_start();

// Enforce authentication
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header('Location: login.php');
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

// Fetch all registrations
try {
    $stmt = $pdo->query("SELECT * FROM registrations ORDER BY created_at DESC");
    $registrations = $stmt->fetchAll();
} catch (PDOException $e) {
    $dbError = "Failed to retrieve registrations.";
    $registrations = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrations - Admin Dashboard</title>
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
    <nav class="bg-brand-navy text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="font-display font-bold text-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">admin_panel_settings</span>
                    LearningBox Admin
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-300">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                    <a href="logout.php" class="bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">logout</span>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Navigation Tabs -->
        <div class="mb-8 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="dashboard.php" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Contact Submissions
                </a>
                <a href="registrations.php" class="border-primary text-brand-navy whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Registrations
                </a>
                <a href="consultations.php" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Consultations
                </a>
            </nav>
        </div>
        
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-display font-bold text-gray-900">Registrations</h1>
                <p class="text-gray-500 mt-2">Manage course registrations and their statuses.</p>
            </div>
            <div class="bg-primary/20 text-brand-navy px-4 py-2 rounded-lg font-bold border border-primary/30">
                Total: <?= count($registrations) ?>
            </div>
        </div>

        <?php if (isset($dbError)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 shadow-sm border border-red-100">
                <?= htmlspecialchars($dbError) ?>
            </div>
        <?php endif; ?>

        <div id="toast" class="fixed top-5 right-5 bg-green-500 text-white px-4 py-2 rounded shadow-lg transform transition-transform duration-300 translate-x-full z-50">
            Update successful!
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider text-xs border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Details</th>
                            <th class="px-6 py-4 w-64">Status & Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($registrations) > 0): ?>
                            <?php foreach ($registrations as $reg): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-gray-500 align-top">#<?= htmlspecialchars($reg['id']) ?></td>
                                    <td class="px-6 py-4 text-gray-600 align-top">
                                        <?= date('M j, Y', strtotime($reg['created_at'])) ?><br>
                                        <span class="text-xs text-gray-400"><?= date('g:i A', strtotime($reg['created_at'])) ?></span>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="font-medium text-gray-900 mb-1"><?= htmlspecialchars($reg['full_name']) ?></div>
                                        <div class="text-blue-600 flex items-center gap-1 mb-1">
                                            <span class="material-symbols-outlined" style="font-size:12px;">mail</span>
                                            <a href="mailto:<?= htmlspecialchars($reg['email']) ?>"><?= htmlspecialchars($reg['email']) ?></a>
                                        </div>
                                        <div class="text-gray-500 flex items-center gap-1 mb-2">
                                            <span class="material-symbols-outlined" style="font-size:12px;">call</span>
                                            <?= htmlspecialchars($reg['phone']) ?>
                                        </div>
                                        <div class="inline-block bg-gray-100 rounded px-2 py-1 text-xs font-semibold text-gray-700">
                                            <?= htmlspecialchars($reg['role']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex flex-col gap-2">
                                            <select class="status-select bg-white border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-brand-navy" data-id="<?= $reg['id'] ?>">
                                                <option value="Pending" <?= $reg['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="In Progress" <?= $reg['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="Completed" <?= $reg['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                            </select>
                                            <textarea class="notes-input bg-gray-50 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-brand-navy resize-none" rows="2" placeholder="Admin notes..." data-id="<?= $reg['id'] ?>"><?= htmlspecialchars($reg['admin_notes'] ?? '') ?></textarea>
                                            <button class="save-btn bg-brand-navy hover:bg-blue-800 text-white text-xs font-bold py-1.5 px-3 rounded transition-colors" data-id="<?= $reg['id'] ?>">Save Update</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">inbox</span>
                                    <p>No registrations found yet.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        const table = 'registrations';

        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `fixed top-5 right-5 px-4 py-2 rounded shadow-lg transform transition-transform duration-300 z-50 text-white ${isError ? 'bg-red-500' : 'bg-green-500'}`;
            toast.classList.remove('translate-x-full');
            setTimeout(() => {
                toast.classList.add('translate-x-full');
            }, 3000);
        }

        document.querySelectorAll('.save-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.getAttribute('data-id');
                const row = e.target.closest('tr');
                const status = row.querySelector('.status-select').value;
                const admin_notes = row.querySelector('.notes-input').value;

                e.target.disabled = true;
                e.target.textContent = 'Saving...';

                try {
                    const response = await fetch('update_record.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            table, id, status, admin_notes
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        showToast('Record updated successfully');
                    } else {
                        showToast(result.message || 'Error updating record', true);
                    }
                } catch (err) {
                    showToast('Network error', true);
                }

                e.target.disabled = false;
                e.target.textContent = 'Save Update';
            });
        });
    </script>
</body>
</html>
