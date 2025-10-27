<?php
require_once 'auth.php';

$api_url = "http://backend:8000";  // Internal Docker network URL

// Fetch orders with user and item details
$orders = json_decode(file_get_contents("$api_url/orders"), true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin Panel</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="container">
        <h1>Order Management</h1>
        
        <div class="filters">
            <select id="statusFilter">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <input type="date" id="dateFilter">
        </div>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['user']['username']); ?></td>
                    <td>
                        <ul class="order-items">
                            <?php foreach ($order['items'] as $item): ?>
                            <li>
                                <?php echo htmlspecialchars($item['book']['title']); ?> 
                                (<?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?>)
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    <td>$<?php echo number_format($order['total'], 2); ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                    <td>
                        <select class="status-select" data-id="<?php echo $order['id']; ?>">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </td>
                    <td>
                        <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)">View</button>
                        <?php if ($order['status'] == 'pending'): ?>
                        <button onclick="cancelOrder(<?php echo $order['id']; ?>)">Cancel</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <h2>Order Details</h2>
            <div id="orderDetails"></div>
            <button onclick="closeModal()">Close</button>
        </div>
    </div>

    <script>
        function viewOrderDetails(id) {
            fetch(`${api_url}/orders/${id}`)
                .then(response => response.json())
                .then(order => {
                    const details = document.getElementById('orderDetails');
                    details.innerHTML = `
                        <h3>Order #${order.id}</h3>
                        <p><strong>Customer:</strong> ${order.user.username}</p>
                        <p><strong>Email:</strong> ${order.user.email}</p>
                        <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                        <p><strong>Status:</strong> ${order.status}</p>
                        <h4>Items:</h4>
                        <ul>
                            ${order.items.map(item => `
                                <li>${item.book.title} - ${item.quantity} × $${item.price.toFixed(2)}</li>
                            `).join('')}
                        </ul>
                        <p><strong>Total:</strong> $${order.total.toFixed(2)}</p>
                    `;
                    document.getElementById('orderModal').style.display = 'block';
                })
                .catch(error => alert('Error loading order details: ' + error));
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        function cancelOrder(id) {
            if (!confirm('Are you sure you want to cancel this order?')) return;
            
            fetch(`${api_url}/orders/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ status: 'cancelled' })
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to cancel order');
                location.reload();
            })
            .catch(error => alert('Error cancelling order: ' + error));
        }

        // Add event listener to status selects
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', (e) => {
                fetch(`${api_url}/orders/${e.target.dataset.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status: e.target.value })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to update status');
                })
                .catch(error => {
                    alert('Error updating order status: ' + error);
                    location.reload();
                });
            });
        });

        // Add event listeners for filters
        document.getElementById('statusFilter').addEventListener('change', filterOrders);
        document.getElementById('dateFilter').addEventListener('change', filterOrders);

        function filterOrders() {
            const status = document.getElementById('statusFilter').value;
            const date = document.getElementById('dateFilter').value;
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                let show = true;
                
                if (status && row.querySelector('.status-select').value !== status) {
                    show = false;
                }
                
                if (date) {
                    const orderDate = row.querySelector('td:nth-child(5)').textContent.split(' ')[0];
                    if (orderDate !== date) {
                        show = false;
                    }
                }
                
                row.style.display = show ? '' : 'none';
            });
        }
    </script>
</body>
</html>