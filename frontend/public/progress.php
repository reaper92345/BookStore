<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Payment in Progress — Online Bookstore</title>
        <link rel="stylesheet" href="/styles.css">
        <style>
            .progress-container { text-align: center; padding: 60px 20px; }
            .spinner { width: 48px; height: 48px; border: 4px solid rgba(255,255,255,0.1); border-left-color: var(--accent); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 24px; }
            @keyframes spin { 100% { transform: rotate(360deg); } }
            .status-text { font-size: 1.2rem; color: var(--white); margin-bottom: 12px; }
            .sub-text { color: var(--muted); }
        </style>
    </head>
    <body>
        <div class="site">
            <header class="site-header">
                <div class="container header-inner">
                    <div class="brand">
                        <a href="/" class="logo">Online<span class="accent">Bookstore</span></a>
                    </div>
                </div>
            </header>

            <main>
                <section class="container">
                    <div class="progress-container">
                        <div class="spinner"></div>
                        <h2 class="status-text">Payment in Progress</h2>
                        <p class="sub-text">Please wait while we redirect you to <span id="paymentProvider">...</span></p>
                    </div>
                </section>
            </main>
        </div>
        <script>
            const params = new URLSearchParams(window.location.search);
            const provider = params.get('provider') || 'Payment Gateway';
            document.getElementById('paymentProvider').textContent = provider;
            
            async function processCheckout() {
                const statusText = document.querySelector('.status-text');
                const subText = document.querySelector('.sub-text');
                
                try {
                    const cartId = localStorage.getItem('cart_id');
                    const userStr = localStorage.getItem('user');
                    
                    if (!userStr) {
                        alert('Please login to complete your purchase.');
                        window.location.href = '/login.php';
                        return;
                    }
                    
                    const user = JSON.parse(userStr);
                    if (!user.id) {
                        // If id is missing, the user needs to re-login (legacy session)
                        alert('Your session is incomplete. Please login again.');
                        window.location.href = '/login.php';
                        return;
                    }

                    if (!cartId) {
                        alert('Your cart is empty.');
                        window.location.href = '/';
                        return;
                    }

                    // 1. Fetch cart items
                    const cartResp = await fetch(`/api/cart/?cart_id=${cartId}`);
                    if (!cartResp.ok) throw new Error('Failed to fetch cart items');
                    const cartItems = await cartResp.json();
                    
                    if (!cartItems || cartItems.length === 0) {
                        alert('Your cart is empty.');
                        window.location.href = '/';
                        return;
                    }

                    // 2. Prepare order payload
                    const orderPayload = {
                        user_id: user.id,
                        items: cartItems.map(item => ({
                            book_id: item.book_id,
                            quantity: item.quantity
                        }))
                    };

                    // 3. Create the order (this triggers stock deduction and validation)
                    statusText.textContent = 'Finalizing Order...';
                    const orderResp = await fetch('/api/orders/', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(orderPayload)
                    });

                    const orderData = await orderResp.json();

                    if (orderResp.ok) {
                        statusText.textContent = 'Payment Successful!';
                        subText.textContent = 'Your order has been placed. Redirecting...';
                        localStorage.removeItem('cart_id');
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 2000);
                    } else {
                        // Handle stock error or other validation issues
                        const errorMsg = orderData.detail || 'Checkout failed points to an error.';
                        alert(`Checkout Error: ${errorMsg}`);
                        window.location.href = '/cart.php';
                    }

                } catch (err) {
                    console.error('Checkout error:', err);
                    alert('A system error occurred during checkout. Please try again.');
                    window.location.href = '/cart.php';
                }
            }

            // Start checkout process instead of simulation
            setTimeout(processCheckout, 1500);
        </script>
    </body>
</html>
