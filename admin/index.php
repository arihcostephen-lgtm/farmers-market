<?php  
	session_start();
	ob_start();
	include "inc/db.php";

	if (!empty($_SESSION['user_id']) || !empty($_SESSION['user_email'])) {
		header("Location: dashboard.php");
	}

	if (isset($db)) {
		$checkAdminSql = "SELECT user_id FROM users WHERE role = 1 LIMIT 1";
		$checkAdminQuery = mysqli_query($db, $checkAdminSql);

		if ($checkAdminQuery && mysqli_num_rows($checkAdminQuery) == 0) {
			$defaultPassword = sha1('12345');
			$insertAdminSql = "INSERT INTO users (user_name, user_email, user_password, user_phone, user_address, role, status) VALUES ('Admin', 'admin@gmail.com', '$defaultPassword', '0000000000', 'Admin Address', 1, 1)";
			mysqli_query($db, $insertAdminSql);
		}
	}
?>
<?php include 'inc/login_header.php'; ?>
		<div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
					<div class="col mx-auto">
					
						<div class="card shadow-none" style="width: 50% !important; margin: 0px auto; ">
							<div class="card-body">
								<div class="border p-4 rounded">
									<div class="text-center mb-4">
										<h3 class="">Sign in</h3>
										<p class="mb-0">Login to your account</p>
									</div>
									<div class="login-separater text-center mb-4"> <span>OR SIGN IN WITH EMAIL</span>
										<hr/>
									</div>
									<div class="form-body">

										<!-- ########## START: FORM ########## -->
										<form action="" method="POST" class="row g-4">
											<div class="col-12">
												<label for="inputEmailAddress" class="form-label">Email Address</label>
									<input type="email" name="email" class="form-control" id="inputEmailAddress" placeholder="Email Address" required autocomplete="off">

											<div class="col-12">
												<label for="inputChoosePassword" class="form-label">Enter Password</label>
												<div class="input-group" id="show_hide_password">
													<input type="password" name="password" class="form-control border-end-0" id="inputChoosePassword" placeholder="Enter Password" required autocomplete="off" value="12345"> 
													<a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
												</div>
											</div>

											<div class="col-12">
												<div class="d-grid">
													<input type="submit" name="adminSubmit" class="btn btn-primary" value="Sign in">
												</div>
											</div>
										</form>
										<!-- ########## END: FORM ########## -->

										<?php  
											if (isset($_POST['adminSubmit'])) {
												$email 		= mysqli_real_escape_string($db, $_POST['email']);
												$password 	= mysqli_real_escape_string($db, $_POST['password']);
												$hassedPass = sha1($password);

												$readSql = "SELECT * FROM users WHERE user_email='$email' AND status = 1";
												$readQuery = mysqli_query($db, $readSql);
												$userCount = mysqli_num_rows($readQuery);

												if ($userCount == 0) { ?>
										  			<div class="alert alert-warning text-center" role="alert">
													  Sorry! No User Found into the Database.
													</div>
										  		<?php }

										  		else {
										  			while ($row = mysqli_fetch_assoc($readQuery)) {
											  			$_SESSION['user_id'] 		= $row['user_id'];
											  			$user_name 					= $row['user_name'];
											  			$_SESSION['user_email']  	= $row['user_email'];
											  			$user_password 				= $row['user_password'];
											  			$role 						= $row['role'];
											  			$status 					= $row['status'];

											  			if ($role == 1) {
											  				if ($_SESSION['user_email'] == $email AND $user_password == $hassedPass) {
											  					header("Location: dashboard.php");
												  			}
												  			else if ($_SESSION['user_email'] != $email AND $user_password != $hassedPass) {
												  				session_destroy();
												  				header("Location: index.php");
												  			}
											  			}
											  			else {
											  				session_destroy();
												  			header("Location: index.php");
											  			}
											  			
											  		}

										  		}
												
											}
										?>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--end row-->
			</div>
		</div>
	</div>
	<!--end wrapper-->
	<!-- Bootstrap JS -->
	<script src="assets/js/bootstrap.bundle.min.js"></script>
	<!--plugins-->
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
	<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
	<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
	<!--Password show & hide js -->
	<script>
		$(document).ready(function () {
			$("#show_hide_password a").on('click', function (event) {
				event.preventDefault();
				if ($('#show_hide_password input').attr("type") == "text") {
					$('#show_hide_password input').attr('type', 'password');
					$('#show_hide_password i').addClass("bx-hide");
					$('#show_hide_password i').removeClass("bx-show");
				} else if ($('#show_hide_password input').attr("type") == "password") {
					$('#show_hide_password input').attr('type', 'text');
					$('#show_hide_password i').removeClass("bx-hide");
					$('#show_hide_password i').addClass("bx-show");
				}
			});
		});
	</script>
	<!--app JS-->
	<script src="assets/js/app.js"></script>
</body>


<!-- Mirrored from codervent.com/rukada/demo/vertical/authentication-signin.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 31 Jan 2023 17:33:39 GMT -->
</html>