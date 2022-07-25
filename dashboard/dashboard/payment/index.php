<?php
include("../../php/config.php");
include("../../php/db_connection.php");

// handle lang
if (!isset($_COOKIE["lang"])) {
	include("php/language.php");
};
$STRINGS = array();
$STRINGS = array_merge(
	$STRINGS,
	json_decode(file_get_contents("../../assets/translations/" . $_COOKIE["lang"] . "/ActionBar.json"), true)
);

session_start();

// check if user connected
if (!isset($_COOKIE['access_token'])) {
	// user is not connected
	header('Location: ' . $webPanel . '?error=3');
	die();
};

// check if have user data
if (!isset($_SESSION['user_id'])) {
	// get user data
	header('Location: ' . $webPanel . 'php/discord.php?getUser');
	die();
};

// get heartbeat
$reponse = $bdd->prepare("SELECT status FROM bot");
$reponse->execute();
$reponse_heartBeat = $reponse->fetch()[0];

// get user data from db
$reponse = $bdd->query("SELECT * FROM users WHERE user_id = '" . $_SESSION['user_id'] . "'");
$user = $reponse->fetch();

if (!$user) {
	$user["points"] = 0;
};
?>
<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Game Query Server Dashboard">
	<meta name="author" content="clemiee">

	<title>Game Query - Donate</title>

	<!-- Custom fonts for this template-->
	<link href="../../assets/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

	<!-- Custom styles for this template-->
	<link href="../../css/sb-admin-2.css" rel="stylesheet">
	<!-- Custom styles for this page -->
	<link href="../../assets/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>

<body id="page-top">

	<!-- Page Wrapper -->
	<div id="wrapper">

		<!-- Sidebar -->
		<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

			<!-- Sidebar - Brand -->
			<a class="sidebar-brand d-flex align-items-center justify-content-center" href="../">
				<div class="sidebar-brand-icon rotate-n-15">
					<i class="fas fa-laugh-wink"></i>
				</div>
				<div class="sidebar-brand-text mx-3">Game Query</div>
			</a>

			<!-- Divider -->
			<hr class="sidebar-divider my-0">

			<!-- Nav Item - Dashboard -->
			<li class="nav-item">
				<a class="nav-link" href="../">
					<i class="fas fa-fw fa-tachometer-alt"></i>
					<span>Dashboard</span></a>
			</li>

			<!-- Divider -->
			<hr class="sidebar-divider">

			<!-- Heading -->
			<div class="sidebar-heading">
				Support
			</div>

			<!-- Nav Item - Discord Server -->
			<li class="nav-item">
				<a class="nav-link" href="<?php echo $supportDiscordServerLink ?>">
					<i class="fab fa-discord"></i>
					<span><?php echo $STRINGS["actionbar_user_Join_Support_Server"] ?></span></a>
			</li>
			<!-- Nav Item - Donate -->
			<li class="nav-item">
				<a class="nav-link" href="./donate">
					<i class="fas fa-heart"></i>
					<span>Donate</span></a>
			</li>

			<!-- Divider -->
			<hr class="sidebar-divider d-none d-md-block">

			<!-- Sidebar Toggler (Sidebar) -->
			<div class="text-center d-none d-md-inline">
				<button class="rounded-circle border-0" id="sidebarToggle"></button>
			</div>

		</ul>
		<!-- End of Sidebar -->

		<!-- Content Wrapper -->
		<div id="content-wrapper" class="d-flex flex-column">

			<!-- Main Content -->
			<div id="content">

				<!-- Topbar -->
				<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

					<!-- Sidebar Toggle (Topbar) -->
					<button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
						<i class="fa fa-bars"></i>
					</button>

					<!-- Topbar Search -->
					<?php echo $STRINGS["actionbar_botStatus"] ?>:
					<?php
					$reponse = $bdd->prepare("SELECT status FROM bot");
					$reponse->execute();
					$reponse = $reponse->fetch()[0];

					if (strtotime('now') - $reponse < $hert_beat_time) {
					?>
						<span class="fa-stack bot-status-icon">
							<i style="color: rgba(50, 220, 25, 0.5)" class="fas fa-circle fa-stack-1x" id="botStatus_icon"></i>
							<i style="color: rgb(50 220 25); font-size:0.65em;" class="fas fa-circle fa-stack-1x" id="botStatus_icon_2"></i>
						</span>
						<label style="color: #1dd01d"><?php echo $STRINGS["actionbar_botStatus_online"] ?></label>
					<?php
					} else {
					?>
						<span class="fa-stack bot-status-icon">
							<i style="color: rgba(255, 0, 0, 0.5)" class="fas fa-circle fa-stack-1x" id="botStatus_icon"></i>
							<i style="color: rgb(245 0 0); font-size:0.65em;" class="fas fa-circle fa-stack-1x" id="botStatus_icon_2"></i>
						</span>
						<label style="color: #e21a36"><?php echo $STRINGS["actionbar_botStatus_offline"] ?></label>
					<?php
					};
					?>

					<!-- Topbar Navbar -->
					<ul class="navbar-nav ml-auto">

						<!-- Nav Item - Search Dropdown (Visible Only XS) -->
						<li class="nav-item dropdown no-arrow d-sm-none">

						</li>

						<!-- Nav Item - Alerts -->
						<li class="nav-item dropdown no-arrow mx-1">
							<a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fas fa-language"></i>
							</a>
							<!-- Dropdown - Alerts -->
							<div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
								<h6 class="dropdown-header">
									Language Selector
								</h6>
								<?php
								foreach ($supportedLanguages as $languageCode => $language) {
									echo "<a class=\"dropdown-item d-flex align-items-center ";
									if (!$language[1]) {
										echo "disabled text-danger";
									};
									echo "\"href=\"../../php/language.php?lang=" . $languageCode . "\"> ";
									echo "<div class='icon-circle bg-primary'> <image style='width:100%;'' src='../../assets/images/flags/" . $languageCode . ".png'></image></div>";
									echo "<div class='font-weight-bold'>   " . $language[0] . "</div>";
									if (!$language[1]) {
										echo "<div><div class='text-truncate'> (Not Translated)</div></div>";
									};
									echo "</a>";
								};
								?>
							</div>
						</li>

						<div class="topbar-divider d-none d-sm-block"></div>

						<!-- Nav Item - User Information -->
						<li class="nav-item dropdown no-arrow">
							<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<span class="mr-2 d-none d-lg-inline text-gray-600 small">
									<?php
									echo $_SESSION['user_username'] . "#" . $_SESSION['user_discriminator'] . "";
									?>
								</span>
								<?php
								echo "<img src=\"https://cdn.discordapp.com/avatars/" . $_SESSION['user_id']  . "/" . $_SESSION['user_avatar']  . ".png\" class=\"img-profile rounded-circle\">";
								?>
							</a>
							<!-- Dropdown - User Information -->
							<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
								<a class="dropdown-item" href="./">
									<i class="fa fa-coins fa-sm fa-fw mr-2 text-gray-400"></i>
									<?php echo $STRINGS["actionbar_user_points"] ?>:
									<?php
									echo $user["points"];
									?>
								</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
									<i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
									<?php echo $STRINGS["actionbar_user_Disconnect"] ?>
								</a>
							</div>
						</li>

					</ul>

				</nav>
				<!-- End of Topbar -->

				<!-- Begin Page Content -->
				<div class="container-fluid">
					<div class="container payments-list-div">

						<h1>How do I get Points ?</h1>
						<br>

						<div class="card-deck">

							<div class="card bg-primary mb-3 mx-auto payment-card" style="max-width: 18rem;" onclick="location.href='https://top.gg/bot/731586234769735750';">
								<div class="card-header text-dark">Make The Bot great Again.</div>
								<div class="card-body text-white">
									<h5 class="card-title">By Voting</h5>
									<p class="card-text">you can get 2 points on weekends, and one point on weekdays just by voting, you could make 76 points a month, thats a lot !</p>
								</div>
							</div>

							<div class="card bg-success mb-3 mx-auto payment-card" style="max-width: 18rem;" onclick="location.href='<?php echo $supportDiscordServerLink; ?>';">
								<div class="card-header text-dark">Ask, and you shall receive.</div>
								<div class="card-body text-white">
									<h5 class="card-title">By Asking</h5>
									<p class="card-text">you need more points ? well i give free points to nice people on my discord.</p>
								</div>
							</div>

							<div class="card bg-danger mb-3 mx-auto payment-card" style="max-width: 18rem;" onclick="location.href='donate.php';">
								<div class="card-header text-dark">Servers doesn't grow on trees.</div>
								<div class="card-body text-white">
									<h5 class="card-title">By Donating</h5>
									<p class="card-text">you may get some points by donating.</p>
								</div>
							</div>

						</div>

					</div>
				</div>
			</div>
			<!-- End of Main Content -->

			<!-- Footer -->
			<footer class="sticky-footer bg-white">
				<div class="container my-auto">
					<div class="copyright text-center my-auto">
						<span>Copyright &copy; 2022-present, Clemie McCartney and <a href="https://github.com/clemiee/game-status-query/graphs/contributors">Contributors</a>.</span>
					</div>
				</div>
			</footer>
			<!-- End of Footer -->

		</div>
		<!-- End of Content Wrapper -->

	</div>
	<!-- End of Page Wrapper -->

	<!-- Scroll to Top Button-->
	<a class="scroll-to-top rounded" href="#page-top">
		<i class="fas fa-angle-up"></i>
	</a>

	<!-- Logout Modal-->
	<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
				<div class="modal-footer">
					<button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
					<a class="btn btn-primary" href="../php/discord.php?login=1">Logout</a>
				</div>
			</div>
		</div>
	</div>

	<!-- Bootstrap core JavaScript-->
	<script src="../../assets/jquery/jquery.min.js"></script>
	<script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

	<!-- Core plugin JavaScript-->
	<script src="../../assets/jquery-easing/jquery.easing.min.js"></script>

	<!-- Custom scripts for all pages-->
	<script src="../../js/sb-admin-2.min.js"></script>

	<!-- Page level plugins -->
	<script src="../../assets/datatables/jquery.dataTables.min.js"></script>
	<script src="../../assets/datatables/dataTables.bootstrap4.min.js"></script>

	<!-- Page level custom scripts -->
	<script src="../../js/demo/datatables-demo.js"></script>

</body>

</html>