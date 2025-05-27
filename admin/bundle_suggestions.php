<?php

$conn = new mysqli('localhost', 'root', '', 'shopping');
if ($conn->connect_error) {
  die("DB Connection failed: " . $conn->connect_error);
}

// Fetch product ID to Name mapping
$productNames = [];
$pRes = $conn->query("SELECT id, productName FROM products");
while ($p = $pRes->fetch_assoc()) {
  $productNames[$p['id']] = $p['productName'];
}

// Helper function to convert CSV of IDs to product names
function displayProductNames($csv, $productNames)
{
  $ids = explode(',', $csv);
  $names = array_map(fn($id) => $productNames[$id] ?? "Product #$id", $ids);
  return implode(', ', $names);
}

// Handle Approve or Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['approve']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $bundleName = trim($_POST['bundle_name'] ?? '');

    $stmt = $conn->prepare("INSERT INTO bundles (items, discount_percent, created_at) VALUES (?, ?, NOW())");
    if ($stmt) {
      $baseProductId = $_POST['base_product_id'] ?? '';
      $suggestedProductIds = $_POST['suggested_product_ids'] ?? '';
      $items = implode(',', array_filter([$baseProductId, $suggestedProductIds]));
      $discountPercent = 10;

      $stmt->bind_param("si", $items, $discountPercent);
      if ($stmt->execute()) {
        $updateStmt = $conn->prepare("UPDATE bundle_suggestions SET bundle_name = ? WHERE id = ?");
        if ($updateStmt) {
          $updateStmt->bind_param("si", $bundleName, $id);
          $updateStmt->execute();
          $updateStmt->close();
        }
        $message = "Bundle approved and added with discount $discountPercent%";
      } else {
        $error = "Error saving bundle: " . $stmt->error;
      }
      $stmt->close();
    } else {
      $error = "Prepare failed: " . $conn->error;
    }
  } elseif (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $delStmt = $conn->prepare("DELETE FROM bundle_suggestions WHERE id = ?");
    if ($delStmt) {
      $delStmt->bind_param("i", $id);
      if ($delStmt->execute()) {
        $message = "Bundle suggestion deleted.";
      } else {
        $error = "Error deleting: " . $delStmt->error;
      }
      $delStmt->close();
    } else {
      $error = "Prepare failed: " . $conn->error;
    }
  }
}

// Fetch bundle suggestions
$result = $conn->query("SELECT * FROM bundle_suggestions ORDER BY confidence DESC, lift DESC LIMIT 50");

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Bundle Suggestions</title>
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
    }

    th,
    td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: left;
    }

    th {
      background-color: #f0f0f0;
    }

    form {
      margin: 0;
    }

    .msg {
      padding: 10px;
      background: #dff0d8;
      color: #3c763d;
      margin-bottom: 15px;
    }

    .error {
      padding: 10px;
      background: #f2dede;
      color: #a94442;
      margin-bottom: 15px;
    }
  </style>
</head>

<body>
  <h1>Bundle Suggestions</h1>

  <?php if (!empty($message)): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Base Product</th>
        <th>Suggested Products</th>
        <th>Support Count</th>
        <th>Confidence (%)</th>
        <th>Lift</th>
        <th>Bundle Name (for approval)</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= displayProductNames($row['base_product_id'], $productNames) ?></td>
            <td><?= displayProductNames($row['suggested_product_ids'], $productNames) ?></td>
            <td><?= $row['support_count'] ?></td>
            <td><?= round($row['confidence'], 2) ?></td>
            <td><?= round($row['lift'], 2) ?></td>
            <td>
              <form method="post" style="display:inline-block;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                <input type="hidden" name="base_product_id" value="<?= htmlspecialchars($row['base_product_id']) ?>" />
                <input type="hidden" name="suggested_product_ids"
                  value="<?= htmlspecialchars($row['suggested_product_ids']) ?>" />
                <input type="text" name="bundle_name" placeholder="Bundle Name"
                  value="<?= htmlspecialchars($row['bundle_name'] ?? '') ?>" />
            </td>
            <td>
              <button type="submit" name="approve">Approve</button>
              </form>
              <form method="post" style="display:inline-block; margin-left:10px;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                <button type="submit" name="delete"
                  onclick="return confirm('Are you sure to delete this suggestion?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="8">No bundle suggestions found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>

</html>