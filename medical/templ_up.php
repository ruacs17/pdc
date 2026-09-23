<?php require_once('authorize.php');
date_default_timezone_set("Asia/Manila");
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('pageTitle.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
?><!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>PhilKonstrak <?php echo pageTitle();?></title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style" href="../css/loader.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <script src="../js/servertime.php"></script>
    <!-- end: CSS -->
    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <link id="ie-style" href="../css/ie.css" rel="stylesheet">
    <![endif]-->
    <!--[if IE 9]>
    <link id="ie9style" href="../css/ie9.css" rel="stylesheet">
    <![endif]-->
</head>
<body>
<!-- start: Header -->
<div id="spinner"></div>
<div class="navbar">
    <div class="navbar-inner">
        <div class="container-fluid">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".top-nav.nav-collapse,.sidebar-nav.nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand"><span><img src="../img/pdcBanner.png"></span></a>
            <!-- start: Header Menu -->
            <div class="nav-no-collapse header-nav">
                <ul class="nav pull-right">
                <!-- start: User Dropdown -->
                    <li class="dropdown"><a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="halflings-icon white calendar"></i> <span id="servertime"></span><script>showservertime()</script></a></li>
                    <li class="dropdown">
                        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="halflings-icon white user"></i> <?php echo ucwords(strtolower($name));?><span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-menu-title"></li>
                            <li><a href="profile.php"><i class="halflings-icon user"></i> Profile</a></li>
                        </ul>
                    </li>
                    <!-- end: User Dropdown -->
                </ul>
            </div>
            <!-- end: Header Menu -->
        </div>
    </div>
</div>
<!-- start: Header -->
<div class="container-fluid-full">
    <div class="row-fluid">
    <!-- start: Main Menu -->
    <?php require_once('link.php');?>
    <!-- end: Main Menu -->
    <!-- start: Content -->
    <div id="content" class="span10">
        <div class="row-fluid">