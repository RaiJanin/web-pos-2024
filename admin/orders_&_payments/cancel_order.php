<?php
include("../../config/database.php");

if (isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    // Begin transaction
    $conn->begin_transaction();

    try {
        // 1. Update the original order status
        $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();

        // 2. Fetch order details
        $stmt = $conn->prepare("SELECT customer_name FROM orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orderData = $result->fetch_assoc();
        $stmt->close();

        if (!$orderData) {
            throw new Exception("Order not found.");
        }

        $customerName = $orderData['customer_name'];

        // 3. Insert into cancelled_orders
        $stmt = $conn->prepare("INSERT INTO cancelled_orders (customer_name) VALUES (?)");
        $stmt->bind_param("s", $customerName);
        $stmt->execute();
        $cancelledOrderId = $stmt->insert_id; // get the new cancelled order ID
        $stmt->close();

        // 4. Fetch order items
        $stmt = $conn->prepare("SELECT item_name, quantity, price FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        // 5. Insert each item into cancelled_order_items
        $stmt = $conn->prepare("INSERT INTO cancelled_order_items (order_id, item_name, quantity, price) VALUES (?, ?, ?, ?)");
        while ($row = $result->fetch_assoc()) {
            $stmt->bind_param("isid", $cancelledOrderId, $row['item_name'], $row['quantity'], $row['price']);
            $stmt->execute();
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();
        echo "success";

    } catch (Exception $e) {
        $conn->rollback();
        echo "error: " . $e->getMessage();
    }
}

$conn->close();
?>
