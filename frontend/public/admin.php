<?php
session_start();

// Basic authentication check
function requireLogin() {
    $validUsername = 'admin';
    $validPassword = 'admin123'; // In production, use proper hashed passwords

    if (!isset($_SESSION['admin_logged_in'])) {
        // Prefer session-based login. If PHP is running under an environment
        // that doesn't populate PHP_AUTH_USER (common with some CGI setups),
        // redirect to a simple login form instead of forcing HTTP Basic auth.
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            if ($_SERVER['PHP_AUTH_USER'] === $validUsername && ($_SERVER['PHP_AUTH_PW'] ?? '') === $validPassword) {
                $_SESSION['admin_logged_in'] = true;
                return;
            }
            // Invalid credentials provided via HTTP auth
            header('HTTP/1.0 401 Unauthorized');
            echo 'Authentication required';
            exit;
        }

        // No session and no PHP_AUTH_USER - redirect to login form
        header('Location: /admin/login.php');
        exit;
    }
}

requireLogin();

// API function to make backend requests
function callApi($method, $endpoint, $data = null) {
    $url = "http://backend:8000" . $endpoint;
    $ch = curl_init();
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ];
    
    if ($data && ($method === 'POST' || $method === 'PUT')) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'data' => json_decode($response, true),
        'status' => $statusCode
    ];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_book':
            $result = callApi('POST', '/books/', [
                'title' => $_POST['title'],
                'author' => $_POST['author'],
                'description' => $_POST['description'],
                'price' => (float)$_POST['price'],
                'stock' => (int)$_POST['stock']
            ]);
            break;
            
        case 'update_book':
            $id = $_POST['book_id'];
            $result = callApi('PUT', "/books/$id", [
                'title' => $_POST['title'],
                'author' => $_POST['author'],
                'description' => $_POST['description'],
                'price' => (float)$_POST['price'],
                'stock' => (int)$_POST['stock']
            ]);
            break;
            
        case 'delete_book':
            $id = $_POST['book_id'];
            $result = callApi('DELETE', "/books/$id");
            break;
    }
}

// Fetch current books
$books = callApi('GET', '/books/')['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookstore Admin</title>
    <link rel="stylesheet" href="./styles.css">
    <style>
        .admin-panel {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .book-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .book-form input,
        .book-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .book-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .book-table th,
        .book-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .book-table th {
            background: #f5f5f5;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-primary {
            background: #2c3e50;
            color: white;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-edit {
            background: #3498db;
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-panel">
        <h1>Bookstore Admin Panel</h1>
        
        <!-- Add/Edit Book Form -->
        <div class="book-form">
            <h2>Add New Book</h2>
            <form method="POST" id="bookForm">
                <input type="hidden" name="action" value="create_book">
                <input type="hidden" name="book_id" id="bookId">
                
                <input type="text" name="title" placeholder="Book Title" required>
                <input type="text" name="author" placeholder="Author" required>
                <textarea name="description" placeholder="Description" rows="3"></textarea>
                <input type="number" name="price" placeholder="Price" step="0.01" required>
                <input type="number" name="stock" placeholder="Stock" required>
                
                <button type="submit" class="btn btn-primary">Save Book</button>
                <button type="button" class="btn btn-danger" onclick="resetForm()">Cancel</button>
            </form>
        </div>

        <!-- Books Table -->
        <div class="book-table-container">
            <h2>Book List</h2>
            <table class="book-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['description']) ?></td>
                        <td>$<?= number_format($book['price'], 2) ?></td>
                        <td><?= $book['stock'] ?></td>
                        <td class="action-buttons">
                            <button class="btn btn-edit" onclick='editBook(<?= json_encode($book) ?>)'>Edit</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete_book">
                                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function editBook(book) {
            const form = document.getElementById('bookForm');
            form.elements['action'].value = 'update_book';
            form.elements['book_id'].value = book.id;
            form.elements['title'].value = book.title;
            form.elements['author'].value = book.author;
            form.elements['description'].value = book.description;
            form.elements['price'].value = book.price;
            form.elements['stock'].value = book.stock;
            form.scrollIntoView({ behavior: 'smooth' });
        }

        function resetForm() {
            const form = document.getElementById('bookForm');
            form.reset();
            form.elements['action'].value = 'create_book';
            form.elements['book_id'].value = '';
        }
    </script>
</body>
</html>