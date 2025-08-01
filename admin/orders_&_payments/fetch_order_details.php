<?php
include("../../config/database.php");
date_default_timezone_set("Asia/Manila");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

    $stmt = $conn->prepare("SELECT o.customer_name, o.table_number, o.order_date, i.item_name, i.quantity, i.subtotal, o.total_price 
                            FROM orders o 
                            JOIN order_items i ON o.id = i.order_id 
                            WHERE o.id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $formatted_date = date("Y-m-d h:i:s A", strtotime($row['order_date'] . 'UTC'));

        echo "<div class='text-left mb-3'>
                <h4>Customer Name: <b>" . htmlspecialchars($row['customer_name']) . "</b></h4>
                <h4>Table Number: <b>" . htmlspecialchars($row['table_number']) . "</b></h4>
                <h4>Order Date: <b>" . htmlspecialchars($formatted_date) . "</b></h4>
              </div>";

        echo "<table class='table'>";
        echo "<thead class='table-dark'><tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Subtotal</th>
              </tr></thead><tbody>";

        $overall_total = $row['total_price'];

        do {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
            echo "<td>" . $row['quantity'] . "</td>";
            echo "<td>" . $row['subtotal'] . " pesos</td>";
            echo "</tr>";
        } while ($row = $result->fetch_assoc());

        echo "</tbody></table>";

        echo "<h4 class='text-success text-center mt-3'>Overall Total: <b>" . $overall_total . " pesos</b></h4>";
    } else {
        echo "<div class='alert alert-danger text-center'>No order found with this ID!</div>";
    }
    exit();
}
?>
