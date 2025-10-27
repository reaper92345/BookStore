<?php
require_once 'auth.php';

$api_url = "http://backend:8000";  // Internal Docker network URL

// Fetch comments with book and user details
$comments = json_decode(file_get_contents("$api_url/comments"), true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment Management - Admin Panel</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="container">
        <h1>Comment Management</h1>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Book</th>
                    <th>User</th>
                    <th>Content</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($comment['id']); ?></td>
                    <td><?php echo htmlspecialchars($comment['book_title']); ?></td>
                    <td><?php echo htmlspecialchars($comment['username']); ?></td>
                    <td><?php echo htmlspecialchars($comment['content']); ?></td>
                    <td><?php echo str_repeat('★', $comment['rating']); ?></td>
                    <td>
                        <select class="status-select" data-id="<?php echo $comment['id']; ?>">
                            <option value="pending" <?php echo $comment['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $comment['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $comment['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </td>
                    <td><?php echo date('Y-m-d', strtotime($comment['created_at'])); ?></td>
                    <td>
                        <button onclick="deleteComment(<?php echo $comment['id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function updateCommentStatus(id, status) {
            fetch(`${api_url}/comments/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to update status');
                location.reload();
            })
            .catch(error => alert('Error updating comment status: ' + error));
        }

        function deleteComment(id) {
            if (!confirm('Are you sure you want to delete this comment?')) return;
            
            fetch(`${api_url}/comments/${id}`, {
                method: 'DELETE'
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to delete comment');
                location.reload();
            })
            .catch(error => alert('Error deleting comment: ' + error));
        }

        // Add event listeners to status selects
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', (e) => {
                updateCommentStatus(e.target.dataset.id, e.target.value);
            });
        });
    </script>
</body>
</html>