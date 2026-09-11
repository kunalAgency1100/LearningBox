    <nav class="bg-brand-navy text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="font-display font-bold text-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">admin_panel_settings</span>
                    LearningBox Admin
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-300">Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <a href="logout.php" class="bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">logout</span>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
