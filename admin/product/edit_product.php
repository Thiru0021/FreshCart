<?php

include("../auth_guard.php");

include("../db.php");

if(!isset($_GET['id'])) {
    header("Location:view_product.php");
    exit();
}
$id = $_GET['id'];

// 1. Fetch the product details to update
$product_query = mysqli_query($conn, "SELECT * FROM product WHERE id=$id");
$product = mysqli_fetch_assoc($product_query);

// 2. Fetch all categories
$category_result = mysqli_query($conn, "SELECT * FROM category");

// 3. Fetch ALL subcategories once so JavaScript can handle the live filtering smoothly
$subcategory_result = mysqli_query($conn, "SELECT * FROM sub_category");
$subcategories = [];
while ($sub = mysqli_fetch_assoc($subcategory_result)) {
    $subcategories[] = $sub;
}

// 4. Process the update submission
if(isset($_POST['update']))
{
    $category_id = $_POST['category_id'];
    $sub_category_id = $_POST['sub_category_id'];
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $master_price = $_POST['master_price'];
    $selling_price = $_POST['selling_price'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];

    $image_name = $_FILES['image']['name'];

    if(!empty($image_name)) {
        $temp_name = $_FILES['image']['tmp_name'];
        move_uploaded_file($temp_name, "../images/" . $image_name);
    } else {
        $image_name = $product['image'];
    }

    mysqli_query(
        $conn,
        "UPDATE product 
         SET 
            category_id='$category_id',
            sub_category_id='$sub_category_id',
            product_name='$product_name',
            description='$description',
            master_price='$master_price',
            selling_price='$selling_price',
            quantity='$quantity',
            unit='$unit',
            image='$image_name'
         WHERE id=$id"
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
    <title>Edit Product - Grocery Admin</title>

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

        /* Sidebar Styling */
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

        /* Main Window Workspace */
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

        /* Grid-based Input Panel */
        .form-container {
            background: #ffffff;
            max-width: 800px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #34495e;
            font-size: 14px;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size: 15px;
            background-color: #fff;
            font-family: inherit;
            transition: border-color 0.2s ease;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
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

        /* Update Action Button */
        .update-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.2s ease;
            display: inline-block;
            margin-top: 10px;
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
        <h1>Edit Product Details</h1>
        <a href="view_product.php" class="back-btn">&larr; Cancel</a>
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
                            $selected = ($product['category_id'] == $cat['category_id']) ? "selected" : "";
                            echo "<option value='{$cat['category_id']}' $selected>{$cat['category_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sub_category_id">Sub Category</label>
                    <select id="sub_category_id" name="sub_category_id" required>
                        <option value="">Select Sub Category</option>
                        </select>
                </div>

                <div class="form-group full-width">
                    <label for="product_name">Product Name</label>
                    <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                </div>

                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="master_price">Master Price (Cost Price)</label>
                    <input type="number" step="0.01" id="master_price" name="master_price" value="<?php echo $product['master_price']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="selling_price">Selling Price (Retail Price)</label>
                    <input type="number" step="0.01" id="selling_price" name="selling_price" value="<?php echo $product['selling_price']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="quantity">Available Stock Quantity</label>
                    <input type="number" step="0.01" id="quantity" name="quantity" value="<?php echo $product['quantity']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="unit">Measurement Unit</label>
                    <select id="unit" name="unit">
                        <?php
                        $units = ["KG", "Gram", "Litre", "ML", "Packet", "Piece", "Dozen"];
                        foreach($units as $u) {
                            $selected = ($product['unit'] == $u) ? "selected" : "";
                            echo "<option value='$u' $selected>$u</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Current Item Image</label>
                    <div class="current-image-box">
                        <img src="../images/<?php echo $product['image']; ?>" width="80" height="80" alt="Product Image">
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Replace Image (Leave empty to retain current)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

            </div>

            <input type="submit" name="update" value="Update Product Details" class="update-btn">
        </form>
    </div>

</div>

<script>
// Convert the PHP subcategories array safely into native JavaScript object arrays
const allSubcategories = <?php echo json_encode($subcategories); ?>;

// Grab our HTML dropdown handles
const categorySelect = document.getElementById('category_id');
const subcategorySelect = document.getElementById('sub_category_id');

// Save the database subcategory value to select it automatically on page load
const currentSavedSubCategory = "<?php echo $product['sub_category_id']; ?>";

function filterSubcategories() {
    const selectedCategoryVal = categorySelect.value;
    
    // Clear out current subcategory selections
    subcategorySelect.innerHTML = '<option value="">Select Sub Category</option>';
    
    if(selectedCategoryVal === "") return;

    // Filter subcategories matching the selected category value
    const matchedSubs = allSubcategories.filter(sub => sub.category_id === selectedCategoryVal);

    if (matchedSubs.length > 0) {
        matchedSubs.forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.sub_category_id;
            option.textContent = sub.sub_category_name;
            
            // Auto-check if this is the product's saved subcategory
            if(sub.sub_category_id === currentSavedSubCategory) {
                option.selected = true;
            }
            subcategorySelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = "";
        option.disabled = true;
        option.textContent = "No subcategories found";
        subcategorySelect.appendChild(option);
    }
}

// Bind event listener to handle changes live
categorySelect.addEventListener('change', filterSubcategories);

// Run once instantly on page load to set up default values
filterSubcategories();
</script>

</body>
</html>