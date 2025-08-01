<?php
session_start();
include("../../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredPassword = trim($_POST["password"]);

    // Fetch stored password hash
    $sql = "SELECT password FROM business_account LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verify password
    if (password_verify($enteredPassword, $hashedPassword)) {
        // Disable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS=0");

        // Truncate tables
        $tables = ["business_account", "cancelled_orders", "cancelled_order_items", "items_stock", "notifications", "orders", "order_items", "sales_report", "sold_items"];
        foreach ($tables as $table) {
            $conn->query("TRUNCATE TABLE $table");
        }

        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
        
        // Delete images from the directory
        $uploadDirectory = realpath(__DIR__ . "/../../uploads");
        if ($uploadDirectory && is_dir($uploadDirectory)) {
            $files = glob($uploadDirectory . "/*"); // Get all files
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file); // Delete the file
                }
            }
        }

        // Destroy session and redirect to login page
        session_destroy();
        header("Location: ../../index.html");
        exit();
    } else {
        $_SESSION["error"] = "Incorrect password. Termination failed.";
        header("Location: ../../admin/pages/account_sec.php");
        exit();
    }
}

$conn->close();
?>
