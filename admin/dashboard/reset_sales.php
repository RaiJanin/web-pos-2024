<?php
session_start();
include("../../config/database.php");

// Check if password is provided
if (!isset($_POST['password']) || empty($_POST['password'])) {
    echo "error";
    exit();
}

$enteredPassword = $_POST['password'];

// Fetch the actual admin password from the database
$stmt = $conn->prepare("SELECT password FROM business_account LIMIT 1");
$stmt->execute();
$stmt->bind_result($storedPassword);
$stmt->fetch();
$stmt->close();

// Check if password is hashed
if (password_needs_rehash($storedPassword, PASSWORD_BCRYPT)) {
    // The password is stored in plaintext
    if ($enteredPassword !== $storedPassword) {
        echo "error"; // Incorrect password
        exit();
    }
} else {
    // The password is hashed, verify using password_verify()
    if (!password_verify($enteredPassword, $storedPassword)) {
        echo "error"; // Incorrect password
        exit();
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS=0");
// If password is correct, reset the sales report
$tables = ["sales_report", "sold_items", "cancelled_order_items",
    "cancelled_orders"];
$success = true;

foreach ($tables as $table) {
    $sql=("TRUNCATE TABLE `$table`"); // backticks for safety
    if (!$conn->query($sql)) {
        echo "Error truncating $table: " . $conn->error;
        $success = false;
        break;
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS=1");

if ($success) {
    echo "success";
} else {
    echo "Error!!!";
}

$conn->close();
?>
