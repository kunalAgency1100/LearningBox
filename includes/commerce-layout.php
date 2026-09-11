<?php
require_once __DIR__ . '/commerce.php';
function page_start(string $title, bool $admin = false): void { ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="referrer" content="no-referrer"><meta name="robots" content="noindex,nofollow"><title><?= esc($title) ?> | LearningBox</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script><link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
<script>tailwind.config={theme:{extend:{colors:{primary:'#F4BD18','primary-hover':'#FF731E','brand-navy':'#000976','accent-blue':'#26AADC'},fontFamily:{display:['Montserrat','sans-serif']}}}}</script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
<style>input:not([type=checkbox]),textarea,select{width:100%;border-radius:.5rem}label{display:block;margin-bottom:.4rem;font-weight:600}button:disabled{opacity:.5}</style></head><body class="bg-gray-50 text-gray-800 min-h-screen">
<?php if ($admin): ?>
<?php require __DIR__ . '/admin-navbar.php'; ?>
<?php else: ?>
<nav class="bg-brand-navy text-white"><div class="max-w-7xl mx-auto p-5 flex flex-wrap items-center gap-5"><a class="font-display font-bold text-xl" href="index.html">LearningBox</a><a href="products.php">Products & services</a><a href="contact.html">Contact us</a></div></nav>
<?php endif; ?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 <?= $admin ? 'py-10' : 'py-12' ?>">
<?php if ($admin) require __DIR__ . '/admin-tabs.php'; ?>
<h1 class="font-display text-3xl font-bold <?= $admin ? 'text-gray-900' : 'text-brand-navy' ?> mb-8"><?= esc($title) ?></h1>
<?php }
function page_end(): void { echo '</main><footer class="text-center text-gray-500 p-6">LearningBox · Educate, Empower and Transform.</footer></body></html>'; }
