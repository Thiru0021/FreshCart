<?php

include("../auth_guard.php");

include("../db.php");

$result = mysqli_query(
    $conn,
    "SELECT p.*,
            c.category_name,
            s.sub_category_name
     FROM product p
     JOIN category c
     ON p.category_id = c.category_id
     JOIN sub_category s
     ON p.sub_category_id = s.sub_category_id"
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List - Grocery Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<div class="app-container">

   <div class="sidebar">
    <h2>Grocery Admin</h2>
    <a href="/grocery-shop/admin/Index.php">Dashboard</a>
    <a href="/grocery-shop/admin/category/view_category.php">Categories</a>
    <a href="/grocery-shop/admin/subcategory/view_subcategory.php">Sub Categories</a>
    <a href="/grocery-shop/admin/product/view_product.php" class="active">Products</a>
    <a href="../orders/view_orders.php">Orders</a>
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
                <h1>Product List</h1>
                <a href="add_product.php" class="add-btn">+ Add Product</a>
            </div>

            <div class="table-container">
                <table style="min-width: 1000px;"> <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product ID</th>
                            <th>Category</th>
                            <th>Sub Category</th>
                            <th>Product Name</th>
                            <th>Description</th>
                            <th>Master Price</th>
                            <th>Selling Price</th>
                            <th>Stock</th>
                            <th>Unit</th>
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
                            <td class="text-muted"><?php echo $row['product_id']; ?></td>
                            <td><?php echo $row['category_name']; ?></td>
                            <td><span class="text-muted"><?php echo $row['sub_category_name']; ?></span></td>
                            <td><strong><?php echo $row['product_name']; ?></strong></td>
                            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo $row['description']; ?>">
                                <?php echo $row['description']; ?>
                            </td>
                            <td class="price-tag">₹<?php echo $row['master_price']; ?></td>
                            <td class="selling-price">₹<?php echo $row['selling_price']; ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td><span class="text-muted"><?php echo $row['unit']; ?></span></td>
                            <td>
                                <img class="product-img" src="../images/<?php echo $row['image']; ?>" width="50" height="50" alt="Product Image">
                            </td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="action-link edit-link">Edit</a>
                                <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="action-link delete-link" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
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