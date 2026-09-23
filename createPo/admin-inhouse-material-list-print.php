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

$txProj = ( isset($_SESSION['in_proj']) ) ? $_SESSION['in_proj'] : '';

$txbYear = ( isset($_SESSION['in_yr']) ) ? $_SESSION['in_yr'] : date('Y');
$txbMon = ( isset($_SESSION['in_mn']) ) ? $_SESSION['in_mn'] : date('m');
$txbDay = ( isset($_SESSION['in_day']) ) ? $_SESSION['in_day'] : date('d');

$txbYearTo = ( isset($_SESSION['in_yrTo']) ) ? $_SESSION['in_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['in_mnTo']) ) ? $_SESSION['in_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['in_dayTo']) ) ? $_SESSION['in_dayTo'] : date('d');

$where = 'WHERE 1'; 

    if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
        $where .=' AND (im_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
    else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
        $where .=' AND ( LEFT(im_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(im_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
    else if($txbMon && $txbMonTo)
        $where .=' AND (SUBSTRING(im_date,6,2)>="'.$txbMon.'" AND SUBSTRING(im_date,6,2) <= "'.$txbMonTo.'")';
    else if($txbYear && $txbYearTo)
        $where .=' AND (LEFT(im_date,4) >= "'.$txbYear.'" AND LEFT(im_date,4) <= "'.$txbYear.'")';
    else if($txbMon && $txbYear && $txbDay)
        $where .=' AND im_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

if($txProj){
    if( $db->getValue('inhouse_material','count(*)',array('proj_id'=>$txProj)) )
        $where .=' AND proj_id="'.$db->clean($txProj).'"';
    else
        $where .=' AND payee="'.$db->clean($txProj).'"';
}
$qList = $db->select('inhouse_material','*',array(),$where.' ORDER BY im_date DESC');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>Equipment Accessory Print</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
     <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <!-- end: CSS -->
    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <link id="ie-style" href="../css/ie.css" rel="stylesheet">
    <![endif]-->
    <!--[if IE 9]>
    <link id="ie9style" href="../css/ie9.css" rel="stylesheet">
    <![endif]-->
    <style type="text/css">
    .padParLeft{padding-left:50px;}
    .padAmLeft{padding-left:60px;}
    </style>
    <script>window.print()</script>
  </head>
  <body bgcolor="#FFFFFF">
  <!-- body content: start here-->
<table  width="700" border="0" align="center">
  <thead>
    <tr>
      <td>
      <?php 
      require_once('../class/print_header.php');
      print_header('WAREHOUSE');
      ?>
      </td>
    </tr>
    <tr>
      <td valign='bottom'>&nbsp;</td>
    </tr>
  </thead>
  <tbody>
  <tr>
    <td height="500" valign="top">
            <div class="box-content">
                <table class="table table-bordered" style="font-size:12px">
                    <thead>
                      <tr>
                        <th width="9%">Purchase Date</th>
                        <th width="35%">Project / Payee</th>
                        <th width="10%">Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                          <?php
                            $totalAmount=0;
                            while($rList = $db->fetch_array($qList)):
                                  $amount = $db->getValue('inhouse_material_item','sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) )',array('im_id'=>$rList['im_id']));
                                  $totalAmount += $amount;
                          ?>
                      <tr>
                        <td><?php echo functions::datearr($rList['im_date']);?></td>
                        <td><?php echo ($rList['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rList['proj_id'])) : $db->getValue('inhouse_material','payee',array('im_id'=>$rList['im_id']));?></td>
                        <td><?php echo functions::formatMoney($amount);?></td>
                      </tr>
                        <?php endwhile; ?>
                      <tr>
                        <td>&nbsp;</td>
                        <td><div align="right"><strong>Total Amount</strong></div></td>
                        <td><div align="left"><strong><?php echo functions::formatMoney($totalAmount);?></strong></div></td>
                      </tr>
                      </tbody>
                  </table>
      <!-- body content: end here-->
<!-- start: JavaScript-->
<!-- end: JavaScript-->
</body>
</html>