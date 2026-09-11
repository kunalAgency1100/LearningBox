<?php
require __DIR__ . '/includes/commerce-layout.php';
commerce_session();
header('Cache-Control: no-store');
require __DIR__ . '/config/db.php';
try {
  $stmt = $pdo->prepare('SELECT * FROM products WHERE slug=? AND active=1');
  $stmt->execute([is_string($_GET['slug'] ?? null) ? $_GET['slug'] : '']);
  $product = $stmt->fetch();
} catch (Throwable $e) {
  error_log('Checkout product lookup failed: ' . $e->getMessage());
  http_response_code(503);
  page_start('Checkout temporarily unavailable');
  echo '<p class="mb-4">We could not load this product right now. Please try again shortly.</p>';
  echo '<a href="products.php" class="underline">Return to products</a>';
  page_end();
  exit;
}
if (!$product) {
    http_response_code(404);
    page_start('Product unavailable');
    echo '<a href="products.php" class="underline">Browse available products</a>';
    page_end();
    exit;
}

// Presentation only: product names, descriptions and amounts remain database-driven.
$checkoutThemes = [
    'relationship-health-diagnostic' => ['tone' => 'diagnostic', 'icon' => 'health_and_safety'],
    'digital-playbook-for-managers' => ['tone' => 'playbook', 'icon' => 'menu_book'],
    'gen-z-work-toolkit' => ['tone' => 'toolkit', 'icon' => 'construction'],
];
$checkoutTheme = $checkoutThemes[$product['slug']] ?? ['tone' => 'diagnostic', 'icon' => 'shopping_bag'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($product['name']) ?> | Checkout | LearningBox</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
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
  <link href="templates/layout.css" rel="stylesheet">
  <link href="assets/checkout.css" rel="stylesheet">
  <script src="templates/layout.js" defer></script>
</head>
<body class="min-h-screen bg-background-light font-sans text-text-light antialiased">
  <div id="site-header"><?php if (is_file(__DIR__ . '/templates/header.html')) require __DIR__ . '/templates/header.html'; ?></div>

  <main id="checkout" class="checkout-page" data-product-theme="<?= esc($checkoutTheme['tone']) ?>">
    <section id="hero" class="checkout-hero relative overflow-hidden bg-brand-navy px-4 pb-24 pt-28 text-white sm:px-6 sm:pb-28 sm:pt-32 lg:px-8" aria-labelledby="checkout-heading">
      <div class="relative z-10 mx-auto max-w-6xl">
        <a href="flagship.html" class="checkout-back mb-5 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-gray-300 transition-colors hover:text-primary">
          <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
          Back to products
        </a>
        <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
          <div class="max-w-3xl">
            <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-200">
              <span class="material-symbols-outlined text-base text-primary" aria-hidden="true">shopping_bag</span>
              LearningBox checkout
            </p>
            <h1 id="checkout-heading" class="font-display text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl lg:text-5xl">Complete your <span class="checkout-heading-accent">purchase.</span></h1>
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-gray-300">Practical tools for better conversations at work. You're one step away.</p>
          </div>
          <div class="flex shrink-0 items-center gap-3 text-sm text-gray-300">
            <span class="flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-white/5 text-primary">
              <span class="material-symbols-outlined" aria-hidden="true">lock</span>
            </span>
            <div>
              <p class="font-semibold text-white">Secure checkout</p>
              <p class="mt-0.5 text-xs text-gray-300">Payments handled by Razorpay</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="relative z-10 mx-auto -mt-12 max-w-6xl px-4 pb-12 sm:-mt-14 sm:px-6 sm:pb-16 lg:px-8">
      <div class="checkout-grid grid items-start gap-6 lg:gap-8">
        <section class="checkout-summary min-w-0 overflow-hidden rounded-3xl border border-gray-200 bg-white" aria-labelledby="order-heading">
          <div class="checkout-product-stripe h-1.5" aria-hidden="true"></div>
          <div class="p-6 sm:p-8">
            <div class="mb-7 flex flex-wrap items-center justify-between gap-3">
              <h2 id="order-heading" class="text-xs font-bold uppercase tracking-widest text-gray-500">Order summary</h2>
              <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">1 item</span>
            </div>
            <div class="checkout-product-icon mb-5 flex h-14 w-14 items-center justify-center rounded-2xl">
              <span class="material-symbols-outlined text-3xl" aria-hidden="true"><?= esc($checkoutTheme['icon']) ?></span>
            </div>
            <h3 class="checkout-product-name font-display text-2xl font-bold leading-snug text-brand-navy"><?= esc($product['name']) ?></h3>
            <p class="checkout-description mt-4 whitespace-pre-line text-sm leading-7 text-gray-600"><?= esc($product['description']) ?></p>
            <div class="mt-7 rounded-2xl border border-gray-100 bg-gray-50 p-5">
              <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-2">
                <span class="text-sm font-semibold text-gray-600">Total payable</span>
                <span class="checkout-price font-display text-2xl font-extrabold text-brand-navy sm:text-3xl"><?= money($product['amount']) ?></span>
              </div>
              <p class="mt-2 text-xs text-gray-500">One-time payment</p>
            </div>
          </div>
          <div class="flex items-start gap-3 border-t border-gray-100 px-6 py-5 sm:px-8">
            <span class="material-symbols-outlined mt-0.5 shrink-0 text-accent-blue" aria-hidden="true">help_outline</span>
            <div class="min-w-0 text-sm leading-6">
              <p class="font-semibold text-brand-navy">Questions before you purchase?</p>
              <a href="mailto:Info@learningbox.in" class="checkout-help text-gray-500 underline decoration-gray-300 underline-offset-4 hover:text-brand-navy">Info@learningbox.in</a>
            </div>
          </div>
        </section>

        <form id="purchase-form" class="checkout-form min-w-0 rounded-3xl border border-gray-200 bg-white p-6 sm:p-8" data-slug="<?= esc($product['slug']) ?>" data-csrf="<?= esc($_SESSION['commerce_csrf']) ?>" aria-labelledby="details-heading">
          <div class="mb-7 border-b border-gray-100 pb-6">
            <div class="mb-3 flex items-center gap-3">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-navy/5 text-brand-navy">
                <span class="material-symbols-outlined" aria-hidden="true">person</span>
              </span>
              <h2 id="details-heading" class="font-display text-xl font-bold text-brand-navy sm:text-2xl">Your details</h2>
            </div>
            <p class="text-sm leading-6 text-gray-500">Enter your contact details to continue. All fields are required.</p>
          </div>

          <div class="space-y-5">
            <div>
              <label for="name" class="mb-2 block text-sm font-semibold text-gray-800">Full name</label>
              <input id="name" name="name" type="text" autocomplete="name" placeholder="Enter your full name" maxlength="120" required class="checkout-input w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-base text-gray-900 placeholder:text-gray-400">
            </div>
            <div>
              <label for="email" class="mb-2 block text-sm font-semibold text-gray-800">Email address</label>
              <input id="email" name="email" type="email" inputmode="email" autocomplete="email" placeholder="you@example.com" maxlength="254" required aria-describedby="email-help" class="checkout-input w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-base text-gray-900 placeholder:text-gray-400">
              <p id="email-help" class="mt-2 text-xs leading-5 text-gray-500">Your payment confirmation will be sent to this address.</p>
            </div>
            <div>
              <label for="phone" class="mb-2 block text-sm font-semibold text-gray-800">Contact number</label>
              <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="+91 98765 43210" maxlength="25" required class="checkout-input w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-base text-gray-900 placeholder:text-gray-400">
            </div>
          </div>

          <div class="mt-6 flex items-start gap-2.5 rounded-xl bg-gray-50 p-4">
            <span class="material-symbols-outlined mt-0.5 shrink-0 text-lg text-brand-navy" aria-hidden="true">privacy_tip</span>
            <p class="text-xs leading-5 text-gray-600">We use these details to process your purchase and contact you about your product or service. Payment details are handled by Razorpay.</p>
          </div>

          <button id="pay-button" type="submit" class="checkout-pay mt-6 w-full rounded-xl bg-gradient-to-r from-primary to-accent-orange px-5 py-4 text-base font-bold text-brand-navy transition duration-200 hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-60">Pay <?= money($product['amount']) ?></button>
          <p class="mt-3 text-center text-xs leading-5 text-gray-500">Choose your preferred payment option in the Razorpay window.</p>
          <p id="checkout-message" role="status" aria-live="polite" class="text-sm text-gray-700"></p>
          <a id="status-link" class="hidden underline text-brand-navy">Check purchase status</a>
        </form>
      </div>

      <p class="mt-8 text-center text-xs font-semibold uppercase tracking-widest text-gray-400">LearningBox &middot; Educate, Empower and Transform</p>
    </div>
  </main>

  <div id="site-footer"><?php if (is_file(__DIR__ . '/templates/footer.html')) require __DIR__ . '/templates/footer.html'; ?></div>
  <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
  <script src="assets/checkout.js?v=<?= substr(hash_file('sha256', __DIR__ . '/assets/checkout.js'), 0, 16) ?>" defer></script>
</body>
</html>
