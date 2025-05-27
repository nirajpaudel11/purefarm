<?php
$server = 'localhost';
$user = 'root';
$password = '';
$database = 'shopping';

$con = mysqli_connect($server, $user, $password, $database);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
