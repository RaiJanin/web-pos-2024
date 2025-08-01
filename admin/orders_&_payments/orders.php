<?php
header('Content-Type: application/json');
include("../../config/database.php");

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $customer_name = $_POST['customer_name'];
        $table_number = $_POST['table_number'];
        $cart_items = json_decode($_POST['cart_items'], true);
        $total_price = $_POST['total_price'];
        date_default_timezone_set("UTC");
        $order_date = date("Y-m-d H:i:s");

        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, table_number, total_price, order_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $customer_name, $table_number, $total_price, $order_date);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        // Initialize total items counter
        $total_quantity = 0;

        // Insert each cart item into order_items
        foreach ($cart_items as $item) {
            $item_name = $item['name'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $subtotal = $price * $quantity;
            
            $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, item_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt_items->bind_param("issdd", $order_id, $item_name, $quantity, $price, $subtotal);
            $stmt_items->execute();

            $total_quantity += $quantity; // Corrected total items count
        }

        echo json_encode([
            "status" => "success",
            "order_number" => $order_id,
            "customer_name" => $customer_name,
            "table_number" => $table_number,
            "total_price" => $total_price,
            "order_date" => $order_date,
            "total_items" => $total_quantity
        ]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    
}
?>
