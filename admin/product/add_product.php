<?php

include("../auth_guard.php");

include("../db.php");

// 1. Fetch all parent categories for the select element options dropdown
$category_result = mysqli_query($conn, "SELECT * FROM category");

// 2. Fetch ALL subcategories to compile into a client-side JavaScript configuration cache
$subcategory_result = mysqli_query($conn, "SELECT * FROM sub_category");
$subcategories = [];
while ($sub = mysqli_fetch_assoc($subcategory_result)) {
    $subcategories[] = $sub;
}

// 3. Process Product Submission
if(isset($_POST['submit']))
{
    $result = mysqli_query($conn, "SELECT MAX(id) as max_id FROM product");
    $row = mysqli_fetch_assoc($result);
    $next = ($row['max_id'] !== null) ? $row['max_id'] + 1 : 1;

    $product_id = "PRD" . str_pad($next, 3, "0", STR_PAD_LEFT);

    $category_id = $_POST['category_id'];
    $sub_category_id = $_POST['sub_category_id'];
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $master_price = $_POST['master_price'];
    $selling_price = $_POST['selling_price'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];

    $image_name = $_FILES['image']['name'];
    $temp_name = $_FILES['image']['tmp_name'];

    move_uploaded_file($temp_name, "../images/" . $image_name);

    mysqli_query(
        $conn,
        "INSERT INTO product
        (
            product_id,
            category_id,
            sub_category_id,
            product_name,
            description,
            master_price,
            selling_price,
            quantity,
            unit,
            image
        )
        VALUES
        (
            '$product_id',
            '$category_id',
            '$sub_category_id',
            '$product_name',
            '$description',
            '$master_price',
            '$selling_price',
            '$quantity',
            '$unit',
            '$image_name'
        )"
    );

    header("Location:view_product.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Grocery Admin</title>
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
                <h1>Add New Product</h1>
                <a href="view_product.php" class="back-btn">&larr; Back to List</a>
            </div>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">

                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php
                                while($cat = mysqli_fetch_assoc($category_result)) {
                                    echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="sub_category_id">Sub Category</label>
                            <select id="sub_category_id" name="sub_category_id" required>
                                <option value="">Select Category First</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="product_name">Product Name</label>
                            <input type="text" id="product_name" name="product_name" placeholder="e.g. Basmati Rice Premium" required>
                        </div>

                        <div class="form-group full-width">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" placeholder="Provide product details..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="master_price">Master Price (Cost Price)</label>
                            <input type="number" step="0.01" id="master_price" name="master_price" placeholder="0.00" required>
                        </div>

                        <div class="form-group">
                            <label for="selling_price">Selling Price (Retail Price)</label>
                            <input type="number" step="0.01" id="selling_price" name="selling_price" placeholder="0.00" required>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Initial Stock Quantity</label>
                            <input type="number" step="0.01" id="quantity" name="quantity" placeholder="0.00" required>
                        </div>

                        <div class="form-group">
                            <label for="unit">Measurement Unit</label>
                            <select id="unit" name="unit">
                                <option value="KG">KG</option>
                                <option value="Gram">Gram</option>
                                <option value="Litre">Litre</option>
                                <option value="ML">ML</option>
                                <option value="Packet">Packet</option>
                                <option value="Piece">Piece</option>
                                <option value="Dozen">Dozen</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="image">Product Image File</label>
                            <input type="file" id="image" name="image" accept="image/*" required>
                        </div>

                    </div>

                    <input type="submit" name="submit" value="Add Product" class="submit-btn">
                </form>
            </div>

        </div> </div> </div> <script>
// Safely transfer your database rows from PHP arrays directly to Client-Side Javascript Objects
const allSubcategories = <?php echo json_encode($subcategories); ?>;

const categorySelect = document.getElementById('category_id');
const subcategorySelect = document.getElementById('sub_category_id');

categorySelect.addEventListener('change', function() {
    const selectedCategoryVal = this.value;
    
    // Clear out current items inside the dropdown selection frame
    subcategorySelect.innerHTML = '<option value="">Select Sub Category</option>';
    
    if(selectedCategoryVal === "") {
        subcategorySelect.innerHTML = '<option value="">Select Category First</option>';
        return;
    }

    // Match criteria rules loop arrays filter tracking
    const matchedSubs = allSubcategories.filter(sub => sub.category_id === selectedCategoryVal);

    if (matchedSubs.length > 0) {
        matchedSubs.forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.sub_category_id;
            option.textContent = sub.sub_category_name;
            subcategorySelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = "";
        option.disabled = true;
        option.textContent = "No subcategories found";
        subcategorySelect.appendChild(option);
    }
});
</script>

</body>
</html>