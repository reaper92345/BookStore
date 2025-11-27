<?php
// Use server-side backend_request helper to fetch books directly from candidates
require_once __DIR__ . '/api/config.php';

$books_result = backend_request('GET', '/books');
$books = $books_result['data'] ?? [];
$books_error = '';
if (empty($books)) {
    $books_error = 'Failed to load books';
    if (!empty($books_result['status'])) $books_error .= ' (status=' . $books_result['status'] . ')';
    if (!empty($books_result['error'])) $books_error .= ' curl_error=' . $books_result['error'];
    if (!empty($books_result['candidate'])) $books_error .= ' tried=' . $books_result['candidate'];
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Books — Online Bookstore</title>
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

            <main>
                <section class="container">
                    <h1>All Books</h1>
                    <div id="booksGrid" class="grid">
                        <?php if (empty($books)): ?>
                            <div class="empty">No books available right now.</div>
                        <?php else: ?>
                            <?php foreach ($books as $book): ?>
                                <article class="card" data-id="<?=htmlspecialchars($book['id'] ?? '')?>" data-title="<?=htmlspecialchars($book['title'] ?? '')?>" data-author="<?=htmlspecialchars($book['author'] ?? '')?>">
                                    <div class="card-cover">
                                        <img src="/images/book-placeholder.png" alt="<?=htmlspecialchars($book['title'] ?? 'Book')?>">
                                    </div>
                                    <div class="card-body">
                                        <h3 class="card-title"><?=htmlspecialchars($book['title'] ?? 'Untitled')?></h3>
                                        <p class="card-author"><?=htmlspecialchars($book['author'] ?? '')?></p>
                                        <p class="card-desc"><?=htmlspecialchars(substr($book['description'] ?? '', 0, 140))?><?= (strlen($book['description'] ?? '')>140)?'...':'' ?></p>
                                        <div class="card-meta">
                                            <span class="price">NPR <?=number_format($book['price'] ?? 0, 2)?></span>
                                            <span class="stock"><?=intval($book['stock'] ?? 0)?> in stock</span>
                                        </div>
                                        <div class="card-actions">
                                            <button class="btn btn-primary btn-buy" data-id="<?=htmlspecialchars($book['id'] ?? '')?>">Add to cart</button>
                                            <a class="btn btn-outline btn-view" href="#">View</a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </main>

            <footer class="site-footer">
                <div class="container">
                    <p>© <?=date('Y')?> Online Bookstore — Built with without using brain</p>
                </div>
            </footer>
        </div>
    </body>
</html>
