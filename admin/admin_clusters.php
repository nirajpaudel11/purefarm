<?php
// DB connection
$mysqli = new mysqli("localhost", "root", "", "shopping");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Helper: Human-readable cluster name
function getClusterName($label) {
    switch ($label) {
        case 2: return "Low Performing";
        case 1: return "Moderate Performing";
        case 0: return "High Performing";
        default: return "Unknown Cluster";
    }
}

// Get all clustered products
$products_query = "SELECT productName, productCompany, productPrice, views, purchases, cluster_label 
                   FROM products WHERE cluster_label IS NOT NULL ORDER BY cluster_label ASC";
$products_result = $mysqli->query($products_query);
$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}

// Get cluster statistics
$cluster_stats_query = "SELECT 
    cluster_label,
    COUNT(*) as product_count,
    SUM(views) as total_views,
    SUM(purchases) as total_purchases,
    AVG(productPrice) as avg_price
    FROM products 
    WHERE cluster_label IS NOT NULL 
    GROUP BY cluster_label 
    ORDER BY cluster_label ASC";
$cluster_stats_result = $mysqli->query($cluster_stats_query);
$cluster_stats = [];
while ($row = $cluster_stats_result->fetch_assoc()) {
    $cluster_stats[] = $row;
}

// Get distinct cluster labels
$cluster_result = $mysqli->query("SELECT DISTINCT cluster_label FROM products WHERE cluster_label IS NOT NULL ORDER BY cluster_label ASC");
$clusters = [];
while ($row = $cluster_result->fetch_assoc()) {
    $clusters[] = (int)$row['cluster_label']; // Ensure integer
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Product Cluster Performance Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
       * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            color: #666;
            -webkit-text-fill-color: #666;
        }

        .admin-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .cluster-selector {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .cluster-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .cluster-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .cluster-btn.active {
            background: linear-gradient(45deg, #ff6b6b, #ee5a52);
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .admin-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .admin-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .run-clustering-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .export-btn {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }

        .admin-btn:hover {
            transform: translateY(-2px);
        }

        .no-data-message {
            text-align: center;
            padding: 40px;
            background: rgba(255, 193, 7, 0.1);
            border-radius: 15px;
            color: #856404;
            font-size: 1.2rem;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .chart-container:hover {
            transform: translateY(-5px);
        }

        .chart-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: scale(1.05);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }

        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .pagination-info {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: rgba(102, 126, 234, 0.1);
            color: #333;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #667eea;
        }

        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .data-table tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .cluster-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .cluster-0 { background: #ffebee; color: #c62828; }
        .cluster-1 { background: #fff3e0; color: #ef6c00; }
        .cluster-2 { background: #e8f5e8; color: #2e7d32; }

        .pagination-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .pagination-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .items-per-page {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .items-per-page select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            font-size: 0.9rem;
        }

        .pagination-buttons {
            display: flex;
            gap: 5px;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            color: #333;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .pagination-btn:hover:not(:disabled) {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #333;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .cluster-selector {
                justify-content: center;
            }

            .pagination-controls {
                flex-direction: column;
                gap: 15px;
            }

            .pagination-left {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Product Cluster Performance Dashboard</h1>
            <p>K-Means Clustering Analysis & Visualization</p>
        </div>

        <div class="admin-controls">
            <div class="cluster-selector">
                <span style="font-weight: 600; color: #333;">Filter by Cluster:</span>
                <button class="cluster-btn active" onclick="filterCluster('all', event)">All Clusters</button>
                <?php foreach ($clusters as $cluster): ?>
                    <button class="cluster-btn" onclick="filterCluster(<?= $cluster ?>, event)">
                        <?= htmlspecialchars(getClusterName($cluster)) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <div class="admin-actions">
                <form method="post" action="kmeans_cluster.php" style="margin: 0;">
                    <button type="submit" class="admin-btn run-clustering-btn">Run K-Means Clustering</button>
                </form>
                <button class="admin-btn export-btn" onclick="exportFilteredData()">Export Current View</button>
            </div>
        </div>

        <?php if (count($products) === 0): ?>
            <div class="no-data-message">
                <h2>No clustered data available</h2>
                <p>Please run K-Means clustering first to see the visualization dashboard.</p>
            </div>
        <?php else: ?>
            <div class="stats-grid" id="statsGrid"></div>

            <div class="charts-grid">
                <div class="chart-container">
                    <h3 class="chart-title">Cluster Distribution</h3>
                    <canvas id="clusterChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3 class="chart-title">Price vs Views Analysis</h3>
                    <canvas id="scatterChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3 class="chart-title">Performance Comparison</h3>
                    <canvas id="performanceChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3 class="chart-title">Conversion Rates by Cluster</h3>
                    <canvas id="conversionChart"></canvas>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Product Details</h3>
                    <div class="pagination-info" id="paginationInfo"></div>
                </div>
                
                <table class="data-table" id="dataTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Product Name</th>
                            <th>Company</th>
                            <th>Price (NRs)</th>
                            <th>Views</th>
                            <th>Purchases</th>
                            <th>Conversion Rate (%)</th>
                            <th>Cluster</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>

                <div id="emptyState" class="empty-state" style="display: none;">
                    <h3>No products found</h3>
                    <p>No products match the current filter criteria.</p>
                </div>

                <div class="pagination-controls">
                    <div class="pagination-left">
                        <div class="items-per-page">
                            <label for="itemsPerPage">Items per page:</label>
                            <select id="itemsPerPage" onchange="changeItemsPerPage()">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div id="totalItemsInfo"></div>
                    </div>
                    <div class="pagination-buttons" id="paginationButtons"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Data from PHP - ensure proper type conversion
        const productData = <?= json_encode($products) ?>.map(item => ({
            ...item,
            productPrice: parseFloat(item.productPrice),
            views: parseInt(item.views),
            purchases: parseInt(item.purchases),
            cluster_label: parseInt(item.cluster_label)
        }));
        
        const clusterStats = <?= json_encode($cluster_stats) ?>.map(stat => ({
            ...stat,
            cluster_label: parseInt(stat.cluster_label),
            product_count: parseInt(stat.product_count),
            total_views: parseInt(stat.total_views),
            total_purchases: parseInt(stat.total_purchases),
            avg_price: parseFloat(stat.avg_price)
        }));
        
        const clusters = <?= json_encode($clusters) ?>.map(c => parseInt(c));

        let currentFilter = 'all';
        let currentPage = 1;
        let itemsPerPage = 25;
        let charts = {};

        // Convert cluster label to human readable name
        function getClusterName(label) {
            switch (parseInt(label)) {
                case 2: return "Low Performing";
                case 1: return "Moderate Performing";
                case 0: return "High Performing";
                default: return "Unknown";
            }
        }

        // Format number with commas
        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Filter products based on cluster label
        function getFilteredData() {
            if (currentFilter === 'all') return productData;
            return productData.filter(item => item.cluster_label === currentFilter);
        }

        // Get paginated data
        function getPaginatedData() {
            const filteredData = getFilteredData();
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            return filteredData.slice(startIndex, endIndex);
        }

        // Calculate conversion rate
        function calculateConversionRate(views, purchases) {
            return views > 0 ? ((purchases / views) * 100).toFixed(2) : "0.00";
        }

        // Update the stats cards
        function updateStats() {
            const data = getFilteredData();
            const totalProducts = data.length;
            const totalViews = data.reduce((sum, item) => sum + item.views, 0);
            const totalPurchases = data.reduce((sum, item) => sum + item.purchases, 0);
            const avgConversion = totalViews > 0 ? ((totalPurchases / totalViews) * 100).toFixed(2) : "0.00";
            const avgPrice = data.length > 0 ? (data.reduce((sum, item) => sum + item.productPrice, 0) / data.length).toFixed(2) : "0.00";

            const statsGrid = document.getElementById('statsGrid');
            statsGrid.innerHTML = `
                <div class="stat-card">
                    <div class="stat-value">${totalProducts}</div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${numberWithCommas(totalViews)}</div>
                    <div class="stat-label">Total Views</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${numberWithCommas(totalPurchases)}</div>
                    <div class="stat-label">Total Purchases</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${avgConversion}%</div>
                    <div class="stat-label">Avg Conversion Rate</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">NRs${numberWithCommas(parseFloat(avgPrice))}</div>
                    <div class="stat-label">Avg Price</div>
                </div>
            `;
        }

        // Update pagination info and controls
        function updatePagination() {
            const filteredData = getFilteredData();
            const totalItems = filteredData.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            
            // Update pagination info
            const startItem = totalItems === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
            const endItem = Math.min(currentPage * itemsPerPage, totalItems);
            
            document.getElementById('paginationInfo').textContent = 
                `Showing ${startItem}-${endItem} of ${totalItems} products`;
            
            document.getElementById('totalItemsInfo').textContent = 
                `Total: ${totalItems} items`;

            // Update pagination buttons
            const paginationButtons = document.getElementById('paginationButtons');
            let buttonsHTML = '';

            // Previous button
            buttonsHTML += `<button class="pagination-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                &laquo; Previous
            </button>`;

            // Page numbers
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            if (startPage > 1) {
                buttonsHTML += `<button class="pagination-btn" onclick="changePage(1)">1</button>`;
                if (startPage > 2) {
                    buttonsHTML += `<span style="padding: 8px 4px;">...</span>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                buttonsHTML += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">
                    ${i}
                </button>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    buttonsHTML += `<span style="padding: 8px 4px;">...</span>`;
                }
                buttonsHTML += `<button class="pagination-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
            }

            // Next button
            buttonsHTML += `<button class="pagination-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}>
                Next &raquo;
            </button>`;

            paginationButtons.innerHTML = buttonsHTML;
        }

        // Change page
        function changePage(page) {
            const filteredData = getFilteredData();
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                updateTable();
                updatePagination();
            }
        }

        // Change items per page
        function changeItemsPerPage() {
            itemsPerPage = parseInt(document.getElementById('itemsPerPage').value);
            currentPage = 1; // Reset to first page
            updateTable();
            updatePagination();
        }

        // Update the product table
        function updateTable() {
            const data = getPaginatedData();
            const filteredData = getFilteredData();
            const tbody = document.getElementById('tableBody');
            const emptyState = document.getElementById('emptyState');
            const dataTable = document.getElementById('dataTable');

            if (filteredData.length === 0) {
                emptyState.style.display = 'block';
                dataTable.style.display = 'none';
            } else {
                emptyState.style.display = 'none';
                dataTable.style.display = 'table';
                
                tbody.innerHTML = '';
                data.forEach((item, index) => {
                    const conversionRate = calculateConversionRate(item.views, item.purchases);
                    const clusterLabel = item.cluster_label;
                    const globalIndex = (currentPage - 1) * itemsPerPage + index + 1;
                    
                    tbody.innerHTML += `
                        <tr data-cluster="${clusterLabel}">
                            <td>${globalIndex}</td>
                            <td>${item.productName}</td>
                            <td>${item.productCompany}</td>
                            <td>NRs${item.productPrice.toFixed(2)}</td>
                            <td>${numberWithCommas(item.views)}</td>
                            <td>${numberWithCommas(item.purchases)}</td>
                            <td>${conversionRate}</td>
                            <td><span class="cluster-badge cluster-${clusterLabel}">${getClusterName(clusterLabel)}</span></td>
                        </tr>
                    `;
                });
            }
        }

        // Filter button handler
        function filterCluster(cluster, event) {
            currentFilter = cluster;
            currentPage = 1; // Reset to first page when filtering
            
            // Update active button style
            document.querySelectorAll('.cluster-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            updateStats();
            updateTable();
            updatePagination();
            updateCharts();
        }

        // Export current filtered view to CSV
        function exportFilteredData() {
            const data = getFilteredData();
            let csv = "S.No,Product Name,Company,Price,Views,Purchases,Conversion Rate (%),Cluster\n";
            data.forEach((item, index) => {
                const conversionRate = calculateConversionRate(item.views, item.purchases);
                csv += `${index + 1},"${item.productName.replace(/"/g, '""')}","${item.productCompany.replace(/"/g, '""')}",${item.productPrice.toFixed(2)},${item.views},${item.purchases},${conversionRate},"${getClusterName(item.cluster_label)}"\n`;
            });

            const encodedUri = encodeURI("data:text/csv;charset=utf-8," + csv);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            const filterText = currentFilter === 'all' ? 'all_clusters' : getClusterName(currentFilter).toLowerCase().replace(/\s+/g, '_');
            link.setAttribute("download", `clustered_products_${filterText}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Initialize charts
        function initCharts() {
            const ctxCluster = document.getElementById('clusterChart').getContext('2d');
            const ctxScatter = document.getElementById('scatterChart').getContext('2d');
            const ctxPerformance = document.getElementById('performanceChart').getContext('2d');
            const ctxConversion = document.getElementById('conversionChart').getContext('2d');

            charts.clusterChart = new Chart(ctxCluster, {
                type: 'pie',
                data: {},
                options: {
                    responsive: true,
                    plugins: { 
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' products (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });

            charts.scatterChart = new Chart(ctxScatter, {
                type: 'scatter',
                data: {},
                options: {
                    responsive: true,
                    scales: {
                        x: { 
                            title: { display: true, text: 'Price (NRs)' }, 
                            beginAtZero: true 
                        },
                        y: { 
                            title: { display: true, text: 'Views' }, 
                            beginAtZero: true 
                        }
                    },
                    plugins: { 
                        tooltip: { 
                            callbacks: { 
                                label: function(context) {
                                    return `${context.dataset.label}: Price NRs${context.parsed.x}, Views ${context.parsed.y}`;
                                }
                            } 
                        } 
                    }
                }
            });

            charts.performanceChart = new Chart(ctxPerformance, {
                type: 'bar',
                data: {},
                options: {
                    responsive: true,
                    scales: { 
                        y: { beginAtZero: true },
                        x: { title: { display: true, text: 'Cluster' } }
                    },
                    plugins: { legend: { position: 'top' } }
                }
            });

            charts.conversionChart = new Chart(ctxConversion, {
                type: 'bar',
                data: {},
                options: {
                    responsive: true,
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            title: { display: true, text: 'Conversion Rate (%)' }
                        },
                        x: { title: { display: true, text: 'Cluster' } }
                    },
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Conversion Rate: ${context.parsed.y}%`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Update charts data based on filter
        function updateCharts() {
            // FIXED: Always use ALL product data for pie chart to show true distribution
            // Only filter other charts based on current filter
            const allData = productData; // Always use all data for pie chart
            const filteredData = getFilteredData(); // Use filtered data for other charts
            
            console.log('Updating charts - All data:', allData.length, 'Filtered:', filteredData.length);

            // Get cluster colors
            const clusterColors = {
                0: '#c62828',
                1: '#ef6c00', 
                2: '#2e7d32'
            };

            // 1. Cluster Distribution Pie Chart - ALWAYS show distribution of ALL data
            const allClusterCounts = {};
            clusters.forEach(c => allClusterCounts[c] = 0);
            allData.forEach(item => {
                allClusterCounts[item.cluster_label]++;
            });

            const pieLabels = [];
            const pieData = [];
            const pieColors = [];
            
            clusters.forEach(c => {
                if (allClusterCounts[c] > 0) {
                    pieLabels.push(getClusterName(c));
                    pieData.push(allClusterCounts[c]);
                    pieColors.push(clusterColors[c]);
                }
            });

            charts.clusterChart.data = {
                labels: pieLabels,
                datasets: [{
                    data: pieData,
                    backgroundColor: pieColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            };
            charts.clusterChart.update();

            // 2. Scatter Chart: Price vs Views - Use filtered data
            const scatterDatasets = [];
            clusters.forEach(c => {
                const clusterData = filteredData.filter(p => p.cluster_label === c);
                if (clusterData.length > 0) {
                    scatterDatasets.push({
                        label: getClusterName(c),
                        data: clusterData.map(p => ({
                            x: p.productPrice,
                            y: p.views
                        })),
                        backgroundColor: clusterColors[c],
                        borderColor: clusterColors[c],
                        pointRadius: 5,
                        pointHoverRadius: 7
                    });
                }
            });

            charts.scatterChart.data = { datasets: scatterDatasets };
            charts.scatterChart.update();

            // 3. Performance Comparison Chart - Use filtered data
            const performanceLabels = [];
            const avgPriceData = [];
            const avgPurchasesData = [];

            clusters.forEach(c => {
                const clusterData = filteredData.filter(p => p.cluster_label === c);
                if (clusterData.length > 0) {
                    performanceLabels.push(getClusterName(c));
                    avgPriceData.push((clusterData.reduce((sum, p) => sum + p.productPrice, 0) / clusterData.length).toFixed(2));
                    avgPurchasesData.push((clusterData.reduce((sum, p) => sum + p.purchases, 0) / clusterData.length).toFixed(2));
                }
            });

            charts.performanceChart.data = {
                labels: performanceLabels,
                datasets: [
                    {
                        label: 'Avg Price (NRs)',
                        backgroundColor: '#1e88e5',
                        borderColor: '#1565c0',
                        borderWidth: 1,
                        data: avgPriceData
                    },
                    {
                        label: 'Avg Purchases',
                        backgroundColor: '#43a047',
                        borderColor: '#2e7d32',
                        borderWidth: 1,
                        data: avgPurchasesData
                    }
                ]
            };
            charts.performanceChart.update();

            // 4. Conversion Rate Chart - Use filtered data
            const conversionLabels = [];
            const conversionData = [];
            const conversionColors = [];

            clusters.forEach(c => {
                const clusterData = filteredData.filter(p => p.cluster_label === c);
                if (clusterData.length > 0) {
                    const totalViews = clusterData.reduce((sum, p) => sum + p.views, 0);
                    const totalPurchases = clusterData.reduce((sum, p) => sum + p.purchases, 0);
                    const conversionRate = totalViews > 0 ? ((totalPurchases / totalViews) * 100).toFixed(2) : 0;
                    
                    conversionLabels.push(getClusterName(c));
                    conversionData.push(parseFloat(conversionRate));
                    conversionColors.push(clusterColors[c]);
                }
            });

            charts.conversionChart.data = {
                labels: conversionLabels,
                datasets: [{
                    label: 'Conversion Rate (%)',
                    data: conversionData,
                    backgroundColor: conversionColors,
                    borderColor: conversionColors.map(color => color + '80'),
                    borderWidth: 2
                }]
            };
            charts.conversionChart.update();
        }

        // Initialize dashboard
        function initDashboard() {
            console.log('Initializing dashboard with products:', productData.length); 
            console.log('Cluster stats:', clusterStats); 
            console.log('Available clusters:', clusters); 
            
            updateStats();
            updateTable();
            updatePagination();
            initCharts();
            updateCharts();
        }

        document.addEventListener('DOMContentLoaded', initDashboard);
    </script>
</body>
</html>