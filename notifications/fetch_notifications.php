<?php
include("../config/database.php");

$sql = "SELECT message FROM notifications ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["message" => $row['message']]);
} else {
    echo json_encode(["message" => ""]);
}
?>
