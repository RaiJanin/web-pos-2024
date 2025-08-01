<?php
include("../../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newName = trim($_POST["restaurant_name"]);
    $newOwner = trim($_POST["restaurant_owner"]);

    if (empty($newName) || empty($newOwner)) {
        echo "error: Fields cannot be empty.";
        exit;
    }

    $stmt = $conn->prepare("UPDATE business_account SET restaurant_name = ?, restaurant_owner = ?");
    $stmt->bind_param("ss", $newName, $newOwner);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: Update failed.";
    }

    $stmt->close();
    $conn->close();
}
?>
