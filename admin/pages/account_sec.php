<?php
session_start();
include("../../config/database.php");
if (!isset($_SESSION['username'])) {
    header("Location: index.html"); // Redirect to login page
    exit();
}
include("../../ui/navbar.html");

$sqlusername = "SELECT username FROM business_account";
$stmtusername = $conn->prepare($sqlusername);
$stmtusername->execute();
$stmtusername->bind_result($username);
$stmtusername->fetch();
$stmtusername->close();

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = trim($_POST["current_password"]);
    $newPassword = trim($_POST["new_password"]);
    $confirmPassword = trim($_POST["confirm_password"]);

    // Fetch current hashed password from the database
    $sql = "SELECT password FROM business_account";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verify current password
    if (!password_verify($currentPassword, $hashedPassword)) {
        $message = "<span class='error'>Incorrect current password.</span>";
    } elseif (strlen($newPassword) < 6) {
        $message = "<span class='error'>New password must be at least 6 characters long.</span>";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "<span class='error'>New passwords do not match.</span>";
    } else {
        // Hash the new password
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password in the database
        $updateSql = "UPDATE business_account SET password = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("s", $newHashedPassword);

        if ($stmt->execute()) {
            $message = "<span class='success'>Password updated successfully!</span>";
        } else {
            $message = "<span class='error'>Error updating password.</span>";
        }

        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Security</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            margin: 0;
        }
        .container {
            background: white;
            padding: 25px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            width: 500px;
            text-align: left;
        }
        h2 {
            margin-bottom: 20px;
            font-size: 22px;
            color: #333;
        }
        .input-group {
            display: flex;
            align-items: center;
            width: 100%;
            margin: 10px 0;
        }
        input {
            width: 400px;
            flex: 1;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .toggle-btn {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            color: white;
            background-color: #007bff;
            transition: background 0.3s;
            margin-left: 5px;
        }
        .toggle-btn:hover {
            background-color: #0056b3;
        }
        button[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
            transition: background 0.3s;
        }
        button[type="submit"]:hover {
            background-color: #218838;
        }
        .error {
            color: red;
            font-size: 14px;
            display: block;
            margin-top: 5px;
        }
        .success {
            color: green;
            font-size: 14px;
            display: block;
            margin-top: 5px;
        }
        .terminate-btn 
        { 
            background-color: #dc3545; 
            color: white;
            width: 120px;
        }
        .terminate-btn:hover 
        { 
            background-color: #c82333; 
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 25px;
            width: 500px;
            border-radius: 10px;
            align-items: center;
            text-align: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }
        .cancel-btn {
            background-color: red;
            color: white;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        <style>
    /* Existing styles here ... */

    @media (max-width: 768px) {
        .container {
            width: 90%;
            padding: 20px;
        }

        input {
            width: 100%;
        }

        .input-group {
            flex-direction: column;
            align-items: stretch;
        }

        .toggle-btn {
            width: 100%;
            margin-left: 0;
            margin-top: 5px;
        }

        button[type="submit"] {
            width: 100%;
        }

        .terminate-btn, .cancel-btn {
            width: 100%;
        }

        .modal-content {
            width: 90%;
        }
    }

    @media (max-width: 480px) {
        h2, h3 {
            font-size: 18px;
        }

        input, button, .toggle-btn {
            font-size: 14px;
            padding: 10px;
            width: 200px;
        }
        
        .modal {
            align-content: center;
            padding: 15px;
        }

        .modal-content {
            padding: 15px;
            width: 337px;
            
        }
        .container{
            width: 480px;
            max-width: 100%;
        }
    }
</style>

    </style>
</head>
<body>
    
 <br><br><br><br><br><br> <br><br><br><br><br><br> <br><br><br><br><br><br> <br><br><br><br><br><br> <br><br><br><br><br><br> <br><br><br><br><br><br>
<div class="container">
    <h2>Enter a new username:<h2>
    <form id="changeuser" method="POST">
        <input type="username" name="username" id="username" placeholder="<?php echo $username; ?>" required>
        <button type="button" class="changeuserbtn" style="width: 180px;">Change Username</button>
    </form>
    <span id="userMessage"></span>
</div>

 <br><br>

<div class="container">
    <h2>Change Password</h2>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
    
    <form method="POST" action="">
        <!-- Current Password -->
        <label>Current Password:</label>
        <div class="input-group">
            <input type="password" name="current_password" id="current_password" placeholder="Enter current password" required>
            <button type="button" class="toggle-btn" onclick="togglePassword('current_password')" style="width: 60px;">Show</button>
        </div>

        <!-- New Password -->
        <label>New Password:</label>
        <div class="input-group">
            <input type="password" id="new_password" name="new_password" placeholder="New Password" required>
            <button type="button" class="toggle-btn" onclick="togglePassword('new_password')" style="width: 60px;">Show</button>
        </div>

        <!-- Confirm Password -->
        <label>Confirm Password:</label>
        <div class="input-group">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="button" class="toggle-btn" onclick="togglePassword('confirm_password')" style="width: 60px;">Show</button>
        </div>

        <!-- Submit Button -->
        <button type="submit">Change Password</button>
    </form>
</div>
 <br><br>
<div class="container">
<h3>Terminate Account</h3>
    <p>This action will delete all your data permanently.</p>
    <button class="terminate-btn" onclick="showAuthModal()">Terminate</button>
</div>
    
<div id="authModal" class="modal">
    <div class="modal-content">
        <h3>Confirm Termination</h3>
        <p>Enter your password to confirm deletion.</p>
        <form id="terminationForm" method="POST" action="../../admin/account_and_login/terminate_account.php">
            <input type="password" name="password" required>
            <button type="submit" class="terminate-btn">Confirm</button>
            <button type="button" class="cancel-btn" onclick="closeAuthModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        var passwordField = document.getElementById(fieldId);
        var button = passwordField.nextElementSibling;

        if (passwordField.type === "password") {
            passwordField.type = "text";
            button.textContent = "Hide";
        } else {
            passwordField.type = "password";
            button.textContent = "Show";
        }
    }
    
    document.querySelector(".changeuserbtn").addEventListener("click", function() {
        var newUsername = document.getElementById("username").value.trim();
        var messageBox = document.getElementById("userMessage");
    
        if (newUsername === "") {
            messageBox.innerHTML = "<span style='color: red;'>Username cannot be empty.</span>";
            return;
        }
    
        var formData = new FormData();
        formData.append("username", newUsername);
    
        fetch("../../admin/account_and_login/change_username.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                messageBox.innerHTML = "<span style='color: green;'>" + data.message + "</span>";
                document.getElementById("username").placeholder = data.newUsername;
                document.getElementById("username").value = "";
            } else {
                messageBox.innerHTML = "<span style='color: red;'>" + data.message + "</span>";
            }
        })
        .catch(error => console.error("Error:", error));
    });
     function showAuthModal() {
        document.getElementById("authModal").style.display = "block";
    }

    function closeAuthModal() {
        document.getElementById("authModal").style.display = "none";
    }
    
    document.getElementById("terminationForm").onsubmit = function() {
        return confirm("⚠️ WARNING: This will permanently delete your account and all data. Proceed?");
    };
</script>

</body>
</html>
