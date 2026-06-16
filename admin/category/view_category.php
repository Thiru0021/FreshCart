<?php

include("../auth_guard.php");

include("../db.php");

$result = mysqli_query(
    $conn,
    "SELECT * FROM category"
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category List - Grocery Admin</title>
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
    <a href="/grocery-shop/admin/view_orders.php">Orders</a> 
    <a href="/grocery-shop/admin/view_customers.php">Customers</a> 
    <a href="/grocery-shop/admin/settings.php">Settings</a>
</div>

    <div class="main-wrapper">
        
       <header class="top-navbar">
    <div class="profile-container" onclick="window.location.href='<?php echo (file_exists('profile.php') ? '' : '../'); ?>profile.php';">
        <div class="profile-info">
            <span class="greeting">Hi, Welcome back</span>
            <span class="user-name"><?php echo $_SESSION['admin_name']; ?></span>
        </div>
        <div class="profile-avatar">
            <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
        </div>
    </div>
</header>

        <div class="main-content">
            
            <div class="header">
                <h1>Category List</h1>
                <a href="add_category.php" class="add-btn">+ Add Category</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category ID</th>
                            <th>Category Name</th>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while($row = mysqli_fetch_assoc($result))
                        {
                        ?>
                        <tr>
                            <td><strong><?php echo $row['id']; ?></strong></td>
                            <td><?php echo $row['category_id']; ?></td>
                            <td><?php echo $row['category_name']; ?></td>
                            <td>
                                <img class="category-img" src="../images/<?php echo $row['image']; ?>" width="60" height="60" alt="Category Image">
                            </td>
                            <td>
                                <a href="edit_category.php?id=<?php echo $row['id']; ?>" class="action-link edit-link">Edit</a>
                                <a href="delete_category.php?id=<?php echo $row['id']; ?>" class="action-link delete-link" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                            </td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div> </div> </div> </body>
</html>