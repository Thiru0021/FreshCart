<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "grocery_db"
);

if(!$conn)
{
    die("Connection Failed");
}

?>