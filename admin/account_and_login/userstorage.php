<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once("../../config/database.php"); 

$sql = "SELECT * FROM business_account"; 

if (empty(trim($sql))) {
    echo "<p style='color: red;'>Oops! Something went wrong. Please try again later.</p>";
    exit();
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "<p style='color: red;'>We encountered a technical issue while fetching data. Please contact support.</p>";
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
        if (mysqli_num_rows($result) == 0) {
            echo "<p style='color: orange;'>No records found.</p>";
            header("Location: ../../admin/account_and_login/register.php");
    ?>  
    <div class="container">
        <label></label>
    </div>

            
       <?php } else
       header("Location: ../../admin/account_and_login/loginverify.php")
    ?>

</body>
</html>


<?php
mysqli_free_result($result);
mysqli_close($conn);
?>
