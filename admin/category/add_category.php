<?php
include("../auth_guard.php");

$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if(!$conn)
{
    die("Connection Failed");
}

if(isset($_POST['submit']))
{
    $result = mysqli_query(
        $conn,
        "SELECT MAX(id) as max_id FROM category"
    );

    $row = mysqli_fetch_assoc($result);

    $next = ($row['max_id'] !== null) ? $row['max_id'] + 1 : 1;

    $category_id = "CAT" . str_pad($next, 3, "0", STR_PAD_LEFT);

    $category_name = $_POST['category_name'];

    $image_name = $_FILES['image']['name'];
    $temp_name = $_FILES['image']['tmp_name'];

    move_uploaded_file(
        $temp_name,
        "../images/" . $image_name
    );

    $sql = "INSERT INTO category
            (
                category_id,
                category_name,
                image
            )
            VALUES
            (
                '$category_id',
                '$category_name',
                '$image_name'
            )";

    mysqli_query($conn,$sql);

    header("Location:view_category.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - Grocery Admin</title>
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
                <h1>Add New Category</h1>
                <a href="view_category.php" class="back-btn">&larr; Back to List</a>
            </div>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="category_name">Category Name</label>
                        <input type="text" id="category_name" name="category_name" placeholder="e.g. Vegetables, Dairy, Bakery" required>
                    </div>

                    <div class="form-group">
                        <label for="image">Category Image File</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>

                    <input type="submit" name="submit" value="Save Category" class="submit-btn">

                </form>
            </div>

        </div> </div> </div> </body>
</html>