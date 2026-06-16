<?php
session_start();

// 1. Terminate only the active customer-specific profile session nodes
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['cart']); // Clear basket arrays out of memory clean on logout
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signing Out... - FreshCart</title>
    <link rel="stylesheet" href="shop.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background-color: #f8fafc;">

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Verify if SweetAlert2 library compiled successfully over active networks
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: "🚪 Signed Out Successfully",
                text: "Your shopping profile sessions have been cleared down safely. See you soon!",
                icon: "success",
                confirmButtonColor: "#10b981",
                timer: 2000,
                timerProgressBar: true,
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(function() {
                // Relocate layout focus to store catalog after modal timer complete countdown loop
                window.location.href = "shop.php";
            });
        } 
        // Failsafe backup redundancy option: Fired if offline or CDN tracking errors strike
        else {
            alert("Signed out successfully!");
            window.location.href = "shop.php";
        }
    });
    </script>

</body>
</html>