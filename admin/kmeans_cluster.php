<?php
// DB config
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "shopping";

// Connect to DB
$mysqli = new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Number of items per page
$itemsPerPage = 20;

// Get selected cluster tab from URL ?cluster=0&page=1
$selectedCluster = isset($_GET['cluster']) ? intval($_GET['cluster']) : 0;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// 1. Fetch products with cluster_label from DB (assuming cluster_label updated)
$sql = "SELECT id,productName, productPrice, views, purchases, cluster_label FROM products WHERE cluster_label IS NOT NULL ORDER BY id ASC";
$result = $mysqli->query($sql);
if (!$result) {
    die("Query failed: " . $mysqli->error);
}

$allProducts = [];
while ($row = $result->fetch_assoc()) {
    $allProducts[] = [
        'id' => (int) $row['id'],
        'name' => $row['productName'],
        'price' => (float) $row['productPrice'],
        'views' => (int) $row['views'],
        'purchases' => (int) $row['purchases'],
        'cluster' => (int) $row['cluster_label'],
    ];


}
$result->free();

// 2. Filter products by selected cluster
$filteredProducts = array_filter($allProducts, function ($p) use ($selectedCluster) {
    return $p['cluster'] === $selectedCluster;
});

// 3. Pagination calculations
$totalItems = count($filteredProducts);
$totalPages = ceil($totalItems / $itemsPerPage);
$currentPage = min($currentPage, max(1, $totalPages));
$startIndex = ($currentPage - 1) * $itemsPerPage;
$paginatedProducts = array_slice(array_values($filteredProducts), $startIndex, $itemsPerPage);

// Cluster labels for tabs (adjust as needed)
$clusterLabels = [
    0 => "Low Performing",
    1 => "Moderate Performing",
    2 => "High Performing",
];

// 4. HTML output
?>
<!DOCTYPE html>
<html>

<head>
    <title>Product Clusters with Pagination & Tabs</title>
    <style>
        /* Simple tab style */
        .tabs {
            margin-bottom: 20px;
        }

        .tab {
            display: inline-block;
            margin-right: 10px;
            padding: 8px 15px;
            background: #eee;
            border-radius: 5px 5px 0 0;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }

        .tab.active {
            background: #4285F4;
            color: white;
            font-weight: bold;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            padding: 8px 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .pagination {
            margin-top: 15px;
        }

        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #4285F4;
        }

        .pagination a.current {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <h2>Product Clusters with Pagination & Tabs</h2>

    <!-- Tabs -->
    <div class="tabs">
        <?php foreach ($clusterLabels as $clusterId => $label): ?>
            <a href="?cluster=<?= $clusterId ?>" class="tab <?= $clusterId === $selectedCluster ? 'active' : '' ?>">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Product Table -->
    <table>
        <thead>
            <tr>
                <th>S.N.</th>
                <th>Name</th>
                <th>Price (NRs)</th>
                <th>Views</th>
                <th>Purchases</th>
                <th>Cluster</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($paginatedProducts) === 0): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No products found in this cluster.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($paginatedProducts as $index => $prod): ?>
                    <tr>
                        <td><?= htmlspecialchars($startIndex + $index + 1) ?></td>
                        <td><?= htmlspecialchars($prod['name']) ?></td>
                        <td><?= number_format($prod['price'], 2) ?></td>
                        <td><?= htmlspecialchars($prod['views']) ?></td>
                        <td><?= htmlspecialchars($prod['purchases']) ?></td>
                        <td><?= htmlspecialchars($clusterLabels[$prod['cluster']] ?? $prod['cluster']) ?></td>
                    </tr>

                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination Links -->
    <div class="pagination">
        <?php if ($totalPages > 1): ?>
            <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                <a href="?cluster=<?= $selectedCluster ?>&page=<?= $page ?>"
                    class="<?= $page === $currentPage ? 'current' : '' ?>">
                    <?= $page ?>
                </a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
    <p><a href="admin_clusters.php" style="text-decoration:none;color:#4285F4;">← Back to Cluster Overview</a></p>


</body>

</html>

<?php
$mysqli->close();
?>