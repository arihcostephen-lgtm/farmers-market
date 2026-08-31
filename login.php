<?php include "inc/header.php"; ?>

	<div role="main" class="main public-auth-page">
		<!-- ########## START: TOP HEADING ########## -->
		<section class="page-header page-header-modern bg-color-light-scale-1 page-header-md"
		style="background-image: linear-gradient(to left, rgba(0,0,0,0.4), rgba(0,0,0,0.4)) ,url(assets/images/breadcrumb.jpg);
					background-repeat: no-repeat;
					background-size: cover;
					background-position: center;
					
					">
			<div class="container">
				<div class="row">
					<div class="col-md-12 align-self-center p-static order-2 text-center">

						<h1 class="text-white font-weight-bold text-8">Farmer & Customer Login</h1>
					</div>

					<div class="col-md-12 align-self-center order-1">

						<ul class="breadcrumb d-block text-center" >
							<li><a href="index.php" class="text-white">HOME</a></li>
							<li class="active text-white" >USER LOGIN</li>
						</ul>
					</div>					
				</div>
			</div>
		</section>
		<!-- ########## END: TOP HEADING ########## -->

		<section class="py-5">
        <div class="container">
          <div class="row pb-5">
			<div class="col-lg-6 offset-lg-3 public-auth-card" style="border-top: 4px solid #08c; padding: 29px 52px 39px; box-shadow: 1px 10px 15px #ccc; border-radius: 5px; background: #F7F7F7; font-size: 16px; color: #000;">

              <?php if (isset($_GET['status']) && $_GET['status'] === 'success') { ?>
                <div class="alert alert-success" role="alert">
                  Account created successfully. Please log in with your new credentials.
                </div>
							<?php } elseif (isset($_GET['status']) && $_GET['status'] === 'pending') { ?>
								<div class="alert alert-info" role="alert">
									Farmer registration submitted. Please wait for admin approval before logging in.
								</div>
              <?php } ?>

              <form action="" method="POST">
                <div class="mb-0">
                  <label for="exampleInputEmail1" class="form-label">Email address</label>
                </div>
                <div class="input-group form-group">
                  <input type="email" name="email" class="form-control" id="exampleInputEmail1" placeholder="enter your email..." aria-label="emailHelp" aria-describedby="basic-addon2" required autocomplete="off" value="">
                  <span class="input-group-text" id="basic-addon2"><i class="fa-solid fa-envelope"></i></span>
                </div>
            

                <div class="mb-0">
								  <label for="exampleInputPassword1" class="form-label">Password</label>
								</div>

								<div class="input-group form-group">
								  <input type="password" name="password" class="form-control" id="myInput" placeholder="enter your password..." required autocomplete="current-password" value="">
								  <span class="input-group-text" id="basic-addon2"><i class="fa-solid fa-lock"></i></span>
								</div>

								<div class="form-group form-check">
								  <input type="checkbox" class="form-check-input" id="exampleCheck1" onclick="myFunction()">
								  <label class="form-check-label" for="exampleCheck1">Show Password</label>
								</div>

								<script>
								function myFunction() {
								  var x = document.getElementById("myInput");
								  if (x.type === "password") {
								    x.type = "text";
								  } else {
								    x.type = "password";
								  }
								}

								</script>


                <div class="form-group">      
                <button type="submit" name="login_btn" class="btn btn-primary btn-lg btn-block">Log in</button>
                </div>

                <div class="form-group">
                	<i class="fa-regular fa-circle-question"></i> Not a Member? <a href="register.php">Signup Here</a>
                </div>
              </form>

              <?php  
								if (isset($_POST['login_btn'])) {
									$userEmail = mysqli_real_escape_string($db, $_POST['email']);
									$password = mysqli_real_escape_string($db, $_POST['password']);
									$hassedPass = sha1($password);

									$sql = "SELECT * FROM users WHERE user_email='$userEmail' LIMIT 1";
									$findData = mysqli_query($db, $sql);
									$row = mysqli_fetch_assoc($findData);

									if (!$row) { ?>
										<div class="alert alert-danger text-center" role="alert">
											Sorry! No user found in the system.
										</div>
									<?php }
									else {
										$_SESSION['user_id'] = (int) $row['user_id'];
										$_SESSION['user_name'] = $row['user_name'];
										$_SESSION['user_email'] = $row['user_email'];
										$_SESSION['user_phone'] = $row['user_phone'];
										$_SESSION['role'] = (int) $row['role'];
										$status = (int) $row['status'];

										if ($status !== 1 && $_SESSION['role'] !== 2) { ?>
											<div class="alert alert-warning text-center" role="alert">
												Your account is pending admin approval.
											</div>
										<?php } elseif ($_SESSION['role'] === 2 && $status !== 1) {
											header("Location: farmerDashboard.php?do=Home");
											exit;
										} elseif ($row['user_password'] !== $hassedPass) { ?>
											<div class="alert alert-danger text-center" role="alert">
												The password you entered is incorrect.
											</div>
										<?php }
										else {											// Transfer temp cart to database for customer
											if ($_SESSION['role'] === 3 && !empty($_SESSION['temp_cart'])) {
												foreach ($_SESSION['temp_cart'] as $tempProductId => $tempQuantity) {
													$tempProductId = (int) $tempProductId;
													$tempQuantity = max(1, (int) $tempQuantity);
													$checkProduct = $db->query("SELECT product_name, price, product_unit, stock_quantity, category_id FROM products WHERE product_id = $tempProductId AND status != 0 LIMIT 1");
													if ($checkProduct && $checkProduct->num_rows > 0) {
														$tempProduct = $checkProduct->fetch_assoc();
														if ($tempQuantity <= (int) $tempProduct['stock_quantity']) {
															$tempSubtotal = (float) $tempProduct['price'] * $tempQuantity;
															$tempProductName = mysqli_real_escape_string($db, $tempProduct['product_name']);
															$tempProductUnit = mysqli_real_escape_string($db, $tempProduct['product_unit'] ?? 'kilogram');
															$tempCategoryId = (int) ($tempProduct['category_id'] ?? 0);
															$tempTaxQuery = $db->query("SELECT rate_percent FROM tax_rules WHERE status = 1 AND min_quantity <= $tempQuantity AND (max_quantity IS NULL OR max_quantity >= $tempQuantity) AND (applies_to = 'all' OR applies_to = '$tempCategoryId') AND (applies_unit = 'all' OR applies_unit = '$tempProductUnit') ORDER BY (applies_to = '$tempCategoryId') DESC, (applies_unit = '$tempProductUnit') DESC, rate_percent DESC LIMIT 1");
															$tempTaxRate = $tempTaxQuery ? (float) ($tempTaxQuery->fetch_assoc()['rate_percent'] ?? 0) : 0;
															$tempTaxAmount = round($tempSubtotal * ($tempTaxRate / 100), 2);
															$tempTotalPrice = round($tempSubtotal + $tempTaxAmount, 2);
															$db->query("INSERT INTO order_list (user_id, user_phone, or_name, or_category, price, tax_amount, total_amount, quantity, order_unit, status, join_date) VALUES ('" . (int) $_SESSION['user_id'] . "', '" . mysqli_real_escape_string($db, $_SESSION['user_phone'] ?? '') . "', '$tempProductName', '$tempProductId', '$tempSubtotal', '$tempTaxAmount', '$tempTotalPrice', '$tempQuantity', '$tempProductUnit', 0, NOW())");
														}
													}
												}
												unset($_SESSION['temp_cart']);
											}
											if ($_SESSION['role'] === 1) {
												header("Location: admin/dashboard.php");
											} elseif ($_SESSION['role'] === 2) {
												header("Location: farmerDashboard.php?do=Home");
											} elseif ($_SESSION['role'] === 4) {
												header("Location: manager/dashboard.php");
											} elseif ($_SESSION['role'] === 5) {
												header("Location: supervisor/dashboard.php");
											} else {
												header("Location: customerDashboard.php");
											}
											exit;
										}
									}
								}
							?>
            </div>
          </div>
        </div>
      </section>
	</div>

<?php 
	include "inc/footer.php";
?>
