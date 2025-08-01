<?php
include("../../config/database.php");

$date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
$allTime = isset($_GET['all_time']) && $_GET['all_time'] == 'true';

// If all_time is set to true, we fetch all orders, else we filter by the selected date.
$sql = "
    SELECT co.id AS order_id, co.customer_name, co.cancelled_at, coi.item_name, coi.price, coi.quantity
    FROM cancelled_orders co
    LEFT JOIN cancelled_order_items coi ON co.id = coi.order_id
";

if (!$allTime) {
    $sql .= " WHERE DATE(co.cancelled_at) = ?";
}

$stmt = $conn->prepare($sql);
if ($allTime) {
    $stmt->execute();
} else {
    $stmt->bind_param("s", $date);
    $stmt->execute();
}

$result = $stmt->get_result();

$orders = [];
$total_price = 0;

while ($row = $result->fetch_assoc()) {
    $row['price'] = (float)$row['price'];
    $row['quantity'] = (int)$row['quantity'];
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total_price += $row['subtotal'];
    $orders[] = $row;
}

echo json_encode([
    "orders" => $orders,
    "total" => number_format($total_price, 2)
]);
?>
