<?php
include("auth_guard.php");

// Connect to database
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Block unauthorized guests immediately if they are not a super_admin
if ($_SESSION['admin_role'] !== 'super_admin') {
    $script_action = "access_denied";
} else {
    $script_action = "";
}

// Variables to hold editing state inputs dynamically
$edit_mode = false;
$edit_id = "";
$form_name = "";
$form_email = "";
$form_username = "";
$form_role = "";

// --- MUTATION OPERATION 1: Handle Staff Deletion (DELETE) ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Prevent self-deletion safety block rule
    if ($delete_id == $_SESSION['admin_id']) {
        $script_action = "self_delete_block";
    } else {
        $delete_query = "DELETE FROM admin WHERE id = $delete_id";
        if (mysqli_query($conn, $delete_query)) {
            header("Location: settings.php?msg=deleted");
            exit();
        }
    }
}

// --- MUTATION OPERATION 2: Populate Form for Editing (READ SINGLE) ---
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_mode = true;
    
    $fetch_staff = mysqli_query($conn, "SELECT * FROM admin WHERE id = $edit_id");
    if ($staff_meta = mysqli_fetch_assoc($fetch_staff)) {
        $form_name = $staff_meta['name'];
        $form_email = $staff_meta['email'];
        $form_username = $staff_meta['username'];
        $form_role = $staff_meta['role'];
    }
}

// --- MUTATION OPERATION 3: Handle Insert or Update Form Form submissions ---
if (isset($_POST['save_staff'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = $_POST['password']; // Stored plain text matching your backend template structure

    if (isset($_POST['is_edit_mode']) && $_POST['is_edit_mode'] == '1') {
        // UPDATE MODE PROCESSOR
        $target_id = intval($_POST['target_staff_id']);
        
        if (!empty($password)) {
            // Update details including a fresh modified password entry string
            $update_query = "UPDATE admin SET name='$name', email='$email', username='$username', password='$password', role='$role' WHERE id=$target_id";
        } else {
            // Update details leaving current existing password value untouched
            $update_query = "UPDATE admin SET name='$name', email='$email', username='$username', role='$role' WHERE id=$target_id";
        }
        
        if (mysqli_query($conn, $update_query)) {
            $script_action = "update_success";
        } else {
            $script_action = "error";
        }
    } else {
        // INSERT NEW STAFF MODE PROCESSOR
        $check_user = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");
        if (mysqli_num_rows($check_user) > 0) {
            $script_action = "username_exists";
        } else {
            $insert_query = "INSERT INTO admin (username, password, name, email, role) VALUES ('$username', '$password', '$name', '$email', '$role')";
            if (mysqli_query($conn, $insert_query)) {
                $script_action = "insert_success";
            } else {
                $script_action = "error";
            }
        }
    }
}

// Fetch all staff accounts currently inside the database grid layout (READ ALL)
$all_staff_result = mysqli_query($conn, "SELECT * FROM admin ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Administration Control Center</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .management-flex { display: flex; gap: 30px; margin-top: 20px; align-items: flex-start; }
        .form-side { flex: 1; background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; }
        .table-side { flex: 2; background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        .form-group-block { margin-bottom: 16px; }
        .form-group-block label { display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px; }
        .form-group-block input, .form-group-block select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; }
        .denied-card { background: #fff; padding: 40px; text-align: center; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 500px; margin: 40px auto; }
        .action-link { text-decoration: none; font-weight: 600; font-size: 13px; padding: 6px 12px; border-radius: 6px; display: inline-block; }
        .edit-lnk { background: #f0fdf4; color: #16a34a; margin-right: 6px; }
        .del-lnk { background: #fef2f2; color: #dc2626; }
        .role-badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .role-super { background-color: #fef3c7; color: #d97706; }
        .role-staff { background-color: #e0f2fe; color: #0284c7; }
    </style>
</head>
<body>

<div class="app-container">

    <div class="sidebar">
        <h2>Grocery Admin</h2>
        <a href="/grocery-shop/admin/Index.php">Dashboard</a>
        <a href="/grocery-shop/admin/category/view_category.php">Categories</a>
        <a href="/grocery-shop/admin/subcategory/view_subcategory.php">Sub Categories</a>
        <a href="/grocery-shop/admin/product/view_product.php">Products</a>
        <a href="/grocery-shop/admin/orders/view_orders.php">Orders</a>
        <a href="/grocery-shop/admin/view_customers.php">Customers</a>
        <a href="/grocery-shop/admin/settings.php" class="active">Settings</a>
    </div>

    <div class="main-wrapper">

        <header class="top-navbar">
            <div class="profile-container" onclick="window.location.href='profile.php';">
                <div class="profile-info">
                    <span class="greeting">Role: <?php echo htmlspecialchars($_SESSION['admin_role']); ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                </div>
                <div class="profile-avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?></div>
            </div>
        </header>

        <div class="main-content">
            <div class="header">
                <h1>System Security & Staff Settings</h1>
            </div>

            <?php if ($_SESSION['admin_role'] !== 'super_admin'): ?>
                <div class="denied-card">
                    <h2 style="color: #c0392b; margin-bottom: 15px;">⚠️ Access Denied</h2>
                    <p style="color: #7f8c8d;">You do not have master administrative clearance parameters to access staff rosters.</p>
                </div>
            <?php else: ?>
                
                <div class="management-flex">
                    
                    <div class="form-side">
                        <h3 style="margin-top: 0; margin-bottom: 20px; color: #0f172a;">
                            <?php echo $edit_mode ? "📝 Edit Staff Parameters" : "✨ Create Staff Admin Account"; ?>
                        </h3>
                        
                        <form method="POST" id="staffCoreForm">
                            <input type="hidden" name="is_edit_mode" value="<?php echo $edit_mode ? '1' : '0'; ?>">
                            <input type="hidden" name="target_staff_id" value="<?php echo $edit_id; ?>">

                            <div class="form-group-block">
                                <label>Full Name</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($form_name); ?>" placeholder="John Doe" required>
                            </div>
                            <div class="form-group-block">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($form_email); ?>" placeholder="john@example.com" required>
                            </div>
                            <div class="form-group-block">
                                <label>Login Username</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($form_username); ?>" placeholder="johndoe123" required>
                            </div>
                            <div class="form-group-block">
                                <label>Account Password 
                                    <?php if($edit_mode): ?><span style="font-weight:normal; color:#64748b;">(Leave blank to keep current)</span><?php endif; ?>
                                </label>
                                <input type="password" name="password" placeholder="<?php echo $edit_mode ? 'Enter new password rule' : 'Create strong text rules'; ?>" <?php echo $edit_mode ? '' : 'required'; ?>>
                            </div>
                            <div class="form-group-block">
                                <label>System Authority Role</label>
                                <select name="role" required>
                                    <option value="staff" <?php if($form_role == 'staff') echo 'selected'; ?>>Standard Staff Admin (Cannot Add Staff)</option>
                                    <option value="super_admin" <?php if($form_role == 'super_admin') echo 'selected'; ?>>Super Admin (Can Add Staff)</option>
                                </select>
                            </div>

                            <input type="button" id="submitStaffBtn" value="<?php echo $edit_mode ? 'Update Account' : 'Register Staff Admin'; ?>" class="submit-btn" style="margin-top: 10px; width: 100%; cursor: pointer;">
                            
                            <?php if($edit_mode): ?>
                                <a href="settings.php" style="display:block; text-align:center; margin-top:12px; font-size:13px; color:#64748b; text-decoration:none; font-weight:600;">Cancel and Return</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-side">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                    <th style="padding: 14px; color: #64748b; font-weight: 600; font-size: 13px;">Name / Email</th>
                                    <th style="padding: 14px; color: #64748b; font-weight: 600; font-size: 13px;">Username</th>
                                    <th style="padding: 14px; color: #64748b; font-weight: 600; font-size: 13px;">Authority Role</th>
                                    <th style="padding: 14px; color: #64748b; font-weight: 600; font-size: 13px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($all_staff_result)): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 14px;">
                                            <div style="font-weight: 700; color: #0f172a;"><?php echo htmlspecialchars($row['name']); ?></div>
                                            <div style="font-size: 12px; color: #64748b;"><?php echo htmlspecialchars($row['email']); ?></div>
                                        </td>
                                        <td style="padding: 14px; color: #334155; font-size: 14px; font-weight: 600;">
                                            @<?php echo htmlspecialchars($row['username']); ?>
                                        </td>
                                        <td style="padding: 14px;">
                                            <?php if($row['role'] == 'super_admin'): ?>
                                                <span class="role-badge role-super">👑 Master Super</span>
                                            <?php else: ?>
                                                <span class="role-badge role-staff">💼 Standard Staff</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 14px; text-align: center; white-space: nowrap;">
                                            <a href="settings.php?edit_id=<?php echo $row['id']; ?>" class="action-link edit-lnk">Edit</a>
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="action-link del-lnk">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Intercept Form Save Submission (For Insert/Update confirmation operations)
    const targetButton = document.getElementById('submitStaffBtn');
    if (targetButton) {
        targetButton.addEventListener('click', function(e) {
            const parentForm = this.closest('form');
            const mode = parentForm.querySelector('input[name="is_edit_mode"]').value;
            
            Swal.fire({
                title: mode === '1' ? "Save Profile Changes?" : "Confirm Staff Registration?",
                text: mode === '1' ? "Are you sure you want to write these modifications back to the admin table logs?" : "Are you sure you want to initialize and create this new administrative staff account?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#10b981",
                cancelButtonColor: "#ef4444",
                confirmButtonText: mode === '1' ? "Yes, Save Details" : "Yes, Register Staff",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'save_staff';
                    hiddenInput.value = '1';
                    parentForm.appendChild(hiddenInput);
                    parentForm.submit();
                }
            });
        });
    }

    // 2. Alert Status Popup Handlers tracking PHP backend server response strings triggers
    var action = "<?php echo $script_action; ?>";
    var urlParams = new URLSearchParams(window.location.search);

    if (action === "insert_success" || urlParams.get('msg') === 'inserted') {
        Swal.fire({ title: "Account Created!", text: "The new staff account profile was committed safely to the roster.", icon: "success", confirmButtonColor: "#10b981" }).then(() => { window.location.href='settings.php'; });
    } else if (action === "update_success" || urlParams.get('msg') === 'updated') {
        Swal.fire({ title: "Profile Rewritten!", text: "Administrative access properties were successfully updated.", icon: "success", confirmButtonColor: "#10b981" }).then(() => { window.location.href='settings.php'; });
    } else if (urlParams.get('msg') === 'deleted') {
        Swal.fire({ title: "Account Dropped", text: "The targeted user record row was permanently excised from your data grids.", icon: "success", confirmButtonColor: "#10b981" }).then(() => { window.location.href='settings.php'; });
    } else if (action === "username_exists") {
        Swal.fire({ title: "Index Clashing!", text: "That username string key is already registered. Try another variation.", icon: "warning", confirmButtonColor: "#ef4444" });
    } else if (action === "self_delete_block") {
        Swal.fire({ title: "Operation Interrupted", text: "Safety Override: You cannot delete your own logged-in master profile session!", icon: "error", confirmButtonColor: "#ef4444" });
    } else if (action === "error") {
        Swal.fire({ title: "System Fault", text: "Database query mutation sequence failed constraints tests.", icon: "error", confirmButtonColor: "#ef4444" });
    }
});

// 3. Asynchronous Delete Modal Trigger Interception Engine
function confirmDelete(id) {
    Swal.fire({
        title: "Excise Admin Profile?",
        text: "Warning! Removing this row drops all linked administrative credentials permanently. Proceed with deletion?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc2626", // Bold administrative danger indicator tone
        cancelButtonColor: "#64748b",
        confirmButtonText: "Yes, Delete Account",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            // Forward user pointer down the execution array line to complete row drop sequence
            window.location.href = "settings.php?delete_id=" + id;
        }
    });
}
</script>

</body>
</html>