<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$project_id = ( isset($_REQUEST['prjID']) && !empty($_REQUEST['prjID']) ) ? functions::decode($_REQUEST['prjID']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Project Billing Report</title>
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
    <!-- end: CSS -->
    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <link id="ie-style" href="../css/ie.css" rel="stylesheet">
    <![endif]-->
    <!--[if IE 9]>
    <link id="ie9style" href="../css/ie9.css" rel="stylesheet">
    <![endif]-->
    <!-- start: Favicon -->
    <link rel="shortcut icon" href="../img/favicon.png">
    <style>.padleft{padding-right: 5px; padding-left: 5px;}</style>
    <style>
        /* Style the header */
        .header {
            background: #CCC;
        }
        /* The sticky class is added to the header with JS when it reaches its scroll position */
        .sticky {
            position: fixed;
            top: 0;
            width: 97%
        }
        .hideit{
            display:none;
        }
        .brdrNone{
            border:none;
        }
    </style>
    <!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header">
            <h2><i class="halflings-icon white th"></i><span class="break"></span>Advances from Customers/Recoupment</h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li class="active"><a href="acctng-proj-ledger-advance.php" style="opacity:.9">Advances</a></li>
                <li><a href="acctng-proj-ledger-collect.php">Collected</a></li>
                <li><a href="acctng-proj-ledger-bill.php">Billing</a></li>
            </ul>
        </div>
        <table class="table-hover" width="100%" border="1" style="font-size:12px;">
            <thead>
                <tr style="background-color:#CCC">
                    <td class="padleft" width="7%" height="35px;"><div align="left"><strong>Collected</strong></div></td>
                    <td class="padleft"><strong>Project</strong></td>
                    <td class="padleft" width="10%"><div align="left"><strong>Progress Billing</strong></div></td>
                    <td class="padleft" width="7%"><div align="right"><strong>Debit</strong></div></td>
                    <td class="padleft" width="7%"><div align="right"><strong>Credit</strong></div></td>
                    <td class="padleft" width="7%"><div align="right"><strong>Balance</strong></div></td>
                </tr>

                <tr class="header" id="myHeader">
                    <td class="padleft" width="7%" height="35px;"><div align="left"><strong>Collected</strong></div></td>
                    <td class="padleft" width="65%"><strong>Project</strong></td>
                    <td class="padleft" width="10%"><div align="left"><strong>Progress Billing</strong></div></td>
                    <td class="padleft" width="7%"><div align="right"><strong>Debit</strong></div></td>
                    <td class="padleft" width="8%"><div align="right"><strong>Credit</strong></div></td>
                    <td class="padleft" width="9%"><div align="right"><strong>Balance</strong></div></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </thead>
            <tbody>
            <?php
            $qPI = $db->select('project_income pi, project p','*',array(),'WHERE p.proj_id=pi.proj_id AND lower(name) != "retention payment" AND pi_date != "" ORDER BY pi_date ASC');
            #$qPI = $db->select('project_income pi, project p','*',array('p.proj_id'=>'6'),'AND p.proj_id=pi.proj_id AND lower(name) != "retention payment" AND pi_date != "" ORDER BY pi_date ASC');
            #echo $db->last_query;

            $credit=0; $debit=0; $total_credit=0; $total_debit=0; $balance=0;
            while($rPI = $db->fetch_array($qPI)):
                $credit=0; $debit=0; 
                if($rPI['name']=='Advance Payment')
                    $credit=$rPI['amount'];
                else
                    $debit=$rPI['recoupment'];

                $balance = ($balance + $credit) - $debit;
                $total_credit+=$credit;
                $total_debit+=$debit;
            ?>
                <tr>
                    <td class="padleft" height="23"><?php echo functions::datearr($rPI['pi_date']);?></td>
                    <td class="padleft"><a id="view<?php echo $rPI['pi_id']?>" class="thickbox" title="Modify Billing ID" data-rel="tooltip" style="cursor:pointer;" onclick="showThis(this.id,'dash-proj-billing-project.php?prjID=<?php echo functions::encode($rPI['proj_id'])?>','Statement Details','1')"><?php echo $rPI['proj_name'];?></a></td>
                    <td class="padleft"><?php echo $rPI['name'];?></td>
                    <td class="padleft"><div align="right"><?php echo ($debit) ? functions::formatMoney($debit) : '';?></div></td>
                    <td class="padleft"><div align="right"><?php echo ($credit) ? functions::formatMoney($credit) : '';?></div></td>
                    <td class="padleft"><div align="right"><?php echo functions::formatMoney($balance);?></div></td>
                </tr>
            <?php endwhile;?>
                <tr>
                    <td height="30">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="padleft"><strong><?php #echo functions::formatMoney($totalAmountBilled);?></strong></td>
                    <td class="padleft"><div align="right"><strong><?php echo ($total_debit) ? functions::formatMoney($total_debit) : '';?></strong></div></td>
                    <td class="padleft"><div align="right"><strong><?php echo ($total_credit) ? functions::formatMoney($total_credit) : '';?></strong></div></td>
                    <td class="padleft"><div align="right"><strong><?php echo functions::formatMoney($total_credit-$total_debit);?></strong></div></td>
                </tr>
                <tr>
                    <td colspan="6">&nbsp;</td>
                </tr>
            </tbody>
        </table>
    </div><!--/span-->
</div><!--/row--> 
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
<script src='../js/fullcalendar.min.js'></script>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<!-- end: JavaScript-->
<script>
// When the user scrolls the page, execute myFunction
window.onload = function(){header.classList.add("hideit");};
window.onscroll = function() {myFunction()};

// Get the header
var header = document.getElementById("myHeader");

// Get the offset position of the navbar
var sticky = header.offsetTop;

// Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
function myFunction() {
    if (window.pageYOffset > sticky) {
        header.classList.add("sticky");
        header.classList.remove("hideit");
    } else {
        header.classList.remove("sticky");
        header.classList.add("hideit");
    }
}
</script>
</body>
</html>