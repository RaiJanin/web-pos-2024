<?php
include("../../config/database.php");
date_default_timezone_set("Asia/Manila");
$currentTime = date("Y-m-d H:i:s A");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = $_POST["order_id"];
    $action = $_POST["action"];
    $cash_received = isset($_POST["cash_received"]) ? floatval($_POST["cash_received"]) : 0;

    function fetch_order_details($conn, $order_id) {
        $stmt = $conn->prepare("SELECT customer_name, table_number, total_price, order_date FROM orders WHERE id = ?");
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row;
        }
        
        $stmt->close();
        return null;
    }

    function fetch_order_items($conn, $order_id) {
        $stmt = $conn->prepare("SELECT item_name, quantity FROM order_items WHERE order_id = ?");
        if (!$stmt) return [];
        
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $order_items = [];
        while ($row = $result->fetch_assoc()) {
            $order_items[] = $row;
        }
        $stmt->close();

        return $order_items;
    }

    function reduce_stock($conn, $order_items) {
        foreach ($order_items as $item) {
            $stmt = $conn->prepare("UPDATE items_stock SET stocks = stocks - ? WHERE item_name = ?");
            if ($stmt) {
                $stmt->bind_param("is", $item["quantity"], $item["item_name"]);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    function get_total_quantity($conn, $order_id) {
        $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM order_items WHERE order_id = ?");
        if (!$stmt) return 0;

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();
        return $row['total'] ?? 0;
    }

    function insert_sold_items($conn, $order_items, $order_id, $order_date) {
        foreach ($order_items as $item) {
            // Fetch the price for the item
            $stmt_price = $conn->prepare("SELECT price FROM items_stock WHERE item_name = ?");
            if ($stmt_price) {
                $stmt_price->bind_param("s", $item["item_name"]);
                $stmt_price->execute();
                $result_price = $stmt_price->get_result();
                if ($row_price = $result_price->fetch_assoc()) {
                    $price = $row_price['price'];
                    $subtotal = $price * $item["quantity"]; // Calculate the subtotal
                }
                $stmt_price->close();
            }
    
            // Check if the item already exists for the given date
            $stmt_check = $conn->prepare("SELECT id, quantity_sold, total_sales FROM sold_items WHERE item_name = ? AND order_date = ?");
            if ($stmt_check) {
                $stmt_check->bind_param("ss", $item["item_name"], $order_date);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
    
                if ($row_check = $result_check->fetch_assoc()) {
                    // Item already exists, update the record
                    $new_quantity = $row_check['quantity_sold'] + $item["quantity"];
                    $new_total_sales = $row_check['total_sales'] + $subtotal;
    
                    $stmt_update = $conn->prepare("UPDATE sold_items SET quantity_sold = ?, total_sales = ? WHERE id = ?");
                    if ($stmt_update) {
                        $stmt_update->bind_param("idi", $new_quantity, $new_total_sales, $row_check['id']);
                        $stmt_update->execute();
                        $stmt_update->close();
                    }
                } else {
                    // Fetch the item ID from items_stock table
                    $stmt_fetch_id = $conn->prepare("SELECT id FROM items_stock WHERE item_name = ?");
                    if ($stmt_fetch_id) {
                        $stmt_fetch_id->bind_param("s", $item["item_name"]);
                        $stmt_fetch_id->execute();
                        $stmt_fetch_id->bind_result($item_id);
                        $stmt_fetch_id->fetch();
                        $stmt_fetch_id->close();

                        // If the item exists, insert it into sold_items
                        if ($item_id) {
                            $stmt_insert = $conn->prepare("INSERT INTO sold_items (item_id, item_name, quantity_sold, total_sales, order_id, order_date) 
                                                        VALUES (?, ?, ?, ?, ?, ?)");
                            if ($stmt_insert) {
                                $stmt_insert->bind_param("isiids", $item_id, $item["item_name"], $item["quantity"], $subtotal, $order_id, $order_date);
                                $stmt_insert->execute();
                                $stmt_insert->close();
                            }
    } else {
        // Handle case where item doesn't exist in items_stock
        error_log("Item not found in items_stock: " . $item["item_name"]);
    }
}

                }
                $stmt_check->close();
            }
        }
    }     

    function add_notification($conn, $customer_name, $order_id) {
        $message = "Attention $customer_name! Your order no: $order_id has been accepted and is now being prepared! Please wait patiently";
        
        $stmt = $conn->prepare("SELECT id FROM notifications WHERE message = ?");
        if (!$stmt) return;

        $stmt->bind_param("s", $message);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            $stmt_insert = $conn->prepare("INSERT INTO notifications (message) VALUES (?)");
            if ($stmt_insert) {
                $stmt_insert->bind_param("s", $message);
                $stmt_insert->execute();
                $stmt_insert->close();
            }
        }
        $stmt->close();

        $deleteStmt = $conn->prepare("DELETE FROM notifications WHERE created_at <= NOW() - INTERVAL 1 MINUTE");
        if ($deleteStmt) {
            $deleteStmt->execute();
            $deleteStmt->close();
        }
    }

    if ($action === "accept" || $action === "pay") {
        $order_details = fetch_order_details($conn, $order_id);
        if (!$order_details) {
            echo json_encode(["status" => "error", "message" => "Order not found!"]);
            exit;
        }

        extract($order_details); // Extract $customer_name, $table_number, $total_price, $order_date
        $order_items = fetch_order_items($conn, $order_id);
        $total_quantity = get_total_quantity($conn, $order_id);
        

        if ($action === "accept") {
            $noAvailItems = [];
            foreach ($order_items as $item) {
                $availItems = $conn->prepare("SELECT stocks FROM items_stock WHERE item_name= ?");
                $availItems->bind_param("s", $item['item_name']);
                $availItems->execute();
                $availItems->bind_result($currentStock);
                $availItems->fetch();
                $availItems->close();
                
                if($item['quantity'] > $currentStock) {
                    $noAvailItems[] = [
                        "item" => $item['item_name'],
                        "ordered" => $item['quantity'],
                        "available" => $currentStock];
                }
            }
            
            if(!empty($noAvailItems)) {
                echo json_encode([
                "status" => "error",
                "message" => "Insufficient stock for one or more items.",
                "insufficient_items" => $noAvailItems
            ]);
            exit;
            }
            
            $stmt = $conn->prepare("UPDATE orders SET status = 'unpaid' WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                $stmt->close();
            }
            reduce_stock($conn, $order_items);
            add_notification($conn, $customer_name, $order_id);
            echo json_encode(["status" => "success", "message" => "Order moved to unpaid orders.", "order_id" => $order_id]);
        } elseif ($action === "pay") {
            if ($cash_received < $total_price) {
                echo json_encode(["status" => "error", "message" => "Insufficient cash received!"]);
                exit;
            }

            $change = $cash_received - $total_price;

            $stmt_sales = $conn->prepare("INSERT INTO sales_report (customer_name, table_number, total_price, order_date, total_items) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt_sales) {
                echo json_encode(["status" => "error", "message" => "Failed to prepare sales report insert statement."]);
                exit;
            }

            $stmt_sales->bind_param("ssdsi", $customer_name, $table_number, $total_price, $currentTime, $total_quantity);
            if (!$stmt_sales->execute()) {
                echo json_encode(["status" => "error", "message" => "Failed to insert into sales report: " . $stmt_sales->error]);
                exit;
            }
            $stmt_sales->close();

            insert_sold_items($conn, $order_items, $order_id, $currentTime);

            $stmt = $conn->prepare("UPDATE orders SET status = 'completed', cash_paid = ?, change_given = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ddi", $cash_received, $change, $order_id);
                $stmt->execute();
                $stmt->close();
                echo json_encode(["status" => "success", "message" => "Payment processed successfully.", "order_id" => $order_id, "change" => $change]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to update order status."]);
            }
        }
        exit;
    } elseif ($action === "reject") {
        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $stmt->close();
        }

        echo json_encode(["status" => "success", "message" => "Order rejected."]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid action!"]);
        exit;
    }
}

$conn->close();
?>
