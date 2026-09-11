<?php
// admin/consultations.php
session_start();

// Enforce authentication
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header('Location: login.php');
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

// Fetch all consultations
try {
    $stmt = $pdo->query("SELECT * FROM consultations ORDER BY created_at DESC");
    $consultations = $stmt->fetchAll();
} catch (PDOException $e) {
    $dbError = "Failed to retrieve consultations.";
    $consultations = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations - Admin Dashboard</title>
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
                <h1 class="text-3xl font-display font-bold text-gray-900">Consultations</h1>
                <p class="text-gray-500 mt-2">Manage consultation requests and client details.</p>
            </div>
            <div class="bg-primary/20 text-brand-navy px-4 py-2 rounded-lg font-bold border border-primary/30">
                Total: <?= count($consultations) ?>
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
                            <th class="px-6 py-4">Contact & Company</th>
                            <th class="px-6 py-4 min-w-[300px]">Requirements</th>
                            <th class="px-6 py-4 w-64">Status & Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($consultations) > 0): ?>
                            <?php foreach ($consultations as $req): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-gray-500 align-top">#<?= htmlspecialchars($req['id']) ?></td>
                                    <td class="px-6 py-4 text-gray-600 align-top">
                                        <?= date('M j, Y', strtotime($req['created_at'])) ?><br>
                                        <span class="text-xs text-gray-400"><?= date('g:i A', strtotime($req['created_at'])) ?></span>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="font-bold text-gray-900"><?= htmlspecialchars($req['full_name']) ?></div>
                                        <div class="text-xs text-gray-500 mb-2"><?= htmlspecialchars($req['job_title']) ?></div>
                                        
                                        <div class="text-blue-600 flex items-center gap-1 mb-1">
                                            <span class="material-symbols-outlined" style="font-size:12px;">mail</span>
                                            <a href="mailto:<?= htmlspecialchars($req['work_email']) ?>"><?= htmlspecialchars($req['work_email']) ?></a>
                                        </div>
                                        <div class="text-gray-500 flex items-center gap-1 mb-3">
                                            <span class="material-symbols-outlined" style="font-size:12px;">call</span>
                                            <?= htmlspecialchars($req['phone']) ?>
                                        </div>
                                        
                                        <div class="bg-gray-50 border border-gray-100 rounded px-3 py-2">
                                            <div class="font-semibold text-gray-800 flex items-center gap-1">
                                                <span class="material-symbols-outlined" style="font-size:14px;">business</span>
                                                <?= htmlspecialchars($req['company_name']) ?>
                                            </div>
                                            <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                <span class="material-symbols-outlined" style="font-size:12px;">location_on</span>
                                                <?= htmlspecialchars($req['location']) ?: 'Not specified' ?>
                                            </div>
                                            <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                <span class="material-symbols-outlined" style="font-size:12px;">groups</span>
                                                Team Size: <?= htmlspecialchars($req['team_size']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-normal align-top">
                                        <div class="mb-3">
                                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1">Entity Type</span>
                                            <span class="bg-blue-50 text-blue-700 border border-blue-100 rounded px-2 py-1 text-xs font-semibold"><?= htmlspecialchars($req['description']) ?></span>
                                        </div>
                                        <div class="mb-3">
                                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1">Looking For</span>
                                            <span class="bg-green-50 text-green-700 border border-green-100 rounded px-2 py-1 text-xs font-semibold"><?= htmlspecialchars($req['looking_for']) ?></span>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1">Current Challenge</span>
                                            <p class="text-sm text-gray-700 bg-yellow-50/50 p-2 border-l-2 border-primary"><?= nl2br(htmlspecialchars($req['challenge'])) ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex flex-col gap-2">
                                            <select class="status-select bg-white border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-brand-navy" data-id="<?= $req['id'] ?>">
                                                <option value="Pending" <?= $req['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="In Progress" <?= $req['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="Completed" <?= $req['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                            </select>
                                            <textarea class="notes-input bg-gray-50 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-brand-navy resize-none" rows="3" placeholder="Admin notes..." data-id="<?= $req['id'] ?>"><?= htmlspecialchars($req['admin_notes'] ?? '') ?></textarea>
                                            <button class="save-btn bg-brand-navy hover:bg-blue-800 text-white text-xs font-bold py-1.5 px-3 rounded transition-colors" data-id="<?= $req['id'] ?>">Save Update</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">inbox</span>
                                    <p>No consultation requests found yet.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        const table = 'consultations';

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
