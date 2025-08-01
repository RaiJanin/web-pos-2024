<?php
session_start();
include("../../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = trim($_POST["username"]);

    if (empty($newUsername)) {
        echo json_encode(["status" => "error", "message" => "Username cannot be empty."]);
        exit;
    }

    // Check if username already exists
    $checkSql = "SELECT username FROM business_account WHERE username = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $newUsername);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Username already taken."]);
        exit;
    }

    $stmt->close();

    // Update the username in database
    $updateSql = "UPDATE business_account SET username = ?"; // Change `id = 1` as needed
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("s", $newUsername);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Username updated successfully!", "newUsername" => $newUsername]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating username."]);
    }

    $stmt->close();
    $conn->close();
}
?>
