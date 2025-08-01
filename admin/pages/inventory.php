<?php
    session_start();
    include("../../config/database.php");
    if (!isset($_SESSION['username'])) {
    header("Location: ../../index.html"); // Redirect to login page
    exit();
}
    include("../../ui/navbar.html");

    $sql = "SELECT * FROM items_stock";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    $sql_acc = "SELECT tables_number FROM business_account LIMIT 1";
    $result_acc = $conn->query($sql_acc);

    if ($result_acc) {
        if ($row = $result_acc->fetch_assoc()) {
            $tables_number = $row['tables_number'];
        } else {
            $tables_number = "No number of tables";
        }
    } else {
        die("Error fetching tables_number: " . $conn->error);
    }

    if(mysqli_num_rows($result) == 0){
        $sql = "ALTER TABLE items_stock AUTO_INCREMENT = 1";
        $conn->query($sql);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
            padding: 20px;
            margin: 0;
            color: #333;
        }

        #desc-fontstyle {
            font-size: smaller;
            font-weight: lighter;
            text-align: left;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
        }

        table {
            width: 90%;
            max-width: 1000px;
            margin-bottom: 40px;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #FF8C00;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:hover {
            background: #f9f9f9;
            transition: background 0.3s ease;
        }

        img {
            width: 80px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .container {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 30px;
        }
        .view-more-btn {
            background: #f47105;
            color: white;
            border: none;
            padding: 8px 15px;
            margin: 10px 0;
            max-width: 90%;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }
        
        .update-price-btn, .price-input{
            width: 130px;
        }
        .view-more-btn:hover {
            background: #868686;
            color: black;
        }

        .labeltable {
            font-size: 22px;
            font-weight: bold;
            background: #FF8C00;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        input, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            transition: 0.3s ease;
        }

        .delete-btn {
            background: #dc3545;
        }

        .delete-btn:hover {
            background: #ff4d4d;
        }

        #preview {
            max-width: 150px;
            margin-top: 10px;
            border: 1px solid #ccc;
            padding: 5px;
            border-radius: 5px;
            display: block;
        }
        #description{
            width: 300px;
            margin: 0;
        }
        .modal { 
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0, 0, 0, 0.5); 
        }
        .modal-content { 
            background: white; 
            margin: 15% auto; 
            padding: 20px; 
            width: 50%; 
            border-radius: 10px; 
        }
        .close-btn { 
            float: right; 
            font-size: 22px; 
            cursor: pointer; 
        }
        .update-stock-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .update-stock-btn:hover {
            background: #0056b3;
        }

        .stock-modal { 
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0, 0, 0, 0.5); 
        }
        .stock-modal-content { 
            background: white; 
            margin: 15% auto; 
            padding: 20px; 
            width: 40%; 
            border-radius: 10px; 
            text-align: center;
        }
        .close-stock-modal { 
            float: right; 
            font-size: 22px; 
            cursor: pointer; 
        }
    </style>
</head>
<body>
    
     <br><br><br><br><br><br>

<h2>List of Items</h2>
<?php if (mysqli_num_rows($result) > 0): ?>
<table>
    <tr>
        <th>Item Name</th>
        <th>Item Description</th>
        <th>Price</th>
        <th>Image</th>
        <th>Stocks</th>
        <th></th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr id="row-<?php echo $row['id']; ?>">
            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
            <td>
                <button class="btn view-more-btn btn-sm" data-id="<?php echo $row['id']; ?>" 
                data-name="<?php echo htmlspecialchars($row['item_name']); ?>" 
                data-desc="<?php echo htmlspecialchars($row['description']); ?>">View Description</button>
            </td>
            <td>
             ₱<input type="number" class="price-input" id="price-<?php echo $row['id']; ?>" 
                   value="<?php echo number_format((float)$row['price'], 2, '.', ''); ?>" 
                   data-id="<?php echo $row['id']; ?>" step="0.01"/>
            <button class="update-price-btn" data-id="<?php echo $row['id']; ?>">Update Price</button>
            </td>
            <td>
                <?php if (!empty($row['image'])): ?>
                    <img src="/uploads/<?php echo htmlspecialchars(basename($row['image'])); ?>" alt="Item Image">
                <?php else: ?>
                    No Image
                <?php endif; ?>
            </td>
            <td>
                <span id="stock-<?php echo $row['id']; ?>"><?php echo $row['stocks']; ?></span>
                <button class="update-stock-btn" data-id="<?php echo $row['id']; ?>" data-stock="<?php echo $row['stocks']; ?>">Update</button>
            </td>
            <td>
                <button class="delete-btn" data-id="<?php echo $row['id']; ?>">Delete</button>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
    <p>No items found in stock.</p>
<?php endif; ?>

<div id="descModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3 id="modalTitle"></h3>
        <textarea id="editDesc" rows="4" style="width: 100%;"></textarea>
        <button id="saveEdit">Save Changes</button>
    </div>
</div>

<div id="stockModal" class="stock-modal">
    <div class="stock-modal-content">
        <span class="close-stock-modal">&times;</span>
        <h3>Update Stock</h3>
        <input type="number" id="newStockValue" placeholder="Enter new stock quantity">
        <button id="saveStockUpdate">Save Changes</button>
    </div>
</div>


<div class="container">
    <div class="labeltable">Add an Item</div>
    <form id="stockForm">
        <label for="name">Name:</label>
        <input type="text" id="name" placeholder="Enter Item Name" required>

        <label for="price">Price:</label>
        <input type="number" id="price" placeholder="Enter Price" required>

        <label for="image">Image:</label>
        <input type="file" id="image" accept="image/*" onchange="previewImage(event)" required>
        <img id="preview" src="#" alt="Image Preview" style="display:none;">

        <label for="description" id="desc-fontstyle">Item Description: (add an item description later)</label>
        
        <button type="submit">Insert</button>
    </form>
</div>

<div class="container">
    <div class="labeltable">Update Number of Tables</div>
    <p>Current Table Numbers: <strong><?php echo $tables_number;?></strong></p>
    <form id="updateTablesForm" method="POST">
        <label for="newTableNumber">Enter New Table Number:</label>
        <input type="number" id="newTableNumber" name="newTableNumber" placeholder="Enter new table number" required>
        <button type="submit" id="update_table_num" >Update</button>
    </form>
    <?php
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['newTableNumber']))
        {
            $newTableNumber = intval($_POST['newTableNumber']);

            if ($newTableNumber >= 0 )
            {
            $tabsql = "UPDATE business_account SET tables_number = ?";
                $stmt_update_tabs = $conn->prepare($tabsql);
                $stmt_update_tabs->bind_param("i", $newTableNumber);

                if ($stmt_update_tabs->execute()) 
                {
                    echo "<script>alert('New table numbers updated successfully!'); window.location.href=window.location.href;</script>";
                } 
                else 
                {
                    echo "<script>alert('Error updating table numbers. Try again!');</script>";
                }
                $stmt_update_tabs->close();
            } 
            else
            {
                echo "<script>alert('Please enter a valid table number greater than 0!');</script>";
            }
        }
    ?>
</div>
    <script>
        function previewImage(event) {
            const preview = document.getElementById("preview");
            preview.src = URL.createObjectURL(event.target.files[0]);
            preview.style.display = "block";
        }

        $(document).ready(function () {
            $("#stockForm").on("submit", function (e) {
                e.preventDefault();

                let formData = new FormData();
                formData.append("name", $("#name").val());
                formData.append("description", $("#description").val());
                formData.append("price", $("#price").val());
                let imageFile = $("#image")[0].files.length > 0 ? $("#image")[0].files[0] : null;
                formData.append("image", imageFile);

                $.ajax({
                    url: "../../admin/inventory/insert.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        alert(response);
                        location.reload();
                    }
                });
            });


            $(".delete-btn").on("click", function () {
                let itemId = $(this).data('id');
                let row = $("#row-" + itemId);

                if (confirm("Are you sure you want to delete this item?")) {
                    $.ajax({
                        url: "../../admin/inventory/delete.php",
                        type: "POST",
                        data: { id: itemId },
                        success: function (response) {
                            if (response === "success") {
                                alert("Item and image deleted successfully");
                                location.reload();
                            } else {
                                alert("Error deleting item.");
                            }
                        }
                    });
                }
            });
        });
        $(document).ready(function () {
        let editId = null;

        $(".view-more-btn").on("click", function () {
            editId = $(this).data("id");
            let itemName = $(this).data("name");
            let itemDesc = $(this).data("desc");
            
            $("#modalTitle").text(itemName);
            $("#editDesc").val(itemDesc);
            $("#descModal").fadeIn();
        });

        $(".close-btn").on("click", function () {
            $("#descModal").fadeOut();
        });

        $("#saveEdit").on("click", function () {
            let newDesc = $("#editDesc").val().trim();

            if (newDesc === "") {
                alert("Description cannot be empty!");
                return;
            }

            $.ajax({
                url: "../../admin/inventory/update_desc.php",
                type: "POST",
                data: { id: editId, description: newDesc },
                success: function (response) {
                    if (response === "success") {
                        alert("Description updated successfully!");
                        $("#descModal").fadeOut();
                    } else {
                        alert("Failed to update description.");
                    }
                }
            });
        });
    });

    $(document).ready(function () {
        let editStockId = null;

        $(".update-stock-btn").on("click", function () {
            editStockId = $(this).data("id");
            let currentStock = $(this).data("stock");

            $("#newStockValue").val(currentStock);
            $("#stockModal").fadeIn();
        });

        $(".close-stock-modal").on("click", function () {
            $("#stockModal").fadeOut();
        });

        $("#saveStockUpdate").on("click", function () {
            let newStock = $("#newStockValue").val().trim();

            if (newStock === "" || isNaN(newStock) || newStock < 0) {
                alert("Please enter a valid stock quantity!");
                return;
            }
            $.ajax({
                url: "../../admin/inventory/update_stock.php",
                type: "POST",
                data: { id: editStockId, stocks: newStock },
                success: function (response) {
                    if (response === "success") {
                        alert("Stock updated successfully!");
                        $("#stock-" + editStockId).text(newStock);
                        $("#stockModal").fadeOut();
                    } else {
                        alert("Error updating stock.");
                    }
                }
            });
        });
    });
    $(document).ready(function () {
    $(".update-price-btn").on("click", function () {
        let itemId = $(this).data('id');
        let newPrice = $("#price-" + itemId).val().trim();

        if (newPrice === "" || isNaN(newPrice) || newPrice < 0) {
            alert("Please enter a valid price!");
            return;
        }

        $.ajax({
            url: "../../admin/inventory/update_price.php",
            type: "POST",
            data: { id: itemId, price: newPrice },
            success: function (response) {
                if (response === "success") {
                    alert("Price updated successfully!");
                } else {
                    alert("Error updating price.");
                }
            }
        });
    });
});

    </script>

</body>
</html>

<?php
mysqli_close($conn);
?>