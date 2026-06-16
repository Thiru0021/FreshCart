<?php
session_start();

// Connect to the core database
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Initialize our tracking flag
$script_action = "";

if (isset($_POST['register_submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['c_name']);
    $email = mysqli_real_escape_string($conn, $_POST['c_email']);
    $phone = mysqli_real_escape_string($conn, $_POST['c_phone']);
    $password = $_POST['c_password'];

    // 1. Check if email already exists
    $check_email = mysqli_query($conn, "SELECT * FROM customers WHERE email = '$email'");
    
    if (mysqli_num_rows($check_email) > 0) {
        $script_action = "email_exists";
    } else {
        // 2. Encrypt password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // 3. CREATE: Insert customer profile
        $query = "INSERT INTO customers (name, email, phone, password) 
                  VALUES ('$name', '$email', '$phone', '$hashed_password')";
        
        if (mysqli_query($conn, $query)) {
            $script_action = "success";
        } else {
            $script_action = "failed";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration - FreshCart</title>
    <link rel="stylesheet" href="shop.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .auth-container { max-width: 450px; margin: 60px auto; padding: 30px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
        .auth-title { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 8px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; color: #334155; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; transition: border 0.2s; }
        .form-control:focus { border-color: #10b981; }
    </style>
</head>
<body class="shop-body">

    <div class="auth-container">
        <h2 class="auth-title">Create an Account</h2>
        <p style="color: #64748b; text-align: center; margin-bottom: 24px;">Join FreshCart to start ordering fresh items.</p>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="c_name" class="form-control" placeholder="Enter your full name" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="c_email" class="form-control" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="c_phone" class="form-control" placeholder="Enter mobile number" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="c_password" class="form-control" placeholder="Create a strong password" required>
            </div>
            <button type="submit" name="register_submit" class="shop-buy-btn" style="padding:14px; font-size:16px; width: 100%; cursor: pointer;">Sign Up</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; font-size: 14px; color: #64748b;">
            Already have an account? <a href="login.php" style="color: #10b981; font-weight: 600; text-decoration: none;">Login here</a>
        </p>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var action = "<?php echo $script_action; ?>";
        
        if (action === "") return; // If form wasn't submitted, do nothing

        // Check if SweetAlert2 loaded correctly from CDN
        if (typeof Swal !== 'undefined') {
            if (action === "success") {
                Swal.fire({
                    title: "🎉 Registration Successful!",
                    text: "Your account has been created. Redirecting to login...",
                    icon: "success",
                    confirmButtonColor: "#10b981",
                    timer: 2500,
                    timerProgressBar: true
                }).then(function() {
                    window.location.href = "login.php";
                });
            } else if (action === "email_exists") {
                Swal.fire({
                    title: "Account Exists",
                    text: "This email address is already registered!",
                    icon: "warning",
                    confirmButtonColor: "#ef4444"
                });
            } else if (action === "failed") {
                Swal.fire({
                    title: "Error",
                    text: "Registration failed. Database error.",
                    icon: "error",
                    confirmButtonColor: "#ef4444"
                });
            }
        } 
        // FAILSAFE: If offline or CDN fails, use browser native alerts so page never freezes
        else {
            if (action === "success") {
                alert("Registration Successful! Redirecting to login...");
                window.location.href = "login.php";
            } else if (action === "email_exists") {
                alert("This email address is already registered!");
            } else if (action === "failed") {
                alert("Registration failed due to a database error.");
            }
        }
    });
    </script>

</body>
</html>