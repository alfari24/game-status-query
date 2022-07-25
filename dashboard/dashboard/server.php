<?php
include("../php/config.php");
include("../php/db_connection.php");

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
    json_decode(file_get_contents("../assets/translations/" . $_COOKIE["lang"] . "/Server.json"), true)
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
$user_guild_ids = array();
for ($i = 0; $i < count($_SESSION["user_servers"]); $i++) {
    array_push($user_guild_ids, $_SESSION["user_servers"][$i]["id"]);
};

$reponse = $bdd->query("SELECT guild_id FROM guilds");
$reponse = $reponse->fetchAll();

$bot_guild_ids = array();
foreach ($reponse as $server) {
    array_push($bot_guild_ids, $server['guild_id']);
};

// check if server id is set
if (!isset($_GET["id"]) || !preg_match('/^[0-9]*$/', $_GET["id"])) {
    // get user data
    header('Location: ' . $webPanel . 'dashboard');
    die();
};

// check if bot is in server
if (!in_array($_GET["id"], $bot_guild_ids)) {
    echo "the bot is not in this server.";
    echo "<br>";
    echo "<a href=\"" . $webPanel . "dashboard\">return</a>";
    die();
};

// check if server is in user servers
if (!in_array($_GET["id"], $user_guild_ids)) {
    echo "you do not have permission to edit this bot config.";
    echo "<br>";
    echo "<a href=\"" . $webPanel . "dashboard\">return</a>";
    die();
};

// get server info
$managedServer = array();
foreach ($_SESSION["user_servers"] as $server) {
    if ($server["id"] == $_GET["id"]) {
        $managedServer = $server;
        break;
    };
};

// get server's instances
$reponse = $bdd->query("SELECT instances FROM guilds WHERE guild_id = " . $managedServer["id"]);
$reponse = $reponse->fetch();

$instances_id = json_decode($reponse["instances"]);

// get user data from db
$reponse = $bdd->query("SELECT * FROM users WHERE user_id = '" . $_SESSION['user_id'] . "'");
$user = $reponse->fetch();

if (!$user) {
    $user["points"] = 0;
};

// get guild info
$reponse = $bdd->query("SELECT level, points FROM guilds WHERE guild_id = " . $managedServer["id"]);
$reponse = $reponse->fetchAll();

$guild_level = $reponse[0][0] + 1;
$guild_points = $reponse[0][1];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Game Query Server Dashboard">
    <meta name="author" content="clemiee">

    <title>Game Query - Manage Instances</title>

    <!-- Custom fonts for this template-->
    <link href="../assets/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
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
                    <?php
                    // handle error bar
                    if (isset($_GET['error'])) {

                        // handle error
                        if ($_GET['error'] == "") {
                        } else {
                            echo "<div class='errorBar'>";
                            switch ($_GET['error']) {
                                case 0:
                                    echo $STRINGS["errorbar_unkown"];
                                    break;
                                case 1:
                                    echo $STRINGS["errorbar_instance_name"];
                                    break;
                                case 2:
                                    echo $STRINGS["errorbar_invalid_id"];
                                    break;
                                case 3:
                                    echo "Your server does not have enough points to be upgraded.";
                                    break;
                                case 4:
                                    echo "You have to set the number of points you want to transfer.";
                                    break;
                                case 5:
                                    echo "You want to transfer an invalid number of points.";
                                    break;
                                case 6:
                                    echo "You do not have enough points.";
                                    break;
                            }
                            echo "</div>";
                        };
                    };
                    // handle info bar
                    if (isset($_GET['info'])) {

                        // handle error
                        if ($_GET['info'] == "") {
                        } else {
                            echo "<div class='infoBar'>";
                            switch ($_GET['info']) {
                                case 0:
                                    echo $STRINGS["infobar_unkown"];
                                    break;
                                case 2:
                                    echo $STRINGS["infobar_spamm"];
                                    break;
                                case 3:
                                    echo $STRINGS["infobar_max_instances"];
                                    break;
                            }
                            echo "</div>";
                        };
                    };


                    // handle info bar
                    if (isset($_GET['success'])) {

                        // handle error
                        if ($_GET['success'] == "") {
                        } else {
                            echo "<div class='successBar'>";
                            switch ($_GET['success']) {
                                case 0:
                                    echo "i don't know what you are tring to do but its a success.";
                                    break;
                                case 1:
                                    echo "Server upgraded successfully.";
                                    break;
                                case 2:
                                    echo "Points transferred successfully.";
                                    break;
                            }
                            echo "</div>";
                        };
                    };
                    ?>
                    <?php
                    if (sizeof($instances_id) > ($guild_level * 3)) {
                    ?>
                        <div class="alert alert-danger" role="alert">
                            <h4 class="alert-heading">Too many Instances !</h4>
                            Your server baypassed the instances limit for its level, you need to delete some instances or <b><a onclick="openUpgradeServerLevelModal()">level up the server</a></b>.
                        </div>
                    <?php
                    };
                    ?>

                    <div class="row">

                        <!-- Instances List -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                <?php echo $STRINGS["instancelist_title"] ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $managedServer["name"]; ?></div>
                                            <?php echo $managedServer["id"]; ?>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instances Counter -->
                        <div class="col-xl-4 col-md-6 mb-4" onclick="openUpgradeServerLevelModal()">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Instances: <label style="<?php if (sizeof($instances_id) > ($guild_level * 3)) echo 'color:red;' ?>">
                                                    <?php
                                                    echo sizeof($instances_id); ?> out of <?php echo $guild_level * 3;
                                                                                            ?>
                                                </label></div>
                                            <div class="h5 mb-0 text-gray-800">
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo (intval($guild_level) / 5 * 100) ?>%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                        level <?php echo $guild_level; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container instance-list-div">

                        <!-- Upgrade server level Modal -->
                        <div class="modal fade" id="upgradeServerLevelModal" tabindex="-1" role="dialog" aria-labelledby="upgradeServerLevelModalTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Server Level</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <center><b>
                                                <?php echo $managedServer["name"] . " (" . $managedServer["id"] . ")"; ?>
                                            </b></center>

                                        <!-- <br>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo (intval($guild_level) / 5 * 100) ?>%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                level <?php echo $guild_level; ?>
                            </div>
                        </div> -->

                                        <br>
                                        Server Level: <?php echo $guild_level; ?>
                                        <br>
                                        Max instances: <?php echo $guild_level * 3; ?>
                                        <br>
                                        Instances: <?php echo sizeof($instances_id); ?>
                                        <br>

                                        <hr>
                                        <b>Transfer my points to this server:</b>
                                        <br>
                                        <br>
                                        My Points: <?php echo $user["points"] ?>
                                        <br>
                                        Server Points: <?php echo $guild_points; ?>
                                        <br>
                                        <br>
                                        <form class="instance-action-form" action="../php/instance.php?transferPoints" method="post">
                                            <input type="hidden" name="serverid" value="<?php echo $managedServer["id"] ?>">
                                            <input type="number" name="nbr_points_transfer" value="0">
                                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="you cannot get back your points after they get transfered to the server.">
                                                <input type="submit" class="btn text-danger" value="Transfer Points">
                                            </span>
                                        </form>

                                    </div>
                                    <div class="modal-footer">
                                        <form class="instance-action-form" action="../php/instance.php?upgrade" method="post">
                                            <input type="hidden" name="serverid" value="<?php echo $managedServer["id"] ?>">
                                            <input type="submit" class="btn btn-success" value="Upgrade Server (50 points)" <?php if ($guild_points < 50) echo "disabled" ?>>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        if (empty($instances_id)) {
                            // if (true) {
                            echo "<br><br>";
                            echo $STRINGS["instancelist_noInstances"];
                        } else {
                        ?>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Registered Instances</h6>
                                </div>
                                <div class="card-body">
                                    <form class="ml-md-3 my-2 my-md-0 mw-100" action="../php/instance.php?create" method="post">
                                        <div class="input-group">
                                            <input type="text" class="form-control bg-light border-0 small" placeholder="<?php echo $STRINGS["instancelist_button_create_new"] ?>" name="name" required="true">
                                            <div class="input-group-append">
                                                <input type="hidden" name="serverid" value="<?php echo $managedServer["id"] ?>">
                                                <input type="submit" class="btn btn-primary" type="button" value="Go!"></input>
                                            </div>

                                    </form>
                                </div>
                                <br>
                                <table class="table table-bordered" id="dataTable">
                                    <thead>
                                        <tr>
                                            <!-- <th scope="col"></th> -->
                                            <th scope="col"><?php echo $STRINGS["instancelist_table_name"] ?></th>
                                            <th scope="col"><?php echo $STRINGS["instancelist_table_id"] ?></th>
                                            <th scope="col"><?php echo $STRINGS["instancelist_table_status"] ?></th>
                                            <th scope="col"><?php echo $STRINGS["instancelist_table_setup"] ?></th>
                                            <th scope="col"><?php echo $STRINGS["instancelist_table_action"] ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php


                                        $instances_id_sql = implode("','", $instances_id);
                                        $reponse = $bdd->query("SELECT * FROM instances WHERE instance_id IN ('" . $instances_id_sql . "')");
                                        $reponse = $reponse->fetchAll();

                                        foreach ($reponse as $instance) {
                                            $started = "<span class=\"badge badge-danger\">" . $STRINGS["instancelist_table_status_stopped"] . "</span>";
                                            if ($instance["started"]) $started = "<span class=\"badge badge-success\">" . $STRINGS["instancelist_table_status_started"] . "</span>";
                                            if (sizeof($instances_id) > ($guild_level * 3)) $started = "<span class=\"badge badge-secondary\">" . "FROZEN" . "</span>";

                                        ?>
                                            <tr>

                                                <td><?php echo $instance["name"]; ?></td>
                                                <td><?php echo $instance["instance_id"]; ?></td>
                                                <td><?php echo $started; ?></td>

                                                <td>
                                                    <form class="instance-action-form" action="./instance.php" method="get">
                                                        <input type="hidden" name="id" value="<?php echo $instance["instance_id"]; ?>">
                                                        <input type="hidden" name="guild" value="<?php echo $managedServer["id"]; ?>">
                                                        <input type="submit" class="btn btn-primary" value="<?php echo $STRINGS["instancelist_table_action_configure"]; ?>">
                                                    </form>
                                                </td>

                                                <td>

                                                    <!-- Button delete instance trigger modal -->
                                                    <button class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteInstanceConfirmationModal_<?php echo $instance["instance_id"]; ?>"><?php echo $STRINGS["instancelist_table_action_delete"]; ?></button>

                                                    <!-- Modal -->
                                                    <div class="modal fade" id="deleteInstanceConfirmationModal_<?php echo $instance["instance_id"]; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteInstanceConfirmationModalTitle" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete this instance ?<br>
                                                                    This will delete all of the instace configuration and possible bought items.
                                                                </div>
                                                                <div class="modal-footer">

                                                                    <form class="instance-action-form" action="../php/instance.php?delete" method="post">
                                                                        <input type="hidden" name="instanceid" value="<?php echo $instance["instance_id"]; ?>">
                                                                        <input type="hidden" name="serverid" value="<?php echo $managedServer["id"]; ?>">
                                                                        <input type="submit" class="btn btn-outline-danger" value="<?php echo $STRINGS["instancelist_table_action_delete"]; ?>">
                                                                    </form>

                                                                    <button class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                                                                        Cancel
                                                                    </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </td>
                                            </tr>

                                    <?php
                                        };
                                    };

                                    ?>
                                    </tbody>
                                </table>
                            </div>
                    </div>
                </div>

            </div>
            <!-- /.container-fluid -->

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

    <!-- toggle upgrade server leve+l modal-->
    <script>
        function openUpgradeServerLevelModal() {
            $('#upgradeServerLevelModal').modal('toggle');
        };

        // enable tooltip
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>

</body>

</html>