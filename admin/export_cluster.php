<?php
$mysqli = new mysqli("localhost", "root", "", "shopping");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_POST['cluster_id'])) {
    $cluster_id = intval($_POST['cluster_id']);

    $stmt = $mysqli->prepare("SELECT id, productName, productCompany, productPrice, views, purchases FROM products WHERE cluster_label = ?");
    $stmt->bind_param("i", $cluster_id);
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=cluster_' . $cluster_id . '_products.csv');

    $output = fopen("php://output", "w");
    fputcsv($output, ['ID', 'Product Name', 'Company', 'Price', 'Views', 'Purchases']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
} else {
    echo "Invalid request!";
}
