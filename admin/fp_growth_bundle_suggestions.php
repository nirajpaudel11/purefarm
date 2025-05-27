<?php
// fp_growth_bundle_suggestions.php
require_once 'fp_growth_library.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli('localhost', 'root', '', 'shopping');
if ($conn->connect_error) {
  die("DB Connection failed: " . $conn->connect_error);
}

$minSupport = max(1, intval($_POST['min_support'] ?? 2));
$minConfidence = max(0.1, min(1.0, floatval($_POST['min_confidence'] ?? 0.5)));

function fetchTransactions($conn)
{
  $transactions = [];
  $result = $conn->query("SELECT order_token, GROUP_CONCAT(DISTINCT productId) as items FROM `order` WHERE productId IS NOT NULL AND order_token IS NOT NULL GROUP BY order_token HAVING items IS NOT NULL");

  while ($row = $result->fetch_assoc()) {
    $items = array_unique(array_map('intval', explode(',', $row['items'])));
    if (count($items) > 1) {
      sort($items);
      $transactions[] = $items;
    }
  }
  return $transactions;
}

function storeFrequentItemsets($patterns, $conn)
{
  $conn->query("TRUNCATE TABLE frequent_itemsets");
  foreach ($patterns as $p) {
    $stmt = $conn->prepare("INSERT INTO frequent_itemsets (itemset, support_count, itemset_size) VALUES (?, ?, ?)");
    $items = implode(',', $p['itemset']);
    $size = count($p['itemset']);
    $stmt->bind_param("sii", $items, $p['support'], $size);
    $stmt->execute();
    $stmt->close();
  }
}

function storeBundleSuggestions($patterns, $transactions, $conn, $minConfidence)
{
  $conn->query("TRUNCATE TABLE bundle_suggestions");
  $total = count($transactions);
  $inserted = [];

  foreach ($patterns as $pattern) {
    if (count($pattern['itemset']) < 2)
      continue;

    $support = $pattern['support'];
    for ($i = 1; $i < count($pattern['itemset']); $i++) {
      foreach (getCombinations($pattern['itemset'], $i) as $antecedent) {
        $consequent = array_diff($pattern['itemset'], $antecedent);
        if (empty($consequent))
          continue;

        $anteSupport = getSupportCount($antecedent, $transactions);
        $confidence = $anteSupport ? $support / $anteSupport : 0;

        if ($confidence >= $minConfidence) {
          $consSupport = getSupportCount($consequent, $transactions);
          $expected = ($anteSupport * $consSupport) / $total;
          $lift = $expected ? ($support / $expected) : 0;

          if ($lift > 1) {
            foreach ($antecedent as $base) {
              $suggested = implode(',', array_diff($pattern['itemset'], [$base]));
              $key = $base . '->' . $suggested;
              if (isset($inserted[$key]))
                continue;

              $stmt = $conn->prepare("INSERT INTO bundle_suggestions (base_product_id, suggested_product_ids, support_count, confidence, lift) VALUES (?, ?, ?, ?, ?)");
              $conf = round($confidence * 100, 2);
              $liftVal = round($lift, 2);
              $stmt->bind_param("isidd", $base, $suggested, $support, $conf, $liftVal);
              $stmt->execute();
              $stmt->close();

              $inserted[$key] = true;
            }
          }
        }
      }
    }
  }
}

function getSupportCount($itemset, $transactions)
{
  return count(array_filter($transactions, fn($t) => !array_diff($itemset, $t)));
}

function getCombinations($items, $r)
{
  if ($r <= 0 || $r > count($items))
    return [];
  $result = [];
  $indices = range(0, $r - 1);
  do {
    $result[] = array_map(fn($i) => $items[$i], $indices);
  } while (nextCombination($indices, count($items), $r));
  return $result;
}

function nextCombination(&$indices, $n, $r)
{
  for ($i = $r - 1; $i >= 0; $i--) {
    if ($indices[$i] < $n - $r + $i) {
      $indices[$i]++;
      for ($j = $i + 1; $j < $r; $j++) {
        $indices[$j] = $indices[$j - 1] + 1;
      }
      return true;
    }
  }
  return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $transactions = fetchTransactions($conn);
    if (empty($transactions))
      throw new Exception("No valid transactions found.");

    $tree = new FPTree($transactions, $minSupport);
    $patterns = $tree->minePatterns();

    storeFrequentItemsets($patterns, $conn);
    storeBundleSuggestions($patterns, $transactions, $conn, $minConfidence);

    echo json_encode(['status' => 'success', 'message' => 'FP-Growth analysis completed successfully.']);
  } catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
  }
}
?>