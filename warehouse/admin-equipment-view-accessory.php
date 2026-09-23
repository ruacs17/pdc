<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="10" ){
  header("Location: ../");
  die();
}
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
unset($_SESSION['arr_proj']);
if( isset($_REQUEST['txItmDel']) && !empty($_REQUEST['txItmDel']) ){
  $er_idDel = functions::decode($_REQUEST['txItmDel']);
  $db->delete('equip_repair',array('er_id'=>$er_idDel,'equip_id'=>$equip_id));
  functions::sendTo($_SERVER['PHP_SELF'].'?vdidVw='.functions::encode($equip_id));
}

$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';

if( isset($_POST['btnSearch']) ){
    $arr = array();
    $_SESSION['eac_proj'] = (isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
    $_SESSION['eac_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    $_SESSION['eac_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $_SESSION['eac_day'] = (isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
    $_SESSION['eac_mnTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $_POST['bdMonTo'] : '';
    $_SESSION['eac_yrTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $_POST['bdYearTo'] : '';
    $_SESSION['eac_dayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $_POST['bdDayTo'] : '';
}

$txProj = ( isset($_SESSION['eac_proj']) ) ? $_SESSION['eac_proj'] : '';
$txbYear = ( isset($_SESSION['eac_yr']) ) ? $_SESSION['eac_yr'] : date('Y');
$txbMon = ( isset($_SESSION['eac_mn']) ) ? $_SESSION['eac_mn'] : date('m');
$txbDay = ( isset($_SESSION['eac_day']) ) ? $_SESSION['eac_day'] : date('d');

$txbYearTo = ( isset($_SESSION['eac_yrTo']) ) ? $_SESSION['eac_yrTo'] : date('Y');
$txbMonTo = ( isset($_SESSION['eac_mnTo']) ) ? $_SESSION['eac_mnTo'] : date('m');
$txbDayTo = ( isset($_SESSION['eac_dayTo']) ) ? $_SESSION['eac_dayTo'] : date('d');

$where = ''; 
    if( $txbMon && $txbYear && $txbDay && $txbMonTo && $txbYearTo && $txbDayTo)
        $where .=' AND (ea_date BETWEEN "'.$txbYear.'-'.$txbMon.'-'.$txbDay.'" AND "'.$txbYearTo.'-'.$txbMonTo.'-'.$txbDayTo.'")';
    else if($txbMon && $txbYear && $txbMonTo && $txbYearTo)
        $where .=' AND ( LEFT(ea_date,7) >= "'.$txbYear.'-'.$txbMon.'" AND LEFT(ea_date,7) <= "'.$txbYearTo.'-'.$txbMonTo.'")';
    else if($txbMon && $txbMonTo)
        $where .=' AND (SUBSTRING(ea_date,6,2)>="'.$txbMon.'" AND SUBSTRING(ea_date,6,2) <= "'.$txbMonTo.'")';
    else if($txbYear && $txbYearTo)
        $where .=' AND (LEFT(ea_date,4) >= "'.$txbYear.'" AND LEFT(ea_date,4) <= "'.$txbYear.'")';
    else if($txbMon && $txbYear && $txbDay)
        $where .=' AND ea_date="'.$txbYear.'-'.$txbMon.'-'.$txbDay.'"';

    if($txProj){
            $where .=' AND proj_id="'.$db->clean($txProj).'"';
    }
$qShow = $db->select('equip_accessory','*',array('equip_id'=>$equip_id),$where.' ORDER BY ea_date DESC');

$arrChargeList=array();
$qProj = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM equip_accessory WHERE equip_id="'.$db->clean($equip_id).'")');
while($rProj = $db->fetch_array($qProj)):
    if($rProj['proj_id'])
    $arrChargeList[$rProj['proj_name']]=$rProj['proj_id'];
endwhile;
ksort($arrChargeList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Equipment ACCESSORY Detail</title>
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
<form method="post">
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>EQUIPMENT ACCESSORY DETAIL</h2>
        </div>
    <div class="box-content">
        <ul class="nav tab-menu nav-tabs">
          <li class="active"><a href="#" style="opacity:.9">Accessory</a></li>
          <li><a href="admin-equipment-view.php?vdidVw=<?php echo functions::encode($equip_id);?>">Detail</a></li>
        </ul>
    </div>
    <table width="100%" border="0">
      <tr>
        <td width="50%">
              <div align="left">
                    &nbsp;Equipment: <strong><?php echo $db->getValue('equipment','equip_desc',array('equip_id'=>$equip_id));?></strong>
              </div>
        </td>
      </tr>
    </table><br>
    <table border="0" style="display: none;">
        <tr>
            <td width="20%">
                <div align="center"> 
                    <div align="center"><strong>FROM</strong></div>
                        <select name="bdYear" id="bdYear" style="width:80px;">
                            <option value="">All Year</option>
                            <?php
                            $qYr = $db->select('equip_accessory','DISTINCT LEFT(ea_date,4) as yr',array(),'ORDER BY ea_date DESC');
                            while($rYr = $db->fetch_array($qYr)):
                            ?>
                            <option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                            <?php endwhile;?>
                        </select>
                        <select name="bdMon" id="bdMon" style="width:95px;">
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
                            <option value="<?php echo $i;?>" <?php if($i==$txbDay)echo 'selected="selected"';?>><?php echo $i;?></option>
                            <?php endfor;?>
                        </select>
                </div>
                <div align="center">
                    <div align="center"><strong>TO</strong></div>
                        <select name="bdYearTo" id="bdYearTo" style="width:80px;">
                            <option value="">All Year</option>
                            <?php
                            $qYr = $db->select('equip_accessory','DISTINCT LEFT(ea_date,4) as yr',array(),'ORDER BY ea_date DESC');
                            while($rYr = $db->fetch_array($qYr)):
                            ?>
                            <option value="<?php echo $rYr['yr']?>" <?php if($txbYearTo==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                            <?php endwhile;?>
                        </select>
                        <select name="bdMonTo" id="bdMonTo" style="width:95px;">
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
                            <option value="<?php echo $i;?>" <?php if($i==$txbDayTo)echo 'selected="selected"';?>><?php echo $i;?></option>
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
    <div class="box-content">
        <table class="table table-hover table-bordered" style="font-size:12px;" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <th width="7%" scope="col"><div align="center">Date</div></th>
            <th width="17%" scope="col"><div align="center">Item</div></th>
            <th width="5%" scope="col"><div align="center">Remarks</div></th>
          </tr>
              <?php
                while($rShow = $db->fetch_array($qShow)):
              ?>
          <tr>
            <td><?php echo functions::datearr($rShow['ea_date'])?></td>
            <td><?php echo $rShow['description']?></td>
            <td><?php echo $rShow['remarks']?></td>
          </tr>
            <?php endwhile;?>
        </table>
    </div><!--/span-->
</div><!--/row-->
</form>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src='../js/jquery.dataTables.min.js'></script>
<!-- end: JavaScript-->
</body>
</html>