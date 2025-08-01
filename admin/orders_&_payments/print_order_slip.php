<?php
include("../../config/database.php");

$sql2 = "SELECT restaurant_name FROM business_account LIMIT 1";
$result2 = $conn->query($sql2);
$restaurantName = $result2->fetch_assoc()['restaurant_name'] ?? "Unknown";


if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
   
    // Fetch order details
    $query = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
    } else {
        die("Order not found.");
    }
    $stmt->close();

    // Fetch ordered items
    $items_query = "SELECT oi.item_name, oi.quantity, oi.price FROM order_items oi WHERE oi.order_id = ?";
    $stmt_items = $conn->prepare($items_query);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result();
    $items = $items_result->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();
    $utc_time = new DateTime($order['order_date'], new DateTimeZone("UTC")); 
    $utc_time->setTimezone(new DateTimeZone("Asia/Manila")); 
    $formatted_date = $utc_time->format("Y-m-d h:i:s A"); 

    $conn->close();
} else {
    die("Invalid request.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Slip<?php echo date('Ymd') . '_Order_' . $order_id; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            text-align: center;
            text-size-adjust: 18%;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .receipt-container {
            max-width: 100%;
            background: #fff;
            margin: auto;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .receipt-header h4 {
            margin: 0;
            font-size: 18px;
        }
        .receipt-header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .receipt-details {
            text-align: left;
            font-size: 10px;
        }
        .receipt-details strong {
            font-weight: bold;
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .order-table th, .order-table td {
            border-bottom: 1px solid #ddd;
            padding: 5px;
            font-size: 10px;
            text-align: left;
        }
        .order-table th {
            background: #eee;
            font-weight: bold;
        }
        .total-section {
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .thank-you {
            text-align: center;
            font-size: 14px;
            margin-top: 15px;
            font-style: italic;
        }
        .print-button, .cancel-button {
            margin-top: 15px;
        }
        .notification {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px;
            border-radius: 5px;
            z-index: 1000;
        }
        @media print {
        .print-button, .cancel-button {
            display: none;
        }
}
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1><strong>Order Slip</strong></h1>
            <h2><strong>Table:</strong> <?php echo htmlspecialchars($order['table_number']); ?></h2>
            <p><strong>Order ID:</strong> <?php echo $order['id']; ?></p>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><strong>Order Date:</strong> <?php echo $formatted_date; ?></p>
        </div>
        <hr>
        <h5>Order Details</h5>
        <table class="order-table">
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
            </tr>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>₱<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="total-section">
            <p>Total: ₱<?php echo number_format($order['total_price'], 2); ?></p>
            <p style="font-weight: light; font-size:smaller;">Processed at: <?php date_default_timezone_set("Asia/Manila");
                                    $currentTime = date("Y-m-d h:i:s A");
                                    echo $currentTime; ?></p>
        </div>
        <p class="thank-you">Please enjoy your food!!</p>
        <h5><strong><?php echo htmlspecialchars($restaurantName); ?></strong></h5>
    </div>
    
    <button class="btn btn-primary print-button" onclick="printReceipt();">Print Receipt</button>
    <button class="btn btn-danger cancel-button" onclick="cancelPrint();">Cancel</button>

    <div id="printNotification" class="notification"></div>

    <script>
        let hasPrinted = false;

        function printReceipt() {
            if (hasPrinted) return; // Prevent multiple print dialogs

            document.title = "Receipt_<?php echo date('Ymd') . '_Order_' . $order_id; ?>";
            window.print();
            
            // Manual confirmation after print dialog closes
            setTimeout(() => {
                let confirmPrint = confirm("Did the receipt print successfully?");
                if (confirmPrint) {
                    showNotification("✅ Receipt printed successfully!", "success");
                    hasPrinted = true;
                    window.location.href = "../../admin/pages/show_orders.php?order_id=<?php echo $order_id; ?>";
                } else {
                    let retryPrint = confirm("Printing was not successful. Do you want to try again?");
                    if (retryPrint) {
                        printReceipt();
                    } else {
                        showNotification("⚠️ Printing was canceled. You can print again if needed.", "warning");
                    }
                }
            }, 1000);
        }

        function cancelPrint() {
            showNotification("⚠️ Printing was canceled.", "warning");
            setTimeout(() => {
                window.location.href = "../../admin/pages/show_orders.php?order_id=<?php echo $order_id; ?>";
            }, 2000);
        }

        function showNotification(message, type) {
            let notification = document.getElementById("printNotification");
            notification.innerHTML = message;
            
            // Apply color based on type
            if (type === "success") {
                notification.style.backgroundColor = "rgba(0, 128, 0, 0.8)"; // Green
            } else if (type === "warning") {
                notification.style.backgroundColor = "rgba(255, 165, 0, 0.8)"; // Orange
            }

            notification.style.display = "block";
            
            setTimeout(() => {
                notification.style.display = "none";
            }, 5000);
        }

        window.onload = function() {
            printReceipt();
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
