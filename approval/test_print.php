<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>P.O. Fuel Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link href="../css/printerfoot.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">
	.padParLeft{padding-left:10px;}
	.padAmLeft{padding-left:60px;}
	</style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
			<?php
			#require_once('../class/print_header.php');
function print_header($title){
echo  '
    <style type="text/css">
    #hName{font-size: 15px; font-family: Tahoma;font-weight: bolder;}
    #hAddress{font-size: 10px; font-family: Tahoma; line-height: 15px;}
    #DTitle{font-size: 15px; font-family: Tahoma;}
    </style>
<table width="700" border="1" align="center">
  <tr>
    <td align="center">
        <table width="100%" border="0" align="center">
          <tr>
            <td width="160" align="right"><img src="../img/header-logo.jpg" width="125" height="153"></td>
            <td align="center">
                <div id="hName">PHILKONSTRAK DEVELOPMENT CORPORATION</div>
                <div id="hAddress">
                    <div>Design &bull; Estimate &bull; Construct &bull; Develop</div>
                    <div>Door 3 MGR Building, Apitong Street, Sunrise Village Extension Bulacao, Cebu City</div>
                    <div>Tel# (Main Office) (032) 407-3213; Cel# (Main Office) 0923-304-3498; 0905-296-7376</div>
                    <div>Tel# (Bohol Coordinating Office) (038) 411-2753</div>
                    <div>E-mail Add: <a href="#">philkonstrak@hotmail.com</a>; <a href="#">philkonstrakdevtcorp@gmail.com</a></div>
                    <div>Website: www.philkonstrak.com</div>
                </div>
            </td>
            <td width="160" align="left"><img src="../img/pdc-iso.gif" width="125" height="153"></td>
          </tr>
        </table>
    </td>
  </tr>
  <tr>
    <td align="center"><div id="DTitle"><strong>'.$title.'</strong></div></td>
  </tr>
</table>
';
}

			print_header('FUEL PURCHASE ORDER');
			?>
<footer>
	<div align="right">18PMD.FRM033.00-10/18</div>
</footer>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
<script src='../js/jquery.dataTables.min.js'></script>
<script src="../js/excanvas.js"></script>
<script src="../js/jquery.flot.js"></script>
<script src="../js/jquery.flot.pie.js"></script>
<script src="../js/jquery.flot.stack.js"></script>
<script src="../js/jquery.flot.resize.min.js"></script>
<script src="../js/jquery.chosen.min.js"></script>
<script src="../js/jquery.uniform.min.js"></script>
<script src="../js/jquery.cleditor.min.js"></script>
<script src="../js/jquery.noty.js"></script>
<script src="../js/jquery.elfinder.min.js"></script>
<script src="../js/jquery.raty.min.js"></script>
<script src="../js/jquery.iphone.toggle.js"></script>
<script src="../js/jquery.uploadify-3.1.min.js"></script>
<script src="../js/jquery.gritter.min.js"></script>
<script src="../js/jquery.imagesloaded.js"></script>
<script src="../js/jquery.masonry.min.js"></script>
<script src="../js/jquery.knob.modified.js"></script>
<script src="../js/jquery.sparkline.min.js"></script>
<script src="../js/counter.js"></script>
<script src="../js/retina.js"></script>
<script src="../js/custom.js"></script>
<!-- end: JavaScript-->
</body>
</html>