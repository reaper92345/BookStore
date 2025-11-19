<?php
// Simple server-side rendered index page that fetches books from the backend
function fetch_books(): array {
    $url = 'http://backend:8000/books/';
    $opts = [
        'http' => [
            'method' => 'GET',
            'timeout' => 5,
            'header' => "Accept: application/json\r\n",
        ]
    ];
    $context = stream_context_create($opts);
    $json = @file_get_contents($url, false, $context);
    if ($json === false) {
        return [];
    }
    $data = json_decode($json, true);
    if (!is_array($data)) return [];
    return $data;
}

$books = fetch_books();
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Online Bookstore</title>
        <link rel="stylesheet" href="/styles.css">
        <script defer src="/main.js"></script>
    </head>
    <body>
        <div class="site">
            <header class="site-header">
                <div class="container header-inner">
                    <div class="brand">
                        <a href="/" class="logo">Online<span class="accent">Bookstore</span></a>
                    </div>
                    <nav class="nav">
                        <a href="/">Home</a>
                        <a href="/books">Books</a>
                        <a href="/admin.php">Admin</a>
                    </nav>
                </div>
            </header>

            <main>
                <section class="hero">
                    <div class="container hero-inner">
                        <div class="hero-text">
                            <h1>Find your next great read</h1>
                            <p>Discover bestsellers, timeless classics, and new releases — curated for you.</p>
                            <div class="hero-actions">
                                <a class="btn btn-primary" href="/books">Browse Books</a>
                                <!-- <a class="btn btn-outline" href="/admin.php">Admin Panel</a> -->
                            </div>
                        </div>
                        <div class="hero-image">
                            <img src="/images/hero-books.svg" alt="Books illustration" />
                        </div>
                    </div>
                </section>

                <section class="container search-section">
                    <input id="searchInput" class="search" placeholder="Search by title or author…" aria-label="Search books">
                </section>

                <section class="container featured">
                    <h2>Featured Books</h2>
                    <div id="booksGrid" class="grid">
                        <?php if (empty($books)): ?>
                            <div class="empty">No books available right now.</div>
                        <?php else: ?>
                                <?php foreach ($books as $book): ?>
                                <article class="card" data-title="<?=htmlspecialchars($book['title'] ?? '')?>" data-author="<?=htmlspecialchars($book['author'] ?? '')?>">
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
                                            <button class="btn btn-primary btn-buy">Add to cart</button>
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
                    <p>© <?=date('Y')?> Online Bookstore — Built with ❤️</p>
                </div>
            </footer>
        </div>
    </body>
</html>
