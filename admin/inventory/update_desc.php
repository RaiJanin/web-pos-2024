<?php
include("../../config/database.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["id"];
    $description = mysqli_real_escape_string($conn, $_POST["description"]);

    $sql = "UPDATE items_stock SET description='$description' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "error";
    }
}

mysqli_close($conn);
?>
