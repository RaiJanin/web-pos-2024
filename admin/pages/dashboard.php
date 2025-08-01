<?php
session_start();
include("../../config/database.php");
if (!isset($_SESSION['username'])) {
    header("Location: ../../index.html"); // Redirect to login page
    exit();
}
include("../../ui/navbar.html");

// Fetch sales report
$sql = "SELECT customer_name, total_price, order_date, total_items FROM sales_report ORDER BY order_date ASC";
$result = $conn->query($sql);

// Initialize variables
$totalSales = 0;
$totalCustomers = 0;
$salesByDate = [];
$customerCountByDate = [];
$totalItemsSold = 0;
$totalItemsByDate = [];

// Fetch business account details
$stmt_rest_details = $conn->prepare("SELECT restaurant_name, restaurant_owner, password FROM business_account");
$stmt_rest_details->execute();
$stmt_rest_details->bind_result($restaurantName, $restaurantOwner, $adminPassword);
$stmt_rest_details->fetch();
$stmt_rest_details->close();


// Process sales data
while ($row = $result->fetch_assoc()) {
    $date = date("Y-m-d", strtotime($row['order_date']));
    $totalSales += $row['total_price'];
    $totalItemsSold += $row['total_items'];
    $totalCustomers++;

    // Track sales by date for graphs
    if (!isset($salesByDate[$date])) {
        $salesByDate[$date] = 0;
        $customerCountByDate[$date] = 0;
        $totalItemsByDate[$date] = 0;
    }
    $salesByDate[$date] += $row['total_price'];
    $customerCountByDate[$date]++;
    $totalItemsByDate[$date] +=$row['total_items'];
}


// Convert data for JavaScript
$dates = json_encode(array_keys($salesByDate));
$sales = json_encode(array_values($salesByDate));
$customers = json_encode(array_values($customerCountByDate));
$items = json_encode(array_values($totalItemsByDate)); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food and Beverage POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            text-align: center;
            display: flex;
        }

        .sales-container {
            width: 800px;
            max-width: 100%;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); 
        }

        h1, h2 {
            color: #333;
        }

        .stats-box {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }

        .stat {
            background: #007BFF;
            color: white;
            padding: 15px;
            border-radius: 8px;
            width: 30%;
            text-align: center;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            font-size: 15px;
            font-weight: bold;
        }

        .chart-container {
            background: #fff3e0;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        canvas {
            margin-top: 10px;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 8px;
        }

        .reset-section {
            margin-top: 20px;
        }

        .reset-button {
            margin-left: 10px;
            margin-top: -10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .reset-button:hover {
            background: #a71d2a;
        }

        @media (max-width: 768px) {
            .stats-box {
                flex-direction: column;
                align-items: center;
            }
            .stat {
                width: 80%;
                margin-bottom: 10px;
            }
        }

        .update-section {
        justify-content: left;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 400px;
        margin: 20px auto;
        text-align: center;
    }

    .update-section h3 {
        color: #333;
        margin-bottom: 15px;
    }

    .update-section input {
        width: 60%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
    }

    .update-section button {
        background: #f47105;
        color: white;
        border: none;
        padding: 10px 15px;
        width: 100px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
    }

    .update-section button:hover {
        background:rgb(156, 153, 151);
    }

    #updateMessage {
        margin-top: 10px;
        font-size: 14px;
    }

    .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            margin: 10% auto;
            width: 400px;
            border-radius: 8px;
            text-align: center;
        }
        .modal-title {
            font-weight: 600;
            color: #333;
        }

        .modal-body {
            padding: 1rem 1.5rem;
            background-color: #fff;
            max-height: 500px;
            overflow-y: auto;
        }

        .modal-body table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .modal-body table thead {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        .modal-body table th,
        .modal-body table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .modal-body table td:nth-child(4),
        .modal-body table td:nth-child(3) {
            text-align: right;
            white-space: nowrap;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }

        .btn-close {
            background: transparent;
            border: none;
            font-size: 1.2rem;
            opacity: 0.8;
        }

        .btn-close:hover {
            opacity: 1;
        }
        .close {
            float: right;
            width: 30px;
            font-size: 22px;
            cursor: pointer;
            position: absolute;
            right: auto;
            margin-left: 0px;
            border-radius: 10px;
            background-color:rgb(249, 45, 45);
            box-shadow: 0 0px 10px rgba(0, 0, 0, 0.5);
        }
        .modal input {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
        }
        .modal button {
            background: #dc3545;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        .sold_items_container {
            border: none;
            justify-content: center;
            border-radius: 12px;
            width: 1000px;
            max-width: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 20px;
            background: #ffffff;
            margin: 30px auto;
        }
        .sold_items_container h2 {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .cancelled-orders-container{
            border: none;
            justify-content: center;
            border-radius: 12px;
            width: 1000px;
            max-width: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 20px;
            background: #ffffff;
            margin: 30px auto;
        }
        .button-row {
            display: flex;
            gap: 10px; /* Space between buttons */
            margin-bottom: 15px;
        }

        .small-button {
            /*padding: 4px 10px;*/
            font-size: 13px;
            background-color:rgb(243, 124, 14);
            border: 1px solid #ccc;
            border-radius: 9px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .small-button:hover {
            background-color: #e0e0e0;
        }

        .small-button:active {
            background-color: #d0d0d0;
        }


    </style>
</head>
<body>
    
    <br><br><br><br><br><br>

<div class="sales-container">
    <h1>Sales Report</h1>
    <h2 style="text-align: left;"><?php echo htmlspecialchars($restaurantName); ?></h2>
    <h3 style="text-align: left;">Restaurant Owner: <?php echo htmlspecialchars($restaurantOwner); ?></h3>

    <div class="stats-box">
        <div class="stat">
            <h3>Total Sales</h3>
            <h2>Php <?php echo number_format($totalSales, 2); ?></h2>
        </div>
        <div class="stat" style="background: #28A745;">
            <h3>Total Customers</h3>
            <h2><?php echo number_format($totalCustomers); ?></h2>
        </div>
        <div class="stat" style="background: #FFC107;">
            <h3>Total Items Sold</h3>
            <h2><?php echo number_format($totalItemsSold); ?></h2>
        </div>
    </div>

    <div class="chart-container">
        <h3>Total Sales (Php)</h3>
        <canvas id="salesChart"></canvas>
    </div>

    <div class="chart-container">
        <h3>Total Customers</h3>
        <canvas id="customersChart"></canvas>
    </div>

    <div class="chart-container">
        <h3>Total Items Sold</h3>
        <canvas id="itemsChart"></canvas>
    </div>

</div>
<div class="sold_items_container">
    <div class="table-container">
        <h2>Sold Items</h2>
    <div class="button-row">
        <button class="small-button" id="viewToday">View Today's Sales</button>
        <button class="small-button" id="viewAllTime">View All-Time Sales</button>
    </div>
    <h5 id="soldItemsIndicator" class="text-info mb-2"></h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Sold Quantity</th>
                    <th>Total Sales (Php)</th>
                    <th>Trend</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="soldItemsTable">
                <!-- Sold items data will be populated here -->
            </tbody>
        </table>
    </div>
</div>
<div class="cancelled-orders-container">
<div class="table-container">
        <h2>Cancelled Orders</h2>
            <div class="d-flex align-items-center gap-2 mb-2">
                <label for="cancelledDate">Select Date:</label>
                <input type="date" id="cancelledDate" value="<?php echo date('Y-m-d'); ?>" class="form-control" style="max-width: 200px;">
                <label> or </label>
                <button id="viewAllCancelled" class="btn btn-sm btn-outline-secondary" style="width: 250px; border-radius: 14px;">View All-Time Cancelled</button>
            </div>
            <h5 id="cancelledIndicator" class="text-primary mb-2"></h5>
        <table class="table table-bordered" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Total Quantity</th>
                    <th>Total Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="cancelledOrdersTable">
                <!-- Cancelled items will appear here -->
            </tbody>
        </table>

        <h3 id="cancelledTotalPrice" class="text-end text-danger mt-2">
            <!-- Total will appear here -->
        </h3>
    </div>
</div>

<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderModalLabel">Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">Close</button>
      </div>
      <div class="modal-body" id="orderModalBody">
        <!-- Populated dynamically -->
      </div>
    </div>
  </div>
</div>

<!-- Graph Modal -->
<div id="graphModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeGraphModal()">&times;</span><br><br>
        <h3>Sales Graph for <span id="itemNameGraph"></span></h3>
        <canvas id="itemSalesChart"></canvas>
    </div>
</div>

<label style="font-weight:light; text-align: left;">Reset Sales Report (this action requires admin authentication)</label>
<button class="reset-button" style="width: 120px;" onclick="openModal()">Reset</button>

<div class="update-section">
    <h2>Restaurant Details</h2>
    <form id="updateRestForm">
        <label>Restaurant Name:</label>
        <input type="text" id="newRestName" placeholder="<?php echo htmlspecialchars($restaurantName); ?>" required>
        <label>Owner: </label>
        <input type="text" id="newRestOwner" placeholder="<?php echo htmlspecialchars($restaurantOwner); ?>" required><br>
        <button type="submit" id="updateRestBtn">Update</button>
    </form>
    <p id="updateMessage"></p>
</div>

<div id="passwordModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Enter Admin Password</h3>
        <input type="password" id="adminPasswordInput" placeholder="Enter password">
        <button onclick="resetSales()">Confirm</button>
    </div>
</div>
<script>
    let itemChartInstance = null;

document.addEventListener("DOMContentLoaded", function () {
    renderMainCharts();
    fetchSoldItems();
});

function renderMainCharts() {
    const ctxSales = document.getElementById('salesChart').getContext('2d');
    const ctxCustomers = document.getElementById('customersChart').getContext('2d');
    const ctxItems = document.getElementById('itemsChart').getContext('2d');

    new Chart(ctxSales, {
        type: 'bar',
        data: {
            labels: <?php echo $dates; ?>,
            datasets: [{
                label: 'Total Sales (Php)',
                data: <?php echo $sales; ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.7)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    new Chart(ctxCustomers, {
        type: 'line',
        data: {
            labels: <?php echo $dates; ?>,
            datasets: [{
                label: 'Total Customers',
                data: <?php echo $customers; ?>,
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    new Chart(ctxItems, {
        type: 'bar',
        data: {
            labels: <?php echo $dates; ?>,
            datasets: [{
                label: 'Total Items Sold',
                data: <?php echo $items; ?>,
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
}

// ================= Update Restaurant Info =================
$(document).ready(function () {
    $("#updateRestForm").submit(function (event) {
        event.preventDefault();

        const newName = $("#newRestName").val().trim();
        const newOwner = $("#newRestOwner").val().trim();

        if (!newName || !newOwner) {
            $("#updateMessage").text("Fields cannot be empty.").css("color", "red");
            return;
        }

        $("#updateRestBtn").text("Updating...").prop("disabled", true);

        $.post("../../admin/account_and_login/update_rest_name.php", { restaurant_name: newName, restaurant_owner: newOwner }, function (response) {
            if (response.trim() === "success") {
                $("#updateMessage").text("Details Updated Successfully!").css("color", "green");
                setTimeout(() => location.reload(), 1000);
            } else {
                $("#updateMessage").text(response).css("color", "red");
            }
        }).fail(function () {
            $("#updateMessage").text("Server error. Please try again.").css("color", "red");
        }).always(function () {
            $("#updateRestBtn").text("Update").prop("disabled", false);
        });
    });
});

// ================= Reset Sales =================
function openModal() {
    $("#passwordModal").show();
}

function closeModal() {
    $("#passwordModal").hide();
}

function resetSales() {
    const password = $("#adminPasswordInput").val().trim();
    if (!password) return alert("Please enter a password.");
    if (!confirm("This action cannot be undone. Are you sure?")) return;

    $.post("../../admin/dashboard/reset_sales.php", { password }, function (response) {
        if (response.trim() === "success") {
            alert("Sales report has been reset successfully.");
            location.reload();
        } else {
            alert("Incorrect password or an error occurred.");
        }
    });
}

// ================= Fetch & Display Sold Items =================
function fetchSoldItems() {
    $.get("../../admin/dashboard/fetch_sold_items.php", function (response) {
        try {
            const data = JSON.parse(response);
            const todayItems = data.today || [];
            const yesterdayItems = data.yesterday || [];
            const allTimeItems = data.all_time || [];

            const yesterdayMap = {};
            yesterdayItems.forEach(item => {
                yesterdayMap[item.item_id] = item.sold_quantity;
            });

            todayItems.forEach(item => {
                const yesterdayQty = yesterdayMap[item.item_id] || 0;
                if (item.sold_quantity > yesterdayQty) {
                    item.trend = "up";
                } else if (item.sold_quantity < yesterdayQty) {
                    item.trend = "down";
                } else {
                    item.trend = "same";
                }
            });

            function displayTodayItems() {
                $("#soldItemsIndicator").html(`Showing: <strong>Today's Sold Items</strong>`);

                if (todayItems.length === 0) {
                    return $("#soldItemsTable").html(`<tr><td colspan="5" class="text-warning">No sold items found today.</td></tr>`);
                }

                todayItems.sort((a, b) => b.sold_quantity - a.sold_quantity);

                const rows = todayItems.map(item => {
                    let trendArrow = "";
                    if (item.trend === "up") trendArrow = `<span class="text-success">🔺</span>`;
                    else if (item.trend === "down") trendArrow = `<span class="text-danger">🔻</span>`;
                    else trendArrow = `<span class="text-muted">⏺</span>`;

                    return `
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.sold_quantity}</td>
                            <td>Php ${formatCurrency(item.total_sales)}</td>
                            <td>${trendArrow}</td>
                            <td><button class="btn btn-info view-graph" data-item="${item.name}" data-item-id="${item.item_id}">View Graph</button></td>
                        </tr>
                    `;
                }).join("");

                $("#soldItemsTable").html(rows);
            }

            function displayAllTimeItems() {
                $("#soldItemsIndicator").html(`Showing: <strong>Overall Sold Items</strong>`);

                if (allTimeItems.length === 0) {
                    return $("#soldItemsTable").html(`<tr><td colspan="5" class="text-warning">No sold items found.</td></tr>`);
                }

                allTimeItems.sort((a, b) => b.sold_quantity - a.sold_quantity);

                const rows = allTimeItems.map(item => `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.sold_quantity}</td>
                        <td>Php ${formatCurrency(item.total_sales)}</td>
                        <td><span class="text-muted">–</span></td>
                        <td><button class="btn btn-info view-graph" data-item="${item.name}" data-item-id="${item.item_id}">View Graph</button></td>
                    </tr>
                `).join("");

                $("#soldItemsTable").html(rows);
            }

            function formatCurrency(amount) {
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(parseFloat(amount));
            }

            // Initial display
            displayTodayItems();

            // Toggle buttons
            $("#viewToday").on("click", displayTodayItems);
            $("#viewAllTime").on("click", displayAllTimeItems);

        } catch (error) {
            console.error("JSON Parse Error:", error);
            $("#soldItemsTable").html(`<tr><td colspan="5" class="text-danger">Invalid server response.</td></tr>`);
        }
    }).fail(function () {
        $("#soldItemsTable").html(`<tr><td colspan="5" class="text-danger">Failed to fetch sold items.</td></tr>`);
    });
}

// ================= Modal Item Chart =================

$(document).on("click", ".view-graph", function () {
    const itemId = $(this).data("item-id");
    const itemName = $(this).data("item");

    $("#itemNameGraph").text(itemName);
    $("#graphModal").show();

    fetchItemSalesChart(itemId);
});

function fetchItemSalesChart(itemId) {
    $("#itemSalesChart").replaceWith('<canvas id="itemSalesChart" height="300"></canvas>');

    $.get("../../admin/dashboard/fetch_items_sales.php", { item_id: itemId }, function (response) {
        try {
            const data = response;

            if (data.error) {
                console.error("Server error:", data.error);
                alert("Server returned an error.");
                return;
            }

            const ctx = document.getElementById("itemSalesChart").getContext("2d");

            if (itemChartInstance) itemChartInstance.destroy();

            itemChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.dates,
                    datasets: [{
                        label: 'Sales (Php)',
                        data: data.sales,
                        borderColor: 'rgba(0, 123, 255, 1)',
                        backgroundColor: 'rgba(0, 123, 255, 0.2)',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });
        } catch (error) {
            console.error("Parse Error:", error);
            alert("Failed to load item chart.");
        }
    }).fail(function () {
        alert("Error retrieving item chart data.");
    });
}

function closeGraphModal() {
    $("#graphModal").hide();
    if (itemChartInstance) {
        itemChartInstance.destroy();
        itemChartInstance = null;
    }
}
document.addEventListener("DOMContentLoaded", () => {
    const today = $("#cancelledDate").val();
    fetchCancelledOrders(today);

    $("#cancelledDate").on("change", function () {
        fetchCancelledOrders(this.value);
    });
});
$(document).ready(function () {
    const today = $("#cancelledDate").val();
    fetchCancelledOrders(today); // Default load

    // When a specific date is selected
    $("#cancelledDate").on("change", function () {
        fetchCancelledOrders($(this).val(), false);
    });

    // When "View All-Time Cancelled" button is clicked
    $("#viewAllCancelled").on("click", function () {
        fetchCancelledOrders('', true);
    });
});

function fetchCancelledOrders(date, isAllTime = false) {
  $.get("../../admin/dashboard/fetch_cancelled_order.php", { date, all_time: isAllTime }, function (response) {
    try {
      const { orders = [], total = 0 } = JSON.parse(response);

      const label = isAllTime
        ? "Showing: <strong>All-Time Cancelled Orders</strong>"
        : `Showing: <strong>Cancelled Orders for ${new Date(date).toDateString()}</strong>`;
      $("#cancelledIndicator").html(label);

      if (!orders.length) {
        const msg = isAllTime
          ? "No cancelled orders found."
          : "No cancelled orders found for this day.";
        $("#cancelledOrdersTable").html(`<tr><td colspan="4" class="text-warning">${msg}</td></tr>`);
        $("#cancelledTotalPrice").text("");
        return;
      }

      const grouped = orders.reduce((acc, order) => {
        if (!acc[order.customer_name]) acc[order.customer_name] = [];
        acc[order.customer_name].push(order);
        return acc;
      }, {});

      const rows = Object.entries(grouped).map(([customer, orders]) => {
        const totalQty = orders.reduce((sum, o) => sum + parseInt(o.quantity), 0);
        const totalPrice = orders.reduce((sum, o) => sum + (o.price * o.quantity), 0).toFixed(2);
        return `
          <tr>
            <td><strong>${customer}</strong></td>
            <td>${totalQty}</td>
            <td>₱${totalPrice}</td>
            <td>
              <button class="btn btn-sm btn-primary" 
                      data-bs-toggle="modal" 
                      data-bs-target="#orderModal" 
                      data-customer="${customer}">
                View
              </button>
            </td>
          </tr>`;
      }).join("");

      $("#cancelledOrdersTable").html(rows);
      const overallTotal = orders.reduce((sum, o) => sum + (o.price * o.quantity), 0);
      const formattedTotal = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2
        }).format(overallTotal);

        $("#cancelledTotalPrice").text(`Total Cancelled Amount: ${formattedTotal}`);

      // Modal detail logic
      $("#cancelledOrdersTable").off("click").on("click", "button[data-customer]", function () {
        const customer = $(this).data("customer");
        const items = grouped[customer] || [];

        const detailRows = items.map(order => `
          <tr>
            <td>${order.item_name}</td>
            <td>${order.quantity}</td>
            <td>₱${parseFloat(order.price).toFixed(2)}</td>
            <td>₱${(order.price * order.quantity).toFixed(2)}</td>
            <td>${new Date(order.cancelled_at.replace(' ', 'T') + 'Z').toLocaleString()}</td>
          </tr>
        `).join("");

        $("#orderModalLabel").text(`Cancelled Orders for ${customer}`);
        $("#orderModalBody").html(`
          <table class="table table-sm">
            <thead>
              <tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th><th>Cancelled At</th></tr>
            </thead>
            <tbody>${detailRows}</tbody>
          </table>
        `);
      });

    } catch (err) {
      console.error("Parsing Error:", err);
      $("#cancelledOrdersTable").html('<tr><td colspan="4" class="text-danger">Error loading cancelled orders.</td></tr>');
      $("#cancelledTotalPrice").text('');
    }
  });
}

// Event listeners
$(document).ready(function () {
  const today = $("#cancelledDate").val();
  fetchCancelledOrders(today);

  $("#cancelledDate").on("change", function () {
    fetchCancelledOrders(this.value);
  });

  $("#viewAllCancelled").on("click", function () {
    fetchCancelledOrders('', true);
  });
});
</script>
</body>
</html>
