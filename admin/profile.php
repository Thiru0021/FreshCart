<?php
// 1. Initialize session monitoring arrays safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Clear browser snapshot memory cache instantly (Fixes Back Button bypass)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 3. Process Logout Query String Parameter Route Action
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    
    // Clear out active session values array records
    $_SESSION = array();

    // Kill the browser session identification tracker token cookie entirely
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Erase the server session register instance
    session_destroy();

    // Route the administrative terminal cleanly out to the login screen
    header("Location: login.php");
    exit();
}

// 4. Load your standard core security gate guard rule file
include("auth_guard.php");

// ... Rest of your profile database fetch calculations logic ...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Grocery Admin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<div class="app-container">

    <div class="sidebar">
        <h2>Grocery Admin</h2>
        <a href="Index.php">Dashboard</a>
        <a href="category/view_category.php">Categories</a>
        <a href="subcategory/view_subcategory.php">Sub Categories</a>
        <a href="product/view_product.php">Products</a>
        <a href="#">Orders</a>
        <a href="view_customers.php">Customers</a> 
        <a href="settings.php" class="active">Settings</a>
    </div>

    <div class="main-wrapper">
        
        <header class="top-navbar">
            <div class="profile-container" onclick="window.location.href='profile.php';">
                <div class="profile-info">
                    <span class="greeting">Signed in as</span>
                    <span class="user-name"><?php echo $_SESSION['admin_name']; ?></span>
                </div>
                <div class="profile-avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?></div>
            </div>
        </header>

        <div class="main-content">
            
            <div class="header">
                <h1>Admin Account Details</h1>
                <a href="profile.php?action=logout" class="back-btn" style="background-color: #c0392b;" onclick="return confirm('Confirm secure sign out?');">Sign Out / Log Out</a>
            </div>

            <div class="form-container" style="max-width: 650px;">
                <div style="display: flex; align-items: center; gap: 25px; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eaeaea;">
                    <div class="profile-avatar" style="width: 80px; height: 80px; font-size: 32px; border-width: 4px; box-shadow: 0 0 0 3px #3498db;">
                        <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <h2 style="color: #2c3e50; font-size: 22px;"><?php echo $_SESSION['admin_name']; ?></h2>
                        <span class="text-muted" style="text-transform: uppercase; font-size: 12px; font-weight: bold; letter-spacing: 1px; color:#2ecc71;">Master System Administrator</span>
                    </div>
                </div>

                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label style="color: #7f8c8d; font-size: 12px; text-transform: uppercase;">System Login Username</label>
                        <input type="text" value="<?php echo $_SESSION['admin_username']; ?>" readonly style="background-color: #f8f9fa; color: #7f8c8d; cursor: not-allowed; font-weight: 600;">
                    </div>

                    <div class="form-group">
                        <label style="color: #7f8c8d; font-size: 12px; text-transform: uppercase;">Registered Email Address</label>
                        <input type="text" value="<?php echo $_SESSION['admin_email']; ?>" readonly style="background-color: #f8f9fa; color: #7f8c8d; cursor: not-allowed; font-weight: 600;">
                    </div>
                    
                    <div class="form-group">
                        <label style="color: #7f8c8d; font-size: 12px; text-transform: uppercase;">Account Security Authorization</label>
                        <div style="padding: 12px 14px; background-color: #e8f8f5; border: 1px solid #a3e4d7; color: #16a085; font-weight: bold; border-radius: 5px; font-size: 14px;">
                            ✓ Full Read/Write Database Access Granted
                        </div>
                    </div>
                </div>
            </div>

        </div> </div> </div> </body>
</html>