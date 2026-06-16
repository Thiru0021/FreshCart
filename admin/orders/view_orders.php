<?php
// Since this file lives in admin/orders/, we step up one level to find auth_guard
include("../auth_guard.php");

// 1. Connect to your database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "grocery_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. UPDATE: Handle status modifications submitted by the Admin
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status_value']);
    
    $update_query = "UPDATE orders SET order_status = '$new_status' WHERE order_id = $order_id";
    $conn->query($update_query);
}

// 3. READ: Pull orders including payment_method column parameters
$query = "SELECT o.order_id, c.name as customer_name, o.total_amount, o.delivery_address, o.payment_method, o.order_status, o.created_at 
          FROM orders o 
          INNER JOIN customers c ON o.customer_id = c.customer_id 
          ORDER BY o.order_id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Grocery Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<div class="app-container">

    <div class="sidebar">
        <h2>Grocery Admin</h2>
        <a href="/grocery-shop/admin/Index.php">Dashboard</a>
        <a href="/grocery-shop/admin/category/view_category.php">Categories</a>
        <a href="/grocery-shop/admin/subcategory/view_subcategory.php">Sub Categories</a>
        <a href="/grocery-shop/admin/product/view_product.php">Products</a>
        <a href="#" class="active">Orders</a> 
        <a href="/grocery-shop/admin/view_customers.php">Customers</a> 
        <a href="/grocery-shop/admin/settings.php">Settings</a>
    </div>

    <div class="main-wrapper">
        
        <header class="top-navbar">
            <div class="profile-container" onclick="window.location.href='../profile.php';">
                <div class="profile-info">
                    <span class="greeting">Logistics Management</span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                </div>
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="main-content">
            
            <div class="header">
                <h1>Customer Order Queues</h1>
            </div>

            <div class="card" style="padding: 0; overflow: hidden; border-radius: 12px; background: white; border: 1px solid #e2e8f0;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">Order ID</th>
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">Customer</th>
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">Total Bill</th>
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">Method</th>
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">Delivery Destination</th>
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">Fulfillment Status</th>
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">Date Placed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 16px 20px; font-weight: 700; color: #0f172a;">
                                        <?php echo "ord" . sprintf("%03d", $row['order_id']); ?>
                                    </td>
                                    <td style="padding: 16px 20px; color: #334155; font-weight: 600;"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td style="padding: 16px 20px; color: #10b981; font-weight: 700;">₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                    
                                    <td style="padding: 16px 20px; font-size: 12px; font-weight: 700;">
                                        <span style="padding: 4px 8px; border-radius: 4px; <?php echo $row['payment_method'] === 'ONLINE' ? 'background:#eff6ff; color:#1d4ed8;' : 'background:#f0fdf4; color:#16a34a;'; ?>">
                                            <?php echo htmlspecialchars($row['payment_method'] ?? 'COD'); ?>
                                        </span>
                                    </td>

                                    <td style="padding: 16px 20px; color: #475569; font-size: 13px; max-width: 250px;"><?php echo htmlspecialchars($row['delivery_address']); ?></td>
                                    <td style="padding: 16px 20px;">
                                        <form action="view_orders.php" method="POST" style="display: flex; gap: 8px; align-items: center;">
                                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                            <select name="status_value" style="padding: 6px 10px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; color: #334155;">
                                                <option value="Pending" <?php if($row['order_status'] == 'Pending') echo 'selected'; ?>>⏳ Pending</option>
                                                <option value="Shipped" <?php if($row['order_status'] == 'Shipped') echo 'selected'; ?>>🚚 Shipped</option>
                                                <option value="Delivered" <?php if($row['order_status'] == 'Delivered') echo 'selected'; ?>>✅ Delivered</option>
                                            </select>
                                            <button type="submit" name="update_status" style="padding: 6px 12px; background: #0f172a; color: white; border: none; border-radius: 6px; font-size: 11px; cursor: pointer; font-weight: 600;">Update</button>
                                        </form>
                                    </td>
                                    <td style="padding: 16px 20px; color: #64748b; font-size: 13px;">
                                        <?php echo date('d M Y - h:i A', strtotime($row['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="padding: 40px; text-align: center; color: #94a3b8; font-weight: 500;">
                                    No transaction records found in the system logs.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div> 
    </div> 
</div> 

</body>
</html>
<?php $conn->close(); ?>