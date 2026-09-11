const form = document.getElementById('purchase-form');
const button = document.getElementById('pay-button');
const message = document.getElementById('checkout-message');
const statusLink = document.getElementById('status-link');
async function paymentApi(action, body) {
    const response = await fetch(`api/payments.php?action=${action}`, {
        method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-Token': form.dataset.csrf},
        body: JSON.stringify(body)
    });
    const data = await response.json();
    if (!response.ok || !data.success) throw new Error(data.message || 'Payment request could not be completed.');
    return data;
}
form.addEventListener('submit', async event => {
    event.preventDefault();
    button.disabled = true;
    message.textContent = 'Preparing your secure checkout…';
    let confirming = false;
    try {
        if (typeof Razorpay === 'undefined') throw new Error('Secure checkout could not load. Please refresh and try again.');
        const buyer = Object.fromEntries(new FormData(form));
        const order = await paymentApi('create', {...buyer, slug: form.dataset.slug});
        const statusUrl = `payment-success.php?token=${encodeURIComponent(order.token)}`;
        statusLink.href = statusUrl;
        statusLink.classList.remove('hidden');
        const checkout = new Razorpay({
            key: order.key_id, order_id: order.order_id, amount: order.amount, currency: order.currency,
            name: 'LearningBox', description: order.product_name,
            prefill: {name: buyer.name, email: buyer.email, contact: buyer.phone}, theme: {color: '#000976'},
            handler: async result => {
                confirming = true;
                button.disabled = true;
                message.textContent = 'Confirming your payment…';
                try {
                    await paymentApi('verify', {...result, token: order.token});
                } catch (error) {
                    message.textContent = 'Confirmation is taking longer than expected. Opening your purchase status…';
                }
                window.location.assign(statusUrl);
            },
            modal: {ondismiss: () => {
                if (!confirming) {
                    button.disabled = false;
                    message.textContent = 'Checkout closed. If money was debited, check your purchase status before trying again.';
                }
            }}
        });
        checkout.on('payment.failed', () => {
            message.textContent = 'Payment attempt failed. You can retry in checkout. If money was debited, check your purchase status first.';
        });
        checkout.open();
        message.textContent = 'Complete your payment in the secure checkout window.';
    } catch (error) {
        message.textContent = error.message || 'Unable to open checkout. Please try again.';
        button.disabled = false;
    }
});
