<?php
require_once __DIR__ . '/api/config.php';

$book_id = $_GET['id'] ?? null;
if (!$book_id) {
    header('Location: /');
    exit;
}

// Fetch book details
$book_result = backend_request('GET', "/books/$book_id");
$book = $book_result['data'] ?? null;

if (!$book || $book_result['status'] !== 200) {
    $error_msg = "Book not found or unable to fetch details.";
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= htmlspecialchars($book['title'] ?? 'Book Details') ?> — Online Bookstore</title>
        <link rel="stylesheet" href="/styles.css">
        <script defer src="/main.js?v=<?=time()?>"></script>
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
                        <a href="/books.php" aria-label="Books">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        </a>
                        <a href="/cart.php" aria-label="Cart">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        </a>
                        <a href="/admin.php" aria-label="Admin">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </a>
                    </nav>
                </div>
            </header>

            <main class="container">
                <?php if (isset($error_msg)): ?>
                    <div class="empty">
                        <p><?= htmlspecialchars($error_msg) ?></p>
                        <a href="/" class="btn btn-primary" style="margin-top: 16px; display: inline-block;">Go Back Home</a>
                    </div>
                <?php else: ?>
                    <div class="book-details">
                        <div class="details-cover">
                            <?php $thumb = (!empty($book['thumbnail_path'])) ? '/api/' . $book['thumbnail_path'] : '/images/book-placeholder.png'; ?>
                            <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                        </div>
                        <div class="details-info">
                            <h1><?= htmlspecialchars($book['title']) ?></h1>
                            <p class="details-author">by <?= htmlspecialchars($book['author']) ?></p>
                            
                            <div class="details-meta">
                                <span class="price">NPR <?= number_format($book['price'], 2) ?></span>
                                <span class="stock"><?= intval($book['stock']) ?> units in stock</span>
                            </div>

                            <p class="details-desc"><?= nl2br(htmlspecialchars($book['description'])) ?></p>

                            <div class="details-actions">
                                <button class="btn btn-primary btn-large btn-buy" data-id="<?= htmlspecialchars($book['id']) ?>">Add to Cart</button>
                                
                                <?php if (!empty($book['file_path'])): ?>
                                    <a href="/api/<?= htmlspecialchars($book['file_path']) ?>" class="btn btn-outline btn-large" target="_blank">
                                        Read PDF
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>

            <footer class="site-footer" style="margin-top: 60px;">
                <div class="container">
                    <p>© <?= date('Y') ?> Online Bookstore — Built with precision</p>
                </div>
            </footer>
        </div>
    </body>
</html>
