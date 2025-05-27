<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Add to cart logic
if (isset($_GET['action']) && $_GET['action'] == "add") {
	$id = intval($_GET['id']);
	if (isset($_SESSION['cart'][$id])) {
		$_SESSION['cart'][$id]['quantity']++;
	} else {
		$sql_p = "SELECT * FROM products WHERE id={$id}";
		$query_p = mysqli_query($con, $sql_p);
		if (mysqli_num_rows($query_p) != 0) {
			$row_p = mysqli_fetch_array($query_p);
			$_SESSION['cart'][$row_p['id']] = array("quantity" => 1, "price" => $row_p['productPrice']);
		} else {
			$message = "Product ID is invalid";
		}
	}
	echo "<script>alert('Product has been added to the cart');</script>";
	echo "<script type='text/javascript'> document.location ='my-cart.php'; </script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>Pure Farm | Home</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<!-- CSS -->
	<link rel="stylesheet" href="assets/css/bootstrap.min.css" />
	<link rel="stylesheet" href="assets/css/main.css" />
	<link rel="stylesheet" href="assets/css/font-awesome.min.css" />
	<link rel="shortcut icon" href="assets/images/favicon.ico" />


	<style>
		.my-4 {
			margin-top: 1.5rem;
			margin-bottom: 1.5rem;
		}

		.product-card {
			box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
			transition: transform 0.2s;
			border-radius: 10px;
			overflow: hidden;
			height: 100%;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
		}

		.product-card:hover {
			transform: translateY(-5px);
		}

		.product-img {
			height: 250px;
			object-fit: cover;
			width: 100%;
		}

		.card-title {
			font-size: 16px;
			font-weight: 600;
			margin-bottom: 0.5rem;
		}

		.price-section .price {
			color: #28a745;
			font-weight: bold;
			font-size: 1.1rem;
		}

		.price-section .price-before {
			text-decoration: line-through;
			color: #888;
			margin-left: 10px;
			font-size: 0.9rem;
		}

		.btn-add-cart {
			margin-top: 10px;
			width: 100%;
		}
	</style>
</head>

<body class="cnt-home">

	<?php include('includes/top-header.php'); ?>
	<?php include('includes/main-header.php'); ?>
	<?php include('includes/menu-bar.php'); ?>

	<div class="body-content outer-top-xs">
		<div class="container">
			<div class="row">
				<!-- Sidebar -->
				<div class="col-md-3">
					<?php include('includes/side-menu.php'); ?>
				</div>

				<!-- Main Content -->
				<div class="col-md-9">
					<!-- Hero Image -->
					<div class="mb-4">
						<img src="assets/images/sliders/slider1.png" class="img-fluid rounded" alt="Hero Image" />
					</div>

					<!-- Filters -->
					<form method="GET" class="row my-4 g-2 align-items-center">
						<div class="col-md-5">
							<select name="category" class="form-control">
								<option value="">All Categories</option>
								<?php
								$cats = mysqli_query($con, "SELECT * FROM category");
								while ($cat = mysqli_fetch_array($cats)) {
									$selected = (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? "selected" : "";
									echo "<option value='{$cat['id']}' $selected>" . htmlentities($cat['categoryName']) . "</option>";
								}
								?>
							</select>
						</div>

						<div class="col-md-5">
							<select name="sort" class="form-control">
								<option value="">Sort By</option>
								<option value="price_asc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'price_asc')
									echo "selected"; ?>>Price: Low to High</option>
								<option value="price_desc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'price_desc')
									echo "selected"; ?>>Price: High to Low</option>
								<option value="name_asc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'name_asc')
									echo "selected"; ?>>Name: A-Z</option>
								<option value="name_desc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'name_desc')
									echo "selected"; ?>>Name: Z-A</option>
							</select>
						</div>

						<div class="col-md-2">
							<button class="btn btn-success w-100" type="submit">Go</button>
						</div>
					</form>

					<!-- Products -->
					<div class="row">
						<?php
						$where = "WHERE 1=1";


						if (!empty($_GET['category'])) {
							$cat = intval($_GET['category']);
							$where .= " AND category = $cat";
						}

						$sortSql = "";
						if (isset($_GET['sort'])) {
							if ($_GET['sort'] == 'price_asc') {
								$sortSql = "ORDER BY productPrice ASC";
							} elseif ($_GET['sort'] == 'price_desc') {
								$sortSql = "ORDER BY productPrice DESC";
							} elseif ($_GET['sort'] == 'name_asc') {
								$sortSql = "ORDER BY productName ASC";
							} elseif ($_GET['sort'] == 'name_desc') {
								$sortSql = "ORDER BY productName DESC";
							}
						}

						$limit = 8;
						$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
						$offset = ($page - 1) * $limit;

						$total_result = mysqli_query($con, "SELECT COUNT(*) as total FROM products $where");
						$total_row = mysqli_fetch_assoc($total_result);
						$total_products = $total_row['total'];
						$total_pages = ceil($total_products / $limit);

						$ret = mysqli_query($con, "SELECT * FROM products $where $sortSql LIMIT $offset, $limit");
						if (mysqli_num_rows($ret) == 0) {
							echo "<p class='text-center'>No products found.</p>";
						} else {
							while ($row = mysqli_fetch_array($ret)) {
								?>
								<div class="col-md-3 col-sm-6 my-4 d-flex">
									<div class="card product-card">
										<img
											src="admin/productimages/<?php echo htmlentities($row['id']); ?>/<?php echo htmlentities($row['productImage1']); ?>"
											alt="<?php echo htmlentities($row['productName']); ?>" class="product-img" />
										<div class="card-body d-flex flex-column">
											<h5 class="card-title">
												<a href="product-details.php?pid=<?php echo htmlentities($row['id']); ?>">
													<?php echo htmlentities($row['productName']); ?>
												</a>
											</h5>
											<div class="price-section mb-auto">
												<span class="price">Rs.<?php echo htmlentities($row['productPrice']); ?></span>
												<?php if ($row['productPriceBeforeDiscount'] > 0) { ?>
													<span class="price-before">Rs.<?php echo htmlentities($row['productPriceBeforeDiscount']); ?></span>
												<?php } ?>
											</div>

											<?php if ($row['productAvailability'] == 'In Stock') { ?>
												<a href="index.php?action=add&id=<?php echo $row['id']; ?>"
													class="btn btn-success btn-sm btn-add-cart mt-auto">Add to Cart</a>
											<?php } else { ?>
												<span class="btn btn-danger btn-sm btn-add-cart mt-auto disabled">Out of Stock</span>
											<?php } ?>
										</div>
									</div>
								</div>
								<?php
							}
						}
						?>
					</div>

					<!-- Pagination -->
					<div class="text-center mt-4">
						<ul class="pagination justify-content-center">
							<?php if ($page > 1): ?>
								<li class="page-item">
									<a class="page-link"
										href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&laquo; Prev</a>
								</li>
							<?php endif; ?>

							<?php for ($i = 1; $i <= $total_pages; $i++): ?>
								<li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
									<a class="page-link"
										href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
								</li>
							<?php endfor; ?>

							<?php if ($page < $total_pages): ?>
								<li class="page-item">
									<a class="page-link"
										href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next &raquo;</a>
								</li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php include('includes/footer.php'); ?>
	<script src="assets/js/jquery-1.11.1.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
</body>

</html>