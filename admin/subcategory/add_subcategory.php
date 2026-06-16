<?php

include("../auth_guard.php");

include("../db.php");

$category_result = mysqli_query(
    $conn,
    "SELECT * FROM category"
);

if(isset($_POST['submit']))
{
    // FIX: Changed from COUNT(*) to MAX(id) to prevent duplicate key errors
    $result = mysqli_query(
        $conn,
        "SELECT MAX(id) as max_id FROM sub_category"
    );

    $row = mysqli_fetch_assoc($result);

    // If the table is completely empty, start at 1
    $next = ($row['max_id'] !== null) ? $row['max_id'] + 1 : 1;

    $sub_category_id = "SUBC" . str_pad($next, 3, "0", STR_PAD_LEFT);

    $category_id = $_POST['category_id'];
    $sub_category_name = $_POST['sub_category_name'];

    // FIXED BUG: File upload logic added here to replace $_POST['image']
    $image_name = $_FILES['image']['name'];
    $temp_name = $_FILES['image']['tmp_name'];

    move_uploaded_file(
        $temp_name,
        "../images/" . $image_name
    );

    mysqli_query(
        $conn,
        "INSERT INTO sub_category
        (
            sub_category_id,
            category_id,
            sub_category_name,
            image
        )
        VALUES
        (
            '$sub_category_id',
            '$category_id',
            '$sub_category_name',
            '$image_name'
        )"
    );

    header("Location:view_subcategory.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sub Category - Grocery Admin</title>
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
                <h1>Add Sub Category</h1>
                <a href="view_subcategory.php" class="back-btn">&larr; Back to List</a>
            </div>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="category_id">Parent Category</label>
                        <select id="category_id" name="category_id" required>
                            <option value="" disabled selected>Select a Category</option>
                            <?php
                            while($cat = mysqli_fetch_assoc($category_result))
                            {
                            ?>
                                <option value="<?php echo $cat['category_id']; ?>">
                                    <?php echo $cat['category_name']; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sub_category_name">Sub Category Name</label>
                        <input type="text" id="sub_category_name" name="sub_category_name" placeholder="e.g. Whole Milk, Cheddar Cheese, Apples" required>
                    </div>

                    <div class="form-group">
                        <label for="image">Sub Category Image File</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>

                    <input type="submit" name="submit" value="Save Sub Category" class="submit-btn">

                </form>
            </div>

        </div> </div> </div> </body>
</html>