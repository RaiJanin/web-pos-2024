<?php
include("../../config/database.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
    $stocks = isset($_POST["stocks"]) ? intval($_POST["stocks"]) : 0;

    if ($id > 0 && $stocks >= 0) {
        $stmt = $conn->prepare("UPDATE items_stock SET stocks = ? WHERE id = ?");
        $stmt->bind_param("ii", $stocks, $id);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
        $stmt->close();
    } else {
        echo "Invalid Request";
    }
}

$conn->close();
?>
