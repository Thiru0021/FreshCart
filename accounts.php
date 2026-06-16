<?php
session_start();

// Connect to the database
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Security Barrier: Redirect to login if a guest tries to access this page
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = intval($_SESSION['customer_id']);
$success_msg = "";
$error_msg = "";

// --- UPDATE PROCESSOR ---
if (isset($_POST['save_account_settings'])) {
    $name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $address = mysqli_real_escape_string($conn, $_POST['customer_address']);

    // Matches your exact database columns: phone and address
    $update_sql = "UPDATE customers 
                   SET name = '$name', 
                       phone = '$phone', 
                       address = '$address' 
                   WHERE customer_id = $customer_id";

    if (mysqli_query($conn, $update_sql)) {
        // Sync sessions immediately for checkout.php to pick up
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_address'] = $address;
        $success_msg = "Account profile details updated successfully!";
    } else {
        $error_msg = "Database error: " . mysqli_error($conn);
    }
}

// --- READ LAYER ---
$profile_query = mysqli_query($conn, "SELECT * FROM customers WHERE customer_id = $customer_id");
$customer_data = mysqli_fetch_assoc($profile_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - FreshCart</title>
    <link rel="stylesheet" href="shop.css?v=1.2">
    <style>
        .account-layout-card { max-width: 550px; margin: 40px auto; background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 35px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
        .account-form-group { margin-bottom: 20px; }
        .account-form-group label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; color: #334155; }
        .account-input-ctrl { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; outline: none; }
        .account-input-ctrl:focus { border-color: #10b981; }
        .account-readonly { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; }
        .status-alert { padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 20px; text-align: center; }
        .status-alert.success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .status-alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    </style>
</head>
<body class="shop-body">

    <nav class="shop-navbar">
        <a href="shop.php" class="brand-logo">Fresh<span>Cart</span></a>
        <div class="nav-right-group">
            <a href="shop.php" class="cart-widget" style="background-color: #f1f5f9; text-decoration: none; font-weight: 600;">
                &larr; Back to Shop
            </a>
        </div>
    </nav>

    <main class="shop-main">
        <div class="account-layout-card">
            <h2 style="margin-top: 0; color: #0f172a; font-weight: 800;">👤 My Profile Dashboard</h2>
            <p style="color: #64748b; font-size: 14px; margin-bottom: 25px;">Manage your account details. Saving your info here secures your automatic read-only billing fields on checkout.</p>

            <?php if (!empty($success_msg)): ?>
                <div class="status-alert success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="status-alert error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form action="accounts.php" method="POST">
                <div class="account-form-group">
                    <label>Email Address</label>
                    <input type="email" class="account-input-ctrl account-readonly" value="<?php echo htmlspecialchars($customer_data['email']); ?>" readonly>
                </div>

                <div class="account-form-group">
                    <label>Full Name</label>
                    <input type="text" name="customer_name" class="account-input-ctrl" value="<?php echo htmlspecialchars($customer_data['name']); ?>" required>
                </div>

                <div class="account-form-group">
                    <label>Mobile Number</label>
                    <input type="tel" name="customer_phone" class="account-input-ctrl" value="<?php echo htmlspecialchars($customer_data['phone'] ?? ''); ?>" placeholder="Enter mobile number" required>
                </div>

                <div class="account-form-group">
                    <label>Permanent Address</label>
                    <textarea name="customer_address" class="account-input-ctrl" style="height: 100px; resize: none;" placeholder="Enter your full shipping address details" required><?php echo htmlspecialchars($customer_data['address'] ?? ''); ?></textarea>
                </div>

                <button type="submit" name="save_account_settings" class="shop-buy-btn" style="width: 100%; padding: 14px; font-size: 16px; border: none; font-weight: 700; border-radius: 8px; cursor: pointer; background-color: #10b981; color: white;">
                    Save Profile Settings
                </button>
            </form>
        </div>
    </main>

</body>
</html>