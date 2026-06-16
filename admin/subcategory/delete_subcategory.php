<?php

include("../db.php");

$id = $_GET['id'];

mysqli_query(
    $conn,
    "DELETE FROM sub_category WHERE id=$id"
);

header("Location:view_subcategory.php");
exit();

?>