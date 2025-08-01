<?php
include("../../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $itemId = $_POST['id'];
    $newPrice = $_POST['price'];

    if (is_numeric($newPrice) && $newPrice >= 0) {
        $sql = "UPDATE items_stock SET price = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $newPrice, $itemId);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
        $stmt->close();
    } else {
        echo "invalid";
    }
}

mysqli_close($conn);
?>
