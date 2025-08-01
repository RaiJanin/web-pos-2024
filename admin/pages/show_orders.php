<?php
    session_start();
    include("../../config/database.php");
    if (!isset($_SESSION['username'])) {
    header("Location: ../../index.html"); // Redirect to login page
    exit();
}
    include("../../ui/navbar.html");
    date_default_timezone_set("Asia/Manila");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .containertable {
            margin-top: 50px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            transition: all 0.5s ease;
        }
        .container {
            margin-top: 50px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            transition: all 0.5s ease;
        }
        table {
            margin-top: 20px;
        }
        th, td {
            text-align: center;
            cursor: pointer;
        }
        #overlay {
            display: none; 
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); 
            backdrop-filter: blur(1.8px); 
            z-index: 1;
        }

        .show {
            display: block !important;
        }
        .order-link {
            color: #0d6efd;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .order-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }
        #order-details-container {
            display: none; 
            padding: 25px;
            background: white;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); 
            border-radius: 12px;
            text-align: left;
            z-index: 2;
            max-width: 50%;
            max-height: 80vh; 
            overflow: auto; 
            box-sizing: border-box;
            animation: fadeIn 0.3s ease-in-out; 
        }
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            width: 30px;
            font-size: 20px;
            background: red;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: color 0.3s;
        }
        .close:hover {
            background-color:rgb(245, 85, 36);
        }
        @media (max-width: 768px) {
        #order-details-container {
            width: 75%;
            max-width: 75%;
            padding: 20px;
        }

        .close {
            width: 30px;
            height: 30px;
            font-size: 13px;
        }
    }

    #loading-prompt {
    text-align: center;
    padding: 20px;
}

.loading-text {
    font-size: 16px;
    color: #555;
    margin-top: 10px;
    font-weight: bold;
}



    @media (max-width: 480px) {
        #order-details-container {
            width: 75%;
            max-width: 75%;
            padding: 15px;
        }

        .close {
            width: 25px;
            height: 25px;
            font-size: 13px;
        }
    @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    }
    </style>
</head>
<body>
     <br><br><br><br><br><br>

    <div class="container mt-5">
    <h2 class="text-center text">Pending Orders</h2>
    <table class="table table-bordered table-hover" id="pending-orders">
        <thead class="table-dark">
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Table Number</th>
                <th>Total Price</th>
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="container mt-5">
        <h2 class="text-center">Unpaid Orders</h2>
        <table class="table" id="unpaid-orders">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Total Price</th>
                    <th>Order Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

   <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Customer Name:</strong> <span id="customerName"></span></p>
                    <p><strong>Confirm payment for Order ID:</strong> <span id="paymentOrderId"></span></p>
                    <div class="mb-3">
                        <label for="cashInput" class="form-label">Cash Received:</label>
                        <input type="number" class="form-control" id="cashInput" placeholder="Enter amount" min="0">
                    </div>
                    <p><strong>Total Price:</strong> <span id="totalPrice"></span> pesos</p>
                    <p><strong>Change:</strong> <span id="changeAmount">0</span> pesos</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" id="confirmPayment">Confirm Payment</button>
                    <button class="btn btn-danger" id="cancelOrder">Cancel Order</button>
                    <button class="btn btn-primary" id="printSlip">Print Slip</button>
                </div>
            </div>
        </div>
    </div>

<br>
    <label for="deleteDays">Delete orders older than:</label>
    <input type="number" id="deleteDays" class="form-control d-inline-block" style="width: 80px;" value="7" min="1"> days

    <button id="deleteOldOrders" class="btn btn-danger btn-sm" style="width: 80px;">Delete</button>

<div class="containertable">
    <h2 class="text-center text-primary">Completed Orders</h2>
    <div class="table-responsive">
        <table class="table table-hover" id="orders-table">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Table Number</th>
                    <th>Total Price</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $result = $conn->query("SELECT id, customer_name, table_number, total_price, order_date FROM orders WHERE status = 'completed' ORDER BY order_date DESC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $formatted_date = date("Y-m-d h:i:s A", strtotime($row['order_date'] . 'UTC'));
                        echo "<tr class='order-link' data-order-id='" . $row['id'] . "'>";
                        echo "<td>" . number_format($row['id']). "</td>";
                        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['table_number']) . "</td>";
                        echo "<td>" . number_format($row['total_price']) . " pesos</td>";
                        echo "<td>" . $formatted_date . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No Completed Orders Found!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Order Details Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetails">
                <p>Loading order details...</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="acceptOrder">Accept</button>
                <button class="btn btn-danger" id="rejectOrder">Reject</button>
                <button class="btn btn-success" id="reprintOrder">Re-print</button>
            </div>
        </div>
    </div>
</div>

<div id="newOrderNotification" style="
    display: none;
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #28a745;
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    font-weight: bold;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    z-index: 1050;
"></div>

<audio id="receivedOrderSound" src="../../notifications/noti_sounds/order_placed_ding.mp3" preload="auto"></audio>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function () {
    var selectedOrderId = 0;  // For pending orders
    var selectedUnpaidOrderId = 0;  // For unpaid orders
    var completedOrderId = 0; // For completed orders
    var previousPendingOrderCount = 0; //For previous orders notification count
    var hasInitializedPendingOrders = false;

    // Function to refresh pending orders list
    function refreshPendingOrders() {
        let ordersTable = $("#pending-orders tbody");
        let loadingPrompt1 = $("#loading-prompt-1");

        loadingPrompt1.fadeIn(200);

        $.ajax({
            url: "../../admin/orders_&_payments/fetch_pend_ords.php",
            type: "GET",
            success: function (response) {
                let tempDiv = $("<div>").html(response);
                let currentCount = tempDiv.find("tr").length;

                if(!hasInitializedPendingOrders){
                    hasInitializedPendingOrders = true;
                    if(currentCount > 0) {
                        showNewOrderNotification(currentCount);
                    }
                    previousPendingOrderCount = currentCount;
                } else {
                    if (currentCount > previousPendingOrderCount) {
                        let newOrders = currentCount - previousPendingOrderCount;
                        showNewOrderNotification(newOrders);
                    }
                        previousPendingOrderCount = currentCount;
                }
                
                ordersTable.html(response);
                loadingPrompt1.fadeOut(200);
            },
            error: function () {
                console.log("Failed to refresh pending orders.");
                loadingPrompt1.hide();
            }
        });
    }

    function showNewOrderNotification(newCount) {
    let notification = $("#newOrderNotification");
    notification.text("🛎️ " + newCount + " New order(s) received! Please Check.");
    notification.fadeIn(400).delay(3000).fadeOut(400);
    document.getElementById("receivedOrderSound").play();
}

    // Function to refresh unpaid orders list
    function refreshUnpaidOrders() {
        let ordersTable = $("#unpaid-orders tbody");
        let loadingPrompt = $("#loading-prompt");

        loadingPrompt.fadeIn(200);

        $.ajax({
            url: "../../admin/orders_&_payments/fetch_unpaid_orders.php",
            type: "GET",
            success: function (response) {
                ordersTable.html(response);
                loadingPrompt.fadeOut(200);
            },
            error: function () {
                console.log("Failed to refresh unpaid orders.");
                loadingPrompt.hide();
            }
        });
    }

    // Auto-refresh pending and unpaid orders every 2 seconds
    setInterval(function () {
        refreshPendingOrders();
        refreshUnpaidOrders();
    }, 2000);

    // Handle clicking on a pending order row
    $(document).on("click", ".order-row", function () {
        selectedOrderId = $(this).data("id");
        selectedUnpaidOrderId = 0; // Clear unpaid selection
        $("#acceptOrder, #rejectOrder").show();
        $("#reprintOrder").hide();
        $("#orderDetails").html("<p class='text-center text-muted'>Loading order details...</p>");

        $.post("../../admin/orders_&_payments/fetch_order_details.php", { order_id: selectedOrderId }, function (response) {
            $("#orderDetails").html(response);
            $("#orderModal").modal("show");
        }).fail(function () {
            alert("Failed to fetch order details.");
        });
    });

    // Handle clicking on an unpaid order row
    $("#unpaid-orders tbody").on("click", "tr", function () {
        selectedUnpaidOrderId = $(this).data("id");
        selectedOrderId = 0; // Clear pending selection
        $("#acceptOrder, #rejectOrder").hide();
        $("#reprintOrder").show();

        if (selectedUnpaidOrderId) {
            $("#orderDetails").html("<p class='text-center text-muted'>Loading order details...</p>");

            $.post("../../admin/orders_&_payments/fetch_order_details.php", { order_id: selectedUnpaidOrderId }, function (response) {
                $("#orderDetails").html(response);
                $("#orderModal").modal("show");
            }).fail(function () {
                alert("Failed to load order details.");
            });
        }
    });

    // Handle clicking on a completed order row
    $("#orders-table tbody").on("click", "tr", function () {
        completedOrderId = $(this).data("order-id") || $(this).attr("data-order-id");
        $("#orders-table tbody tr").removeClass("table-active");
        $(this).addClass("table-active");

        if (completedOrderId) {
            $("#orderDetails").html("<p class='text-center text-muted'>Loading order details...</p>");
            $("#acceptOrder, #rejectOrder").hide();
            $("#reprintOrder").show();

            $.post("../../admin/orders_&_payments/fetch_order_details.php", { order_id: completedOrderId }, function (response) {
                $("#orderDetails").html(response);
                $("#orderModal").modal("show");
            }).fail(function () {
                alert("Failed to load order details.");
            });
        }
    });

    // Accept Order (for pending orders)
    $("#acceptOrder").click(function () {
        if (confirm("Accept this order?")) {
            if (!selectedOrderId || selectedOrderId <= 0) {
                alert("No pending order selected!");
                return;
            }
            $.post("../../admin/orders_&_payments/handle_order.php", { order_id: selectedOrderId, action: "accept" }, function (response) {
                let result = JSON.parse(response);
                console.log(response);
                console.log(result.order_id); 
                if (result.status === "success") {
                    if (confirm(result.message + " Print Slip?")) {
                        console.log("Redirecting to: " + "../../admin/orders_&_payments/print_order_slip.php?order_id=" + result.order_id);
                        window.location.href = "../../admin/orders_&_payments/print_order_slip.php?order_id=" + result.order_id;
                    } else {
                        location.reload();
                    }
                } else {
                    alert(result.message);
                }
            }).fail(function () {
                alert("Failed to process order.");
            });
        }
    });

    // Reject Order (for pending orders)
    $("#rejectOrder").click(function () {
        if (confirm("Are you sure you want to reject the order?")) {
            if (!selectedOrderId || selectedOrderId <= 0) {
                alert("No pending order selected!");
                return;
            }
            $.post("../../admin/orders_&_payments/handle_order.php", { order_id: selectedOrderId, action: "reject" }, function (response) {
                let result = JSON.parse(response);
                alert(result.message);
                location.reload();
            }).fail(function () {
                alert("Failed to reject order.");
            });
        }
    });

    // Reprint Order (for completed orders)
    $("#reprintOrder").click(function () {
        if (completedOrderId) {
            window.location.href = "../../admin/orders_&_payments/print_receipt.php?order_id=" + completedOrderId;
        } else {
            alert("No completed orders selected.");
        }
    });

    $(document).on("click", ".review-order", function () {
        let orderId = $(this).data("id");
    
        // Highlight row visually (optional)
        $("#unpaid-orders tbody tr").removeClass("table-active");
        $(this).closest("tr").addClass("table-active");
    
        selectedUnpaidOrderId = orderId;
        selectedOrderId = 0;
    
        $("#acceptOrder, #rejectOrder").hide();
        $("#reprintOrder").hide();
        $("#orderDetails").html("<p class='text-center text-muted'>Loading order details...</p>");
    
        $.post("../../admin/orders_&_payments/fetch_order_details.php", { order_id: orderId }, function (response) {
            $("#orderDetails").html(response);
            $("#orderModal").modal("show");
        }).fail(function () {
            alert("Failed to load order details.");
        });
    });
    // Process payment (for unpaid orders)
    $(document).on("click", ".process-payment", function () {
        let orderId = $(this).data("id");
        let totalPrice = $(this).data("total");
        let customerName = $(this).data("customer") || "Unknown"; // Get the customer's name from the button

        $("#paymentOrderId").text(orderId);
        $("#customerName").text(customerName); // Display customer name
        $("#totalPrice").text(totalPrice);
        $("#changeAmount").text("0.00");

        $("#confirmPayment").data("id", orderId);
        $("#paymentModal").modal("show");
    });


    $("#cashInput").on("input", function () {
        let cash = parseFloat($(this).val()) || 0;
        let totalPrice = parseFloat($("#totalPrice").text()) || 0;
        let change = cash - totalPrice;
        $("#changeAmount").text(change >= 0 ? change.toFixed(2) : "0");
    });

    $("#confirmPayment").click(function () {
        let $btn = $(this);
        let orderId = $(this).data("id");
        let cash = parseFloat($("#cashInput").val()) || 0;
        let totalPrice = parseFloat($("#totalPrice").text()) || 0;
        let change = cash - totalPrice;

        if (cash < totalPrice) {
            alert("Insufficient cash received!");
            return;
        }

        $btn.prop("disabled", true).text("Processing...");

        $.post("../../admin/orders_&_payments/handle_order.php", { order_id: orderId, action: "pay", cash_received: cash }, function (response) {
            let result = JSON.parse(response);
            
            if (result.status === "success") {
                if (confirm(result.message + " Print Receipt?")) {
                    window.location.href = "../../admin/orders_&_payments/print_receipt.php?order_id=" + result.order_id;
                } else {
                    location.reload();
                }
            } else {
                alert(result.message);
            }

            $("#paymentModal").modal("hide");
            refreshUnpaidOrders();
        }).fail(function () {
            alert("Failed to process payment.");
            $btn.prop("disabled", false).text("Confirm Payment");
        });

        $('#paymentModal').on('hidden.bs.modal', function () {
        $("#confirmPayment").prop("disabled", false).text("Confirm Payment");
        $("#cancelOrder").prop("disabled", false).text("Cancel Order");
    });
    });

    $("#printSlip").click(function () {
        let orderId = $("#paymentOrderId").text();
        window.open("../../admin/orders_&_payments/print_order_slip.php?order_id=" + orderId, "_blank");
    });
    $("#deleteOldOrders").click(function () {
    var deleteDays = $("#deleteDays").val();

    if (deleteDays <= 0) {
        alert("Please enter a valid number of days.");
        return;
    }

    if (confirm("Are you sure you want to delete orders older than " + deleteDays + " days?")) {
        $.post("../../admin/orders_&_payments/cleanup.php", { days: deleteDays }, function (response) {
            let result = JSON.parse(response);
            if (result.status === "success") {
                alert(result.message);
                location.reload(); // Reload the page to reflect changes
            } else {
                alert(result.message);
            }
        }).fail(function () {
            alert("Failed to delete old orders.");
        });
    }
});

$(document).on("click", "#cancelOrder", function () {
    let $btn = $(this);
    let orderId = $("#paymentOrderId").text();  // Get the order ID from the modal
    if (confirm("Are you sure you want to cancel this order?")) {
        $btn.prop("disabled", true).text("Cancelling...");
        $.ajax({
            type: "POST",
            url: "../../admin/orders_&_payments/cancel_order.php",  // Create this PHP script to handle order cancellation
            data: { order_id: orderId },
            success: function (response) {
                if (response === 'success') {
                    alert("Order has been cancelled.");
                    location.reload();  // Reload the page to reflect the change
                } else {
                    alert("There was an error canceling the order.");
                    $btn.prop("disabled", false).text("Cancel Order");
                }
            }
        });
    }

    $('#paymentModal').on('hidden.bs.modal', function () {
        $("#confirmPayment").prop("disabled", false).text("Confirm Payment");
        $("#cancelOrder").prop("disabled", false).text("Cancel Order");
    });

});

});
</script>

</body>
</html>
<?php 
mysqli_close($conn);
?>