<?php
include("../../config/database.php"); // your database connection file

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

function getSoldItemsByDate($conn, $date) {
    $sql = "
        SELECT 
            i.id,
            i.item_name,
            SUM(s.quantity_sold) AS sold_quantity,
            SUM(s.total_sales) AS total_sales
        FROM sold_items s
        JOIN items_stock i ON s.item_id = i.id
        WHERE DATE(s.order_date) = ?
        GROUP BY i.id, i.item_name
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            "item_id" => (int)$row['id'],
            "name" => $row['item_name'],
            "sold_quantity" => (int)$row['sold_quantity'],
            "total_sales" => number_format($row['total_sales'], 2, '.', '')
        ];
    }

    return $items;
}

function getAllSoldItems($conn) {
    $sql = "
        SELECT 
            i.id,
            i.item_name,
            SUM(s.quantity_sold) AS sold_quantity,
            SUM(s.total_sales) AS total_sales
        FROM sold_items s
        JOIN items_stock i ON s.item_id = i.id
        GROUP BY i.id, i.item_name
    ";

    $result = $conn->query($sql);
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            "item_id" => (int)$row['id'],
            "name" => $row['item_name'],
            "sold_quantity" => (int)$row['sold_quantity'],
            "total_sales" => number_format($row['total_sales'], 2, '.', '')
        ];
    }
    return $items;
}

$todayItems = getSoldItemsByDate($conn, $today);
$yesterdayItems = getSoldItemsByDate($conn, $yesterday);
$allTimeItems = getAllSoldItems($conn);

echo json_encode([
    "today" => $todayItems,
    "yesterday" => $yesterdayItems,
    "all_time" => $allTimeItems
]);

$conn->close();
?>
