<?php
// apriori.php

// Enable error reporting (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB connection
$conn = new mysqli('localhost', 'root', '', 'shopping');
if ($conn->connect_error) {
  die("DB Connection failed: " . $conn->connect_error);
}

// Create frequent_itemsets table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS frequent_itemsets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        itemset TEXT NOT NULL,
        support_count INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Fetch transactions grouped by order_token with product IDs
function fetchTransactions($conn)
{
  $sql = "SELECT order_token, GROUP_CONCAT(productId SEPARATOR ',') as items FROM orders GROUP BY order_token";
  $result = $conn->query($sql);
  $transactions = [];
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $basket = array_unique(array_map('intval', explode(',', $row['items'])));
      sort($basket);
      $transactions[] = $basket;
    }
  }
  return $transactions;
}

// Get support count of an itemset in transactions
function getSupportCount($itemset, $transactions)
{
  $count = 0;
  foreach ($transactions as $transaction) {
    if (!array_diff($itemset, $transaction)) {
      $count++;
    }
  }
  return $count;
}

// Generate candidate itemsets of size k from previous frequent itemsets
function generateCandidates($frequentItemsets, $k)
{
  $candidates = [];
  $len = count($frequentItemsets);
  for ($i = 0; $i < $len; $i++) {
    for ($j = $i + 1; $j < $len; $j++) {
      $prefix1 = array_slice($frequentItemsets[$i], 0, $k - 2);
      $prefix2 = array_slice($frequentItemsets[$j], 0, $k - 2);
      if ($prefix1 === $prefix2) {
        $candidate = array_unique(array_merge($frequentItemsets[$i], $frequentItemsets[$j]));
        sort($candidate);
        if (!in_array($candidate, $candidates, true)) {
          $candidates[] = $candidate;
        }
      }
    }
  }
  return $candidates;
}

// Save itemset to DB
function saveItemset($conn, $itemset, $support)
{
  $itemset_str = implode(',', $itemset);
  $stmt = $conn->prepare("INSERT INTO frequent_itemsets (itemset, support_count) VALUES (?, ?)");
  $stmt->bind_param("si", $itemset_str, $support);
  $stmt->execute();
  $stmt->close();
}

// Apriori main function
function apriori($transactions, $min_support, $conn)
{
  // Clear previous results
  $conn->query("TRUNCATE TABLE frequent_itemsets");

  // Count 1-itemsets
  $itemCounts = [];
  foreach ($transactions as $transaction) {
    foreach ($transaction as $item) {
      $itemCounts[$item] = ($itemCounts[$item] ?? 0) + 1;
    }
  }

  $frequentItemsets = [];
  $resultFrequentItemsets = [];

  // Filter by min support
  foreach ($itemCounts as $item => $count) {
    if ($count >= $min_support) {
      $frequentItemsets[] = [$item];
      saveItemset($conn, [$item], $count);
    }
  }

  $k = 2;
  while (!empty($frequentItemsets)) {
    $candidates = generateCandidates($frequentItemsets, $k);
    $frequentItemsets = [];
    foreach ($candidates as $candidate) {
      $count = getSupportCount($candidate, $transactions);
      if ($count >= $min_support) {
        $frequentItemsets[] = $candidate;
        saveItemset($conn, $candidate, $count);
      }
    }
    $k++;
  }
}

// Handle form submission
$min_support = 2;
$processed = false;
$processing_time = 0;
$total_transactions = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['min_support'])) {
  $min_support = max(1, intval($_POST['min_support']));
  $start_time = microtime(true);
  $transactions = fetchTransactions($conn);
  $total_transactions = count($transactions);
  apriori($transactions, $min_support, $conn);
  $end_time = microtime(true);
  $processing_time = round(($end_time - $start_time) * 1000, 2);
  $processed = true;
}

// Get results from DB
$results = [];
$res = $conn->query("SELECT * FROM frequent_itemsets ORDER BY support_count DESC, LENGTH(itemset) ASC");
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $results[] = $row;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Apriori Algorithm - Market Basket Analysis</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      max-width: 900px;
      margin: auto;
      background: #f9f9f9;
      color: #222;
    }

    h1 {
      color: #2c3e50;
    }

    form {
      margin-bottom: 30px;
    }

    input[type="number"] {
      width: 80px;
      padding: 6px;
      font-size: 1rem;
      margin-right: 10px;
    }

    button {
      padding: 7px 15px;
      font-size: 1rem;
      cursor: pointer;
    }

    .info {
      margin-bottom: 20px;
      color: green;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      max-width: 700px;
    }

    th,
    td {
      border: 1px solid #ccc;
      padding: 8px 12px;
      text-align: left;
      font-family: monospace;
    }

    th {
      background: #eee;
    }

    .no-results {
      color: #666;
      font-style: italic;
    }
  </style>
</head>

<body>
  <h1>Apriori Algorithm - Market Basket Analysis</h1>

  <form method="post" action="">
    <label for="min_support">Minimum Support Count:</label>
    <input type="number" id="min_support" name="min_support" min="1" max="100"
      value="<?= htmlspecialchars($min_support) ?>" required />
    <button type="submit">Run Algorithm</button>
  </form>

  <?php if ($processed): ?>
    <div class="info">
      Processed <?= $total_transactions ?> transactions in <?= $processing_time ?> ms.
    </div>
  <?php endif; ?>

  <h2>Frequent Itemsets</h2>

  <?php if (!empty($results)): ?>
    <table>
      <thead>
        <tr>
          <th>Itemset (Product IDs)</th>
          <th>Support Count</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($results as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['itemset']) ?></td>
            <td><?= $row['support_count'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="no-results">No frequent itemsets found. Try lowering the minimum support count.</p>
  <?php endif; ?>
</body>

</html>