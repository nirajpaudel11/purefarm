<?php
// handle_bundle_action.php
$conn = new mysqli('localhost', 'root', '', 'shopping');
if ($conn->connect_error) {
  die("DB Connection failed: " . $conn->connect_error);
}

$id = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id && in_array($action, ['approve', 'delete'])) {
  if ($action === 'approve') {
    // Move to approved bundles table
    $stmt = $conn->prepare("INSERT INTO bundles (items, discount_percent) 
                            SELECT CONCAT(base_product_id, ',', suggested_product_ids), 10 
                            FROM bundle_suggestions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
  }

  // Delete from suggestions
  $stmt = $conn->prepare("DELETE FROM bundle_suggestions WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
}

header("Location: bundle_dashboard.php");
exit;
