<?php
// Fix: Removed '../' because this file sits directly in the admin folder
include("auth_guard.php");

// 1. Connect to your database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "grocery_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. READ: Pull all customer profiles
$query = "SELECT customer_id, name, email, phone, created_at FROM customers ORDER BY customer_id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Grocery Admin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<div class="app-container">

    <div class="sidebar">
    <h2>Grocery Admin</h2>
    <a href="Index.php">Dashboard</a>
    <a href="category/view_category.php">Categories</a>
    <a href="subcategory/view_subcategory.php">Sub Categories</a>
    <a href="product/view_product.php">Products</a>
    <a href="orders/view_orders.php">Orders</a>
    <a href="#" class="active">Customers</a>
    <a href="settings.php">Settings</a>
</div>

    <div class="main-wrapper">
        
        <header class="top-navbar">
            <div class="profile-container" onclick="window.location.href='profile.php';">
                <div class="profile-info">
                    <span class="greeting">System Operator</span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                </div>
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="main-content">
            
            <div class="header">
                <h1>Registered Customer Accounts</h1>
            </div>

            <div class="card" style="padding: 0; overflow: hidden; border-radius: 12px;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">ID</th>
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">Full Name</th>
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">Email Address</th>
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">Phone Number</th>
                            <th style="padding: 16px 20px; color: #64748b; font-weight: 600;">Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                                    <td style="padding: 16px 20px; font-weight: 600; color: #0f172a;"><?php echo $row['customer_id']; ?></td>
                                    <td style="padding: 16px 20px; color: #334155; font-weight: 500;"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td style="padding: 16px 20px; color: #334155;"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td style="padding: 16px 20px; color: #64748b;"><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td style="padding: 16px 20px; color: #64748b;">
                                        <?php echo date('d M Y - h:i A', strtotime($row['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="padding: 40px; text-align: center; color: #94a3b8; font-weight: 500;">
                                    No registered customer accounts found in the database.
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