<?php
include_once("../../config/database.php");

$sqlverify = "SELECT * FROM business_account";
$result23 = mysqli_query($conn, $sqlverify);

if (!$result23) {
    echo "<p style='color: red;'>We encountered a technical issue while fetching data.</p>";
    exit();
}

if (mysqli_num_rows($result23) > 0) {
    header("Location: ../../auth/prohibited.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $business_name = trim($_POST["business_name"]);
    $owner = trim($_POST["owner"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if (empty($business_name) || empty($owner) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare SQL query
        $sql = "INSERT INTO business_account (restaurant_name, restaurant_owner, password, username) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $business_name, $owner, $hashed_password, $username);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION["username"] = $username;
                setcookie("username", $username, time() + (86400 * 30), "/");
                header("Location: ../../auth/loginsuccess.html");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Business</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .login-container {
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            width: 320px;
            text-align: center;
        }
        input[type="text"], input[type="password"] {
            width: calc(100% - 40px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .password-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        .password-container input {
            flex: 1;
        }
        .password-container button {
            border: none;
            background-color: #ddd;
            cursor: pointer;
            padding: 5px 10px;
            margin-left: 5px;
            border-radius: 5px;
            font-size: 12px;
        }
        .password-container button:hover {
            background-color: #bbb;
        }
        button[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background-color: #218838;
        }
        .error {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Register to start a Business</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="" method="POST">
            <label>Business Name: </label><br>
            <input type="text" name="business_name" required>
            <label>Owner Name: </label><br>
            <input type="text" name="owner" required>
            <label>Username: </label><br>
            <input type="text" name="username" placeholder="Username" required>
            
            <label>Password: </label><br>
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="button" onclick="togglePassword('password')">Show</button>
            </div>

            <label>Confirm Password: </label><br>
            <div class="password-container">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="button" onclick="togglePassword('confirm_password')">Show</button>
            </div>

            <button type="submit">Create Business</button>
        </form>
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
    </script>
</body>
</html>
