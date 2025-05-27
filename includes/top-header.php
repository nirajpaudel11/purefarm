<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
?>

<div class="top-bar animate-dropdown">
	<div class="container">
		<div class="header-top-inner">
			<div class="cnt-account">
				<ul class="list-unstyled">

					<?php if (isset($_SESSION['login']) && strlen($_SESSION['login'])): ?>
						<li>
							<a href="#"><i class="icon fa fa-user"></i>
								Welcome - <?= htmlentities($_SESSION['username'] ?? 'User') ?>
							</a>
						</li>
					<?php endif; ?>

					<li><a href="my-account.php"><i class="icon fa fa-user"></i>My Account</a></li>
					<li><a href="my-wishlist.php"><i class="icon fa fa-heart"></i>Wishlist</a></li>
					<li><a href="my-cart.php"><i class="icon fa fa-shopping-cart"></i>My Cart</a></li>

					<?php if (!isset($_SESSION['login']) || strlen($_SESSION['login']) == 0): ?>
						<li><a href="login.php"><i class="icon fa fa-sign-in"></i>Login</a></li>
						<li><a href="admin/index.php"><i class="icon fa fa-sign-in"></i>Admin</a></li>
					<?php else: ?>
						<li><a href="logout.php"><i class="icon fa fa-sign-out"></i>Logout</a></li>
					<?php endif; ?>

				</ul>
			</div><!-- /.cnt-account -->

			<div class="cnt-block">
				<ul class="list-unstyled list-inline">
					<!-- Additional top bar items if needed -->
				</ul>
			</div>

			<div class="clearfix"></div>
		</div><!-- /.header-top-inner -->
	</div><!-- /.container -->
</div><!-- /.header-top -->