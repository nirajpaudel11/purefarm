<?php
session_start();
include('includes/config.php');

// Update Cart Quantities
if (isset($_POST['submit']) && !empty($_SESSION['cart'])) {
  $valid = true;
  $errorMessages = [];

  foreach ($_POST['quantity'] as $productId => $qty) {
    $productId = (int) $productId;
    $qty = (int) $qty;

    if (!isset($_SESSION['cart'][$productId])) {
      $errorMessages[] = "Invalid product ID $productId.";
      $valid = false;
      continue;
    }

    if ($qty <= 0) {
      $errorMessages[] = "Quantity for product ID $productId must be greater than 0.";
      $valid = false;
      continue;
    }

    $stmt = $con->prepare("SELECT productQuantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      $errorMessages[] = "Product ID $productId does not exist.";
      $valid = false;
      $stmt->close();
      continue;
    }

    $row = $result->fetch_assoc();
    $availableStock = (int) $row['productQuantity'];
    $stmt->close();

    if ($qty > $availableStock) {
      $errorMessages[] = "Only $availableStock item(s) available for product ID $productId.";
      $valid = false;
    }
  }

  if ($valid) {
    foreach ($_POST['quantity'] as $productId => $qty) {
      $productId = (int) $productId;
      $qty = (int) $qty;
      $_SESSION['cart'][$productId]['quantity'] = $qty;
    }
    echo "<script>alert('Your cart has been updated successfully.');</script>";
  } else {
    foreach ($errorMessages as $msg) {
      echo "<script>alert('$msg');</script>";
    }
  }
}

// Remove selected cart items
if (isset($_POST['remove_selected']) && isset($_POST['remove_code']) && is_array($_POST['remove_code'])) {
  foreach ($_POST['remove_code'] as $productId) {
    $productId = (int) $productId;
    unset($_SESSION['cart'][$productId]);
  }
  echo "<script>alert('Selected items removed from your cart.');</script>";
}

// Submit Order
if (isset($_POST['ordersubmit'])) {
  if (!isset($_SESSION['login'])) {
    $_SESSION['redirect_after_login'] = 'my-cart.php';
    header('Location: login.php');
    exit();
  }

  if (!isset($_SESSION['ordertoken']) || empty($_SESSION['ordertoken'])) {
    function generateRandomString($length = 6)
    {
      return substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"), 0, $length);
    }
    $_SESSION['ordertoken'] = generateRandomString();
  }

  $order_token = $_SESSION['ordertoken'];
  $quantity = $_POST['quantity'] ?? [];

  foreach ($quantity as $productId => $qty) {
    $productId = (int) $productId;
    $qty = (int) $qty;
    if ($qty > 0) {
      $userId = $_SESSION['id'];
      mysqli_query($con, "INSERT INTO orders(userId, productId, quantity, order_token) VALUES('$userId', '$productId', '$qty', '$order_token')");
    }
  }

  header('Location: payment-method.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>My Cart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <link href="assets/css/red.css" rel="stylesheet">
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
          <li class="active">My Cart</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="body-content outer-top-xs">
    <div class="container">
      <div class="row inner-bottom-sm">
        <div class="shopping-cart">
          <form method="post">
            <div class="col-md-12 col-sm-12 shopping-cart-table">
              <div class="table-responsive">
                <?php if (!empty($_SESSION['cart'])): ?>
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Remove</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Stock</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Shipping</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $productIds = array_keys($_SESSION['cart']);
                      $sql = "SELECT * FROM products WHERE id IN (" . implode(',', $productIds) . ")";
                      $query = mysqli_query($con, $sql);
                      $totalprice = 0;

                      while ($row = mysqli_fetch_assoc($query)) {
                        $pid = $row['id'];
                        $stock = (int) $row['productQuantity'];
                        $qty = $_SESSION['cart'][$pid]['quantity'] ?? 1;

                        // Adjust quantity if stock is less
                        if ($qty > $stock) {
                          $qty = $stock;
                          $_SESSION['cart'][$pid]['quantity'] = $qty;
                        }

                        $subtotal = ($qty * $row['productPrice']) + $row['shippingCharge'];
                        $totalprice += $subtotal;
                        ?>
                        <tr>
                          <td>
                            <input type="checkbox" name="remove_code[]" value="<?= $pid ?>">
                          </td>
                          <td><img src="admin/productimages/<?= $pid ?>/<?= $row['productImage1'] ?>" width="80"></td>
                          <td><?= htmlspecialchars($row['productName']) ?></td>
                          <td><?= $stock == 0 ? '<span class="text-danger">Out of Stock</span>' : $stock ?></td>
                          <td>
                            <?php if ($stock == 0): ?>
                              <span class="text-danger">Out of Stock</span>
                            <?php elseif ($qty > $stock): ?>
                              <span class="text-warning">Only <?= $stock ?> left</span><br>
                              <input type="number" name="quantity[<?= $pid ?>]" value="<?= $stock ?>" min="1"
                                max="<?= $stock ?>" required>
                            <?php else: ?>
                              <input type="number" name="quantity[<?= $pid ?>]" value="<?= $qty ?>" min="1"
                                max="<?= $stock ?>" required>
                            <?php endif; ?>
                          </td>
                          <td>Rs. <?= number_format($row['productPrice'], 2) ?></td>
                          <td>Rs. <?= number_format($row['shippingCharge'], 2) ?></td>
                          <td>Rs. <?= number_format($subtotal, 2) ?></td>
                        </tr>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="7" align="right"><strong>Grand Total:</strong></td>
                        <td><strong>Rs. <?= number_format($totalprice, 2) ?></strong></td>
                      </tr>
                    </tfoot>
                  </table>
                  <div class="text-right">
                    <input type="submit" name="submit" value="Update Cart" class="btn btn-primary">
                    <input type="submit" name="ordersubmit" value="Place Order" class="btn btn-success">
                    <input type="submit" name="remove_selected" value="Remove Selected" class="btn btn-danger"
                      onclick="return confirm('Are you sure?');">
                  </div>
                <?php else: ?>
                  <p class="text-center">Your shopping cart is empty.</p>
                <?php endif; ?>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php include('includes/footer.php'); ?>

  <script src="assets/js/jquery-1.11.1.min.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>