<?php
require_once __DIR__ . '/api/config.php';
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Cart — Online Bookstore</title>
        <link rel="stylesheet" href="/styles.css">
        <script defer src="/main.js?v=<?=time()?>"></script>
        <style>
            .cart-items { display: grid; gap: 16px; margin-bottom: 24px; }
            .cart-item { background: rgba(255,255,255,0.02); padding: 16px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
            .cart-total { font-size: 1.2rem; font-weight: bold; text-align: right; margin-bottom: 24px; color: var(--white); }
            .checkout-actions { display: flex; justify-content: flex-end; }
            
            /* Modal Styles */
            .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; }
            .modal.active { display: flex; }
            .modal-content { background: var(--card); padding: 32px; border-radius: 16px; width: 100%; max-width: 400px; text-align: center; border: 1px solid rgba(255,255,255,0.1); }
            .modal h3 { color: var(--white); margin-top: 0; }
            .payment-options { display: grid; gap: 12px; margin-top: 24px; }
            .payment-btn { padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: var(--white); cursor: pointer; font-weight: 600; transition: all 0.2s; }
            .payment-btn:hover { background: rgba(255,255,255,0.1); border-color: var(--accent); }
            .payment-btn.esewa { border-left: 4px solid #60bb46; }
            .payment-btn.khalti { border-left: 4px solid #5c2d91; }
            .payment-btn.connectips { border-left: 4px solid #2f75bb; }
            .close-modal { margin-top: 16px; background: transparent; border: none; color: var(--muted); cursor: pointer; text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class="site">
            <header class="site-header">
                <div class="container header-inner">
                    <div class="brand">
                        <a href="/" class="logo">Online<span class="accent">Bookstore</span></a>
                    </div>
                    <nav class="nav">
                        <a href="/" aria-label="Home">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        </a>
                        <!--<a href="/books.php" aria-label="Books">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        </a>-->
                        <a href="/cart.php" class="active" aria-label="Cart">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        </a>
                        <a href="/admin.php" aria-label="Admin">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </a>
                    </nav>
                </div>
            </header>

            <main>
                <section class="container">
                    <h1>Your Cart</h1>
                    <div id="cartGrid" class="cart-grid">
                        <div id="cartItemsList" class="cart-items">
                            <div class="empty">Loading cart...</div>
                        </div>
                        
                        <div id="cartSummaryCard" class="cart-summary-card" style="display:none;">
                            <h2>Order Summary</h2>
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>NPR <span id="cartSubtotal">0.00</span></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total</span>
                                <span>NPR <span id="cartTotal">0.00</span></span>
                            </div>
                            <button id="checkoutBtn" class="btn btn-primary btn-checkout">Proceed to Checkout</button>
                            <div style="margin-top:16px;text-align:center;">
                                <a href="/books.php" style="color:var(--accent-2);text-decoration:none;font-size:0.9rem;">Continue Shopping</a>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <!-- Payment Modal -->
            <div id="paymentModal" class="modal">
                <div class="modal-content">
                    <h3>Select Payment Method</h3>
                    <div class="payment-options">
                        <button class="payment-btn esewa" onclick="selectPayment('eSewa')">eSewa</button>
                        <button class="payment-btn khalti" onclick="selectPayment('Khalti')">Khalti</button>
                        <button class="payment-btn connectips" onclick="selectPayment('ConnectIPS')">ConnectIPS</button>
                    </div>
                    <button class="close-modal" onclick="closeModal()">Cancel</button>
                </div>
            </div>

            <footer class="site-footer">
                <div class="container">
                    <p>© <?=date('Y')?> Online Bookstore</p>
                </div>
            </footer>
        </div>
    </body>
</html>
