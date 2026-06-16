<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = intval($_SESSION['customer_id']);
$success_msg = "";
$error_msg = "";

// 1. UPDATE: Save changed details back to the database row
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $update_query = "UPDATE customers 
                    SET name = '$name', phone = '$phone', address = '$address' 
                    WHERE customer_id = $customer_id";

    if (mysqli_query($conn, $update_query)) {
        // Sync active session variables instantly
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_address'] = $address;
        $success_msg = "Profile configurations updated successfully!";
    } else {
        $error_msg = "Database update error: " . mysqli_error($conn);
    }
}

// 2. READ: Fetch the freshest current profile row data to display inside input fields
$profile_query = mysqli_query($conn, "SELECT * FROM customers WHERE customer_id = $customer_id");
$customer = mysqli_fetch_assoc($profile_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings - FreshCart</title>
    <link rel="stylesheet" href="shop.css">
    <style>
        .profile-container { max-width: 500px; margin: 60px auto; background: white; padding: 35px; border-radius: 12px; border: 1px solid #e2e8f0; }
        .form-block { margin-bottom: 25px; }
        .form-block label { display: block; font-weight: 600; margin-bottom: 8px; color: #334155; font-size: 14px; }
        .input-box { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        .msg-banner { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; font-weight: 700; text-align: center; }
        .msg-banner.success { background: #dcfce7; color: #15803d; }
    </style>
</head>
<body class="shop-body">

<nav class="shop-navbar">
    <a href="shop.php" class="brand-logo">Fresh<span>Cart</span></a>
    <a href="shop.php" class="cart-widget" style="background-color: #f1f5f9; text-decoration: none; font-weight: 600;">&larr; Return to Store</a>
</nav>

<main class="shop-main">
    <div class="profile-container">
        <h2 style="margin-top:0; color:#0f172a;">⚙️ Account Settings Dashboard</h2>
        <p style="color:#64748b; font-size:14px; margin-bottom:25px;">Update your permanent coordinates. These metrics will form your read-only default checkout manifests.</p>

        <?php if(!empty($success_msg)): ?>
            <div class="msg-banner success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <form action="edit_profile.php" method="POST">
            <div class="form-block">
                <label>Account Bound Email (Fixed ID)</label>
                <input type="email" class="input-box" value="<?php echo htmlspecialchars($customer['email']); ?>" readonly style="background:#f1f5f9; color:#64748b; cursor:not-allowed;">
            </div>
            <div class="form-block">
                <label>Full Display Name</label>
                <input type="text" name="name" class="input-box" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
            </div>
            <div class="form-block">
                <label>Permanent Contact Mobile Number</label>
                <input type="tel" name="phone" class="input-box" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" placeholder="Enter 10-digit mobile number" required>
            </div>
            <div class="form-block">
                <label>Default Permanent Billing Address</label>
                <textarea name="address" class="input-box" style="height:100px; resize:none;" placeholder="Enter your full home/delivery address street details" required><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
            </div>
            <button type="submit" name="update_profile" class="shop-buy-btn" style="width:100%; padding:14px; font-size:15px; border:none; cursor:pointer; background:#10b981; color:white; border-radius:8px; font-weight:600;">Save Account Changes</button>
        </form>
    </div>
</main>

</body>
</html>