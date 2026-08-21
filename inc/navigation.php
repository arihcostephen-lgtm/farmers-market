<div class="header-row">
	<div class="header-nav pt-1">
		<div class="header-nav-main header-nav-main-effect-1 header-nav-main-sub-effect-1">
			<nav class="collapse">
				<ul class="nav nav-pills" id="mainNav">
					<li class="">
						<a class="dropdown-item dropdown-toggle active" href="index.php">
							<?php echo t('Home'); ?>
						</a>
					</li>

					<li class="">
						<a class="dropdown-item dropdown-toggle" href="aboutUs.php">
							<?php echo t('About Us'); ?>
						</a>
					</li>

					<li class="">
						<a class="dropdown-item dropdown-toggle" href="blog.php">
							<?php echo t('Blog'); ?>
						</a>
					</li>

					<!-- For users login or nor -->
					<?php  
						if (!empty($_SESSION['user_id'])) { 

							$user_id = $_SESSION['user_id'];
							$readUId_Sql = "SELECT * FROM users WHERE status=1 AND user_id='$user_id'";
							$readUId_Query = mysqli_query($db, $readUId_Sql);

							while( $row = mysqli_fetch_assoc($readUId_Query) ) {
								$user_id 				= $row['user_id'];
								$fullname 				= $row['user_name'];
								$_SESSION['email'] 		= $row['user_email'];
								$password 				= $row['user_password'];
								$role 					= $row['role'];
								$status 				= $row['status'];
								$user_image 			= $row['user_image'];
								?>
									<li class="dropdown">
										<a class="dropdown-item dropdown-toggle" href="">
											<div class="d-flex ">
												<div>
													<?php  
											      		if (!empty($user_image)) {
															echo '<img src="admin/assets/images/users/' . $user_image . '" style="width: 38px;margin: 0px 10px;">';
														}
														else {
															echo '<img src="admin/assets/images/users/default.png" style="width: 50px;margin: 0px 10px;">';
														}
											      	?>
												</div>
												<div>
													<?php echo $fullname; ?>
												</div>
											</div>
											
										</a>
										<ul class="dropdown-menu">
											<li><a class="dropdown-item" href="user_manage.php?uid=<?php echo $_SESSION['user_id']; ?>"><?php echo t('Profile Update'); ?></a></li>
											<li><a class="dropdown-item" href="order_history.php"><?php echo t('Order List'); ?></a></li>
											<li><a class="dropdown-item" href="logout.php"><?php echo t('Log Out'); ?></a></li>
										</ul>
									</li>

																										
								<?php
							}

							?>
							
						<?php }

						else { ?>
							<li class="dropdown">
								<a class="dropdown-item dropdown-toggle" href="login.php">
									<i class="fa-solid fa-arrow-right-to-bracket px-1"></i> <?php echo t('Login'); ?>
								</a>
							</li>

							<li class="dropdown">
								<a class="dropdown-item dropdown-toggle" href="register.php">
									<i class="fa-regular fa-address-card px-1"></i> <?php echo t('Register'); ?>
								</a>
							</li>

							<li class="">
								<a class="dropdown-item dropdown-toggle" href="seller.php">
									<i class="fa-solid fa-wheat-awn px-1"></i> <?php echo t('Farmer Account'); ?>
								</a>
							</li>

							<li class="">
								<a class="dropdown-item dropdown-toggle" href="admin/index.php">
									<i class="fa-solid fa-user-shield px-1"></i> <?php echo t('Admin Login'); ?>
								</a>
							</li>
							<?php  

							?>

						<?php }
					?>
					<!-- For users login or nor -->
					<li class="dropdown">
						<a class="dropdown-item dropdown-toggle" href="#"><?php echo t('Language'); ?>: <?php echo $currentLanguage === 'lg' ? t('Luganda') : t('English'); ?></a>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="<?php echo language_url('en'); ?>"><?php echo t('English'); ?></a></li>
							<li><a class="dropdown-item" href="<?php echo language_url('lg'); ?>"><?php echo t('Luganda'); ?></a></li>
						</ul>
					</li>

					
				</ul>
			</nav>
		</div>
		
		<button class="btn header-btn-collapse-nav" data-toggle="collapse" data-target=".header-nav-main nav">
			<i class="fas fa-bars"></i>
		</button>
	</div>
</div>

