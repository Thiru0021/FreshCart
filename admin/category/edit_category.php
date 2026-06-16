<?php
include("../auth_guard.php");

include("../db.php");

$id = $_GET['id'];

$result = mysqli_query(
    $conn,
    "SELECT * FROM category WHERE id=$id"
);

$row = mysqli_fetch_assoc($result);

if(isset($_POST['update']))
{
    $category_name = $_POST['category_name'];

    $image_name = $_FILES['image']['name'];

    if(!empty($image_name))
    {
        $temp_name = $_FILES['image']['tmp_name'];

        move_uploaded_file(
            $temp_name,
            "../images/" . $image_name
        );
    }
    else
    {
        $image_name = $row['image'];
    }

    mysqli_query(
        $conn,
        "UPDATE category
         SET
         category_name='$category_name',
         image='$image_name'
         WHERE id=$id"
    );

    header("Location:view_category.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - Grocery Admin</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling Layout */
        .sidebar {
            width: 260px;
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            border-bottom: 1px solid #34495e;
            padding-bottom: 15px;
        }

        .sidebar a {
            color: #bdc3c7;
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: #34495e;
            color: #ffffff;
        }

        /* Main Workspace Content Window */
        .main-content {
            flex: 1;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            color: #2c3e50;
        }

        /* Gray Escape Button Utility */
        .back-btn {
            text-decoration: none;
            background-color: #7f8c8d;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.2s ease;
        }

        .back-btn:hover {
            background-color: #95a5a6;
        }

        /* Modern Container Card for Forms */
        .form-container {
            background: #ffffff;
            max-width: 600px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #34495e;
            font-size: 14px;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size: 15px;
            transition: border-color 0.2s ease;
        }

        .form-group input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
        }

        /* Current Image Display Box */
        .current-image-box {
            display: inline-block;
            margin-top: 5px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
        }

        .current-image-box img {
            display: block;
            object-fit: cover;
            border-radius: 4px;
        }

        .form-group input[type="file"] {
            display: block;
            width: 100%;
            padding: 8px 0;
            font-size: 14px;
            margin-top: 5px;
        }

        /* Primary Execution Button Accent (Orange/Blue split representation for updates) */
        .update-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .update-btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

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

<div class="main-content">
    
    <div class="header">
        <h1>Edit Category</h1>
        <a href="view_category.php" class="back-btn">&larr; Cancel</a>
    </div>

    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label for="category_name">Category Name</label>
                <input type="text" id="category_name" name="category_name" value="<?php echo $row['category_name']; ?>" required>
            </div>

            <div class="form-group">
                <label>Current Saved Image</label>
                <div class="current-image-box">
                    <img src="../images/<?php echo $row['image']; ?>" width="80" height="80" alt="Current Image">
                </div>
            </div>

            <div class="form-group">
                <label for="image">Replace Image (Leave empty to keep current)</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <input type="submit" name="update" value="Update Category" class="update-btn">

        </form>
    </div>

</div>

</body>
</html>