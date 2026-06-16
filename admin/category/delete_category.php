<?php
include("auth_guard.php");

include("../db.php");

$id = $_GET['id'];

mysqli_query(
    $conn,
    "DELETE FROM category WHERE id=$id"
);

header("Location:view_category.php");
exit();

?>