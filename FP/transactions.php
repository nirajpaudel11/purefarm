<?php
// Include config.php using relative path
require_once('../includes/config.php');

// Your SQL query
$sql = "
    SELECT o.order_token, GROUP_CONCAT(DISTINCT p.productName SEPARATOR ',') as items
    FROM orders o
    JOIN products p ON o.productId = p.id
    GROUP BY o.order_token
";


$result = $con->query($sql);

$transactions = [];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $basket = explode(',', $row['items']);
    $transactions[] = array_map('trim', $basket); // Clean whitespace
  }
}

// echo "<pre>";
// print_r($transactions);
// echo "</pre>";
?>