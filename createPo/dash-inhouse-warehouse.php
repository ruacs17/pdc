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
$selYear = (isset($_REQUEST['yr']) && !empty($_REQUEST['yr']) ) ? functions::decode($_REQUEST['yr']) : date('Y');

$selMonFrom='';$selDayFrom='';$selYrFrom='';$selMonTo='';$selDayTo='';$selYrTo='';


if( isset($_POST['btnSearch']) ){
    $_SESSION['ih_mon_from'] = ( isset($_POST['selMonFrom']) && !empty($_POST['selMonFrom']) ) ? $db->clean($_POST['selMonFrom']) : '';
    $_SESSION['ih_day_from'] = ( isset($_POST['selDayFrom']) && !empty($_POST['selDayFrom']) ) ? $db->clean($_POST['selDayFrom']) : '';
    $_SESSION['ih_yr_from'] = ( isset($_POST['selYrFrom']) && !empty($_POST['selYrFrom']) ) ? $db->clean($_POST['selYrFrom']) : '';
    $_SESSION['ih_mon_to'] = ( isset($_POST['selMonTo']) && !empty($_POST['selMonTo']) ) ? $db->clean($_POST['selMonTo']) : '';
    $_SESSION['ih_day_to'] = ( isset($_POST['selDayTo']) && !empty($_POST['selDayTo']) ) ? $db->clean($_POST['selDayTo']) : '';
    $_SESSION['ih_yr_to'] = ( isset($_POST['selYrTo']) && !empty($_POST['selYrTo']) ) ? $db->clean($_POST['selYrTo']) : '';
    functions::sendTo($_SERVER['PHP_SELF']);
    die();
}
$selMonFrom = ( isset($_SESSION['ih_mon_from']) ) ? $_SESSION['ih_mon_from']: date('m');
$selDayFrom = ( isset($_SESSION['ih_day_from']) ) ? $_SESSION['ih_day_from']: date('d');
$selYrFrom = ( isset($_SESSION['ih_yr_from']) ) ? $_SESSION['ih_yr_from']: date('Y');

$selMonTo = ( isset($_SESSION['ih_mon_to']) ) ? $_SESSION['ih_mon_to']: date('m');
$selDayTo = ( isset($_SESSION['ih_day_to']) ) ? $_SESSION['ih_day_to']: date('d');
$selYrTo = ( isset($_SESSION['ih_yr_to']) ) ? $_SESSION['ih_yr_to']: date('Y');

$qYear='';
if( $selMonFrom && $selDayFrom && $selYrFrom && $selMonTo && $selDayTo && $selYrTo)
    $qYear =' AND (im_date BETWEEN "'.$selYrFrom.'-'.$selMonFrom.'-'.$selDayFrom.'" AND "'.$selYrTo.'-'.$selMonTo.'-'.$selDayTo.'")';
else if($selMonFrom && $selYrFrom && $selMonTo && $selYrTo)
    $qYear =' AND ( LEFT(im_date,7) >= "'.$selYrFrom.'-'.$selMonFrom.'" AND LEFT(im_date,7) <= "'.$selYrTo.'-'.$selMonTo.'")';
else if($selMonFrom && $selMonTo)
    $qYear =' AND (SUBSTRING(im_date,6,2)>="'.$selMonFrom.'" AND SUBSTRING(im_date,6,2) <= "'.$selMonTo.'")';
else if($selYrFrom && $selYrTo)
    $qYear =' AND (LEFT(im_date,4) >= "'.$selYrFrom.'" AND LEFT(im_date,4) <= "'.$selYrTo.'")';
else if($selMonFrom && $selYrFrom && $selDayFrom)
    $qYear =' AND im_date="'.$selYrFrom.'-'.$selMonFrom.'-'.$selDayFrom.'"';


$count=0;

$arrChargeList=array();
$qProj = $db->query('SELECT * FROM inhouse_material im, project p WHERE im.proj_id=p.proj_id '.$qYear.' ORDER BY proj_name');
while($rProj = $db->fetch_array($qProj)):
    $arrChargeList[strtoupper($rProj['proj_name'])]=$rProj['proj_id'];
endwhile;

$qPayee = $db->query('SELECT DISTINCT payee FROM inhouse_material WHERE payee != "" '.$qYear.' ORDER BY payee');
while($rPayee = $db->fetch_array($qPayee)):
    $arrChargeList[strtoupper($rPayee['payee'])]=$rPayee['payee'];
endwhile;
ksort($arrChargeList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>In-house Warehouse Report</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
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
    <!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>In-house Warehouse Report</h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li><a href="dash-inhouse-equip-leasing.php">Equipment Leasing</a></li>
                <li class="active"><a href="#">Warehouse</a></li>
            </ul>
        </div>
        <div align="center"><br>
            <form method="post">
            <table width="70%" border="0">
                <tr>
                    <td width="40%"><div align="center"><strong>Date From</strong></div></td>
                    <td width="40%"><div align="center"><strong>Date To</strong></div></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td style="padding: 12px 0px 4px 0px">
                        <div align="center">
                            <select name="selYrFrom" id="selYrFrom" style="width:90px;">
                                <option value="">All Year</option>
                                <?php
                                $qYr = $db->query('SELECT DISTINCT left(im_date,4) as yr FROM inhouse_material ORDER BY im_date DESC ');
                                while($rYr = $db->fetch_array($qYr)):
                                ?>
                                <option value="<?php echo $rYr['yr']?>" <?php if($selYrFrom==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                                <?php endwhile;?>
                            </select>
                            <select name="selMonFrom" id="selMonFrom" style="width:95px;">
                                <option value="">All Month</option>
                                <option value="01" <?php if($selMonFrom=='01')echo 'selected="selected"';?>>Jan</option>
                                <option value="02" <?php if($selMonFrom=='02')echo 'selected="selected"';?>>Feb</option>
                                <option value="03" <?php if($selMonFrom=='03')echo 'selected="selected"';?>>Mar</option>
                                <option value="04" <?php if($selMonFrom=='04')echo 'selected="selected"';?>>Apr</option>
                                <option value="05" <?php if($selMonFrom=='05')echo 'selected="selected"';?>>May</option>
                                <option value="06" <?php if($selMonFrom=='06')echo 'selected="selected"';?>>Jun</option>
                                <option value="07" <?php if($selMonFrom=='07')echo 'selected="selected"';?>>Jul</option>
                                <option value="08" <?php if($selMonFrom=='08')echo 'selected="selected"';?>>Aug</option>
                                <option value="09" <?php if($selMonFrom=='09')echo 'selected="selected"';?>>Sep</option>
                                <option value="10" <?php if($selMonFrom=='10')echo 'selected="selected"';?>>Oct</option>
                                <option value="11" <?php if($selMonFrom=='11')echo 'selected="selected"';?>>Nov</option>
                                <option value="12" <?php if($selMonFrom=='12')echo 'selected="selected"';?>>Dec</option>
                            </select>
                            <select name="selDayFrom" id="selDayFrom" style="width:60px;">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$selDayFrom)echo 'selected="selected"';?>><?php echo $i;?></option>
                                <?php endfor;?>
                            </select>
                        </div>
                    </td>
                    <td style="padding: 12px 0px 4px 0px">
                        <div align="center">
                            <select name="selYrTo" id="selYrTo" style="width:90px;">
                                <option value="">All Year</option>
                                <?php
                                $qYr = $db->query('SELECT DISTINCT left(im_date,4) as yr FROM inhouse_material ORDER BY im_date DESC ');
                                while($rYr = $db->fetch_array($qYr)):
                                ?>
                                <option value="<?php echo $rYr['yr']?>" <?php if($selYrTo==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                                <?php endwhile;?>
                            </select>
                            <select name="selMonTo" id="selMonTo" style="width:95px;">
                                <option value="">All Month</option>
                                <option value="01" <?php if($selMonTo=='01')echo 'selected="selected"';?>>Jan</option>
                                <option value="02" <?php if($selMonTo=='02')echo 'selected="selected"';?>>Feb</option>
                                <option value="03" <?php if($selMonTo=='03')echo 'selected="selected"';?>>Mar</option>
                                <option value="04" <?php if($selMonTo=='04')echo 'selected="selected"';?>>Apr</option>
                                <option value="05" <?php if($selMonTo=='05')echo 'selected="selected"';?>>May</option>
                                <option value="06" <?php if($selMonTo=='06')echo 'selected="selected"';?>>Jun</option>
                                <option value="07" <?php if($selMonTo=='07')echo 'selected="selected"';?>>Jul</option>
                                <option value="08" <?php if($selMonTo=='08')echo 'selected="selected"';?>>Aug</option>
                                <option value="09" <?php if($selMonTo=='09')echo 'selected="selected"';?>>Sep</option>
                                <option value="10" <?php if($selMonTo=='10')echo 'selected="selected"';?>>Oct</option>
                                <option value="11" <?php if($selMonTo=='11')echo 'selected="selected"';?>>Nov</option>
                                <option value="12" <?php if($selMonTo=='12')echo 'selected="selected"';?>>Dec</option>
                            </select>
                            <select name="selDayTo" id="selDayTo" style="width:60px;">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$selDayTo)echo 'selected="selected"';?>><?php echo $i;?></option>
                                <?php endfor;?>
                            </select>
                        </div>
                    </td>
                    <td><input type="submit" class="btn btn-info" name="btnSearch" id="btnSearch" value="Search"></td>
                </tr>
            </table><br>
            </form>
        </div>
        <div class="box-content" align="center">
            <form method="post">
                <table width="50%" align="center" border="1" class="table table-bordered table-hover">
                    <tr>
                     	  <td width="50%" height="30"><strong>Project / Payee</strong></td>
                        <td width="10%"><strong>Paid</strong></td>
                        <td width="10%"><strong>Unpaid</strong></td>
                        <td width="15%"><strong>Total</strong></td>
                    </tr>
                    <?php
                        $curMonth='';$totalCost=0;$totalPaid=0;$totalUnpaid=0;$monthlyCost=0;$cost=0;$costPayee=0;$rank=0;
                    ?>
                    <tr>
                        <td height="30">&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php
                        foreach($arrChargeList as $name => $id):

                        $qCost = $db->query('SELECT sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ) as res FROM inhouse_material im, inhouse_material_item imi where im.im_id=imi.im_id and im.proj_id="'.$db->clean($id).'"'.$qYear);
                        $cost = $db->result($qCost,0);

                        $qCostPayee = $db->query('SELECT sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ) as res FROM inhouse_material im, inhouse_material_item imi where im.im_id=imi.im_id and im.payee="'.$db->clean($id).'"'.$qYear);
                        $costPayee = $db->result($qCostPayee,0);

                        $allCost = $cost + $costPayee;
                        $totalCost += $allCost;

                        $qPaid = $db->query('SELECT sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ) as res FROM inhouse_material im, inhouse_material_item imi where im.im_id=imi.im_id and im.proj_id="'.$db->clean($id).'" AND is_paid="2"'.$qYear);
                        $paid = $db->result($qPaid,0);

                        $qPaidPayee = $db->query('SELECT sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ) as res FROM inhouse_material im, inhouse_material_item imi where im.im_id=imi.im_id and im.payee="'.$db->clean($id).'" AND is_paid="2"'.$qYear);
                        $paidPayee = $db->result($qPaidPayee,0);

                        $allPaid = $paid + $paidPayee;
                        $totalPaid += $allPaid;

                        $qUnpaid = $db->query('SELECT sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ) as res FROM inhouse_material im, inhouse_material_item imi where im.im_id=imi.im_id and im.proj_id="'.$db->clean($id).'" AND is_paid="1"'.$qYear);
                        $unpaid = $db->result($qUnpaid,0);

                        $qUnpaidPayee = $db->query('SELECT sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ) as res FROM inhouse_material im, inhouse_material_item imi where im.im_id=imi.im_id and im.payee="'.$db->clean($id).'" AND is_paid="1"'.$qYear);
                        $unpaidPayee = $db->result($qUnpaidPayee,0);

                        $allUnpaid = $unpaid + $unpaidPayee;
                        $totalUnpaid += $allUnpaid;

                        $count++;
                        $pname = $db->getValue('project','proj_name',array('proj_id'=>$id));
                        $reqst = 'prjID='.functions::encode($id);
                        #$reqst = 'prjID='.functions::encode($id).'&yr='.functions::encode($selYear).'&yrFrm='.functions::encode($selYrFrom).'&mnFrm='.functions::encode($selMonFrom).'&dayFrm='.functions::encode($selDayFrom).'&yrTo='.functions::encode($selYrTo).'&mnTo='.functions::encode($selMonTo).'&dayTo='.functions::encode($selDayTo);
					?>  
                    <tr>
                        <td height="30"><strong><?php echo ($pname) ? $pname : $db->getValue('inhouse_material','payee',array('payee'=>$id));?></strong></td>
                        <td><a id="detailPaid<?php echo $count?>" class="label label-info thickbox" title="View Paid Warehouse Details" data-rel="tooltip" onclick="showThis(this.id,'dash-inhouse-warehouse-detail.php?<?php echo $reqst?>&paidStat=2','Inhouse Paid Details','1')"><?php echo functions::formatMoney($allPaid);?></a></td>
                        <td><a id="detailUnpaid<?php echo $count?>" class="label label-info thickbox" title="View UnPaid Warehouse Details" data-rel="tooltip" onclick="showThis(this.id,'dash-inhouse-warehouse-detail.php?<?php echo $reqst?>&paidStat=1','Inhouse Paid Details','1')"><?php echo functions::formatMoney($allUnpaid);?></a></td>
                        <td><a id="detailAll<?php echo $count?>" class="label label-info thickbox" title="View Paid and Unpaid Warehouse Details" data-rel="tooltip" onclick="showThis(this.id,'dash-inhouse-warehouse-detail.php?<?php echo $reqst?>','Inhouse Details','1')"><?php echo functions::formatMoney($allCost);?></a></td>
                    </tr>
                    <?php endforeach; ?> 
                    <?php 
                        $monthlyCost=0;
                    ?>
                    <tr>
                        <td height="30"><div align="right"><strong>Total </strong></div></td>
                        <td><strong><?php echo functions::formatMoney($totalPaid);?></strong></td>
                        <td><strong><?php echo functions::formatMoney($totalUnpaid);?></strong></td>
                        <td><strong><?php echo functions::formatMoney($totalCost);?></strong></td>
                    </tr>
                </table>
            </form>
        </div>
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
<script>function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?yr="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>