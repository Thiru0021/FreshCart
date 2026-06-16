<?php

include("../db.php");

$id = $_GET['id'];

mysqli_query(
    $conn,
    "DELETE FROM product WHERE id=$id"
);

header("Location:view_product.php");
exit();

?>