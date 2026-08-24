<?php 
	include "inc/header.php";
?>

			<div role="main" class="main public-auth-page">

				<section class="page-header page-header-modern bg-color-light-scale-1 page-header-md">
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
								<div class="card public-auth-card">
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
														<input type="password" name="password" class="form-control" data-password="new" required autocomplete="new-password" autofocus placeholder="password..">
													</div>

													<div class="form-group">
														<label for="">Re-type Password</label>
														<input type="password" name="re_password" class="form-control" data-password="confirmation" required autocomplete="new-password" autofocus placeholder="re-type password..">
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

																	<div id="farmerDetails" class="d-none">
																			<div class="form-group">
																				<label for="farmName">Farm name</label>
																				<input type="text" name="farm_name" id="farmName" class="form-control" placeholder="Name of your farm">
																			</div>
																		<div class="form-group">
																			<label for="farmLocation">Farm location</label>
																			<textarea name="farm_location" id="farmLocation" class="form-control" rows="3" placeholder="Village, district, landmark or GPS details"></textarea>
																		</div>
																		<div class="form-group">
																			<label for="farmDocument">Farm document (optional)</label>
																			<input type="file" name="farm_document" id="farmDocument" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.txt">
																		</div>
																	</div>

													
												</div>

												<div class="col-lg-4">

													<div class="mb-3">
														<label for="role">Account Type</label>
														<select name="role" id="role" class="form-select" required>
												<option value="3" selected>Customer</option>
																		<option value="2">Farmer (pending admin approval)</option>
																	</select>
																	<script>
																		const accountType = document.getElementById('role');
																		const farmerDetails = document.getElementById('farmerDetails');
																		const farmLocation = document.getElementById('farmLocation');
																		function toggleFarmerDetails() {
																			const isFarmer = accountType.value === '2';
																			farmerDetails.classList.toggle('d-none', !isFarmer);
																			farmLocation.required = isFarmer;
																		}
																		accountType.addEventListener('change', toggleFarmerDetails);
																		toggleFarmerDetails();
																	</script>
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
																							$farmLocation = trim($_POST['farm_location'] ?? '');
																							$farmName = trim($_POST['farm_name'] ?? '');
																							$farmNameEscaped = mysqli_real_escape_string($db, $farmName);
																							$farmLocationEscaped = mysqli_real_escape_string($db, $farmLocation);
												$role 			= (int) $_POST['role'];
												$status 		= ($role == 2) ? 2 : 1;
																							$documentValid = $role !== 2 || $farmLocation !== '';
																							$documentValid = $documentValid && ($role !== 2 || $farmName !== '');
																							if (!$documentValid) {
																								$registrationMessage = $farmName === '' ? 'Farm name is required for farmer registration.' : 'Farm location is required for farmer registration.';
																								$registrationType = 'warning';
																							}

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

																												$farmDocument = '';
																												if ($role === 2 && !empty($_FILES['farm_document']['name'])) {
																													$allowedDocumentExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'doc', 'docx', 'txt'];
																													$documentExtension = strtolower(pathinfo($_FILES['farm_document']['name'], PATHINFO_EXTENSION));
																													if ($_FILES['farm_document']['error'] !== UPLOAD_ERR_OK || !in_array($documentExtension, $allowedDocumentExtensions, true) || $_FILES['farm_document']['size'] > 10 * 1024 * 1024) {
																														$documentValid = false;
																														$registrationMessage = 'The farm document must be a supported file up to 10 MB.';
																														$registrationType = 'warning';
																													} else {
																														$documentName = bin2hex(random_bytes(8)) . '.' . $documentExtension;
																														$documentDirectory = __DIR__ . '/uploads/docs/' . md5($_POST['email']) . '/';
																														if (!is_dir($documentDirectory)) {
																																mkdir($documentDirectory, 0755, true);
																																}
																														if (move_uploaded_file($_FILES['farm_document']['tmp_name'], $documentDirectory . $documentName)) {
																															$farmDocument = 'uploads/docs/' . md5($_POST['email']) . '/' . $documentName;
																													}
																													}
																												}
														
																												$addUserSql = "INSERT INTO users (user_name, user_email, user_password, user_phone, user_address, role, status, user_image, join_date) VALUES ('$fullname', '$email', '$hassedPass', '$phone', '$address', '$role', '$status', '$img', now())";
																												$addUserQuery = $documentValid ? mysqli_query($db, $addUserSql) : false;

														if ($addUserQuery) {
																													if ($role === 2) {
																														mysqli_query($db, "INSERT INTO farmer (farm_name, farm_phone, farm_email, farm_address, farm_document, status, join_date) VALUES ('$farmNameEscaped', '$phone', '$email', '$farmLocationEscaped', '" . mysqli_real_escape_string($db, $farmDocument) . "', 1, NOW())");
																													}
															header("Location: login.php?status=success");
															exit;
														} else {
																												if ($registrationMessage === '') {
																													$registrationMessage = 'Registration failed. Please try again.';
																												}
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