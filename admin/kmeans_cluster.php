<?php
// Database Connection
$mysqli = new mysqli("localhost", "root", "", "shopping");
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// --- Fetch product data for clustering ---
$data = [];
$stmt = $mysqli->prepare("SELECT id, productName, views, purchases, productPrice FROM products");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => (int) $row['id'],
        'name' => $row['productName'],
        'views' => (float) $row['views'],
        'purchases' => (float) $row['purchases'],
        'price' => (float) $row['productPrice'],
    ];
}
$stmt->close();

if (empty($data)) {
    die("No product data found.");
}

// --- Enhanced feature engineering for performance-based clustering ---
function prepareFeatures(array $data): array
{
    $features = [];
    foreach ($data as $item) {
        // Calculate conversion rate (purchases/views)
        $conversionRate = $item['views'] > 0 ? $item['purchases'] / $item['views'] : 0;

        // Calculate revenue (purchases * price)
        $revenue = $item['purchases'] * $item['price'];

        // Calculate engagement score (views relative to other products)
        $engagementScore = $item['views'];

        $features[] = [
            'conversion_rate' => $conversionRate,
            'revenue' => $revenue,
            'engagement' => $engagementScore,
            'purchases' => $item['purchases']
        ];
    }
    return $features;
}

// --- Improved normalization with min-max scaling ---
function normalize(array $features): array
{
    $featureNames = ['conversion_rate', 'revenue', 'engagement', 'purchases'];
    $min = [];
    $max = [];

    // Find min and max for each feature
    foreach ($featureNames as $feature) {
        $values = array_column($features, $feature);
        $min[$feature] = min($values);
        $max[$feature] = max($values);
    }

    return array_map(function ($item) use ($min, $max, $featureNames) {
        $normalized = [];
        foreach ($featureNames as $feature) {
            $range = $max[$feature] - $min[$feature];
            $normalized[] = ($range == 0) ? 0 : (($item[$feature] - $min[$feature]) / $range);
        }
        return $normalized;
    }, $features);
}

// --- Euclidean distance calculation ---
function euclidean(array $a, array $b): float
{
    $sum = 0;
    for ($i = 0; $i < count($a); $i++) {
        $sum += pow($a[$i] - $b[$i], 2);
    }
    return sqrt($sum);
}

// --- Improved K-Means++ initialization ---
function initializeCentroids(array $data, int $k): array
{
    if (count($data) < $k) {
        throw new Exception("Not enough data points for $k clusters");
    }

    $centroids = [];
    $n = count($data);

    // Choose first centroid randomly (using deterministic seed for consistency)
    mt_srand(42); // Fixed seed for reproducible results
    $centroids[] = $data[mt_rand(0, $n - 1)];

    // Choose remaining centroids using K-means++ method
    for ($c = 1; $c < $k; $c++) {
        $distances = [];
        $totalDistance = 0;

        // Calculate squared distances to nearest centroid for each point
        foreach ($data as $i => $point) {
            $minDist = INF;
            foreach ($centroids as $centroid) {
                $dist = euclidean($point, $centroid);
                $minDist = min($minDist, $dist);
            }
            $distances[$i] = $minDist * $minDist; // Squared distance
            $totalDistance += $distances[$i];
        }

        // Choose next centroid with probability proportional to squared distance
        $target = mt_rand() / mt_getrandmax() * $totalDistance;
        $cumulative = 0;

        foreach ($distances as $i => $dist) {
            $cumulative += $dist;
            if ($cumulative >= $target) {
                $centroids[] = $data[$i];
                break;
            }
        }
    }

    return $centroids;
}

// --- Assign points to nearest centroid ---
function assignClusters(array $data, array $centroids): array
{
    $assignments = [];

    foreach ($data as $i => $point) {
        $minDistance = INF;
        $bestCluster = 0;

        foreach ($centroids as $j => $centroid) {
            $distance = euclidean($point, $centroid);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $bestCluster = $j;
            }
        }

        $assignments[$i] = $bestCluster;
    }

    return $assignments;
}

// --- Update centroids based on cluster assignments ---
function updateCentroids(array $data, array $assignments, int $k): array
{
    $newCentroids = [];
    $dimensions = count($data[0]);

    for ($cluster = 0; $cluster < $k; $cluster++) {
        $clusterPoints = [];

        // Collect all points in this cluster
        foreach ($assignments as $i => $assignedCluster) {
            if ($assignedCluster === $cluster) {
                $clusterPoints[] = $data[$i];
            }
        }

        if (empty($clusterPoints)) {
            // If cluster is empty, keep the old centroid or reinitialize
            $newCentroids[$cluster] = array_fill(0, $dimensions, 0);
        } else {
            // Calculate mean for each dimension
            $centroid = array_fill(0, $dimensions, 0);
            foreach ($clusterPoints as $point) {
                for ($d = 0; $d < $dimensions; $d++) {
                    $centroid[$d] += $point[$d];
                }
            }

            // Average the values
            for ($d = 0; $d < $dimensions; $d++) {
                $centroid[$d] /= count($clusterPoints);
            }

            $newCentroids[$cluster] = $centroid;
        }
    }

    return $newCentroids;
}

// --- Check if centroids have converged ---
function hasConverged(array $oldCentroids, array $newCentroids, float $tolerance = 1e-6): bool
{
    if (count($oldCentroids) !== count($newCentroids)) {
        return false;
    }

    foreach ($oldCentroids as $i => $oldCentroid) {
        $distance = euclidean($oldCentroid, $newCentroids[$i]);
        if ($distance > $tolerance) {
            return false;
        }
    }

    return true;
}

// --- Run K-Means clustering ---
$k = 3;  // Number of clusters
$maxIterations = 100;
$tolerance = 1e-6;

// Prepare features for clustering
$features = prepareFeatures($data);
$normalizedFeatures = normalize($features);

// Initialize centroids
$centroids = initializeCentroids($normalizedFeatures, $k);
$assignments = [];

// Run K-means iterations
for ($iter = 0; $iter < $maxIterations; $iter++) {
    $assignments = assignClusters($normalizedFeatures, $centroids);
    $newCentroids = updateCentroids($normalizedFeatures, $assignments, $k);

    if (hasConverged($centroids, $newCentroids, $tolerance)) {
        echo "<!-- K-means converged after $iter iterations -->\n";
        break;
    }

    $centroids = $newCentroids;
}

// --- Analyze clusters to determine performance levels ---
$clusterStats = [];
for ($cluster = 0; $cluster < $k; $cluster++) {
    $clusterProducts = [];
    foreach ($assignments as $i => $assignedCluster) {
        if ($assignedCluster === $cluster) {
            $clusterProducts[] = $i;
        }
    }

    if (!empty($clusterProducts)) {
        $totalRevenue = 0;
        $totalConversion = 0;
        $totalPurchases = 0;

        foreach ($clusterProducts as $productIndex) {
            $totalRevenue += $features[$productIndex]['revenue'];
            $totalConversion += $features[$productIndex]['conversion_rate'];
            $totalPurchases += $features[$productIndex]['purchases'];
        }

        $clusterStats[$cluster] = [
            'count' => count($clusterProducts),
            'avg_revenue' => $totalRevenue / count($clusterProducts),
            'avg_conversion' => $totalConversion / count($clusterProducts),
            'avg_purchases' => $totalPurchases / count($clusterProducts),
            'total_revenue' => $totalRevenue
        ];
    }
}

// --- Map clusters to performance levels based on overall performance score ---
$performanceScores = [];
foreach ($clusterStats as $cluster => $stats) {
    // Composite performance score (you can adjust weights as needed)
    $performanceScores[$cluster] = (
        $stats['avg_revenue'] * 0.4 +
        $stats['avg_conversion'] * 0.3 +
        $stats['avg_purchases'] * 0.3
    );
}

// Sort clusters by performance score
arsort($performanceScores);
$clusterMapping = [];
$performanceLevels = ['high_performing', 'moderate_performing', 'low_performing'];

$index = 0;
foreach ($performanceScores as $cluster => $score) {
    $clusterMapping[$cluster] = $index;
    $index++;
}

// Remap assignments to performance-based labels
$finalAssignments = [];
foreach ($assignments as $i => $cluster) {
    $finalAssignments[$i] = $clusterMapping[$cluster];
}

// --- Update cluster labels in database ---
$updateStmt = $mysqli->prepare("UPDATE products SET cluster_label = ? WHERE id = ?");
foreach ($finalAssignments as $i => $performanceLevel) {
    $id = $data[$i]['id'];
    $updateStmt->bind_param("ii", $performanceLevel, $id);
    $updateStmt->execute();
}
$updateStmt->close();

// --- Performance-based cluster labels ---
$clusterLabels = [
    0 => "High Performing",
    1 => "Moderate Performing",
    2 => "Low Performing"
];

// --- Get all products with cluster label from DB ---
$products = [];
$res = $mysqli->query("SELECT id, productName, productPrice, views, purchases, cluster_label FROM products ORDER BY cluster_label, productPrice DESC");
while ($row = $res->fetch_assoc()) {
    $products[] = [
        'id' => (int) $row['id'],
        'name' => $row['productName'],
        'price' => (float) $row['productPrice'],
        'views' => (float) $row['views'],
        'purchases' => (float) $row['purchases'],
        'cluster' => isset($row['cluster_label']) ? (int) $row['cluster_label'] : null,
    ];
}

// --- Get selected cluster from URL, default to 0 (High Performing) ---
$selectedCluster = isset($_GET['cluster']) ? (int) $_GET['cluster'] : 0;
if (!isset($clusterLabels[$selectedCluster])) {
    $selectedCluster = 0;
}

// --- Filter products by selected cluster ---
$filteredProducts = array_filter($products, fn($p) => $p['cluster'] === $selectedCluster);

// --- Pagination setup ---
$itemsPerPage = 10;
$totalProducts = count($filteredProducts);
$totalPages = $totalProducts > 0 ? (int) ceil($totalProducts / $itemsPerPage) : 1;

$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($currentPage < 1)
    $currentPage = 1;
if ($currentPage > $totalPages)
    $currentPage = $totalPages;

$startIndex = ($currentPage - 1) * $itemsPerPage;
$paginatedProducts = array_slice($filteredProducts, $startIndex, $itemsPerPage);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Performance Clusters</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h2 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 15px;
            margin-bottom: 30px;
            font-size: 2.2em;
        }

        .cluster-stats {
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .tabs {
            margin-bottom: 25px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

        .tab {
            padding: 12px 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            font-weight: 500;
            border: 2px solid transparent;
        }

        .tab:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
            border-color: #5a6fd8;
        }

        .tab.high-performing.active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .tab.moderate-performing.active {
            background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
        }

        .tab.low-performing.active {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            color: #333;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 25px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        th,
        td {
            padding: 15px 18px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9em;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e3f2fd;
            transform: scale(1.01);
            transition: all 0.2s ease;
        }

        .performance-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
        }

        .high-performing {
            background: #d4edda;
            color: #155724;
            border: 1px solid #28a745;
        }

        .moderate-performing {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }

        .low-performing {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #dc3545;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 10px 15px;
            text-decoration: none;
            color: #667eea;
            border: 2px solid #e3f2fd;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .pagination a:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-2px);
        }

        .pagination .current {
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #5a6fd8;
        }

        .pagination .disabled {
            color: #aaa;
            pointer-events: none;
            opacity: 0.5;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 25px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 25px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .back-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(240, 147, 251, 0.4);
        }

        .no-products {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
            font-size: 1.1em;
        }

        .conversion-rate {
            font-weight: bold;
        }

        .conversion-rate.high {
            color: #28a745;
        }

        .conversion-rate.medium {
            color: #ffc107;
        }

        .conversion-rate.low {
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .tabs {
                justify-content: center;
            }

            .tab {
                padding: 10px 15px;
                font-size: 0.9em;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Product Performance Analysis</h2>


        <!-- Cluster Tabs -->
        <div class="tabs">
            <?php foreach ($clusterLabels as $clusterId => $label): ?>
                <?php
                $countInCluster = count(array_filter($products, fn($p) => $p['cluster'] === $clusterId));
                $labelClass = strtolower(str_replace(' ', '-', $label));
                ?>
                <a href="?cluster=<?= $clusterId ?>&page=1"
                    class="tab <?= $labelClass ?> <?= $clusterId === $selectedCluster ? 'active' : '' ?>">
                    <?= htmlspecialchars($label) ?> (<?= $countInCluster ?>)
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Product Table -->
        <table>
            <thead>
                <tr>
                    <th>S.N.</th>
                    <th>Product Name</th>
                    <th>Price (NRs)</th>
                    <th>Views</th>
                    <th>Purchases</th>
                    <th>Conversion Rate</th>
                    <th>Revenue</th>
                    <th>Performance</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paginatedProducts)): ?>
                    <tr>
                        <td colspan="8" class="no-products">No products found in this cluster.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paginatedProducts as $index => $prod):
                        $conversionRate = $prod['views'] > 0 ? ($prod['purchases'] / $prod['views']) * 100 : 0;
                        $revenue = $prod['purchases'] * $prod['price'];

                        $conversionClass = 'low';
                        if ($conversionRate >= 10)
                            $conversionClass = 'high';
                        elseif ($conversionRate >= 5)
                            $conversionClass = 'medium';

                        $performanceClass = strtolower(str_replace(' ', '-', $clusterLabels[$prod['cluster']] ?? 'unknown'));
                        ?>
                        <tr>
                            <td><?= $startIndex + $index + 1 ?></td>
                            <td><strong><?= htmlspecialchars($prod['name']) ?></strong></td>
                            <td>NRs <?= number_format($prod['price'], 2) ?></td>
                            <td><?= number_format($prod['views']) ?></td>
                            <td><?= number_format($prod['purchases']) ?></td>
                            <td><span
                                    class="conversion-rate <?= $conversionClass ?>"><?= number_format($conversionRate, 2) ?>%</span>
                            </td>
                            <td>NRs <?= number_format($revenue, 2) ?></td>
                            <td><span class="performance-badge <?= $performanceClass ?>">
                                    <?= htmlspecialchars($clusterLabels[$prod['cluster']] ?? 'N/A') ?>
                                </span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination" role="navigation" aria-label="Pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?cluster=<?= $selectedCluster ?>&page=<?= $currentPage - 1 ?>" aria-label="Previous page">&laquo;
                        Prev</a>
                <?php else: ?>
                    <span class="disabled">&laquo; Prev</span>
                <?php endif; ?>

                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);

                if ($startPage > 1) {
                    echo '<a href="?cluster=' . $selectedCluster . '&page=1">1</a>';
                    if ($startPage > 2)
                        echo '<span>...</span>';
                }

                for ($p = $startPage; $p <= $endPage; $p++): ?>
                    <?php if ($p === $currentPage): ?>
                        <span class="current" aria-current="page"><?= $p ?></span>
                    <?php else: ?>
                        <a href="?cluster=<?= $selectedCluster ?>&page=<?= $p ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor;

                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1)
                        echo '<span>...</span>';
                    echo '<a href="?cluster=' . $selectedCluster . '&page=' . $totalPages . '">' . $totalPages . '</a>';
                }
                ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?cluster=<?= $selectedCluster ?>&page=<?= $currentPage + 1 ?>" aria-label="Next page">Next
                        &raquo;</a>
                <?php else: ?>
                    <span class="disabled">Next &raquo;</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <a href="admin_clusters.php" class="back-link">← Back to Home</a>
    </div>
</body>

</html>

<?php
$mysqli->close();
?>