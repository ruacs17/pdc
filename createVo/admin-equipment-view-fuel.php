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
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;

$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';

if( isset($_POST['btnSearch']) ){
    $arr = array();
    $_SESSION['efo_proj'] = (isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
    $_SESSION['efo_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    $_SESSION['efo_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $_SESSION['efo_day'] = (isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
    $_SESSION['efo_mnTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $_POST['bdMonTo'] : '';
    $_SESSION['efo_yrTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $_POST['bdYearTo'] : '';
    $_SESSION['efo_dayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $_POST['bdDayTo'] : '';
}

$txProj = ( isset($_SESSION['efo_proj']) ) ? $_SESSION['efo_proj'] : '';
$txbYear = ( isset($_SESSION['efo_yr']) ) ? $_SESSION['efo_yr'] : date('Y');
$txbMon = ( isset($_SESSION['efo_mn']) ) ? $_SESSION['efo_mn'] : date('m');
$txbDay = ( isset($_SESSION['efo_day']) ) ? $_SESSION['efo_day'] : date('d');

$txbYearTo = ( isset($_SESSION['efo_yrTo']) ) ? $_SESSION['efo_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['efo_mnTo']) ) ? $_SESSION['efo_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['efo_dayTo']) ) ? $_SESSION['efo_dayTo'] : date('d');
$count=0;
$totalUnpaidAmount=0;
$totalAmount=0;
$totalAmountFuel=0;
$totalAmountOil=0;
$totalFuelLiter=0;
$totalOilLiter=0;
$arrFPO = array();

$where = ''; 
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
    $where .=' AND (po_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
    $where .=' AND ( LEFT(po_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(po_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
    $where .=' AND (SUBSTRING(po_date,6,2)>="'.$txbMon.'" AND SUBSTRING(po_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
    $where .=' AND (LEFT(po_date,4) >= "'.$txbYear.'" AND LEFT(po_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
    $where .=' AND po_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj){
    if( $db->getValue('po','count(*)',array('proj_id'=>$txProj)) )
        $where .=' AND p.proj_id="'.$db->clean($txProj).'"';
    else
        $where .=' AND pf.payee="'.$db->clean($txProj).'"';
}

$qPO = $db->query('SELECT * FROM po p, po_fuel pf, po_item poi, po_fuel_equipment pfe WHERE p.po_id=poi.po_id AND p.po_id=pf.po_id AND p.po_id=pfe.po_id AND pfe.po_item_id=poi.po_item_id AND po_type="fuel" AND pfe.equip_id="'.$db->clean($equip_id).'" '.$where.' ORDER BY po_date DESC');
while($rPO = $db->fetch_array($qPO)):
    $count++;
    $payee = $db->getValue('po_fuel','payee',array('po_id'=>$rPO['po_id']));
    $amountFuel = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%fuel%"');
    $totalAmountFuel += $amountFuel;

    $fuelLiter = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%fuel%"');
    $totalFuelLiter += $fuelLiter;
    $fuelPrice = $db->getValue('po_item','cost',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%fuel%"');
    $fuelLiter = (functions::isfloat($fuelLiter)) ? functions::formatMoney($fuelLiter) : $fuelLiter;

    $oilLiter = $db->getValue('po_item','sum(quantity)',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%oil%"');
    $totalOilLiter += $oilLiter;
    $oilLiter = (functions::isfloat($oilLiter)) ? functions::formatMoney($oilLiter) : $oilLiter;
    $oilPrice = $db->getValue('po_item','cost',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%oil%"');

    $amountOil = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']),'AND item LIKE "%oil%"');
    $totalAmountOil += $amountOil;

    $amount = $db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']));
    $totalAmount += $amount;
    $amount = ($amount) ? $amount : $db->getValue('po_item','sum( cost - (cost * (discount/100)) )',array('po_id'=>$rPO['po_id'],'po_item_id'=>$rPO['po_item_id']));
    $arrFPO[] = array('po_no'=>$rPO['po_no'],'po_date'=>$rPO['po_date'],'invoice'=>$rPO['invoice'],'proj_id'=>$rPO['proj_id'],'payee'=>$payee,'fuelLiter'=>$fuelLiter,'fuelPrice'=>$fuelPrice,'oilLiter'=>$oilLiter,'oilPrice'=>$oilPrice,'amount'=>$amount,'tranType'=>'po');
endwhile;

$whereVoucher = ''; 
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
    $whereVoucher .=' AND (vd_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
    $whereVoucher .=' AND ( LEFT(vd_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(vd_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
    $whereVoucher .=' AND (SUBSTRING(vd_date,6,2)>="'.$txbMon.'" AND SUBSTRING(vd_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
    $whereVoucher .=' AND (LEFT(vd_date,4) >= "'.$txbYear.'" AND LEFT(vd_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
    $whereVoucher .=' AND vd_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj)
    $whereVoucher .=' AND proj_id="'.$db->clean($txProj).'"';

$qFuelVoucher = $db->query('SELECT * FROM equip_fuel ef, voucher_detail vd WHERE vd.ef_id=ef.ef_id AND ef.equip_id="'.$db->clean($equip_id).'" '.$whereVoucher.' ORDER BY vd_date DESC');
while( $rFV = $db->fetch_array($qFuelVoucher)):
    $count++;
    $oilPrice=0;
    $fuelPrice=0;
    $voucher_id = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$rFV['vp_id']));
    $voucher_no = $db->getValue('voucher','voucher_no',array('voucher_id'=>$voucher_id));
    $amount = $rFV['amount_issue'];
    $totalAmount += $amount;
    if( $db->getValue('equip_fuel','count(*)',array('ef_id'=>$rFV['ef_id']),'AND remarks LIKE "%oil%"') )
        $oilPrice = $amount;
    else
        $fuelPrice = $amount;
    $totalAmountOil += $oilPrice;
    $totalAmountFuel += $fuelPrice;
    $arrFPO[] = array('po_no'=>$voucher_no,'po_date'=>$rFV['vd_date'],'invoice'=>'','proj_id'=>$rFV['proj_id'],'payee'=>'','fuelLiter'=>'','fuelPrice'=>$fuelPrice,'oilLiter'=>'','oilPrice'=>$oilPrice,'amount'=>$amount,'tranType'=>'voucher');
endwhile;


#inhouse
$whereInhouse = ''; 
if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
    $whereInhouse .=' AND (im_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
    $whereInhouse .=' AND ( LEFT(im_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(im_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
else if($txbMon && $txbMonTo)
    $whereInhouse .=' AND (SUBSTRING(im_date,6,2)>="'.$txbMon.'" AND SUBSTRING(im_date,6,2) <= "'.$txbMonTo.'")';
else if($txbYear && $txbYearTo)
    $whereInhouse .=' AND (LEFT(im_date,4) >= "'.$txbYear.'" AND LEFT(im_date,4) <= "'.$txbYear.'")';
else if($txbMon && $txbYear && $txbDay)
    $whereInhouse .=' AND im_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj)
    $whereInhouse .=' AND proj_id="'.$db->clean($txProj).'"';

$qFuelInhouse = $db->query('SELECT * FROM inhouse_material WHERE im_id IN (SELECT DISTINCT im_id FROM  inhouse_material_fuel_equipment WHERE equip_id="'.$db->clean($equip_id).'") '.$whereInhouse);
while( $rFI = $db->fetch_array($qFuelInhouse)):
    $count++;
    
    $fuelPrice=0;
    $fuelPriceArr=array();
    $amountFuel=0;
    $fuelLiter=0;

    $oilLiter=0;
    $amountOil=0;
    $oilPrice=0;
    $oilPriceArr=array();

    $dt = $rFI['im_date'];
    $expDate = explode('-', $dt);
    $no = (isset($expDate[0]) && isset($expDate[1]) ) ? $expDate[0].$expDate[1].$rFI['im_id'] : '';
    $payee = $rFI['payee'];

    $qFIFuel = $db->select('inhouse_material_item imi, inhouse_material_fuel_equipment imfe','*',array('imi.im_id'=>$rFI['im_id'],'equip_id'=>$equip_id),'AND imi.imi_id=imfe.imi_id AND item LIKE "%fuel%"');
    while($rFIFuel = $db->fetch_array($qFIFuel)):
        $amountFuel += ($rFIFuel['quantity'] * $rFIFuel['cost']) - ( ($rFIFuel['quantity'] * $rFIFuel['cost']) * ($rFIFuel['discount']/100) );
        $fuelLiter += $rFIFuel['quantity'];
        $fuelPriceArr[] = $rFIFuel['cost'];
    endwhile;

    $qFIOil = $db->select('inhouse_material_item imi, inhouse_material_fuel_equipment imfe','*',array('imi.im_id'=>$rFI['im_id'],'equip_id'=>$equip_id),'AND imi.imi_id=imfe.imi_id AND item LIKE "%oil%"');
    while($rFIOil = $db->fetch_array($qFIOil)):
        $amountOil += ($rFIOil['quantity'] * $rFIOil['cost']) - ( ($rFIOil['quantity'] * $rFIOil['cost']) * ($rFIOil['discount']/100) );
        $oilLiter += $rFIOil['quantity'];
        $oilPriceArr[] = $rFIOil['cost'];
    endwhile;
    $totalAmountFuel += $amountFuel;

    $totalFuelLiter += $fuelLiter;
    $fuelPrice = functions::average($fuelPriceArr);
    $fuelLiter = (functions::isfloat($fuelLiter)) ? functions::formatMoney($fuelLiter) : $fuelLiter;

    $totalOilLiter += $oilLiter;
    $oilLiter = (functions::isfloat($oilLiter)) ? functions::formatMoney($oilLiter) : $oilLiter;
    $oilPrice = $db->getValue('inhouse_material_item imi, inhouse_material_fuel_equipment imfe','avg(cost)',array('imi.im_id'=>$rFI['im_id'],'equip_id'=>$equip_id),'AND imi.imi_id=imfe.imi_id AND item LIKE "%oil%"');

    $totalAmountOil += $amountOil;

    $amount = $amountFuel + $amountOil;
    $totalAmount += $amount;

    $arrFPO[] = array('po_no'=>$no,'po_date'=>$rFI['im_date'],'invoice'=>$no,'proj_id'=>$rFI['proj_id'],'payee'=>$payee,'fuelLiter'=>$fuelLiter,'fuelPrice'=>$fuelPrice,'oilLiter'=>$oilLiter,'oilPrice'=>$oilPrice,'amount'=>$amount,'tranType'=>'inhouse');
endwhile;

if(count($arrFPO))
    functions::sortMultiArray($arrFPO,$orderBy='po_date',$ascDes='DESC');


$arrChargeList=array();
$qProj = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM po p, po_fuel_equipment pfe WHERE p.po_id=pfe.po_id AND equip_id="'.$db->clean($equip_id).'")');
while($rProj = $db->fetch_array($qProj)):
    if($rProj['proj_id'])
        $arrChargeList[$rProj['proj_name']]=$rProj['proj_id'];
endwhile;

$qProjV = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM equip_fuel ef, voucher_detail vd WHERE vd.ef_id=ef.ef_id AND ef.equip_id="'.$db->clean($equip_id).'")');
while($rProjV = $db->fetch_array($qProjV)):
    if($rProjV['proj_id']){
        if( !isset($arrChargeList[$rProjV['proj_name']]) )
            $arrChargeList[$rProjV['proj_name']]=$rProjV['proj_id'];
    }
endwhile;

$qProjIH = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM inhouse_material im, inhouse_material_fuel_equipment imfe WHERE im.im_id=imfe.im_id AND equip_id="'.$db->clean($equip_id).'")');
while($rProjIH = $db->fetch_array($qProjIH)):
    if($rProjIH['proj_id'])
        $arrChargeList[$rProjIH['proj_name']]=$rProjIH['proj_id'];
endwhile;

$qPayee = $db->select('po_fuel','DISTINCT payee',array('equip_id'=>$equip_id));
while($rPayee = $db->fetch_array($qPayee)):
    if($rPayee['payee'])
        $arrChargeList[strtoupper($rPayee['payee'])]=$rPayee['payee'];
endwhile;
ksort($arrChargeList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Equipment Fuel Detail</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <script src="../js/formatCurrency.js"></script>
    <script src="../js/inputInt.js"></script>
    <script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
    <style>.tdSpace{padding: 4px 0px 4px 0px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>EQUIPMENT FUEL & OIL CONSUMPTION HISTORY</h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li><a href="admin-equipment-view-mr.php?vdidVw=<?php echo functions::encode($equip_id);?>">MR</a></li>
                <li class="active"><a href="#" style="opacity:.9">Fuel & Oil</a></li>
                <li><a href="admin-equipment-view-usage.php?vdidVw=<?php echo functions::encode($equip_id);?>">Usage</a></li>
                <li><a href="admin-equipment-view-accessory.php?vdidVw=<?php echo functions::encode($equip_id);?>">Accessory</a></li>
                <li><a href="admin-equipment-view-repair.php?vdidVw=<?php echo functions::encode($equip_id);?>">Repair</a></li>
                <li><a href="admin-equipment-view.php?vdidVw=<?php echo functions::encode($equip_id);?>">Detail</a></li>
            </ul>
        </div>
        <div align="right"><a id="mrPrint" href="admin-equipment-view-fuel-print.php?vdidVw=<?php echo functions::encode($equip_id);?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div>
        <table width="100%" border="0">
            <tr>
                <td width="50%">
                    <div align="left">&nbsp;Equipment: <strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong></div>
                </td>
            </tr>
        </table><br>
        <form method="post">
            <table border="0">
                <tr>
                    <td width="20%">
                        <div align="center"> 
                            <div align="center"><strong>FROM</strong></div>
                            <select name="bdYear" id="bdYear" style="width:85px;">
                                <option value="">All Year</option>
                                <?php
                                $qYr = $db->select('po','DISTINCT LEFT(po_date,4) as yr',array('po_type'=>'fuel'),'ORDER BY po_date DESC');
                                while($rYr = $db->fetch_array($qYr)):
                                ?>
                                <option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                                <?php endwhile;?>
                            </select>
                            <select name="bdMon" id="bdMon" style="width:85px;">
                                <option value="">All Month</option>
                                <option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
                                <option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
                                <option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
                                <option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
                                <option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
                                <option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
                                <option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
                                <option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
                                <option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
                                <option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
                                <option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
                                <option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
                            </select>
                            <select name="bdDay" id="bdDay" style="width:60px;">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDay)echo 'selected="selected"';?>><?php echo $i;?></option>
                                <?php endfor;?>
                            </select>
                        </div>
                        <div align="center">
                            <div align="center"><strong>TO</strong></div>
                            <select name="bdYearTo" id="bdYearTo" style="width:80px;">
                                <option value="">All Year</option>
                                <?php
                                $qYr = $db->select('po','DISTINCT LEFT(po_date,4) as yr',array('po_type'=>'fuel'),'ORDER BY po_date DESC');
                                while($rYr = $db->fetch_array($qYr)):
                                ?>
                                <option value="<?php echo $rYr['yr']?>" <?php if($txbYearTo==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                                <?php endwhile;?>
                            </select>
                            <select name="bdMonTo" id="bdMonTo" style="width:85px;">
                                <option value="">All Month</option>
                                <option value="01" <?php if($txbMonTo=='01')echo 'selected="selected"';?>>Jan</option>
                                <option value="02" <?php if($txbMonTo=='02')echo 'selected="selected"';?>>Feb</option>
                                <option value="03" <?php if($txbMonTo=='03')echo 'selected="selected"';?>>Mar</option>
                                <option value="04" <?php if($txbMonTo=='04')echo 'selected="selected"';?>>Apr</option>
                                <option value="05" <?php if($txbMonTo=='05')echo 'selected="selected"';?>>May</option>
                                <option value="06" <?php if($txbMonTo=='06')echo 'selected="selected"';?>>Jun</option>
                                <option value="07" <?php if($txbMonTo=='07')echo 'selected="selected"';?>>Jul</option>
                                <option value="08" <?php if($txbMonTo=='08')echo 'selected="selected"';?>>Aug</option>
                                <option value="09" <?php if($txbMonTo=='09')echo 'selected="selected"';?>>Sep</option>
                                <option value="10" <?php if($txbMonTo=='10')echo 'selected="selected"';?>>Oct</option>
                                <option value="11" <?php if($txbMonTo=='11')echo 'selected="selected"';?>>Nov</option>
                                <option value="12" <?php if($txbMonTo=='12')echo 'selected="selected"';?>>Dec</option>
                            </select>
                            <select name="bdDayTo" id="bdDayTo" style="width:60px;">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$txbDayTo)echo 'selected="selected"';?>><?php echo $i;?></option>
                                <?php endfor;?>
                            </select>
                        </div>
                    </td>
                    <td width="35%">
                        <div align="left">
                            <select name="selProj" id="selProj" data-rel="chosen" style="width:550px;">
                                <option value="">All Project / Payee</option>
                                <?php foreach($arrChargeList as $name => $id): ?>
                                <option value="<?php echo $id?>" <?php if($txProj===$id)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                    </td>
                    <td width="8%">
                        <div align="center">
                            <input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary">
                        </div>
                    </td>
                </tr>
                <tr><td colspan="4"><hr width="100%"></td></tr>
            </table>
        </form>             
        <div class="box-content">
            <table class="table table-bordered table-hover" style="font-size:12px">
                <thead>
                    <tr>
                        <th width="9%">P.O. No</th>
                        <th width="9%">P.O. Date</th>
                        <th width="9%">Invoice</th>
                        <th width="21%">Charge To</th>
                        <th width="10%"><div align="center">Fuel (Liter) / Price</div></th>
                        <th width="10%"><div align="center">Oil / Price</div></th>
                        <th width="10%">Cost</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($arrFPO as $rPO):?>
                    <tr>
                        <td>
                            <?php
                            if($rPO['tranType']=='voucher')
                                echo $rPO['po_no'].' <i>(voucher #)</i>';
                            elseif($rPO['tranType']=='inhouse')
                                echo $rPO['po_no'].' <i>(inhouse #)</i>';
                            else
                                echo $rPO['po_no'];
                            ?>
                        </td>
                        <td><?php echo functions::datearr($rPO['po_date']);?></td>
                        <td><?php echo $rPO['invoice'];?></td>
                        <td><?php echo ($rPO['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id'])) : $rPO['payee'].' <i>(outsider)</i>';?></td>
                        <td>
                            <div align="center">
                                <?php 
                                if($rPO['tranType']=='po' || $rPO['tranType']=='inhouse')
                                    echo ($rPO['fuelLiter']) ? $rPO['fuelLiter'].' @ '.functions::formatMoney($rPO['fuelPrice']) : '';
                                else
                                    echo ($rPO['fuelPrice']) ? functions::formatMoney($rPO['fuelPrice']) : '';
                                ?>
                            </div>
                        </td>
                        <td>
                            <div align="center">
                                <?php
                                if($rPO['tranType']=='po' || $rPO['tranType']=='inhouse')
                                    echo ($rPO['oilLiter']) ? $rPO['oilLiter'].' @ '.functions::formatMoney($rPO['oilPrice']) : '';
                                else
                                    echo ($rPO['oilPrice']) ? functions::formatMoney($rPO['oilPrice']) : '';
                                ?>
                            </div>
                        </td>
                        <td><?php echo ($rPO['amount']) ? functions::formatMoney($rPO['amount']) : '--';?></td>
                    </tr>
                <?php endforeach;
                if($count==0){
                    echo '<tr><td colspan="7"><div align="center"><strong>-- No result --</strong></div></td></tr>';
                }
                else{?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><div align="center"><strong><?php echo (functions::isfloat($totalFuelLiter)) ? functions::formatMoney($totalFuelLiter) : $totalFuelLiter; echo ' | '.functions::formatMoney($totalAmountFuel)?></strong></div></td>
                        <td><div align="center"><strong><?php echo (functions::isfloat($totalOilLiter)) ? functions::formatMoney($totalOilLiter) : $totalOilLiter; echo ' | '.functions::formatMoney($totalAmountOil)?></strong></div></td>
                        <td><strong><?php echo (functions::isfloat($totalAmount)) ? functions::formatMoney($totalAmount) : $totalAmount;?></strong></td>
                    </tr>
                <?php }?>
                </tbody>
            </table>
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
<!-- end: JavaScript-->
</body>
</html>