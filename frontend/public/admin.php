<?php
session_start();

// Basic authentication check
function requireLogin() {
    $validUsername = 'admin';
    $validPassword = 'admin123';

    if (!isset($_SESSION['admin_logged_in'])) {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            if ($_SERVER['PHP_AUTH_USER'] === $validUsername && ($_SERVER['PHP_AUTH_PW'] ?? '') === $validPassword) {
                $_SESSION['admin_logged_in'] = true;
                return;
            }
            header('HTTP/1.0 401 Unauthorized');
            echo 'Authentication required';
            exit;
        }

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
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 10
    ];
    
    if ($data && ($method === 'POST' || $method === 'PUT')) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'data' => json_decode($response, true),
        'status' => $statusCode,
        'error' => $error,
        'raw_response' => $response
    ];
}

// Handle form submissions
$result = null;
$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_book':
            $result = callApi('POST', '/books/', [
                'title' => $_POST['title'] ?? '',
                'author' => $_POST['author'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price' => (float)($_POST['price'] ?? 0),
                'stock' => (int)($_POST['stock'] ?? 0)
            ]);
            
            if ($result['status'] === 200 || $result['status'] === 201) {
                $success_msg = "Book created successfully!";
            } else {
                $error_msg = "Failed to create book. Status: {$result['status']}";
                if ($result['error']) {
                    $error_msg .= " Error: {$result['error']}";
                }
                if (isset($result['data']['detail'])) {
                    $error_msg .= " Details: {$result['data']['detail']}";
                }
            }
            break;
            
        case 'update_book':
            $id = $_POST['book_id'] ?? '';
            if (!$id) {
                $error_msg = "Book ID is required for update.";
                break;
            }
            
            $result = callApi('PUT', "/books/$id", [
                'title' => $_POST['title'] ?? '',
                'author' => $_POST['author'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price' => (float)($_POST['price'] ?? 0),
                'stock' => (int)($_POST['stock'] ?? 0)
            ]);
            
            if ($result['status'] === 200) {
                $success_msg = "Book updated successfully!";
            } else {
                $error_msg = "Failed to update book. Status: {$result['status']}";
            }
            break;
            
        case 'delete_book':
            $id = $_POST['book_id'] ?? '';
            if (!$id) {
                $error_msg = "Book ID is required for deletion.";
                break;
            }
            
            $result = callApi('DELETE', "/books/$id");
            
            if ($result['status'] === 200) {
                $success_msg = "Book deleted successfully!";
            } else {
                $error_msg = "Failed to delete book. Status: {$result['status']}";
            }
            break;
    }
}

// Fetch current books
$books_result = callApi('GET', '/books/');
$books = $books_result['data'] ?? [];

if ($books_result['status'] !== 200) {
    $error_msg = "Failed to fetch books from server.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookstore Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        .admin-panel {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            font-size: 24px;
        }
        
        .admin-header a {
            color: white;
            text-decoration: none;
            background: #e74c3c;
            padding: 8px 16px;
            border-radius: 4px;
        }
        
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .alert-error {
            background: #ffe0e0;
            color: #c00;
            border: 1px solid #e00;
        }
        
        .alert-success {
            background: #e0ffe0;
            color: #0a0;
            border: 1px solid #0e0;
        }
        
        .book-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .book-form h2 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .form-group {
            margin-bottom: 12px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        
        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .book-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .book-table-container h2 {
            padding: 20px;
            margin: 0;
            background: #f9f9f9;
            border-bottom: 1px solid #eee;
        }
        
        .book-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .book-table th,
        .book-table td {
            padding: 12px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .book-table th {
            background: #f5f5f5;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .book-table tr:hover {
            background: #f9f9f9;
        }
        
        .book-table td:last-child {
            white-space: nowrap;
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
            font-size: 14px;
            transition: background 0.2s;
        }
        
        .btn-primary {
            background: #2c3e50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1a252f;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
        }
        
        .btn-edit:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="admin-panel">
        <div class="admin-header">
            <h1>Bookstore Admin Panel</h1>
            <a href="/admin/logout.php">Logout</a>
        </div>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        
        <!-- Add/Edit Book Form -->
        <div class="book-form">
            <h2 id="formTitle">Add New Book</h2>
            <form method="POST" id="bookForm">
                <input type="hidden" name="action" value="create_book">
                <input type="hidden" name="book_id" id="bookId">
                
                <div class="form-group">
                    <label for="title">Book Title *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="author">Author *</label>
                    <input type="text" id="author" name="author" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock *</label>
                    <input type="number" id="stock" name="stock" required>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary">Save Book</button>
                    <button type="button" class="btn btn-danger" onclick="resetForm()">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Books Table -->
        <div class="book-table-container">
            <h2>Book List</h2>
            <?php if (empty($books)): ?>
                <p style="padding: 20px; color: #999;">No books found. <?php echo $error_msg ? 'Check connection to backend.' : ''; ?></p>
            <?php else: ?>
            <table class="book-table">
                <thead>
                    <tr>
                        <th>ID</th>
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
                        <td><?php echo htmlspecialchars($book['id']); ?></td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars(substr($book['description'] ?? '', 0, 50)); ?></td>
                        <td>NPR <?php echo number_format($book['price'], 2); ?></td>
                        <td><?php echo $book['stock']; ?></td>
                        <td class="action-buttons">
                            <button class="btn btn-edit" onclick='editBook(<?php echo json_encode($book); ?>)'>Edit</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete_book">
                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function editBook(book) {
            const form = document.getElementById('bookForm');
            document.getElementById('formTitle').textContent = 'Edit Book';
            form.elements['action'].value = 'update_book';
            form.elements['book_id'].value = book.id;
            form.elements['title'].value = book.title;
            form.elements['author'].value = book.author;
            form.elements['description'].value = book.description || '';
            form.elements['price'].value = book.price;
            form.elements['stock'].value = book.stock;
            form.scrollIntoView({ behavior: 'smooth' });
        }

        function resetForm() {
            const form = document.getElementById('bookForm');
            document.getElementById('formTitle').textContent = 'Add New Book';
            form.reset();
            form.elements['action'].value = 'create_book';
            form.elements['book_id'].value = '';
        }
    </script>
</body>
</html>