<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Update cart
if (isset($_POST['submit'])) {
	if (!empty($_SESSION['cart'])) {
		$valid = true;
		$errorMessages = [];

		foreach ($_POST['quantity'] as $key => $val) {
			$productId = (int) $_SESSION['cart'][$key]['id'];
			$val = (int) $val;

			$query = mysqli_query($con, "SELECT productQuantity FROM products WHERE id = '$productId'");
			$row = mysqli_fetch_assoc($query);
			$availableStock = (int) $row['productQuantity'];

			if ($val <= 0) {
				$errorMessages[] = "Quantity for product ID $productId must be greater than 0.";
				$valid = false;
			} elseif ($val > $availableStock) {
				$errorMessages[] = "Only $availableStock item(s) available for product ID $productId.";
				$valid = false;
			}
		}

		if ($valid) {
			foreach ($_POST['quantity'] as $key => $val) {
				$val = (int) $val;
				$_SESSION['cart'][$key]['quantity'] = $val;
			}
			echo "<script>alert('Your cart has been updated successfully.');</script>";
		} else {
			foreach ($errorMessages as $msg) {
				echo "<script>alert('$msg');</script>";
			}
		}
	}
}

// Remove product from cart
if (isset($_POST['remove_code'])) {
	if (!empty($_SESSION['cart'])) {
		foreach ($_POST['remove_code'] as $key) {
			unset($_SESSION['cart'][$key]);
		}
		echo "<script>alert('Your cart has been updated');</script>";
	}
}

// Submit order
if (isset($_POST['ordersubmit'])) {
	if (strlen($_SESSION['login']) == 0) {
		header('location:login.php');
		exit();
	}

	if (!isset($_SESSION['ordertoken']) || $_SESSION['ordertoken'] == "") {
		function generateRandomString($length = 6)
		{
			$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}
		$_SESSION['ordertoken'] = generateRandomString();
	}

	$order_token = $_SESSION['ordertoken'];
	$quantity = $_POST['quantity'];
	$pdd = $_SESSION['pid'];
	$value = array_combine($pdd, $quantity);

	foreach ($value as $productId => $qty) {
		$productId = (int) $productId;
		$qty = (int) $qty;
		mysqli_query($con, "INSERT INTO orders(userId, productId, quantity, order_token) VALUES('" . $_SESSION['id'] . "', '$productId', '$qty', '$order_token')");
	}

	header('location:payment-method.php');
	exit();
}

// Update billing address
if (isset($_POST['update'])) {
	$baddress = mysqli_real_escape_string($con, $_POST['billingaddress']);
	$bstate = mysqli_real_escape_string($con, $_POST['bilingstate']);
	$bcity = mysqli_real_escape_string($con, $_POST['billingcity']);
	$bpincode = mysqli_real_escape_string($con, $_POST['billingpincode']);

	$query = mysqli_query($con, "UPDATE users SET billingAddress='$baddress', billingState='$bstate', billingCity='$bcity', billingPincode='$bpincode' WHERE id='" . $_SESSION['id'] . "'");
	if ($query) {
		echo "<script>alert('Billing Address has been updated');</script>";
	}
}

// Update shipping address
if (isset($_POST['shipupdate'])) {
	$saddress = mysqli_real_escape_string($con, $_POST['shippingaddress']);
	$sstate = mysqli_real_escape_string($con, $_POST['shippingstate']);
	$scity = mysqli_real_escape_string($con, $_POST['shippingcity']);
	$spincode = mysqli_real_escape_string($con, $_POST['shippingpincode']);

	$query = mysqli_query($con, "UPDATE users SET shippingAddress='$saddress', shippingState='$sstate', shippingCity='$scity', shippingPincode='$spincode' WHERE id='" . $_SESSION['id'] . "'");
	if ($query) {
		echo "<script>alert('Shipping Address has been updated');</script>";
	}
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<!-- Meta -->
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<meta name="keywords" content="MediaCenter, Template, eCommerce">
	<meta name="robots" content="all">

	<title>My Cart</title>
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/main.css">
	<link rel="stylesheet" href="assets/css/red.css">
	<link rel="stylesheet" href="assets/css/owl.carousel.css">
	<link rel="stylesheet" href="assets/css/owl.transitions.css">
	<!--<link rel="stylesheet" href="assets/css/owl.theme.css">-->
	<link href="assets/css/lightbox.css" rel="stylesheet">
	<link rel="stylesheet" href="assets/css/animate.min.css">
	<link rel="stylesheet" href="assets/css/rateit.css">
	<link rel="stylesheet" href="assets/css/bootstrap-select.min.css">

	<!-- Demo Purpose Only. Should be removed in production -->
	<link rel="stylesheet" href="assets/css/config.css">

	<link href="assets/css/green.css" rel="alternate stylesheet" title="Green color">
	<link href="assets/css/blue.css" rel="alternate stylesheet" title="Blue color">
	<link href="assets/css/red.css" rel="alternate stylesheet" title="Red color">
	<link href="assets/css/orange.css" rel="alternate stylesheet" title="Orange color">
	<link href="assets/css/dark-green.css" rel="alternate stylesheet" title="Darkgreen color">
	<!-- Demo Purpose Only. Should be removed in production : END -->


	<!-- Icons/Glyphs -->
	<link rel="stylesheet" href="assets/css/font-awesome.min.css">

	<!-- Fonts -->
	<link href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,700' rel='stylesheet' type='text/css'>

	<!-- Favicon -->
	<link rel="shortcut icon" href="assets/images/favicon.ico">

	<!-- HTML5 elements and media queries Support for IE8 : HTML5 shim and Respond.js -->
	<!--[if lt IE 9]>
			<script src="assets/js/html5shiv.js"></script>
			<script src="assets/js/respond.min.js"></script>
		<![endif]-->

</head>

<body class="cnt-home">



	<!-- ============================================== HEADER ============================================== -->
	<header class="header-style-1">
		<?php include('includes/top-header.php'); ?>
		<?php include('includes/main-header.php'); ?>
		<?php include('includes/menu-bar.php'); ?>
	</header>
	<!-- ============================================== HEADER : END ============================================== -->
	<div class="breadcrumb">
		<div class="container">
			<div class="breadcrumb-inner">
				<ul class="list-inline list-unstyled">
					<li><a href="#">Home</a></li>
					<li class='active'>Shopping Cart</li>
				</ul>
			</div><!-- /.breadcrumb-inner -->
		</div><!-- /.container -->
	</div><!-- /.breadcrumb -->

	<div class="body-content outer-top-xs">
		<div class="container">
			<div class="row inner-bottom-sm">
				<div class="shopping-cart">
					<form name="cart" method="post">
						<div class="col-md-12 col-sm-12 shopping-cart-table">
							<div class="table-responsive">
								<?php if (!empty($_SESSION['cart'])): ?>
									<table class="table table-bordered">
										<thead>
											<tr>
												<th>Remove</th>
												<th>Image</th>
												<th>Product Name</th>
												<th>Stock available</th>
												<th>Quantity</th>
												<th>Price</th>
												<th>Shipping</th>
												<th>Grand Total</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$pdtid = [];
											$sql = "SELECT * FROM products WHERE id IN(" . implode(',', array_keys($_SESSION['cart'])) . ")";
											$query = mysqli_query($con, $sql);
											$totalprice = 0;
											$totalqunty = 0;

											while ($row = mysqli_fetch_array($query)):
												$pid = $row['id'];
												$qty = $_SESSION['cart'][$pid]['quantity'];
												$subtotal = $qty * $row['productPrice'] + $row['shippingCharge'];
												$totalprice += $subtotal;
												$totalqunty += $qty;
												$pdtid[] = $pid;
												?>
												<tr>
													<td><input type="checkbox" name="remove_code[]" value="<?= $pid ?>"></td>
													<td><img src="admin/productimages/<?= $pid ?>/<?= $row['productImage1'] ?>" width="114"
															height="146"></td>
													<td>
														<h4><a href="product-details.php?pid=<?= $pid ?>"><?= $row['productName'] ?></a></h4>
														<div class="reviews">
															(<?= mysqli_num_rows(mysqli_query($con, "SELECT * FROM productreviews WHERE productId = $pid")) ?>
															Reviews)
														</div>
													</td>
													<td><?= $row['productQuantity'] ?></td> <!-- Available Stock column added here -->
													<td>
														<div class="quant-input">
															<input type="text" name="quantity[<?= $pid ?>]" value="<?= $qty ?>">
														</div>
													</td>
													<td>Rs <?= $row['productPrice'] ?>.00</td>
													<td>Rs <?= $row['shippingCharge'] ?>.00</td>
													<td>Rs <?= $subtotal ?>.00</td>
												</tr>

											<?php endwhile;
											$_SESSION['pid'] = $pdtid;
											$_SESSION['qnty'] = $totalqunty; ?>
										</tbody>
										<tfoot>
											<tr>
												<td colspan="7">
													<a href="index.php" class="btn btn-upper btn-primary outer-left-xs">Continue Shopping</a>
													<input type="submit" name="submit" value="Update shopping cart"
														class="btn btn-upper btn-primary pull-right outer-right-xs">
												</td>
											</tr>
										</tfoot>
									</table>
								<?php else: ?>
									<h4>Your shopping cart is empty.</h4>
								<?php endif; ?>
							</div>
						</div>

						<?php if (!empty($_SESSION['cart'])): ?>
							<!-- Billing Address -->
							<div class="col-md-4 col-sm-12 estimate-ship-tax">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>Billing Address</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<?php $row = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM users WHERE id = '" . $_SESSION['id'] . "'")); ?>
												<div class="form-group">
													<label>Billing Address <span>*</span></label>
													<textarea name="billingaddress" required
														class="form-control"><?= $row['billingAddress'] ?></textarea>
												</div>
												<div class="form-group">
													<label>Billing State <span>*</span></label>
													<input type="text" name="bilingstate" required value="<?= $row['billingState'] ?>"
														class="form-control">
												</div>
												<div class="form-group">
													<label>Billing City <span>*</span></label>
													<input type="text" name="billingcity" required value="<?= $row['billingCity'] ?>"
														class="form-control">
												</div>
												<div class="form-group">
													<label>Billing Pincode <span>*</span></label>
													<input type="text" name="billingpincode" required value="<?= $row['billingPincode'] ?>"
														class="form-control">
												</div>
												<button type="submit" name="update" class="btn btn-primary">Update</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>

							<!-- Shipping Address -->
							<div class="col-md-4 col-sm-12 estimate-ship-tax">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>Shipping Address</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<div class="form-group">
													<label>Shipping Address <span>*</span></label>
													<textarea name="shippingaddress" required
														class="form-control"><?= $row['shippingAddress'] ?></textarea>
												</div>
												<div class="form-group">
													<label>Shipping State <span>*</span></label>
													<input type="text" name="shippingstate" required value="<?= $row['shippingState'] ?>"
														class="form-control">
												</div>
												<div class="form-group">
													<label>Shipping City <span>*</span></label>
													<input type="text" name="shippingcity" required value="<?= $row['shippingCity'] ?>"
														class="form-control">
												</div>
												<div class="form-group">
													<label>Shipping Pincode <span>*</span></label>
													<input type="text" name="shippingpincode" required value="<?= $row['shippingPincode'] ?>"
														class="form-control">
												</div>
												<button type="submit" name="shipupdate" class="btn btn-primary">Update</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>

							<!-- Order Total & Checkout -->
							<div class="col-md-4 col-sm-12 cart-shopping-total">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>Grand Total <span class="pull-right">Rs <?= $_SESSION['tp'] = "$totalprice.00" ?></span></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<button type="submit" name="ordersubmit" class="btn btn-primary pull-right">PROCEED TO
													CHECKOUT</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
					</form>
				</div>
			</div>
		</div>
	</div>

	<?php include('includes/footer.php'); ?>

	<script src="assets/js/jquery-1.11.1.min.js"></script>

	<script src="assets/js/bootstrap.min.js"></script>

	<script src="assets/js/bootstrap-hover-dropdown.min.js"></script>
	<script src="assets/js/owl.carousel.min.js"></script>

	<script src="assets/js/echo.min.js"></script>
	<script src="assets/js/jquery.easing-1.3.min.js"></script>
	<script src="assets/js/bootstrap-slider.min.js"></script>
	<script src="assets/js/jquery.rateit.min.js"></script>
	<script type="text/javascript" src="assets/js/lightbox.min.js"></script>
	<script src="assets/js/bootstrap-select.min.js"></script>
	<script src="assets/js/wow.min.js"></script>
	<script src="assets/js/scripts.js"></script>

	<!-- For demo purposes – can be removed on production -->

	<script src="switchstylesheet/switchstylesheet.js"></script>

	<script>
		$(document).ready(function () {
			$(".changecolor").switchstylesheet({ seperator: "color" });
			$('.show-theme-options').click(function () {
				$(this).parent().toggleClass('open');
				return false;
			});
		});

		$(window).bind("load", function () {
			$('.show-theme-options').delay(2000).trigger('click');
		});
	</script>
	<!-- For demo purposes – can be removed on production : End -->
</body>

</html>