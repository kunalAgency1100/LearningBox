<?php
require __DIR__ . '/includes/commerce-layout.php';
require __DIR__ . '/config/db.php';
$products = $pdo->query('SELECT * FROM products WHERE active=1 ORDER BY created_at DESC')->fetchAll();
//page_start('Products & services'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Contact Us - LearningBox</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
    <script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              primary: "#F4BD18",        // Brand Yellow
              "primary-hover": "#FF731E", // Brand Orange
              "background-light": "#F3F4F6",
              "background-dark": "#000976", // Brand Navy
              "text-light": "#1F2937",
              "text-dark": "#F9FAFB",
              "accent-blue": "#26AADC",  // Brand Blue
              "accent-orange": "#FF731E", // Brand Orange
              "brand-navy": "#000976",   // Brand Navy
            },
            fontFamily: {
              display: ["Montserrat", "sans-serif"],
              sans: ["system-ui", "sans-serif"],
            },
            borderRadius: {
              DEFAULT: "0.5rem",
            },
          },
        },
      };
    </script>
    <style>
        .hero-overlay {
            background: linear-gradient(to bottom, rgba(17, 24, 39, 0.4) 0%, rgba(17, 24, 39, 0.7) 60%, rgba(17, 24, 39, 0.9) 100%);
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-sans text-text-light dark:text-text-dark antialiased flex flex-col min-h-screen">

<!-- Navbar -->
<nav id="navbar" class="fixed top-0 left-0 w-full z-50 transition-all duration-300 border-b border-white/10 bg-brand-navy shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <div class="flex-shrink-0 flex items-center gap-3 cursor-pointer">
                <div class="bg-white rounded-full p-2 h-10 w-10 flex items-center justify-center shadow-lg">
                    <a href="."><img src="logo.png" height="100" width="100" alt="Logo"></a>
                </div>
                <span class="font-display font-bold text-2xl text-white tracking-wide">
                    <a href="."><span class="text-transparent bg-clip-text" style="background-image:linear-gradient(90deg,#fff,#F4BD18);">LearningBox</span></a>
                </span>
            </div>
            <div class="hidden md:flex items-center space-x-8">
                <a class="text-white hover:text-primary transition-colors duration-200 text-sm font-semibold uppercase tracking-wider" href="about.html">About</a>
                <a class="text-white hover:text-primary transition-colors duration-200 text-sm font-semibold uppercase tracking-wider" href="flagship.html">Flagship</a>
                <a class="text-white hover:text-primary transition-colors duration-200 text-sm font-semibold uppercase tracking-wider" href="manager.html">Managers</a>
                <a class="text-white hover:text-primary transition-colors duration-200 text-sm font-semibold uppercase tracking-wider" href="gen-z.html">Gen Z</a>
            </div>
            <div class="hidden md:flex items-center">
                <a href="/register.html" class="bg-primary hover:bg-yellow-400 text-gray-900 font-bold py-2.5 px-6 rounded-full shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2" href="#">
                    <span>REGISTER TODAY</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-btn" class="text-gray-200 hover:text-white focus:outline-none" type="button">
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </button>
            </div>
        </div>
    </div>
</nav>

<main class="" style="margin-top:200px; min-height:80vh;">
<p class="text-gray-600 mb-8">Choose your next learning experience.</p>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($products as $product): ?>
        <article class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm flex flex-col">
            <h2 class="font-display text-xl font-bold text-brand-navy"><?= esc($product['name']) ?></h2>
            <p class="mt-4 text-gray-600 whitespace-pre-line flex-1"><?= esc($product['description']) ?></p>
            <p class="text-2xl font-bold mt-6"><?= money($product['amount']) ?></p><a class="mt-6 text-center bg-primary hover:bg-primary-hover text-brand-navy font-bold rounded-lg px-6 py-3" href="checkout.php?slug=<?= esc(rawurlencode($product['slug'])) ?>">Buy now</a>
        </article>
    <?php endforeach; ?>
</div>

<?php if (!$products): ?><p class="bg-white p-8 rounded-xl">New products and services are coming soon. <a class="underline" href="contact.html">Contact us</a> for more information.</p><?php endif; ?>

</main>

<!-- Mobile Navigation Sidebar -->
<div id="mobile-nav" class="fixed inset-y-0 right-0 w-64 bg-brand-navy shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-[60] flex flex-col">
  <div class="p-6 flex justify-between items-center border-b border-white/10">
    <span class="font-display font-bold text-xl text-white tracking-wide">Menu</span>
    <button id="close-mobile-nav" class="text-gray-300 hover:text-white focus:outline-none transition-colors">
      <span class="material-symbols-outlined text-3xl">close</span>
    </button>
  </div>
  <div class="flex flex-col px-6 py-8 space-y-6 flex-grow">
    <a class="text-gray-300 hover:text-primary transition-colors duration-200 text-base font-semibold uppercase tracking-wider" href="about.html">About</a>
    <a class="text-gray-300 hover:text-primary transition-colors duration-200 text-base font-semibold uppercase tracking-wider" href="flagship.html">Flagship</a>
    <a class="text-gray-300 hover:text-primary transition-colors duration-200 text-base font-semibold uppercase tracking-wider" href="manager.html">Managers</a>
    <a class="text-gray-300 hover:text-primary transition-colors duration-200 text-base font-semibold uppercase tracking-wider" href="gen-z.html">Gen Z</a>
  </div>
  <div class="p-6 border-t border-white/10">
    <a href="/contact.html" class="w-full bg-primary hover:bg-yellow-400 text-gray-900 font-bold py-3 px-6 rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
      <span>REGISTER TODAY</span>
    </a>
  </div>
</div>
<!-- Overlay -->
<div id="mobile-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300 pointer-events-none"></div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const mobileNav = document.getElementById('mobile-nav');
    const overlay = document.getElementById('mobile-overlay');
    const openBtn = document.getElementById('mobile-menu-btn');
    const closeBtn = document.getElementById('close-mobile-nav');

    function openMobileNav() {
      mobileNav.classList.remove('translate-x-full');
      overlay.classList.remove('hidden');
      overlay.classList.remove('pointer-events-none');
      setTimeout(() => overlay.classList.remove('opacity-0'), 10);
      document.body.style.overflow = 'hidden';
    }

    function closeMobileNav() {
      mobileNav.classList.add('translate-x-full');
      overlay.classList.add('opacity-0');
      overlay.classList.add('pointer-events-none');
      setTimeout(() => overlay.classList.add('hidden'), 300);
      document.body.style.overflow = '';
    }

    if (openBtn) openBtn.addEventListener('click', openMobileNav);
    if (closeBtn) closeBtn.addEventListener('click', closeMobileNav);
    if (overlay) overlay.addEventListener('click', closeMobileNav);
  });
</script>

<!-- Footer -->
<footer id="contact" class="py-16 px-4 relative overflow-hidden" style="background:#000976;">
  <div class="max-w-4xl mx-auto relative z-10 text-center">
    <!-- Social Links (LinkedIn & Instagram) -->
    <div class="flex justify-center gap-8 mb-12">
      <!-- LinkedIn Icon -->
      <a href="https://www.linkedin.com/company/learningbox-lb/" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-[#F4BD18] transition-colors" aria-label="LinkedIn">
        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd" />
        </svg>
      </a>
      <!-- Instagram Icon -->
      <a href="https://www.instagram.com/learningbox.co?igsh=MXBhcW50aDdpNTBqZw==" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-[#FF731E] transition-colors" aria-label="Instagram">
        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path fill-rule="evenodd" d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" clip-rule="evenodd" />
        </svg>
      </a>
    </div>

    <!-- Copyright -->
    <div class="pt-8 mt-8 border-t border-white/10">
      <p class="text-sm text-gray-500">
        &copy; 2026 Learning Box Consulting. All rights reserved.
      </p>
    </div>
  </div>
</footer>


</body>
</html>
<? //php page_end(); ?>