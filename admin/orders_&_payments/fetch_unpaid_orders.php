<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("../../config/database.php");
date_default_timezone_set("Asia/Manila");

$result = $conn->query("SELECT id, customer_name, total_price, order_date FROM orders WHERE status = 'unpaid' ORDER BY order_date DESC");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $formatted_date = date("Y-m-d h:i:s A", strtotime($row['order_date'] . ' UTC'));
        echo "<tr>
                <td>{$row['id']}</td>
                <td>".htmlspecialchars($row['customer_name'])."</td>
                <td>".number_format($row['total_price'], 2)." pesos</td>
                <td>{$formatted_date}</td>
                <td>
                    <button class='btn btn-info btn-sm review-order' data-id='{$row['id']}'>Review</button>
                    <button class='btn btn-success process-payment'
                    data-id='{$row['id']}' 
                    data-total='{$row['total_price']}'
                    data-customer='".htmlspecialchars($row['customer_name'], ENT_QUOTES)."'>
                    Process Order
                    </button>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center'>No unpaid orders found!</td></tr>";
}
$conn->close();
?>
