<!--start header -->
		<header>
			<style>
				headers, .topbar, .topbar .navbar {
					background: #06130d !important;
					border-bottom: 1px solid rgba(255,255,255,0.08);
				}
				.topbar .navbar-nav .nav-link,
				.topbar .navbar-nav .nav-link i,
				.topbar .navbar-nav .user-box .nav-link {
					color: rgba(255,255,255,0.85) !important;
				}
				.topbar .navbar-nav .nav-link:hover,
				.topbar .navbar-nav .nav-link:focus,
				.topbar .user-box .nav-link:hover,
				.user-box .dropdown-menu .dropdown-item:hover {
					background: rgba(16,185,129,0.12) !important;
					color: #a7f3d0 !important;
				}
				.user-box .nav-link {
					padding: 0.5rem 0.75rem;
				}
				.dropdown-menu {
					background: #06130d !important;
					border-color: rgba(16,185,129,0.18) !important;
				}
				.dropdown-item {
					color: #d7ffe8 !important;
				}
				.user-info .user-name,
				.user-info .designattion {
					color: #d7ffe8 !important;
				}
			</style>
			<div class="topbar d-flex align-items-center">
				<?php
					$pendingOrdersResult = mysqli_query($db, "SELECT COUNT(*) AS total FROM order_list WHERE status = 0");
					$pendingOrders = $pendingOrdersResult ? (int) mysqli_fetch_assoc($pendingOrdersResult)['total'] : 0;
					$pendingInquiriesResult = mysqli_query($db, "SELECT COUNT(*) AS total FROM comments WHERE status IN (0, 2)");
					$pendingInquiries = $pendingInquiriesResult ? (int) mysqli_fetch_assoc($pendingInquiriesResult)['total'] : 0;
					$productInquiriesResult = mysqli_query($db, "SELECT COUNT(*) AS total FROM product_inquiries WHERE status = 0");
					$pendingProductInquiries = $productInquiriesResult ? (int) mysqli_fetch_assoc($productInquiriesResult)['total'] : 0;
					$pendingInquiries += $pendingProductInquiries;
					$lowStockResult = mysqli_query($db, "SELECT COUNT(*) AS total FROM products WHERE status = 1 AND stock_quantity <= low_stock_threshold");
					$lowStockProducts = $lowStockResult ? (int) mysqli_fetch_assoc($lowStockResult)['total'] : 0;
					$pendingProductsResult = mysqli_query($db, "SELECT COUNT(*) AS total FROM products WHERE status = 2");
					$pendingProducts = $pendingProductsResult ? (int) mysqli_fetch_assoc($pendingProductsResult)['total'] : 0;
					$notificationTotal = $pendingOrders + $pendingInquiries + $lowStockProducts + $pendingProducts;
				?>
				<nav class="navbar navbar-expand">
					<div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
					</div>
					
					<div class="top-menu ms-auto">
						<ul class="navbar-nav align-items-center">
							<li class="nav-item mobile-search-icon">
								<a class="nav-link" href="#">	<i class='bx bx-search'></i>
								</a>
							</li>
							<li class="nav-item dropdown dropdown-large" id="adminNotifications">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" aria-expanded="false" aria-controls="adminNotificationsMenu" title="Notifications">
									<i class="bx bx-bell fs-4"></i>
									<?php if ($notificationTotal > 0) { ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo $notificationTotal > 99 ? '99+' : $notificationTotal; ?></span><?php } ?>
								</a>
								<div class="dropdown-menu dropdown-menu-end" id="adminNotificationsMenu">
									<div class="header-notifications-list">
										<?php if ($pendingOrders > 0) { ?><a class="dropdown-item d-flex align-items-center gap-2" href="order_list.php?do=Manage"><i class="bx bx-cart text-success"></i><span><?php echo $pendingOrders; ?> new order<?php echo $pendingOrders === 1 ? '' : 's'; ?> waiting for review</span></a><?php } ?>
										<?php if ($pendingInquiries > 0) { ?><a class="dropdown-item d-flex align-items-center gap-2" href="comments.php?do=Manage"><i class="bx bx-message-rounded-dots text-info"></i><span><?php echo $pendingInquiries; ?> new inquir<?php echo $pendingInquiries === 1 ? 'y' : 'ies'; ?> to review</span></a><?php } ?>
										<?php if ($lowStockProducts > 0) { ?><a class="dropdown-item d-flex align-items-center gap-2" href="products.php?do=Manage"><i class="bx bx-error text-warning"></i><span><?php echo $lowStockProducts; ?> product<?php echo $lowStockProducts === 1 ? '' : 's'; ?> low in stock</span></a><?php } ?>
										<?php if ($pendingProducts > 0) { ?><a class="dropdown-item d-flex align-items-center gap-2" href="products.php?do=Manage"><i class="bx bx-time-five text-warning"></i><span><?php echo $pendingProducts; ?> farmer product<?php echo $pendingProducts === 1 ? '' : 's'; ?> awaiting approval</span></a><?php } ?>
										<?php if ($notificationTotal === 0) { ?><div class="dropdown-item-text text-muted">No new notifications</div><?php } ?>
									</div>
								</div>
							</li>
							<li class="nav-item dropdown dropdown-large">
								
								<div class="dropdown-menu dropdown-menu-end">
									
									<div class="header-message-list">
									</div>
								</div>
							</li>
						</ul>
					</div>
					<div class="nav-item dropdown me-3" id="adminLanguageMenu">
						<a class="nav-link dropdown-toggle" href="#" role="button" aria-expanded="false" aria-controls="adminLanguageOptions"><?php echo t('Language'); ?>: <?php echo $currentLanguage === 'lg' ? 'Luganda' : 'English'; ?></a>
						<ul class="dropdown-menu dropdown-menu-end" id="adminLanguageOptions">
							<li><a class="dropdown-item" href="<?php echo language_url('en'); ?>">English</a></li>
							<li><a class="dropdown-item" href="<?php echo language_url('lg'); ?>">Luganda</a></li>
						</ul>
					</div>
					<div class="user-box dropdown">
						<a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						<?php 
							$sessionMail = $_SESSION['user_email'];

							$usersReadsql = "SELECT * FROM users WHERE user_email='$sessionMail' AND role=1 AND status=1 ORDER BY user_name ASC";
							$usersRead = mysqli_query($db, $usersReadsql);
							
							while ($row = mysqli_fetch_assoc($usersRead)) {
					  			$user_name 		= $row['user_name'];
					  			$user_email 	= $row['user_email'];
					  			$user_image 	= $row['user_image'];
								
								echo '<img src="assets/images/users/' . $user_image . '" style="width: 40px;">';
								?>

								<div class="user-info ps-3">
									<p class="user-name mb-0"><?php echo $user_name; ?></p>	
									
									<p class="designattion mb-0"><?php echo $_SESSION['user_email']; ?></p>
								</div>
							</a>
							<ul class="dropdown-menu dropdown-menu-end">
								
								<li><a class="dropdown-item" href="dashboard.php"><i class='bx bx-home-circle'></i><span>Dashboard</span></a>
								</li>
								<li><a class="dropdown-item" href="profile.php"><i class='bx bx-user'></i><span>My Profile</span></a>
								</li>
								<li>
									<div class="dropdown-divider mb-0"></div>
								</li>
								<li><a class="dropdown-item" href="logout.php"><i class='bx bx-log-out-circle'></i><span>Logout</span></a>
								</li>
							</ul>

								<?php
							}

						?>
					</div>
				</nav>
			</div>
		</header>
		<!--end header -->
