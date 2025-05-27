<?php
// admin/bundle_dashboard.php
$conn = new mysqli('localhost', 'root', '', 'shopping');
if ($conn->connect_error) {
  die("DB Connection failed: " . $conn->connect_error);
}

// Fetch bundle suggestions with product names
$suggestions = $conn->query("
    SELECT bs.id, p1.productName AS base_product, bs.suggested_product_ids, bs.support_count, bs.confidence, bs.lift 
    FROM bundle_suggestions bs 
    JOIN products p1 ON p1.id = bs.base_product_id
");

function getProductNamesByIds($conn, $ids)
{
  if (empty($ids))
    return [];
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $types = str_repeat('i', count($ids));

  $stmt = $conn->prepare("SELECT productName FROM products WHERE id IN ($placeholders)");

  // Bind params dynamically (works for older PHP too)
  $refs = [];
  foreach ($ids as $key => $value) {
    $refs[$key] = &$ids[$key];
  }
  array_unshift($refs, $types);
  call_user_func_array([$stmt, 'bind_param'], $refs);

  $stmt->execute();
  $result = $stmt->get_result();

  $names = [];
  while ($row = $result->fetch_assoc()) {
    $names[] = $row['productName'];
  }
  $stmt->close();
  return $names;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Bundle Suggestions Dashboard</title>
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
    }

    th,
    td {
      padding: 8px 12px;
      border: 1px solid #ccc;
    }

    th {
      background: #eee;
    }
  </style>
</head>

<body>
  <h1>Bundle Suggestions Dashboard</h1>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Base Product</th>
        <th>Suggested Products</th>
        <th>Support Count</th>
        <th>Confidence (%)</th>
        <th>Lift</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($suggestions && $suggestions->num_rows > 0): ?>
        <?php while ($row = $suggestions->fetch_assoc()): ?>
          <?php
          $suggestedIds = array_map('intval', explode(',', $row['suggested_product_ids']));
          $suggestedNames = getProductNamesByIds($conn, $suggestedIds);
          ?>
          <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['base_product']) ?></td>
            <td><?= htmlspecialchars(implode(', ', $suggestedNames)) ?></td>
            <td><?= htmlspecialchars($row['support_count']) ?></td>
            <td><?= htmlspecialchars($row['confidence']) ?></td>
            <td><?= htmlspecialchars($row['lift']) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="6">No bundle suggestions found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>

</html>