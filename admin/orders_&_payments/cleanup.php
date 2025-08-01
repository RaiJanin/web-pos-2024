<?php
include("../../config/database.php");
date_default_timezone_set("Asia/Manila");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $days = isset($_POST['days']) ? intval($_POST['days']) : 7;
    $date_limit = date('Y-m-d H:i:s', strtotime("-$days days"));

    // Delete order items first (to maintain referential integrity)
    $delete_items_query = "DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE order_date < ?)";
    $stmt_items = $conn->prepare($delete_items_query);
    $stmt_items->bind_param("s", $date_limit);
    $items_deleted = $stmt_items->execute();
    $stmt_items->close();

    // Delete orders older than 7 days
    $delete_orders_query = "DELETE FROM orders WHERE order_date < ?";
    $stmt_orders = $conn->prepare($delete_orders_query);
    $stmt_orders->bind_param("s", $date_limit);
    $orders_deleted = $stmt_orders->execute();
    $stmt_orders->close();

    if ($items_deleted || $orders_deleted) {
        echo json_encode(["status" => "success" , "message" => "Orders older than $days days have been deleted."]);
    } else {
        echo json_encode(["status" => "error" , "message" => "Error deleting orders."]);
    }

    $conn->close();
}
?>
