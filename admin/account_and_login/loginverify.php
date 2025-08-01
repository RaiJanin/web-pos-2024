<?php
    session_start();
    include_once("../../config/database.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background-image: radial-gradient(circle, rgba(0, 0, 0, 0) 30%, rgba(0, 0, 0, 0.8) 100%), url('../../assets/image/bkgnd.jpg');
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease-in-out;
        }

        .login-container:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        h2 {
            color: white;
            font-size: 26px;
            text-shadow: 2px 2px 5px grey;
            margin-bottom: 20px;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid rgba(213, 235, 218, 0.5);
            border-radius: 10px;
            outline: none;
            font-size: 16px;
            background: rgba(252, 249, 249, 0.91);
            color: black;
            box-shadow: inset 2px 2px 6px rgba(0, 0, 0, 0.3);
            transition: 0.3s;
        }

        input:focus {
            border: 2px solid rgb (33, 177, 17);
            box-shadow: 0 0 8pxrgb(248, 247, 246);
            background: linear-gradient(to right,rgb(233, 237, 234),rgb(102, 105, 103));
        }

        button {
            background: linear-gradient(to right,rgb(30, 211, 73), #218838);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease-in-out;
        }

        button:hover {
            background:rgb(149, 161, 145);
            color: black;
            transform: scale(1.05);
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
            animation: shake 0.3s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
            }

            h2 {
                font-size: 22px;
            }

            button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Login</h2>
    <?php if (isset($_SESSION['error'])) { echo "<p class='error'>{$_SESSION['error']}</p>"; unset($_SESSION['error']); } ?>
    <form action="" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" id="pwd" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
</div>
</body>
</html>

<?php
if (isset($_POST["login"])) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT * FROM business_account WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $row['username'];
            header("Location: ../../admin/pages/dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Incorrect username or password!";
        }
    } else {
        $_SESSION['error'] = "Incorrect username or password!";
    }

    mysqli_stmt_close($stmt);
    header("Location: loginverify.php");
    exit();
}

mysqli_close($conn);
?>