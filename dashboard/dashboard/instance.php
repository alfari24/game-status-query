<?php
include("../php/config.php");
include("../php/db_connection.php");
include("../php/affiliateBanners.php");

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
    json_decode(file_get_contents("../assets/translations/" . $_COOKIE["lang"] . "/Instance.json"), true)
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
if (!isset($_GET["guild"]) || !preg_match('/^[0-9]*$/', $_GET["guild"])) {
    // get user data
    header('Location: ' . $webPanel . 'dashboard');
    die();
};

// check if bot is in server
if (!in_array($_GET["guild"], $bot_guild_ids)) {
    echo "the bot is not in this server.";
    echo "<br>";
    echo "<a href=\"" . $webPanel . "dashboard\">return</a>";
    die();
};

// check if server is in user servers
if (!in_array($_GET["guild"], $user_guild_ids)) {
    echo "you do not have permission to edit this server config.";
    echo "<br>";
    echo "<a href=\"" . $webPanel . "dashboard\">return</a>";
    die();
};

// get server's instances
$reponse = $bdd->query("SELECT instances FROM guilds WHERE guild_id = " . $_GET["guild"]);
$reponse = $reponse->fetch();

$instances_id = json_decode($reponse["instances"]);

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header('Location: ' . $webPanel . "dashboard/server.php?id=" . $_GET["guild"]);
    die();
};

if (!preg_match('/^[A-Za-z0-9",\s]+$/', $_GET["id"])) {
    header('Location: ' . $webPanel . "dashboard/server.php?id=" . $_GET["guild"]);
    die();
};

// check instance registred in server
if (!in_array($_GET["id"], $instances_id)) {
    echo "you do not have permission to edit this instance config.";
    echo "<br>";
    echo "<a href=\"" . $webPanel . "dashboard/server.php?id=" . $_GET["guild"] . "\">return</a>";
    die();
};

// get instance config
$reponse = $bdd->query("SELECT * FROM instances WHERE instance_id = '" . $_GET["id"] . "'");
$instance = $reponse->fetch();

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
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?php echo $STRINGS["instance_title"]; ?></h6>
                        </div>
                        <div class="card-body">
                            <form action="../php/instance.php?edit" method="post" class="was-validated">
                                <div class='required-div'>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label for="instance-name"><?php echo $STRINGS["instance_config_name"]; ?> :</label>
                                                <input type="text" class="form-control" id="instance-name" name="name" value="<?php echo $instance["name"] ?>" required>
                                                <div class="invalid-feedback"><?php echo $STRINGS["instance_config_name_error"]; ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label for="instance-channels"><?php echo $STRINGS["instance_config_channel"]; ?> :</label>

                                                <select id="instance-channels" class="form-control" name="channel" required>
                                                    <option value=""><?php echo $STRINGS["instance_empty_value"]; ?></option>
                                                </select>
                                                <a type="button" id="refresh-instance-channels" class="btn btn-default btn-sm" onclick="getGuildChannels();">
                                                    <i id="refresh-instance-channels-icon" style="color: rgba(50, 220, 25, 0.5)" class="fas fa-sync-alt"></i> <?php echo $STRINGS["instance_config_channel_refresh"]; ?>
                                                </a>
                                                <div class="invalid-feedback"><?php echo $STRINGS["instance_config_channel_error"]; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label for="instance-host"><?php echo $STRINGS["instance_config_host"]; ?> :</label>
                                                <input type="text" class="form-control" id="instance-host" name="host" placeholder="exemple.com or 123.45.67.89" value="<?php echo $instance["host"] ?>" required>
                                                <div class="invalid-feedback"><?php echo $STRINGS["instance_config_host_error"]; ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label for="instance-port"><?php echo $STRINGS["instance_config_port"]; ?> :</label>
                                                <input type="number" class="form-control" id="instance-port" name="port" placeholder="1234" value="<?php echo $instance["port"] ?>" required>
                                                <div class="invalid-feedback"><?php echo $STRINGS["instance_config_port_error"]; ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="instance-Game"><?php echo $STRINGS["instance_config_game"]; ?> :</label>

                                        <?php
                                        // get game types
                                        $reponse = $bdd->query("SELECT * FROM games ORDER BY name");
                                        $gametypes = $reponse->fetchAll();

                                        $gameTypeInfo = false;

                                        echo "<select id=\"instance-Game\" class=\"form-control\" name=\"game\" required>";
                                        echo "<option value=\"\" >" . $STRINGS["instance_config_game"] . "</option>";
                                        foreach ($gametypes as $game) {
                                            // print_r($game);
                                            if ($instance["game"] == $game["type"]) {
                                                echo "<option value=\"" . $game["type"] . "\" selected=\"selected\">" . $game["name"] . "</option>";
                                                if ($game["info"]) $gameTypeInfo = $game["info"];
                                            } else {
                                                echo "<option value=\"" . $game["type"] . "\" >" . $game["name"] . "</option>";
                                            }
                                        };
                                        echo "</select>";

                                        if ($gameTypeInfo) {
                                            echo "<br><div class='alert alert-warning ErrorNotification' role='alert'>";
                                            echo "<label style='font-size:20px;'>" . $STRINGS["instance_config_game_requirements"] . " :</label><br>";
                                            echo $gameTypeInfo;
                                            echo "</div>";
                                        }
                                        ?>

                                        <div class="invalid-feedback"><?php echo $STRINGS["instance_config_game_error"]; ?></div>
                                    </div>
                                    <br>
                                </div>

                                <a data-toggle="collapse" href="#optional-div" role="button" class="btn btn-info" aria-expanded="false" aria-controls="optional-div">
                                    More Options >
                                </a>
                                <br>

                                <div class='collapse optional-div card card-body' id="optional-div">
                                    <div class="form-group">
                                        <label for="instance-timezones"><?php echo $STRINGS["instance_config_timezone"]; ?> :</label>
                                        <select id="instance-timezones" class="form-control" name="timezone">
                                            <?php
                                            $timezones = array('UTC-12:00', 'UTC-11:00', 'UTC-10:00', 'UTC-09:30', 'UTC-09:00', 'UTC-08:00', 'UTC-07:00', 'UTC-06:00', 'UTC-05:00', 'UTC-04:00', 'UTC-03:30', 'UTC-03:00', 'UTC-02:00', 'UTC-01:00', 'UTC+00:00', 'UTC+01:00', 'UTC+02:00', 'UTC+03:00', 'UTC+03:30', 'UTC+04:00', 'UTC+04:30', 'UTC+05:00', 'UTC+05:30', 'UTC+05:45', 'UTC+06:00', 'UTC+06:30', 'UTC+07:00', 'UTC+08:00', 'UTC+08:45', 'UTC+09:00', 'UTC+09:30', 'UTC+10:00', 'UTC+10:30', 'UTC+11:00', 'UTC+12:00', 'UTC+12:45', 'UTC+13:00', 'UTC+14:00');

                                            for ($i = 0; $i < count($timezones); $i++) {
                                                if ($instance["timezone"] == $i) {
                                                    echo "<option value=\"" . $i . "\" selected=\"selected\">" . $timezones[$i] . "</option>";
                                                } else {
                                                    echo "<option value=\"" . $i . "\">" . $timezones[$i] . "</option>";
                                                };
                                            };
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="instance-timeformat"><?php echo $STRINGS["instance_config_timeformat"]; ?> :</label>
                                        <?php
                                        echo "<select id=\"instance-timeformat\" class=\"form-control\" name=\"timeformat\">";
                                        if ($instance["timeformat"]) {
                                            echo "<option value=\"" . 0 . "\" >" . "AM / PM" . "</option>";
                                            echo "<option value=\"" . 1 . "\" selected=\"selected\">" . "24H" . "</option>";
                                        } else {
                                            echo "<option value=\"" . 0 . "\" selected=\"selected\">" . "AM / PM" . "</option>";
                                            echo "<option value=\"" . 1 . "\" >" . "24H" . "</option>";
                                        };
                                        echo "</select>";
                                        ?>
                                    </div>
                                    <br><br>

                                    <div class="form-group">
                                        <?php
                                        if (!empty($instance["logo"])) echo "<img class=\"rounded-circle\" style='height:32px; width: 32px; margin: 5px;' src=\"" . $instance["logo"] . "\"></img>";
                                        ?>
                                        <label for="instance-logo"><?php echo $STRINGS["instance_config_logo"]; ?> :</label>
                                        <input type="url" class="form-control" id="instance-logo" name="logo" value="<?php echo $instance["logo"] ?>">
                                        <div class="invalid-feedback"><?php echo $STRINGS["instance_config_logo_error"]; ?></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="instance-language"><?php echo $STRINGS["instance_config_language"]; ?> :</label>
                                        <select id="instance-language" class="form-control" name="language">
                                            <?php
                                            foreach ($supportedLanguages as $languageCode => $language) {
                                                if ($language[1]) {
                                                    if ($instance["language"] == $languageCode) {
                                                        echo "<option value=\"" . $languageCode . "\" selected=\"selected\">" . $language[0] . "</option>";
                                                    } else if (!$instance["language"] && $languageCode == "EN") {
                                                        echo "<option value=\"" . $languageCode . "\" selected=\"selected\">" . $language[0] . "</option>";
                                                    } else {
                                                        echo "<option value=\"" . $languageCode . "\">" . $language[0] . "</option>";
                                                    };
                                                };
                                            };
                                            ?>
                                        </select>
                                    </div>
                                    <br><br>

                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="form-group form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="checkbox" name="hide_ip" value="1" <?php if ($instance["hide_ip"]) echo "checked" ?>> <?php echo "hide server ip." ?>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="form-group form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="checkbox" name="hide_port" value="1" <?php if ($instance["hide_port"]) echo "checked" ?>> <?php echo "hide server port." ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="form-group form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="checkbox" name="playerlist" value="1" <?php if ($instance["playerlist"]) echo "checked" ?>> <?php echo $STRINGS["instance_config_playerlist"]; ?>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="form-group form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="checkbox" name="full_playerlist" value="1" <?php if ($instance["full_playerlist"]) echo "checked" ?>> <?php echo "show full playerlist." ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="form-group form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="checkbox" name="minimal" value="1" <?php if ($instance["minimal"]) echo "checked" ?>> <?php echo $STRINGS["instance_config_minimalmode"]; ?>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="instance-color"><?php echo $STRINGS["instance_config_embed_color"]; ?> :</label>
                                                <input type="color" id="instance-color" name="color" <?php
                                                                                                        if (!empty($instance["color"])) {
                                                                                                            echo "value=\"" . $instance["color"] . "\"";
                                                                                                        } else {
                                                                                                            echo "value=\"#00ff00\"";
                                                                                                        };
                                                                                                        ?>>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>Refresh Rate : </label>
                                            <select name="refresh_rate">
                                                <option value="0" selected="selected">15 mins</option>
                                                <option value="1" disabled>10 mins</option>
                                                <option value="2" disabled>5 mins</option>
                                                <option value="3" disabled>1 mins</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="form-group form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="checkbox" name="graph" value="1" <?php if ($instance["graph"]) echo "checked" ?>> <?php echo $STRINGS["instance_config_graph"]; ?>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-8">
                                            <button id="clearGraph_btn" type="button" class="btn btn-primary" onclick="document.getElementById('clearGraph_form').submit();"><?php echo $STRINGS["instance_config_button_clear_graph"]; ?></button>
                                        </div>
                                    </div>

                                    <br><br>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label>Info Field:</label>
                                            <textarea style="width: 100%;" maxlength="1000" placeholder="my **Amazing** cusom `server` 🌈 message." name="custom_field" disabled></textarea>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <div class="row">
                                    <input type="hidden" name="serverid" value="<?php echo $_GET["guild"] ?>">
                                    <input type="hidden" name="instanceid" value="<?php echo $instance["instance_id"] ?>">
                                    <input class="btn btn-outline-success btn-lg btn-block" type="submit">
                                </div>


                            </form>

                            <form id="clearGraph_form" class="instance-action-form" action="../php/instance.php?clearGraph" method="post">
                                <input type="hidden" name="instanceid" value="<?php echo $instance["instance_id"] ?>">
                                <input type="hidden" name="serverid" value="<?php echo $_GET["guild"] ?>">
                            </form>

                        </div>
                        <div class="container options-div">
                            <!-- <label class="list-label">Instance options:</label><br> -->
                            <?php
                            if ($instance["started"]) {
                            ?>
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <form class="instance-action-form" action="../php/instance.php?restart" method="post">
                                            <input type="hidden" name="instanceid" value="<?php echo $instance["instance_id"]; ?>">
                                            <input type="hidden" name="serverid" value="<?php echo $_GET["guild"]; ?>">
                                            <input type="submit" class="btn btn-warning" value="<?php echo $STRINGS["instance_option_restart_instance"]; ?>">
                                        </form>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <form class="instance-action-form" action="../php/instance.php?stop" method="post">
                                            <input type="hidden" name="instanceid" value="<?php echo $instance["instance_id"]; ?>">
                                            <input type="hidden" name="serverid" value="<?php echo $_GET["guild"]; ?>">
                                            <input type="submit" class="btn btn-danger" value="<?php echo $STRINGS["instance_option_stop_instance"] ?>">
                                        </form>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <form class="instance-action-form" action="" method="post">
                                            <input type="hidden" name="instanceid" value="<?php echo $instance["instance_id"]; ?>">
                                            <input type="hidden" name="serverid" value="<?php echo $_GET["guild"]; ?>">
                                            <input type="submit" class="btn btn-info" value="<?php echo "Update Status Message Now"; ?>" disabled>
                                        </form>
                                    </div>
                                </div>
                            <?php
                            } else {
                            ?>
                                <form class="instance-action-form" action="../php/instance.php?start" method="post">
                                    <input type="hidden" name="instanceid" value="<?php echo $instance["instance_id"]; ?>">
                                    <input type="hidden" name="serverid" value="<?php echo $_GET["guild"]; ?>">
                                    <input type="submit" class="btn btn-success" value="<?php echo $STRINGS["instance_option_start_instance"]; ?>">
                                </form>
                            <?php
                            };
                            ?>
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

        <!-- get guild's channels -->
        <script>
            async function getGuildChannels() {
                var channel_select = document.getElementById('instance-channels');
                var refresh_channel_btn = document.getElementById('refresh-instance-channels');
                var refresh_channel_icon = document.getElementById('refresh-instance-channels-icon');

                // disable refresh button
                refresh_channel_btn.classList.add("disabled");
                refresh_channel_icon.classList.add("fa-spin");

                // clear all options
                while (channel_select.firstChild) {
                    channel_select.removeChild(channel_select.firstChild);
                };

                // create the first none option
                let none_opt = document.createElement('option');
                none_opt.innerHTML = <?php echo "\"" . $STRINGS["instance_empty_value"] . "\""; ?>;
                none_opt.value = "";
                channel_select.appendChild(none_opt);

                try {
                    // get channels from bot
                    const response = await fetch(<?php echo "'../api.php?getGuild=" . $_GET["guild"] . "'" ?>);
                    const data = await response.json();

                    if (data["error"] == "") {
                        for (let i = 0; i < data["channels"].length; i++) {
                            let channel = data["channels"][i];
                            let opt = document.createElement('option');

                            opt.innerHTML = "(" + channel["id"] + ") " + channel["name"];
                            opt.value = channel["id"];
                            channel_select.appendChild(opt);

                            if (opt.value == '<?php echo $instance["channel"] ?>') {
                                channel_select.value = opt.value;
                            };
                        };

                    };

                } catch (err) {};

                //reenable refresh button
                refresh_channel_btn.classList.remove("disabled");
                refresh_channel_icon.classList.remove("fa-spin");

            };

            getGuildChannels();
        </script>

</body>

</html>