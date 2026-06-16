<?php
session_start();

// Connect directly to your core grocery database
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

$error_message = "";

// If the admin is already signed in, bypass the login screen instantly
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: Index.php");
    exit();
}

if (isset($_POST['login_btn'])) {
    // Escape string values to protect database queries against SQL Injection attempts
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Assuming you have an 'admin' table with columns: username, password, email, name, avatar
    $query = "SELECT * FROM admin WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $admin_data = mysqli_fetch_assoc($result);
        
        // Simple plain-text password match check (For production, upgrade to password_hash / password_verify)
        if ($password === $admin_data['password']) {
            
            // Populate your secure global session storage keys
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin_data['id'];
            $_SESSION['admin_username']  = $admin_data['username'];
            $_SESSION['admin_name']      = $admin_data['name'];
            $_SESSION['admin_email']     = $admin_data['email'];
            $_SESSION['admin_role']      = $admin_data['role'];
            
            header("Location: Index.php");
            exit();
        } else {
            $error_message = "Invalid password entry!";
        }
    } else {
        $error_message = "Admin account username not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Grocery Shop</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
    *{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(
        135deg,
        #0f172a,
        #1e293b,
        #334155
    );
}

.login-card{
    width:420px;
    background:#ffffff;
    padding:40px;
    border-radius:20px;
    box-shadow:
    0 20px 40px rgba(0,0,0,0.25);
}

.logo{
    text-align:center;
    margin-bottom:25px;
}

.logo h1{
    color:#16a34a;
    font-size:32px;
    font-weight:700;
}

.logo p{
    color:#64748b;
    font-size:14px;
    margin-top:5px;
}

.login-card h2{
    text-align:center;
    margin-bottom:25px;
    color:#1e293b;
}

.form-group{
    margin-bottom:20px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-size:14px;
    font-weight:600;
    color:#334155;
}

.form-group input{
    width:100%;
    padding:14px;
    border:1px solid #d1d5db;
    border-radius:10px;
    font-size:15px;
    outline:none;
    transition:0.3s;
}

.form-group input:focus{
    border-color:#16a34a;
    box-shadow:
    0 0 0 4px rgba(22,163,74,0.15);
}

.submit-btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:#16a34a;
    color:white;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

.submit-btn:hover{
    background:#15803d;
}

.alert-danger{
    background:#fee2e2;
    color:#b91c1c;
    padding:12px;
    border-radius:10px;
    margin-bottom:20px;
    font-size:14px;
}

.footer-text{
    text-align:center;
    margin-top:20px;
    color:#64748b;
    font-size:13px;
}

@media(max-width:500px){

    .login-card{
        width:90%;
        padding:30px;
    }

    

}
</style>
</head>
<body class="login-body">

<div class="login-card">

<div class="logo">
    <a href="shop.php" class="brand-logo"> 🛒 Fresh<span>Cart</span></a>
    <p>Inventory & Product Management System</p>
</div>

<?php if(!empty($error_message)): ?>
    <div class="alert-danger">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<h2>Admin Login</h2>

<form method="POST">

    <div class="form-group">
        <label>Username</label>
        <input
            type="text"
            name="username"
            placeholder="Enter Username"
            required>
    </div>

    <div class="form-group">
        <label>Password</label>
        <input
            type="password"
            name="password"
            placeholder="Enter Password"
            required>
    </div>

    <button
        type="submit"
        name="login_btn"
        class="submit-btn">
        Login
    </button>

</form>

</div>


</body>
</html>