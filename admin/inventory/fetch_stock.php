<?php
include ("../../config/database.php");

$query = "SELECT id, stocks FROM items_stock"; 
$result = mysqli_query($conn, $query);

$stock_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $stock_data[$row['id']] = $row['stocks'];
}

echo json_encode($stock_data);
?>
