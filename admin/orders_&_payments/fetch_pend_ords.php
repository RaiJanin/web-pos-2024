<?php
include("../../config/database.php");
date_default_timezone_set("Asia/Manila");

$result = $conn->query("SELECT id, customer_name, table_number, total_price, order_date FROM orders WHERE status = 'pending' ORDER BY order_date ASC");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $formatted_date = date("Y-m-d h:i:s A", strtotime($row['order_date'] . 'UTC'));
        $formatted_price = number_format($row['total_price'], 2); 
        echo "<tr class='order-row' data-id='{$row['id']}'>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['table_number']) . "</td>";
        echo "<td>{$row['total_price']} pesos</td>";
        echo "<td>{$formatted_date}</td>";
        echo "</tr>";
    }
} else {
    echo "<div style='text-align: center; margin: 40px 0;'>No Pending Orders Found!</div>";
}

mysqli_close($conn);
?>
