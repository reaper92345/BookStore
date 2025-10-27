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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bookstore</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <div id="app">
        <header>
            <h1>Online Bookstore</h1>
            <nav>
                <a href="/">Home</a> | 
                <a href="/books">Books</a> | 
                <a href="/admin.php">Admin</a>
            </nav>
        </header>

        <main id="root">
            <section id="book-list">
                <h2>Book List</h2>
                <ul id="books">
                    <?php if (empty($books)): ?>
                        <li>No books available.</li>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
                            <li>
                                <h3><?php echo htmlspecialchars($book['title'] ?? 'Untitled'); ?></h3>
                                <p><?php echo htmlspecialchars($book['author'] ?? ''); ?></p>
                                <p><?php echo htmlspecialchars($book['description'] ?? ''); ?></p>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </section>
        </main>

        <footer>
            <p>© Online Bookstore</p>
        </footer>
    </div>
</body>
</html>
