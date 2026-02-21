<?php
// register.php
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Register - Online Bookstore</title>
        <link rel="stylesheet" href="/styles.css">
        <style>
            .auth-container {
                max-width: 400px;
                margin: 60px auto;
                padding: 32px;
                background: var(--card);
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }
            .auth-header {
                text-align: center;
                margin-bottom: 32px;
            }
            .auth-header h1 {
                font-size: 1.8rem;
                color: var(--white);
                margin: 0;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: var(--muted);
            }
            .form-group input {
                width: 100%;
                padding: 12px;
                border: 1px solid rgba(0, 0, 0, 0.1);
                border-radius: 8px;
                background: #f9fafb;
                color: var(--white);
            }
            .auth-btn {
                width: 100%;
                padding: 14px;
                margin-top: 10px;
                font-size: 1rem;
            }
            .auth-footer {
                text-align: center;
                margin-top: 24px;
                color: var(--muted);
            }
            .auth-footer a {
                color: var(--accent);
                text-decoration: none;
                font-weight: 600;
            }
            .error-message {
                color: #ef4444;
                background: #fee2e2;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                display: none;
            }
            .success-message {
                color: #10b981;
                background: #d1fae5;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                display: none;
            }
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
                <div class="auth-container">
                    <div class="auth-header">
                        <h1>Create Account</h1>
                        <p>Join our community of readers</p>
                    </div>
                    
                    <div id="errorMessage" class="error-message"></div>
                    <div id="successMessage" class="success-message"></div>

                    <form id="registerForm">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary auth-btn">Register</button>
                    </form>

                    <div class="auth-footer">
                        Already have an account? <a href="/login.php">Login</a>
                    </div>
                </div>
            </main>
        </div>

        <script>
            document.getElementById('registerForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const username = document.getElementById('username').value;
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const errorDiv = document.getElementById('errorMessage');
                const successDiv = document.getElementById('successMessage');
                
                errorDiv.style.display = 'none';
                successDiv.style.display = 'none';

                try {
                    const response = await fetch('/api/users/', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ username, email, password })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        successDiv.textContent = 'Account created successfully! Redirecting to login...';
                        successDiv.style.display = 'block';
                        setTimeout(() => {
                            window.location.href = '/login.php';
                        }, 2000);
                    } else {
                        errorDiv.textContent = data.detail || 'Registration failed. Please try again.';
                        errorDiv.style.display = 'block';
                    }
                } catch (err) {
                    console.error('Registration error:', err);
                    errorDiv.textContent = 'An error occurred. Please try again.';
                    errorDiv.style.display = 'block';
                }
            });
        </script>
    </body>
</html>
