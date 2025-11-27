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
            
            // Simulate redirect or completion
            setTimeout(() => {
                // In a real app, this would be the callback from payment gateway
                alert('This is a demo. Payment would happen here.');
                window.location.href = '/';
            }, 3000);
        </script>
    </body>
</html>
