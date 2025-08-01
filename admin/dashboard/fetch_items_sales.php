<?php
include("../../config/database.php");

header('Content-Type: application/json');

if (!isset($_GET['item_id']) || !is_numeric($_GET['item_id'])) {
    echo json_encode(['error' => 'Invalid or missing item_id']);
    exit;
}

$itemId = $_GET['item_id'];

$sql = "SELECT order_date, SUM(total_sales) AS overall_sales 
        FROM sold_items
        WHERE item_id = ?
        GROUP BY order_date
        ORDER BY order_date ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare statement']);
    exit;
}

$stmt->bind_param("i", $itemId);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to execute query']);
    exit;
}

$result = $stmt->get_result();

$dates = [];
$sales = [];

while ($row = $result->fetch_assoc()) {
    $dates[] = $row['order_date'];
    $sales[] = $row['overall_sales'];
}

echo json_encode([
    'dates' => $dates,
    'sales' => $sales
]);

$stmt->close();
$conn->close();
?>
