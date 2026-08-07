<?php 
	include "inc/header.php";
?>

			<div role="main" class="main">

				<section class="page-header page-header-modern b`g-color-light-scale-1 page-header-md">
					<div class="container">
						<div class="row">

							<div class="col-md-12 align-self-center p-static order-2 text-center">

								<h1 class="text-dark font-weight-bold text-8">Regsiter New User</h1>
							</div>

							<div class="col-md-12 align-self-center order-1">

								<ul class="breadcrumb d-block text-center">
									<li><a href="index.php">Home</a></li>
									<li class="active">register</li>
								</ul>
							</div>
						</div>
					</div>
				</section>

				<section>
					<div class="container py-5">
						<div class="row pb-5">
							<div class="col-lg-12">
								<!-- ########## START: MAIN BODY ########## -->
								<div class="card">
									<div class="card-body" style="box-shadow: 1px 10px 15px #ccc; border-top: 4px solid #08c;; border-radius: 5px; color: #000; background: #F7F7F7; font-size: 16px;">
 
										<form action="" method="POST" enctype="multipart/form-data">
											<div class="row">
												<div class="col-lg-4">
													<div class="form-group">
														<label for="">Full Name</label>
														<input type="text" name="fullname" class="form-control" required autocomplete="off" autofocus placeholder="full name..">
													</div>

													<div class="form-group">
														<label for="">Email Address</label>
														<input type="email" name="email" class="form-control" required autocomplete="off" autofocus placeholder="email address..">
													</div>

													<div class="form-group">
														<label for="">Password</label>
														<input type="password" name="password" class="form-control" required autocomplete="off" autofocus placeholder="password..">
													</div>

													<div class="form-group">
														<label for="">Re-type Password</label>
														<input type="password" name="re_password" class="form-control" required autocomplete="off" autofocus placeholder="re-type password..">
													</div>
												</div>

												<div class="col-lg-4">
													<div class="form-group">
														<label for="">Phone No.</label>
														<input type="tel" name="phone" class="form-control" required autocomplete="off" autofocus  placeholder="phone no..">
													</div>

													<div class="form-group">
														<label for="">Address</label>
														<textarea name="address" class="form-control" autocomplete="off" autofocus cols="30" rows="7"  placeholder="address.."></textarea>
													</div>

													
												</div>

												<div class="col-lg-4">

													<div class="mb-3">
														<label for="role">Account Type</label>
														<select name="role" id="role" class="form-select" required>
												<option value="3" selected>Customer</option>
												<option value="2">Farmer (pending admin approval)</option>
													<div class="form-group">
														<label for="">Image</label>
														<input type="file" name="image" class="form-control-file" >
													</div>

													<div class="form-group">
														<input type="submit" name="addUser" class="btn btn-primary btn-lg btn-block">
													</div>
												</div>
											</div>
										</form>

										<?php  
											$registrationMessage = '';
											$registrationType = 'info';

											if (isset($_POST['addUser'])) {
												$fullname 		= mysqli_real_escape_string($db, $_POST['fullname']);
												$email 			= mysqli_real_escape_string($db, $_POST['email']);
												$password 		= mysqli_real_escape_string($db, $_POST['password']);
												$re_password 	= mysqli_real_escape_string($db, $_POST['re_password']);
												$phone 			= mysqli_real_escape_string($db, $_POST['phone']);
												$address 		= mysqli_real_escape_string($db, $_POST['address']);
												$role 			= (int) $_POST['role'];
												$status 		= ($role == 2) ? 2 : 1;

												$image 			= mysqli_real_escape_string($db, $_FILES['image']['name']);
												$temp_image 	= $_FILES['image']['tmp_name'];

												if ($password != $re_password) {
													$registrationMessage = 'Please enter the same password in both fields.';
													$registrationType = 'warning';
												} else {
													$checkUserSql = "SELECT user_id FROM users WHERE user_email='$email' LIMIT 1";
													$checkUserQuery = mysqli_query($db, $checkUserSql);

													if (mysqli_num_rows($checkUserQuery) > 0) {
														$registrationMessage = 'An account with this email already exists. Please use another email.';
														$registrationType = 'danger';
													} else {
														$hassedPass = sha1($password);

														if (!empty($image)) {
															$img = rand(1, 9999999). "-" . $image;
															move_uploaded_file($temp_image, 'admin/assets/images/users/' . $img);
														} else {
															$img = '';
														}
														
														$addUserSql = "INSERT INTO users (user_name, user_email, user_password, user_phone, user_address, role, status, user_image, join_date) VALUES ('$fullname', '$email', '$hassedPass', '$phone', '$address', '$role', '$status', '$img', now())";
														$addUserQuery = mysqli_query($db, $addUserSql);

														if ($addUserQuery) {
															header("Location: login.php?status=success");
															exit;
														} else {
															$registrationMessage = 'Registration failed. Please try again.';
															$registrationType = 'danger';
														}
													}
												}
											}
										?>

										<?php if (!empty($registrationMessage)) { ?>
											<div class="alert alert-<?php echo htmlspecialchars($registrationType); ?>" role="alert">
												<?php echo htmlspecialchars($registrationMessage); ?>
											</div>
										<?php } ?>

									</div>
								</div>				
								<!-- ########## END: MAIN BODY ########## -->
							</div>
						</div>
					</div>
				</section>
			</div>
<?php 
	include "inc/footer.php";
?>