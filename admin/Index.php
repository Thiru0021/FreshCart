<?php
include("auth_guard.php");

// 1. Connect to your database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "grocery_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. FETCH ANALYTICS METRICS (READ OPERATIONS)

// Categories Count
$cat_result = $conn->query("SELECT COUNT(*) as total FROM category");
$cat_row = $cat_result->fetch_assoc();
$total_categories = $cat_row['total'];

// Sub Categories Count 
$subcat_result = $conn->query("SELECT COUNT(*) as total FROM sub_category");
$subcat_row = $subcat_result->fetch_assoc();
$total_subcategories = $subcat_row['total'];

// Active Products Count (Filters out orphaned products)
$prod_result = $conn->query("SELECT COUNT(*) as total FROM product WHERE quantity > 0 AND category_id IN (SELECT category_id FROM category)");
$prod_row = $prod_result->fetch_assoc();
$total_products = $prod_row['total'];

// NEW: Total Registered Customers Count
$cust_result = $conn->query("SELECT COUNT(*) as total FROM customers");
$cust_row = $cust_result->fetch_assoc();
$total_customers = $cust_row['total'];

// NEW: Pending Orders Count
$order_result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE order_status = 'Pending'");
$order_row = $order_result->fetch_assoc();
$total_orders = $order_row['total'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery Admin Panel - Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Ensures the new metric row items distribute beautifully across the layout grid */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
    </style>
</head>
<body>

<div class="app-container">

    <div class="sidebar">
    <h2>Grocery Admin</h2>
    <a href="#" class="active">Dashboard</a>
    <a href="category/view_category.php">Categories</a>
    <a href="subcategory/view_subcategory.php">Sub Categories</a>
    <a href="product/view_product.php">Products</a>
    <a href="orders/view_orders.php">Orders</a>
    <a href="view_customers.php">Customers</a> 
    <a href="settings.php">Settings</a>
</div>

    <div class="main-wrapper">
        
        <header class="top-navbar">
            <div class="profile-container" onclick="window.location.href='<?php echo (file_exists('profile.php') ? '' : '../'); ?>profile.php';">
                <div class="profile-info">
                    <span class="greeting">Hi, Welcome back</span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                </div>
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="main-content">
            
            <div class="header">
                <h1>Dashboard Overview</h1>
            </div>

            <div class="stats-row">
                <div class="stat-card">
                    <h3>Total Categories</h3>
                    <p><?php echo $total_categories; ?></p> 
                </div>
                <div class="stat-card">
                    <h3>Total Sub Categories</h3>
                    <p><?php echo $total_subcategories; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Active Products</h3>
                    <p><?php echo $total_products; ?></p>
                </div>
                <div class="stat-card" style="border-left: 4px solid #10b981;">
                    <h3>Total Customers</h3>
                    <p><?php echo $total_customers; ?></p>
                </div>
                <div class="stat-card" style="border-left: 4px solid #f59e0b;">
                    <h3>Pending Orders</h3>
                    <p><?php echo $total_orders; ?></p>
                </div>
            </div>

            <div class="menu-grid">

                <div class="card">
                    <h2>Category</h2>
                    <p>Organize high-level grocery divisions like Fruits, Dairy, or Bakery.</p>
                    <br>
                    <a href="category/view_category.php" class="action-link edit-link" style="margin: 0; display: inline-block; width: 100%; text-align: center; padding: 10px;">Manage Categories</a>
                </div>

                <div class="card">
                    <h2>Sub Category</h2>
                    <p>Refine item groupings like Milk, Cheese under the Dairy umbrella.</p>
                    <br>
                    <a href="subcategory/view_subcategory.php" class="action-link edit-link" style="margin: 0; display: inline-block; width: 100%; text-align: center; padding: 10px;">Manage Sub Categories</a>
                </div>

                <div class="card">
                    <h2>Products</h2>
                    <p>Control pricing, stock counts, descriptions, and item details.</p>
                    <br>
                    <a href="product/view_product.php" class="action-link edit-link" style="margin: 0; display: inline-block; width: 100%; text-align: center; padding: 10px;">Manage Products</a>
                </div>

                <div class="card">
                    <h2>Customers</h2>
                    <p>View registered buyer accounts profiles, contact phones, and history details.</p>
                    <br>
                    <a href="customers/view_customers.php" class="action-link edit-link" style="margin: 0; display: inline-block; width: 100%; text-align: center; padding: 10px; background-color: #10b981;">Manage Customers</a>
                </div>

                <div class="card">
                    <h2>Incoming Orders</h2>
                    <p>Track grocery delivery items queues, modify pending logistics statuses, and manage bills.</p>
                    <br>
                    <a href="orders/view_orders.php" class="action-link edit-link" style="margin: 0; display: inline-block; width: 100%; text-align: center; padding: 10px; background-color: #f59e0b;">Manage Orders</a>
                </div>

            </div>

        </div> 
    </div> 
</div> 
</body>
</html>