<?php
session_start();
error_reporting(0);
include('includes/config.php');
if (strlen($_SESSION['login']) == 0) {
	header('location:login.php');
	exit;
}

$userId = $_SESSION['id'];

// Fetch current user info including shipping info
$stmt = $con->prepare("SELECT name, email, contactno, shippingAddress, shippingState, shippingCity, shippingPincode FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Update shipping info first
	$shippingAddress = $_POST['shippingAddress'] ?? '';
	$shippingState = $_POST['shippingState'] ?? '';
	$shippingCity = $_POST['shippingCity'] ?? '';

	// Update shipping info in users table
	$updateStmt = $con->prepare("UPDATE users SET shippingAddress=?, shippingState=?, shippingCity=? WHERE id=?");
	$updateStmt->bind_param("sssii", $shippingAddress, $shippingState, $shippingCity, $userId);
	$updateStmt->execute();

	// Handle Khalti payment initiation
	if (isset($_POST['khaltipay'])) {
		// Prepare Khalti payment request

		$amount = $_SESSION['tp'] * 100; // amount in paisa
		$purchase_order_id = rand(100000, 999999); // some random order id
		$purchase_order_name = "FarmFreshStore Order";

		$postFields = array(
			"return_url" => "http://localhost/shopping/verify_khalti_payment.php",
			"website_url" => "http://localhost/shopping/",
			"amount" => $amount,
			"purchase_order_id" => $purchase_order_id,
			"purchase_order_name" => $purchase_order_name,
			"customer_info" => array(
				"name" => $userData['name'],
				"email" => $userData['email'],
				"phone" => $userData['contactno']
			)
		);

		$jsonData = json_encode($postFields);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $jsonData,
			CURLOPT_HTTPHEADER => array(
				'Authorization: key b2d09c43309f41feb8db370e76c37558',
				'Content-Type: application/json',
			),
		));

		$response = curl_exec($curl);

		if (curl_errno($curl)) {
			echo 'Curl Error: ' . curl_error($curl);
			exit;
		}

		curl_close($curl);

		$responseArray = json_decode($response, true);

		if (isset($responseArray['error'])) {
			echo 'Error: ' . $responseArray['error'];
			exit;
		} elseif (isset($responseArray['payment_url'])) {
			// Update orders table with payment method khalti_wallet for this user, only where paymentMethod is null
			$con->query("UPDATE orders SET paymentMethod='khalti_wallet' WHERE userId='$userId' AND paymentMethod IS NULL");
			// Clear cart session
			unset($_SESSION['cart']);
			// Redirect to Khalti payment page
			header('Location: ' . $responseArray['payment_url']);
			exit;
		} else {
			echo 'Unexpected response: ' . $response;
			exit;
		}
	}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<title>Farm Fresh Store | Payment Method</title>
	<link rel="stylesheet" href="assets/css/bootstrap.min.css" />
	<link rel="stylesheet" href="assets/css/main.css" />
	<link rel="stylesheet" href="assets/css/red.css" />
	<link rel="stylesheet" href="assets/css/font-awesome.min.css" />
</head>

<body class="cnt-home">

	<header class="header-style-1">
		<?php include('includes/top-header.php'); ?>
		<?php include('includes/main-header.php'); ?>
		<?php include('includes/menu-bar.php'); ?>
	</header>

	<div class="breadcrumb">
		<div class="container">
			<div class="breadcrumb-inner">
				<ul class="list-inline list-unstyled">
					<li><a href="index.php">Home</a></li>
					<li class='active'>Payment Method</li>
				</ul>
			</div>
		</div>
	</div>

	<div class="body-content outer-top-bd">
		<div class="container">
			<div class="checkout-box faq-page inner-bottom-sm">
				<div class="row">
					<div class="col-md-12">
						<h2>Shipping Address & Payment Method</h2>

						<form method="post" id="paymentForm">

							<h4>Shipping Details</h4>



							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="shippingAddress">Address</label>
										<input type="text" name="shippingAddress" id="shippingAddress" class="form-control" rows="4"
											placeholder="address" value="<?php echo htmlspecialchars($userData['shippingAddress']); ?>"
											required></input>
									</div>
									<div class="form-group">
										<label for="shippingState">State</label>
										<input type="text" name="shippingState" id="shippingState" class="form-control" placeholder="State"
											value="<?php echo htmlspecialchars($userData['shippingState']); ?>" required>
									</div>

									<div class="form-group">
										<label for="shippingCity">City</label>
										<input type="text" name="shippingCity" id="shippingCity" class="form-control" placeholder="City"
											value="<?php echo htmlspecialchars($userData['shippingCity']); ?>" required>
									</div>
								</div>


								<div class="col-md-6">
									<hr />

									<h4>Select Payment Method</h4>

									<input type="radio" name="paymethod" value="khalti_wallet" id="khalti" checked>
									<label for="khalti">Khalti Wallet</label>

									<br><br>

									<button type="submit" id="khaltiPaymentButton" class="btn btn-primary" name="khaltipay">Pay with
										Khalti</button>

								</div>
							</div>




						</form>

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