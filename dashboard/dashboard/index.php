<?php
include("../php/config.php");
include("../php/db_connection.php");
include("../php/affiliateBanners.php");

$inviteAPI = 'https://discord.com/api/oauth2/authorize';

// handle lang
if (!isset($_COOKIE["lang"])) {
    include("php/language.php");
};
$STRINGS = array();
$STRINGS = array_merge(
    $STRINGS,
    json_decode(file_get_contents("../assets/translations/" . $_COOKIE["lang"] . "/ActionBar.json"), true)
);
$STRINGS = array_merge(
    $STRINGS,
    json_decode(file_get_contents("../assets/translations/" . $_COOKIE["lang"] . "/Dashboard.json"), true)
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

// check if have user servers
if (!isset($_SESSION["user_servers"])) {
    // get user servers
    header('Location: ' . $webPanel . 'php/discord.php?getServers');
    die();
};

// get bot servers
$guild_ids = array();
for ($i = 0; $i < count($_SESSION["user_servers"]); $i++) {
    array_push($guild_ids, $_SESSION["user_servers"][$i]["id"]);
};

$guild_ids = implode("','", $guild_ids);
$reponse = $bdd->query("SELECT guild_id, level FROM guilds WHERE guild_id IN ('" . $guild_ids . "')");
$reponse = $reponse->fetchAll();

$guild_ids = array();
$guild_levels = array();
foreach ($reponse as $server) {
    array_push($guild_ids, $server['guild_id']);
    $guild_levels[$server['guild_id']] = $server['level'];
};

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

    <title>Game Query - Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="../assets/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <!-- Custom styles for this page -->
    <link href="../assets/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="./">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Game Query</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="./">
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
                <a class="nav-link" href="./payment/donate">
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
                                    echo "\"href=\"../php/language.php?lang=" . $languageCode . "\"> ";
                                    echo "<div class='icon-circle bg-primary'> <image style='width:100%;'' src='../assets/images/flags/" . $languageCode . ".png'></image></div>";
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
                                <a class="dropdown-item" href="./payment">
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
                    <div class="col-sm-1">
                        <div class="row">
                            <?php
                            echo $zap_landscape_banners[array_rand($zap_landscape_banners)];
                            ?>
                        </div>
                        <br>
                    </div>
                    <div class="row">
                        <div class="col-sm-11">
                            <div class="container server-list-div">

                                <h3 class="list-label"><?php echo $STRINGS["serverlist_title"] ?></h3>
                                <a class="list-refresh btn btn-success" href="../php/discord.php?clearUserServers"><i class="fas fa-redo"></i> <?php echo $STRINGS["serverlist_refresh"] ?></a>
                                <br><br>
                                <?php
                                if (empty($_SESSION["user_servers"])) {
                                    echo "<br><br>you are not administrating any server.";
                                } else {
                                ?>
                                    <table class="table" id="server-list-table" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
                                                <th scope="col"><?php echo $STRINGS["serverlist_table_name"] ?></th>
                                                <th scope="col"><?php echo $STRINGS["serverlist_table_id"] ?></th>
                                                <th scope="col"><?php echo "Level" ?></th>
                                                <th scope="col"><?php echo $STRINGS["serverlist_table_action"] ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php

                                        foreach ($_SESSION["user_servers"] as &$server) {
                                            echo "<tr>";
                                            $serverName = explode(" ", $server["name"]);

                                            echo "<td image=\"" . $server["icon"] . "\"><label style=\"display:block;\">";

                                            foreach ($serverName as &$word) {
                                                echo $word[0];
                                            };

                                            echo "</label>
                                        <img style=\"display:none;\" class=\"rounded-circle guild-profile-pic\"/>
                                    </td>";
                                            echo "<td>" . $server["name"] . "</td>";
                                            echo "<td>" . $server["id"] . "</td>";

                                            // check if bot joined this guild
                                            if (in_array($server["id"], $guild_ids)) {
                                                echo "<td>" . strval($guild_levels[$server["id"]] + 1) . "</td>";

                                                echo "<td><a class='btn btn-primary' href=\"server.php?id=" . $server["id"] . "\">" .  $STRINGS["serverlist_table_action_configure"] . "</a></td>";
                                            } else {
                                                echo "<td>" . 0 . "</td>";

                                                $invite = $inviteAPI . "?client_id=" . OAUTH2_CLIENT_ID . "&permissions=" . BOT_PERMISSION_VALUE . "&redirect_uri=" . urlencode($webPanel) . "&guild_id=" . $server["id"] . "&scope=bot&response_type=code";
                                                echo "<td><a class='btn btn-info' href=\"" . $invite . "\">" . $STRINGS["serverlist_table_action_invite"] . "</a></td>";
                                            };
                                            echo "</tr>";
                                        };
                                    };
                                        ?>
                                        </tbody>
                                    </table>
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
    <script src="../assets/jquery/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../assets/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../assets/datatables/jquery.dataTables.min.js"></script>
    <script src="../assets/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../js/demo/datatables-demo.js"></script>

    <script>
        var rows = document.getElementById("server-list-table").rows;
        for (let i = 0; i < rows.length; i++) {
            let cell = rows[i].cells[0].children;
            let label = cell[0];
            let img = cell[1];

            let server_img = new Image();
            server_img.onload = function() {
                label.style.display = "none";
                img.src = server_img.src;
                img.style.display = "block";
            };
            server_img.src = rows[i].cells[0].getAttribute("image");
        };
    </script>

</body>

</html>