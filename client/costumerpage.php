<?php
session_start();
include("../config/database.php");

$sql = "SELECT * FROM items_stock";
$result = $conn->query($sql);

$sql2 = "SELECT * FROM business_account";
$rsltname = $conn->query($sql2);

$sql3 = "SELECT tables_number FROM business_account LIMIT 1";
$tables_number = $conn->query($sql3);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$foodItems = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $foodItems[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering System</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../assets/style.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            min-height: 100vh;
            background: url(../assets/image/bckr16.jpg);
            background-position: center;
            background-size: cover;
            background-blend-mode: overlay;
        }
        #menu {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }
        .food-item {
            width: 200px;
            padding: 10px;
            cursor: pointer;
            transition: transform 0.3s;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(16px);
            border: 3px solid rgba(150, 122, 145);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(15, 14, 14, 0.86); 
        }
        .food-item:hover {
            transform: scale(1.1);
        }
        .food-item img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }
        #details-container, #customer-form {
            display: none; /* Hidden by default */
            padding: 50px;
            background: rgba(233, 231, 231, 0.89);
            backdrop-filter: blur(16px);
            border: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 10px 30px rgba(223, 220, 220, 0.89); 
            border-radius: 15px;
            text-align: left;
            z-index: 4;
            width: 300px;
            max-width: 100%;
            max-height: 80vh; /* Limit height for smaller screens */
            overflow: auto; /* Scroll if content is too big */
            box-sizing: border-box;
            animation: fadeIn 0.3s ease-in-out; /* Smooth fade-in effect */
        }
        .view-more-btn {
            width: 200px;
            box-shadow: 0 2px 10px rgba(15, 14, 14, 0.86); 
        }
        #overlay {
                display: none; /* Hidden by default */
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.4); /* Dark overlay */
                backdrop-filter: blur(5px); /* Blur background for focus effect */
                z-index: 1;
        }
    
        .show {
            display: block !important;
        } 
        .close {
            text-align: center;
            width: 15%;
            padding-top: 1%;
            padding-bottom: 1%;
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            background: red;
            border: none;
            box-shadow: 0 2px 8px rgba(15, 14, 14, 0.86); 
            cursor: pointer;
            transition: color 0.3s;
        }
        .close:hover {
            background-color:rgb(245, 85, 36);
        }
        #cart-icon {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background:rgb(88, 85, 80);
            color: white;
            padding: 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 18px;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        #cart-icon {
            animation: cartGlow 1s infinite alternate;
        }
        @keyframes cartGlow {
            from {
                box-shadow: 0 0 0px black;
            }
            to {
                box-shadow: 0 0 20px black;
            }
        }

        #cart-icon span {
            position: absolute;
            top: 5px;
            right: 5px;
            background: red;
            color: white;
            font-size: 12px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #cart-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(233, 231, 231, 0.89);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(223, 220, 220, 0.89); 
            z-index: 1001;
            width: 460px;
            max-height: 80vh;
            overflow: auto;
        }
        #cart-modal.show {
            display: block;
        }
        #cart-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 2;
        }
        
        .cart-table-container {
            max-height: 150px; /* Limit height */
            overflow: auto;
            margin-bottom: 10px;
        }
        
        #cart-modal table {
            width: 80%;
            border-collapse: collapse;
            overflow: auto;
        }
        
        #cart-modal th,
        #cart-modal td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        #cart-modal button.checkout {
            padding: 8px 12px;
            cursor: pointer;
            margin-right: 10px;
            border-radius: 4px;
        }
        
        #cart-modal button.close {
            position: fixed;
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 15px;
        }
        
        #total-price {
            font-weight: bold;
            margin-top: 10px;
        }

        #details-image {
            width: 155px;
            height: auto;
            display: block;
            margin: 0 auto;
            object-fit: contain;
            box-shadow: 0 15px 20px rgba(41, 39, 39, 0.42),
            0 15px 50px rgba(41, 39, 39, 0.42);
            transition: transform 0.2s ease-in-out;
        }
        .head-container{
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .sys_head {
            position: relative;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(30px);
            padding: 25px;
            width: 600px;
            text-align: center;
            border-radius: 20px;
            color: black;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 0 10px rgba(130, 4, 103, 1);
        }
         table {
             height: 380px;
         }
        .sys_head::before {
            content: "";
            position: absolute;
            inset: -3px; /* Expand the border */
            border-radius: inherit;
            background: linear-gradient(to right, brown, white, purple);
            z-index: -1;
        
            /* Mask to make the inner part transparent */
            padding: 3px;
            -webkit-mask: linear-gradient(white, white) content-box, linear-gradient(white, white);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background: rgba(233, 231, 231, 0.89);
            backdrop-filter: blur(16px);
            border: none;
            box-shadow: 0 10px 30px rgba(223, 220, 220, 0.89); 
            border-radius: 16px;
            overflow: auto;
        }
        
        #description-modal{
            height: 400px;
            overflow: auto;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.9); /* Slight transparency */
            border-radius: 10px;
            height: 300px;
            padding: 20px;
            max-height: 100vh; /* Make it scrollable */
            overflow-y: auto;
            width: 400px; /* Adjust width as needed */
            text-align: center;
        }

        #total-price, #form-total-price {
        font-size: 20px;
        font-weight: bold;
        text-align: right;
        color: #d32f2f; /* Red color to stand out */
        }


        .checkout {
            background: #0794db;
            color: white;
            padding: 10px 20px;
            width: 120px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .checkout:hover {
            background: #067cb8;
        }
            @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translate(-50%, -55%);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%);
        }
}
        @media (max-width: 768px) {
        table img {
            width: 60px; /* Smaller image for mobile */
        }
        #details-container, #customer-form{
            max-width: 90%;
        }
        .modal-content {
            width: 270px;
            height: 380px;
            max-height: 80vh;
        }
        .checkout {
            width: 100%; /* Full width on mobile */
        }
        #cart-modal{
            max-width: 80%;
        }
        h1{
            font-size: 24px;
        }
        h2{
            font-size: 18px;
        }
}

    @media (max-width: 480px) {
    #details-container, #customer-form{
        max-width: 90%;
    }
    .modal-content {
        width: 270px;
        height: 380px;
        max-height: 80vh;
    }
    .checkout {
        width: 100%; /* Full width on mobile */
    }
    #cart-modal{
        max-width: 80%;
    }
    h1{
        font-size: 24px;
    }
    h2{
        font-size: 18px;
    }
    .view-more-btn {
        width: 215px;
    }
}


    </style>
</head>
<body>
<div class="hero">
    <div class="head-container">
        <div class="sys_head">
        <h1>Food Ordering System</h1>
        <h2>Welcome to <?php while ( $rownme = $rsltname->fetch_assoc()){
            echo $rownme['restaurant_name'];}?></h2>
        </div>
    </div>
<div id="menu">
    <?php foreach ($foodItems as $item) { 
    $imageWebPath = '/uploads/' . basename($item['image']);?>
        <div class="food-item" 
            onclick="show_details(this)"
            data-id="<?php echo $item['id']; ?>"
            data-name="<?php echo addslashes($item['item_name']); ?>"
            data-price="<?php echo $item['price']; ?>"
            data-image="<?php echo addslashes($imageWebPath); ?>"
            data-description='<?php echo json_encode($item['description'], JSON_HEX_APOS | JSON_HEX_QUOT); ?>'
            data-stocks="<?php echo $item['stocks']; ?>">
            <img src="<?php echo $imageWebPath; ?>" alt="<?php echo $item['item_name']; ?>">
            <p><?php echo $item['item_name']; ?> - <?php echo $item['price']; ?> pesos</p>
            <p class="stocks-display">Stocks: <?php echo $item['stocks']; ?></p>
        </div>
    <?php } ?>
</div>
</div>

<div id="details-container">
    <button class="close" onclick="closeDetails()">X</button>
    <img id="details-image" src="" alt="Item Image">
    <h3 id="item-name"></h3>
    <p>Price: <span id="item-price"></span> pesos</p>
    <p>Stocks: <span id="stocks-number"></p>
    <button class="view-more-btn" onclick="showDescriptionModal()">
        Item Description
    </button>
    <input type="number" id="quantity" min="1" value="1">
    <button onclick="addToCart()">Add to Cart</button>
</div>

<div id="description-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDescriptionModal()" style="border-radius: 10px;">&times;</span>
        <h3>Item Description</h3>
        <p id="description-text">Loading...</p>
    </div>
</div>


<div id="cart-icon" onclick="toggleCart()">
    🛒 My cart<span id="cart-count">0</span>
</div>

<div id="cart-overlay" onclick="toggleCart()"></div>
<div id="cart-modal">
    <h2>Your Cart</h2>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="cart"></tbody>
    </table>
    <p id="total-price">Total: 0 pesos</p>
    <button class="checkout" onclick="placeOrder()">Checkout</button>
    <button class="close" onclick="toggleCart()">x</button>
</div>

<div id="customer-form">
    <button class="close" onclick="closeCustomerForm()">X</button>
    <h2>Customer Information</h2>
    <input type="text" id="customer-name" placeholder="Enter Name"><br>
    <label>Dine in or Take out?</label>
    <select id="table-number">
        <?php
            $max_table = 1; // Default value in case no result is found

            if ($tables_number->num_rows > 0) {
                $rowtab = $tables_number->fetch_assoc();
                $max_table = (int) $rowtab['tables_number'];
                echo "<option>Take out Order (No table)</option>";
                
                 for ($i = 1; $i <= $max_table; $i++) {
                echo "<option value='$i'>Table $i</option>";
                }
            } else {
                echo "<option>Take out Order (No table)</option>";
            }
        ?>
    </select>
    <label style="font-weight: light; font-size: xx-small;">
        If you want to dine in, please select a table.
    </label>
    <p id="form-total-price">Total: 0 pesos</p>
    <label style="font-weight: light; font-size: xx-small;">
        Please take note:<br>
This system currently accepts cash payments only.
For cashless payments, transactions can be processed externally with assistance from the seller or waiter.
       </label>
    <button id="submit_order" onclick="submitOrder()">Checkout</button>
</div>

<div id="order-processing-modal" class="modal" style="display: none;">
    <div class="modal-content" style="text-align: center; padding: 20px;">
        <h3>Please do not close this page</h3>
        <p><strong>Name:</strong> <span id="customer-name-display"></span></p>
        <p><strong>Order No:</strong> <span id="order-number-display"></span></p>
        <p>Your order confirmation is being processed.</p>
        <p style="font-weight: light; font-size: smaller">(You can take a screenshot for this window and present it to the counter)</p>
        <button onclick="closeProcessingModal()">Okay</button>
    </div>
</div>

<div id="overlay"></div>

<script>
    let cart = [];

    window.onclick = function(event) {
    if (event.target == document.getElementById("overlay")) {
        closeDetails();
        closeCustomerForm();
    }
}

    function show_details(element) {
        let id = element.dataset.id;
        let name = element.dataset.name;
        let price = element.dataset.price;
        let image = element.dataset.image;
        let description = element.dataset.description ? JSON.parse(element.dataset.description) : "No description available.";
        //let stock = element.dataset.stocks;

        document.getElementById("item-name").innerText = name;
        document.getElementById("item-price").innerText = price;
        document.getElementById("details-image").src = image;
        document.getElementById("details-container").dataset.id = id;
        document.getElementById("details-container").dataset.description = description;
        //document.getElementById("stocks-number").innerText = stock;
        
        fetchStockForItem(id);
        document.getElementById("details-container").classList.add("show");
        document.getElementById("overlay").style.display = "block";
    }
    
   function updateStock() {
       if ($("#details-container").hasClass("show")) return;
       
    $.ajax({
        url: "../admin/inventory/fetch_stock.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            $(".food-item").each(function() {
                let itemId = $(this).data("id");
                if (data[itemId] !== undefined) {
                    $(this).find(".stocks-display").text(`Stocks: ${data[itemId]}`);
                }
            });

            // If details modal is open, update its stock display
            let openItemId = $("#details-container").data("id");
            if (openItemId && data[openItemId] !== undefined) {
                $("#stocks-number").text(data[openItemId]);
            }
        }
    });
}

function fetchStockForItem(id) {
    $.ajax({
        url: "../admin/inventory/fetch_stock.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            if (data[id] !== undefined) {
                document.getElementById("stocks-number").innerText = data[id];
            } else {
                document.getElementById("stocks-number").innerText = "0";
            }
        },
        error: function() {
            console.error("Error fetching stock for item ID:", id);
            document.getElementById("stocks-number").innerText = "N/A";
        }
    });
}


// Run stock update every 2 seconds
setInterval(updateStock, 2000);

    function showDescriptionModal() {
        let description = document.getElementById("details-container").dataset.description || "No description available.";
        document.getElementById("description-text").innerText = description || "No description available.";
        document.getElementById("description-modal").style.display = "block";
    }

    function closeDescriptionModal() {
        document.getElementById("description-modal").style.display = "none";
    }

    function toggleCart() {
        document.getElementById("cart-modal").classList.toggle("show");
        document.getElementById("cart-overlay").style.display = document.getElementById("cart-modal").classList.contains("show") ? "block" : "none";
    }


    function closeDetails() {
        document.getElementById("details-container").classList.remove("show");
        document.getElementById("description-modal").style.display = "none";
        $('#overlay').hide();
    }

    function addToCart() {
        let id = document.getElementById("details-container").dataset.id;
        let name = document.getElementById("item-name").innerText;
        let price = parseFloat(document.getElementById("item-price").innerText);
        let quantity = parseInt(document.getElementById("quantity").value);
        let stock = parseInt(document.getElementById("stocks-number").innerText);
        
        if (stock == 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Action cannot process!',
                text: 'Item unavailable for the meantime.',
            })
            return;
        }
        
        if (quantity > stock) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Quantity!',
                text: 'Please enter a valid quantity within the available stock.',
            })
            return;
        }

        let existing = cart.find(item => item.id === id);

        if (existing) {
            existing.quantity += quantity;
        } else {
            cart.push({ id, name, price, quantity });
        }

        document.getElementById("quantity").value = 1;
        document.getElementById("stocks-number").innerText = stock - quantity;
        updateCart();
        closeDetails();
    }

    function updateCart() {
        let total = 0;
        $('#cart').empty();
        
        if (cart.length === 0) {
            $('#cart').html('<tr><td colspan="4">Your cart is empty</td></tr>');
        } else {
            cart.forEach((item, index) => {
                let row = `<tr>
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td>${item.price * item.quantity} pesos</td>
                    <td><button onclick="removeItem(${index})">Remove</button></td>
                </tr>`;
                $('#cart').append(row);
                total += item.price * item.quantity;
            });
        }
        
        $('#total-price').html(`<strong>Total: ${total.toLocaleString()} pesos</strong>`);
        $('#form-total-price').html(`<strong>Total: ${total.toLocaleString()} pesos</strong>`);
        $("#cart-count").text(cart.length);
    }

    function removeItem(index) {
        Swal.fire({
        title: "Are you sure?",
        text: "You want to remove this item?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, Remove!",
        cancelButtonText: "No, Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            cart.splice(index, 1);
            updateCart();
        }
    });
    }

    function placeOrder() {
        $('#cart-overlay').hide();
        if (cart.lenght === 0){
            Swal.fire({
                icon: 'warning',
                title: 'Cart is Empty!',
                text: 'Please add items before checking out',
            });
            return;
        }
        document.getElementById("cart-modal").classList.remove("show");
        document.getElementById("cart-overlay").style.display = "none";
        document.getElementById("form-total-price").innerText = document.getElementById("total-price").innerText;
        document.getElementById("customer-form").classList.add("show");
        document.getElementById("overlay").style.display = "block";
        document.getElementById("overlay").classList.add("block");
    }

    function closeCustomerForm() {
        document.getElementById("customer-form").classList.remove("show");
        $('#overlay').hide();
    }

    function submitOrder() {
        $('#cart-overlay').hide();
        let name = document.getElementById("customer-name").value;
        let table = document.getElementById("table-number").value;

    if (name === "") {
        alert("Please Enter Customer Name!");
        return;
    }
}
function submitOrder() {
    let name = document.getElementById("customer-name").value;
    let table = document.getElementById("table-number").value;

    if (name === "") {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Please enter customer name!',
        });
        return;
    }

    if (cart.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Cart is Empty!',
            text: 'Please add items before placing the order.',
        });
        return;
    }

    let total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    $.ajax({
        url: "../admin/orders_&_payments/orders.php",
        method: "POST",
        data: {
            customer_name: name,
            table_number: table,
            cart_items: JSON.stringify(cart),
            total_price: total
        },
        dataType: "json",
        success: function(response) {
            let orderNumber = response.order_number;
            
            Swal.fire({
                title: "Order Placed Successfully!",
                icon: "success",
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                cart = []; 
                updateCart(); 
                closeCustomerForm(); 
                $("#customer-name").val("");
                
                document.getElementById("customer-name-display").innerText = name;
                document.getElementById("order-number-display").innerText = "#" + orderNumber;
                showProcessingModal();
            });
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong!',
            });
        }
    });
}
function showProcessingModal() {
    document.getElementById("order-processing-modal").style.display = "flex";
    document.getElementById("overlay").style.display = "block";
}

function closeProcessingModal() {
    document.getElementById("order-processing-modal").style.display = "none";
    location.reload();
}

</script>
<script>
$(document).ready(function () {
    let lastNotification = ""; // Initialize as an empty string to avoid showing notification on first load
    let notificationSound = new Audio("../notifications/noti_sounds/order_accepted_bell.mp3");
    let isFirstRequest = true; // Flag to skip notification on the first request
    let showNotifications = false; // Flag to control notification display

    function fetchNotifications() {
        $.ajax({
            url: "../notifications/fetch_notifications.php",
            type: "GET",
            success: function (response) {
                let data = JSON.parse(response);

                // Skip showing notification on the first request
                if (isFirstRequest) {
                    isFirstRequest = false; // After the first request, allow notifications
                    return; // Exit the function without showing any notification
                }

                // Only process if there is a valid message and it's not the same as the last one
                if (data.message && data.message !== lastNotification) {
                    lastNotification = data.message; // Update last message

                    // Show notification only if the flag is true
                    if (showNotifications) {
                        showNotification(data.message);
                        notificationSound.play();
                    }
                }
            },
            error: function () {
                console.error("Error fetching notifications.");
                // Optionally, you can show an error notification here
            }
        });
    }

    function showNotification(message) {
        let notification = $("<div class='notification-popup'>" + message + "</div>");
        $("body").append(notification);
        setTimeout(() => notification.fadeOut(400, function () { $(this).remove(); }), 10000);
    }

    // First request: start fetching immediately
    fetchNotifications(); // Make the first fetch manually

    // Start the interval for subsequent fetches
    setInterval(fetchNotifications, 1000); // Fetch new notifications every second

    // Example: Enable notifications after a certain condition
    // You can change this condition based on your application's logic
    setTimeout(() => {
        showNotifications = true; // Allow notifications to be shown after 5 seconds
    }, 5000); // Change the timeout duration as needed
});
</script>
<style>
    .notification-popup {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    animation: fadeIn 0.5s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

</style>
</body>
</html>