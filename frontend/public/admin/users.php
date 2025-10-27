<?php
require_once 'auth.php';

$api_url = "http://backend:8000";  // Internal Docker network URL

// Fetch users
$users = json_decode(file_get_contents("$api_url/users"), true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="container">
        <h1>User Management</h1>
        
        <button onclick="showAddUserForm()" class="add-button">Add New User</button>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <select class="role-select" data-id="<?php echo $user['id']; ?>">
                            <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </td>
                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                    <td>
                        <select class="status-select" data-id="<?php echo $user['id']; ?>">
                            <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="suspended" <?php echo $user['status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                            <option value="banned" <?php echo $user['status'] == 'banned' ? 'selected' : ''; ?>>Banned</option>
                        </select>
                    </td>
                    <td>
                        <button onclick="editUser(<?php echo $user['id']; ?>)">Edit</button>
                        <button onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Add New User</h2>
            <form id="userForm" onsubmit="submitUserForm(event)">
                <input type="hidden" id="userId">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password">
                    <small>(Leave blank to keep existing password when editing)</small>
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit">Save</button>
                <button type="button" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function showAddUserForm() {
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('userModal').style.display = 'block';
        }

        function editUser(id) {
            fetch(`${api_url}/users/${id}`)
                .then(response => response.json())
                .then(user => {
                    document.getElementById('modalTitle').textContent = 'Edit User';
                    document.getElementById('userId').value = user.id;
                    document.getElementById('username').value = user.username;
                    document.getElementById('email').value = user.email;
                    document.getElementById('role').value = user.role;
                    document.getElementById('userModal').style.display = 'block';
                })
                .catch(error => alert('Error loading user: ' + error));
        }

        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        function submitUserForm(event) {
            event.preventDefault();
            const userId = document.getElementById('userId').value;
            const data = {
                username: document.getElementById('username').value,
                email: document.getElementById('email').value,
                role: document.getElementById('role').value
            };
            
            if (document.getElementById('password').value) {
                data.password = document.getElementById('password').value;
            }

            const method = userId ? 'PUT' : 'POST';
            const url = userId ? `${api_url}/users/${userId}` : `${api_url}/users`;

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to save user');
                location.reload();
            })
            .catch(error => alert('Error saving user: ' + error));
        }

        function deleteUser(id) {
            if (!confirm('Are you sure you want to delete this user?')) return;
            
            fetch(`${api_url}/users/${id}`, {
                method: 'DELETE'
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to delete user');
                location.reload();
            })
            .catch(error => alert('Error deleting user: ' + error));
        }

        // Add event listeners to role and status selects
        document.querySelectorAll('.role-select, .status-select').forEach(select => {
            select.addEventListener('change', (e) => {
                const field = e.target.classList.contains('role-select') ? 'role' : 'status';
                fetch(`${api_url}/users/${e.target.dataset.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ [field]: e.target.value })
                })
                .then(response => {
                    if (!response.ok) throw new Error(`Failed to update ${field}`);
                })
                .catch(error => {
                    alert(`Error updating ${field}: ` + error);
                    location.reload();
                });
            });
        });
    </script>
</body>
</html>