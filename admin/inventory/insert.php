<?php
include("../../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if fields are empty
    if (empty($_POST["name"]) || empty($_POST["price"]) || empty($_POST['description']) || !isset($_FILES["image"])) {
        die("❌ All fields are required.");
    }

    $name = htmlspecialchars(trim($_POST["name"]));
    $price = filter_var($_POST["price"], FILTER_VALIDATE_FLOAT);
    $description = $_POST['description'];

    if ($price === false) {
        die("❌ Invalid price value.");
    }

    // Allowed file types
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $upload_dir = "../../uploads/";

    // Create folder if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_name = $_FILES["image"]["name"];
    $image_tmp = $_FILES["image"]["tmp_name"];
    $image_size = $_FILES["image"]["size"];
    $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

    // Check if file type is allowed
    if (!in_array($image_ext, $allowed_extensions)) {
        die("❌ Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
    }

    // Check file size limit (5MB)
    if ($image_size > 5 * 1024 * 1024) {
        die("❌ File size exceeds the 5MB limit.");
    }

    // Rename Image with Timestamp
    $image_path = $upload_dir . time() . "_" . basename($image_name);

    $check_name = $conn->prepare("SELECT * FROM items_stock WHERE item_name = ?");
    $check_name->bind_param("s", $name);
    $check_name->execute();
    $result_name = $check_name->get_result();

    if ($result_name->num_rows > 0) {
        die("❌ Item Name already exists.");
    }
    $check_name->close();

    // 🔍 Check if the Image already exists
    $check_image = $conn->prepare("SELECT * FROM items_stock WHERE image = ?");
    $check_image->bind_param("s", $image_path);
    $check_image->execute();
    $result_image = $check_image->get_result();

    if ($result_image->num_rows > 0) {
        die("❌ Image already exists.");
    }
    $check_image->close();
    
    if (move_uploaded_file($image_tmp, $image_path)) {
        $stmt = $conn->prepare("INSERT INTO items_stock (item_name, price, image, description) VALUES (?, ?, ?, ?)");

        if (!$stmt) {
            die("❌ Database error: " . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("sdss", $name, $price, $image_path, $description);

        if ($stmt->execute()) {
            echo "✅ Item Inserted Successfully!";
        } else {
            echo "❌ Error: " . $stmt->error;
        } 

        $stmt->close();
    } else {
        echo "❌ Failed to upload image.";
    }
}


$conn->close();
?>
