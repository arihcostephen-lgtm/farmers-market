<?php include "inc/header.php"; ?>

	<div role="main" class="main">

		<!-- START: HERO SECTION -->
		<section class="py-5" style="background: linear-gradient(135deg, #f7fdf4 0%, #eef8eb 100%);">
			<div class="container py-4">
				<div class="row align-items-center g-5">
					<div class="col-lg-7">
						<span class="badge rounded-pill bg-success-subtle text-success px-3 py-2 mb-3">Trusted marketplace for farmers and customers</span>
						<h1 class="display-4 fw-bold text-dark mb-3">From farm animals to fresh produce, everything is available in one place.</h1>
						<p class="lead text-muted mb-4">Discover livestock, dairy, eggs, grains, vegetables, fruits, and farm essentials through a modern and centralized platform built for the Local Farm Market.</p>
						<div class="d-flex flex-wrap gap-3 mb-4">
							<a href="register.php" class="btn btn-success btn-lg px-4">Register as Farmer</a>
							<a href="login.php" class="btn btn-outline-dark btn-lg px-4">Shop as Customer</a>
						</div>
						<div class="row g-3">
							<div class="col-sm-4">
								<div class="card border-0 shadow-sm h-100">
									<div class="card-body">
										<h5 class="fw-bold mb-1">Live Listings</h5>
										<p class="text-muted mb-0">Fresh products from trusted farms</p>
									</div>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="card border-0 shadow-sm h-100">
									<div class="card-body">
										<h5 class="fw-bold mb-1">Quick Orders</h5>
										<p class="text-muted mb-0">Order in minutes for pickup or delivery</p>
									</div>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="card border-0 shadow-sm h-100">
									<div class="card-body">
										<h5 class="fw-bold mb-1">Verified Farmers</h5>
										<p class="text-muted mb-0">Each farm is reviewed by the admin</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-5">
						<div class="card border-0 shadow-lg overflow-hidden">
							<img src="assets/images/slide/2.jpeg" class="img-fluid" alt="Farm market showcase">
							<div class="card-body">
								<h5 class="fw-bold mb-2">Your one-stop farm marketplace</h5>
								<p class="text-muted mb-0">Support local producers, browse new products, and connect farmers with customers across a growing community.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- END: HERO SECTION -->

		<!-- START: WELCOME PART -->
		<section>
			<?php  
				$aboutSql = "SELECT * FROM about WHERE status=1 ORDER BY title ASC";
		  		$aboutQuery = mysqli_query( $db, $aboutSql );

			  		while ($row = mysqli_fetch_assoc($aboutQuery)) {
			  			$id  		= $row['id'];
			  			$title 		= $row['title'];
			  			$descrive 	= $row['descrive'];
			  			$year 		= $row['year'];
			  			$total_age 	= $row['total_age'];
			  			$a_image 	= $row['a_image'];
			  			$status 	= $row['status'];
			  			?>
			  			<div class="conatiner py-3">
							<div class="row text-center pt-3 d-flex align-items-center">
								<div class="col-md-10 mx-md-auto">	
									<div class="d-flex align-items-center justify-content-center">
										<h1 class="year pt-3 px-4">Since <span><?php echo $year; ?></span></h1>	
										<img src="assets/images/icon.png" alt="">
									</div>
									
									<h1 class="welcome">Welcome to <span><?php echo $title; ?></span></h1>
									<p class="text-dark font-weight-normal appear-animation" data-appear-animation="fadeInUpShorter" data-appear-animation-delay="300" style="font-size: 17px;"><?php echo $descrive; ?></p>
								</div>
							</div>
						</div>
			  		<?php }
			?>
			
		</section>
		
		<!-- END: WELCOME PART -->

<!-- START: PRODUCT RANGE -->
		<section class="py-5 bg-light" id="service">
			<div class="container">
				<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
					<div>
						<h2 class="fw-bold mb-2">Explore the full product range</h2>
						<p class="text-muted mb-0">Browse categories across livestock, dairy, crops, and packaged farm goods.</p>
					</div>
					<a href="details.php?do=Manage" class="btn btn-outline-success mt-3 mt-md-0">View all products</a>
				</div>
				<div class="row g-4">
					<?php
						$catSql = "SELECT * FROM category WHERE status=1 ORDER BY cat_name ASC LIMIT 6";
						$catQuery = mysqli_query($db, $catSql);
						while ($row = mysqli_fetch_assoc($catQuery)) {
							$cat_id = $row['cat_id'];
							$cat_name = $row['cat_name'];
							$cat_desc = $row['cat_desc'];
							$cat_image = $row['cat_image'];
							?>
						<div class="col-md-6 col-lg-4">
							<div class="card border-0 shadow-sm h-100">
								<?php if (!empty($cat_image)) { echo '<img src="admin/assets/images/category/' . $cat_image . '" class="card-img-top" style="height: 220px; object-fit: cover;">'; } ?>
								<div class="card-body">
									<h5 class="fw-bold mb-2"><a href="details.php?do=Manage&did=<?php echo $cat_id; ?>" class="text-dark text-decoration-none"><?php echo $cat_name; ?></a></h5>
									<p class="text-muted mb-2"><?php echo $cat_desc ?: 'Fresh farm product now available for ordering.'; ?></p>
									<a href="details.php?do=Manage&did=<?php echo $cat_id; ?>" class="btn btn-sm btn-outline-success">View</a>
								</div>
							</div>
						</div>
						<?php }
					?>
				</div>
			</div>
		</section>
		<!-- END: PRODUCT RANGE -->

		<!-- START: WHY CHOOSE US -->
		<section class="py-5">
			<div class="container">
				<div class="row g-4 align-items-center">
					<div class="col-lg-6">
						<h2 class="fw-bold mb-3">A centralized marketplace built for modern farming</h2>
						<p class="text-muted lead">From verified sellers to quick orders, the Local Farm Market helps farmers and customers connect effortlessly.</p>
						<div class="row g-3 mt-2">
							<div class="col-sm-6">
								<div class="border rounded p-3 bg-white shadow-sm">
									<h6 class="fw-bold mb-1">Verified farmers</h6>
									<p class="text-muted mb-0">Admin-approved accounts for trusted sellers.</p>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="border rounded p-3 bg-white shadow-sm">
									<h6 class="fw-bold mb-1">Simple orders</h6>
									<p class="text-muted mb-0">Customers can place orders directly online.</p>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="row g-3">
							<div class="col-6">
								<div class="bg-success text-white rounded p-4 h-100">
									<h3 class="fw-bold mb-1"><?php echo mysqli_num_rows(mysqli_query($db, "SELECT cat_id FROM category WHERE status=1")); ?></h3>
									<p class="mb-0">Active categories</p>
								</div>
							</div>
							<div class="col-6">
								<div class="bg-dark text-white rounded p-4 h-100">
									<h3 class="fw-bold mb-1"><?php echo mysqli_num_rows(mysqli_query($db, "SELECT farm_id FROM farmer WHERE status=1")); ?></h3>
									<p class="mb-0">Registered farmers</p>
								</div>
							</div>
							<div class="col-6">
								<div class="border rounded p-4 h-100 bg-light">
									<h3 class="fw-bold mb-1"><?php echo mysqli_num_rows(mysqli_query($db, "SELECT post_id FROM post WHERE status=1")); ?></h3>
									<p class="mb-0">Latest posts</p>
								</div>
							</div>
							<div class="col-6">
								<div class="border rounded p-4 h-100 bg-light">
									<h3 class="fw-bold mb-1"><?php echo mysqli_num_rows(mysqli_query($db, "SELECT or_id FROM order_list")); ?></h3>
									<p class="mb-0">Orders tracked</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- END: WHY CHOOSE US -->

		
		<!-- START: ANIMATION PART -->
		<section class="pb-3">
			<div class="appear-animation" data-appear-animation="fadeInUpShorter" data-appear-animation-delay="200">
				<div class="home-concept mt-5">
					<div class="container">
			
						<div class="row text-center">
							<span class="sun"></span>
							<span class="cloud"></span>
							<div class="col-lg-4 ml-lg-auto">
								<div class="process-image">
									<img src="assets/images/guarantee_icon.png" alt="" />
									<strong>100% Satisfaction Guarantee</strong>
								</div>
							</div>
							<div class="col-lg-4">
								<div class="process-image process-image-on-middle">
									<img src="assets/images/shiping_icon1.png" alt="" />
									<strong>Free Shipping</strong>
								</div>
							</div>
							<div class="col-lg-4">
								<div class="process-image">
									<img src="assets/images/discount_icon.png" alt="" />
									<strong>Daily Discount</strong>
								</div>
							</div>
						</div>
			
					</div>
				</div>
			</div>
		</section>
		<!-- END: ANIMATION PART -->

		<!-- START: SHOWCASE PART -->
		<section class="showcase_part">
			<div class="container">
				<div class="row text-center text-md-left mt-4">
					<?php  
						$markSql = "SELECT * FROM marketing WHERE status=1 ORDER BY title ASC";
				  		$markQuery = mysqli_query( $db, $markSql );

					  		while ($row = mysqli_fetch_assoc($markQuery)) {
					  			$m_id  		= $row['m_id'];
					  			$title 		= $row['title'];
					  			$descrive 	= $row['descrive'];
					  			$m_image 	= $row['m_image'];
					  			$status 	= $row['status'];
					  			$join_date 	= $row['join_date'];
					  			?>

					  			<div class="col-md-6 mb-6 mb-md-0 appear-animation animated fadeInLeftShorter appear-animation-visible" data-appear-animation="fadeInLeftShorter" data-appear-animation-delay="200" style="animation-delay: 200ms;">
									<div class="row d-flex justify-content-center justify-content-md-start align-items-center">
										<div class="col-7">

											<?php  
												if (!empty($m_image)) {
													echo '<img src="admin/assets/images/marketing/' . $m_image . '" style="width: 100%; border-radius: 5px;"; >';
												}
											?>
										</div>
										<div class="col-lg-5">
											<h2 class="font-weight-bold text-5 line-height-5 mb-1"><?php echo $title;  ?></h2>
											<p class="text-dark font-weight-normal appear-animation" data-appear-animation="fadeInUpShorter" data-appear-animation-delay="300" style="font-size: 16px; margin: 0;"><?php echo substr($descrive, 0, 50); ?>...read more</p>
										</div>
									</div>
								</div>

					  			<?php
					  		}
					?>
					


				</div>
			</div>
		</section>
		<!-- END: SHOWCASE PART -->

		<!-- START: FARMER PART -->
		<section class="py-5 bg-light">
			<div class="container">
				<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
					<div>
						<h2 class="fw-bold mb-2">Meet trusted farmers</h2>
						<p class="text-muted mb-0">Our farmers bring quality produce and animals to the marketplace.</p>
					</div>
					<a href="farmers.php" class="btn btn-outline-success mt-3 mt-md-0">See all farmers</a>
				</div>
				<div class="row g-4">
					<?php
						$farmerSql = "SELECT * FROM farmer WHERE status=1 LIMIT 4";
						$farmerQuery = mysqli_query($db, $farmerSql);
						while ($row = mysqli_fetch_assoc($farmerQuery)) {
							$farm_id = $row['farm_id'];
							$farm_name = $row['farm_name'];
							$farm_about = $row['farm_about'];
							$farm_image = $row['farm_image'];
							?>
						<div class="col-md-6 col-lg-3">
							<div class="card border-0 shadow-sm h-100 text-center">
								<?php if (!empty($farm_image)) { echo '<img src="admin/assets/images/farmer/' . $farm_image . '" class="card-img-top" style="height: 220px; object-fit: cover;">'; } ?>
								<div class="card-body">
									<h5 class="fw-bold"><a href="farmers.php?fid=<?php echo $farm_id; ?>" class="text-dark text-decoration-none"><?php echo $farm_name; ?></a></h5>
									<p class="text-muted small mb-0"><?php echo substr($farm_about, 0, 90); ?>...</p>
								</div>
							</div>
						</div>
						<?php }
					?>
				</div>
			</div>
		</section>

		<!-- START: IMAGE SHOWCASE PART -->
		<section class="image_show py-5">
			<div class="conatiner py-3">
				<div class="row text-center pt-3 d-flex align-items-center">
					<div class="col-md-10 mx-md-auto">							
						<h1 class="welcomes">Our Gallary</h1>
					</div>
				</div>
			</div>

			<div class="container">
				<div class="row">
					<?php  
						$overviewSql = "SELECT * FROM farm_overview WHERE ov_category=1 AND status=1 ORDER BY title ASC";
				  		$overviewQuery = mysqli_query( $db, $overviewSql );
					  		while ($row = mysqli_fetch_assoc($overviewQuery)) {
					  			$ov_id  		= $row['ov_id'];
					  			$title 			= $row['title'];
					  			$descrive 		= $row['descrive'];
					  			$ov_category 	= $row['ov_category'];
					  			$ov_image 		= $row['ov_image'];
					  			$status 		= $row['status'];
					  			?>
					  				<div class="col-lg-3 py-3">
										<span class="thumb-info thumb-info-no-borders thumb-info-no-borders-rounded thumb-info-lighten thumb-info-bottom-info thumb-info-bottom-info-dark thumb-info-bottom-info-dark-linear thumb-info-centered-icons">
											<span class="thumb-info-wrapper">
												<?php  
													if (!empty($ov_image)) { 
														echo '<img src="admin/assets/images/overview_img/' . $ov_image . '" style="width: 100%";>'; 
														?>
														<span class="thumb-info-title">
															<span class="thumb-info-inner line-height-1 galery"><?php echo $title; ?></span>
															<span class="thumb-info-type galerys"><?php echo substr($descrive, 0, 30); ?>...</span>
														</span>

													<?php }
												?>
												
											</span>
										</span>
									</div>
					  			<?php
					  		}
					?>
					
				</div>
			</div>
		</section>
		<!-- END: IMAGE SHOWCASE PART -->

		<!-- START: BLOG PART -->
		<section class="py-5" id="blog">
			<div class="container">
				<div class="row g-4">
					<div class="col-lg-8">
						<div class="card border-0 shadow-sm h-100">
							<div class="card-body">
								<h2 class="fw-bold mb-4">Latest farm stories</h2>
								<div class="row g-3">
									<?php
									$sql = "SELECT * FROM post WHERE status=1 ORDER BY post_id DESC LIMIT 3";
									$postData = mysqli_query($db, $sql);
									while ($row = mysqli_fetch_assoc($postData)) {
										$post_id = $row['post_id'];
										$title = $row['title'];
										$post_desc = $row['post_desc'];
										$image = $row['image'];
										?>
										<div class="col-md-4">
											<div class="border rounded p-3 h-100">
												<?php if (!empty($image)) { echo '<img src="admin/assets/images/posts/' . $image . '" class="img-fluid rounded mb-3" style="height: 140px; object-fit: cover; width: 100%;">'; } ?>
											<h6 class="fw-bold"><a href="blog_details.php?dId=<?php echo $post_id; ?>" class="text-dark text-decoration-none"><?php echo $title; ?></a></h6>
											<p class="text-muted small mb-0"><?php echo substr($post_desc, 0, 90); ?>...</p>
											</div>
										</div>
										<?php }
									?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="card border-0 shadow-sm h-100">
							<div class="card-body">
								<h3 class="fw-bold mb-3">Talk to our team</h3>
								<p class="text-muted">Need help listing products or placing an order? We are ready to help.</p>
								<a href="login.php" class="btn btn-dark">Get started</a>
								<hr>
								<?php
									$userSql = "SELECT * FROM users WHERE role=1 AND status=1 ORDER BY user_id ASC LIMIT 1";
									$userQuery = mysqli_query($db, $userSql);
									while ($row = mysqli_fetch_assoc($userQuery)) {
										$user_phone = $row['user_phone'];
										$user_email = $row['user_email'];
										?>
									<p class="mb-1"><strong>Phone:</strong> <a href="tel:+<?php echo $user_phone; ?>" class="text-success text-decoration-none"><?php echo $user_phone; ?></a></p>
									<p class="mb-0"><strong>Email:</strong> <a href="mailto:<?php echo $user_email; ?>" class="text-success text-decoration-none"><?php echo $user_email; ?></a></p>
									<?php }
								?>
							</div>
						</div>
			</div>
		</section>
		<!-- END: BLOG PART -->

		<!-- START: CONTACT FORM -->
		<section class="contact-form py-5 bg-dark text-light" id="contact">
			<div class="container">
				<div class="row g-5 align-items-center">
					<div class="col-lg-6">
						<h2 class="fw-bold mb-3">Contact the Local Farm Market team</h2>
						<p class="text-light opacity-75">Share your questions about products, verification, or orders and we will respond quickly.</p>
						<div class="card border-0 bg-white text-dark mt-4">
							<div class="card-body">
								<?php
									$aboutSql = "SELECT * FROM about WHERE status=1 ORDER BY title ASC";
									$aboutQuery = mysqli_query($db, $aboutSql);
									while ($row = mysqli_fetch_assoc($aboutQuery)) {
										$title = $row['title'];
										$descrive = $row['descrive'];
										?>
									<p class="mb-0"><strong><?php echo $title; ?></strong><br><?php echo substr($descrive, 0, 180); ?></p>
									<?php }
								?>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="card border-0 shadow-sm">
							<div class="card-body">
								<?php
									if (isset($_SESSION['msg'])) {
										$message = $_SESSION['msg'];
										unset($_SESSION['msg']);
										?>
									<div class="alert alert-info text-center" role="alert"><?php echo $message; ?></div>
									<?php }
								?>
								<form action="" method="POST" enctype="multipart/form-data">
									<div class="mb-3">
										<label for="subject" class="form-label">Subject</label>
										<input type="text" name="title" class="form-control" id="subject" placeholder="subject.." required autocomplete="off">
									</div>
									<div class="mb-3">
										<label for="message" class="form-label">Message</label>
										<textarea name="message" class="form-control" id="message" rows="5" placeholder="message" required autocomplete="off"></textarea>
									</div>
									<?php if (empty($_SESSION['user_id'])) { ?>
										<a href="login.php" class="text-success">Login to reserve your service</a>
									<?php } else { ?>
										<input type="hidden" name="status" value="1">
										<input type="hidden" name="useremail" value="<?php echo $_SESSION['user_email'] ?? $_SESSION['email'] ?? ''; ?>">
										<input type="hidden" name="userphone" value="<?php echo $_SESSION['user_phone'] ?? $_SESSION['phone'] ?? ''; ?>">
										<input type="submit" name="addUser" class="btn btn-success btn-lg btn-block" value="Send message">
									<?php } ?>
								</form>
								<?php
									if (isset($_POST['addUser'])) {
										$title = mysqli_real_escape_string($db, $_POST['title']);
										$message = mysqli_real_escape_string($db, $_POST['message']);
										$status = mysqli_real_escape_string($db, $_POST['status']);
										$useremail = mysqli_real_escape_string($db, $_POST['useremail']);
										$userphone = mysqli_real_escape_string($db, $_POST['userphone']);
										$sql = "INSERT INTO comments (user_id, user_number, subject, comments, status, cmt_date) VALUES('$useremail', '$userphone', '$title', '$message', '$status', now())";
										$query = mysqli_query($db, $sql);
										if ($query) {
											farmers_market_notify_admin_support($db, $_POST['useremail'], $_POST['userphone'], $_POST['title'], $_POST['message']);
											$_SESSION['msg'] = "We received your message. We will follow up soon.";
											header("Location: index.php");
										} else {
											die("Mysql Error." . mysqli_error($db));
										}
									}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<!-- END: CONTACT FORM -->

		<!-- whats app linking part -->
		<div id="myDiv" class="py-5"></div>
		<!-- whats app linking part -->
		
	</div>
 
<?php include "inc/footer.php"; ?>