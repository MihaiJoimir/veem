<?php
$host = 'localhost';
$db   = 'veem';
$user = 'mihai';
$pass = 'Afkplm1910!';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
