<!doctype html>
<html lang="en">

<head>

	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<title>Dashboard - DWAdmin</title>

	<!-- Bootstrap CSS-->
	<link rel="stylesheet" href="assets/vendors/bootstrap/css/bootstrap.css">
	<!-- Style CSS (White)-->
	<link rel="stylesheet" href="assets/css/White.css">
	<!-- Style CSS (Dark)-->
	<link rel="stylesheet" href="assets/css/Dark.css">
	<!-- FontAwesome CSS-->
	<link rel="stylesheet" href="assets/vendors/fontawesome/css/all.css">
	<!-- Icon LineAwesome CSS-->
	<link rel="stylesheet" href="assets/vendors/lineawesome/css/line-awesome.min.css">

</head>

<body>

	<div class="auth-dark">
		<div class="theme-switch-wrapper">
			<label class="theme-switch" for="checkbox">
				<input type="checkbox" id="checkbox" title="Dark Or White" />
				<div class="slider round"></div>
			</label>
		</div>
	</div>

	<div class="container">
		<div class="row vh-100 d-flex justify-content-center align-items-center auth">
			<div class="col-md-7 col-lg-5">
				<div class="card">
					<div class="card-body">
						<h3 class="mb-5">SIGN IN</h3>
						<form action="login.php" method="POST">
							<div class="form-group">
								<input type="text" name="nip" class="form-control" placeholder="NIP">
							</div>
							<div class="form-group">
								<input type="text" name="kode_akses" class="form-control" placeholder="Kode Akses">
							</div>
							<div class="form-group position-relative">
								<input type="password" name="password" id="password" class="form-control" placeholder="Password">
								<span onclick="togglePassword()" style="position:absolute; top:50%; right:15px; transform:translateY(-50%); cursor:pointer;">
									<i id="eyeIcon" class="fa fa-eye"></i>
								</span>
							</div>
							<div class="row">
								<div class="col-6 text-left">
									<div class="form-group form-check ml-2">
										<input type="checkbox" class="form-check-input" id="remember">
										<label class="form-check-label ml-2" for="remember">Remember</label>
									</div>
								</div>
								<div class="col-6 text-right">
									<a href="forgot.html">Forgot your password?</a>
								</div>
							</div>
							<div class="form-group my-4">
								<button type="submit" class="btn btn-linear-primary btn-rounded px-5">Sign in</button>
							</div>
						</form>

					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Tambahkan ini di bawah atau di akhir sebelum </body> -->
	<script>
		function togglePassword() {
			const passwordInput = document.getElementById("password");
			const eyeIcon = document.getElementById("eyeIcon");

			if (passwordInput.type === "password") {
				passwordInput.type = "text";
				eyeIcon.classList.remove("fa-eye");
				eyeIcon.classList.add("fa-eye-slash");
			} else {
				passwordInput.type = "password";
				eyeIcon.classList.remove("fa-eye-slash");
				eyeIcon.classList.add("fa-eye");
			}
		}
	</script>

	<!-- Tambahkan Font Awesome jika belum ada -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />


	<!-- Library Javascipt-->
	<script src="assets/vendors/bootstrap/js/jquery.min.js"></script>
	<script src="assets/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="assets/vendors/bootstrap/js/popper.min.js"></script>
	<script src="assets/js/script.js"></script>
</body>

</html>