<?php require_once('templ_up.php');?>
<?php
$arr = array();
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$itemDel = (isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? functions::decode($_REQUEST['itemDel']) : 0;
$mon='';$txProj = '';$txPayee = ''; 
$txbMon = '';$txbYear = ''; $txbDay='';
$txbMonTo = '';$txbYearTo = ''; $txbDayTo='';
if( isset($_POST['btnSearch']) ){
    $arr = array();
    $_SESSION['in_proj'] = (isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
    $_SESSION['in_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    $_SESSION['in_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $_SESSION['in_day'] = (isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
    $_SESSION['in_mnTo'] = ( isset($_POST['bdMonTo']) && !empty($_POST['bdMonTo']) ) ? $_POST['bdMonTo'] : '';
    $_SESSION['in_yrTo'] = ( isset($_POST['bdYearTo']) && !empty($_POST['bdYearTo']) ) ? $_POST['bdYearTo'] : '';
    $_SESSION['in_dayTo'] = ( isset($_POST['bdDayTo']) && !empty($_POST['bdDayTo']) ) ? $_POST['bdDayTo'] : '';
}
if($itemDel){
    $amount = $db->getValue('inhouse_material_item','count(*)',array('im_id'=>$itemDel));
    if($amount == 0){
        $db->delete('inhouse_material',array('im_id'=>$itemDel));
        functions::sendTo($_SERVER['PHP_SELF']);
    }
}
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

if(count($arr)){
    $rowdisplay=1000;
    $startrow=0;
}
$qList = $db->select('inhouse_material','*',array(),$where.' ORDER BY im_date DESC');
$num_record = $db->getValue('inhouse_material','count(*)',$arr);


$arrFE = array();
$qFE = $db->select('inhouse_material_fuel_equipment','DISTINCT im_id',array());
while($rFE = $db->fetch_array($qFE)):
    $arrFE[$rFE['im_id']]=1;
endwhile;

$arrChargeList=array();
$qProj = $db->query('SELECT * FROM project WHERE proj_id IN (SELECT DISTINCT proj_id FROM inhouse_material) ORDER BY proj_name');
while($rProj = $db->fetch_array($qProj)):
    if($rProj['proj_id'])
    $arrChargeList[$rProj['proj_name']]=$rProj['proj_id'];
endwhile;

$qPayee = $db->query('SELECT DISTINCT payee FROM inhouse_material ORDER BY payee');
while($rPayee = $db->fetch_array($qPayee)):
    if($rPayee['payee'])
    $arrChargeList[strtoupper($rPayee['payee'])]=$rPayee['payee'];
endwhile;
ksort($arrChargeList);
?>
    <!-- body content: start here-->
      <form method="post">
          <div class="row-fluid">
                <div class="box span12">
                      <div class="box-header" data-original-title>
                                <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>WAREHOUSE STOCKS</h2>
                      </div>
                      <div class="box-content">
                            <table width="200" cellspacing="0" cellpadding="0" border='0' align="right">
                                <tr>
                                    <td width="10" height='30'><div style="background-color:#e6eb9c; width:20px;">&nbsp;</div></td>
                                    <td width="90"> Unpaid</td>
                                </tr>
                            </table>
                            <br><br>
                            <div align="right"><a id="adc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'admin-inhouse-material-add.php?','In-house Warehouse Stock')">Add New Order</a>&nbsp;&nbsp;<a id="adcd" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'admin-inhouse-material-add.php?type=fe','In-house Warehouse Stock')">Add New Order (Equipment Fuel)</a>&nbsp;&nbsp;<a id="mrPrint" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'admin-inhouse-material-list-print.php?','In-house Warehouse Stock')"><i class="halflings-icon white print"></i></a>&nbsp;&nbsp;&nbsp;</div>
                            <br><br>
                                <table class="table" border="0">
                                        <tr>
                                            <td width="25%">
                                                <div align="center"> 
                                                    <div align="center"><strong>FROM</strong></div>
                                                    <select name="bdYear" id="bdYear" style="width:80px;">
                                                        <option value="">All Year</option>
                                                        <?php
                                                            $qYr = $db->select('inhouse_material','DISTINCT LEFT(im_date,4) as yr',array(),'ORDER BY im_date DESC');
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
                                                        <option value="<?php echo $i;?>" <?php if($i==$txbDay)echo 'selected="selected"';?>><?php echo $i;?></option>
                                                        <?php endfor;?>
                                                    </select>
                                                </div>
                                                <div align="center">
                                                    <div align="center"><strong>TO</strong></div>
                                                    <select name="bdYearTo" id="bdYearTo" style="width:80px;">
                                                        <option value="">All Year</option>
                                                        <?php
                                                            $qYr = $db->select('inhouse_material','DISTINCT LEFT(im_date,4) as yr',array(),'ORDER BY im_date DESC');
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
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="9%">Purchase Date</th>
                                            <th width="35%">Project / Payee</th>
                                            <th width="10%">Amount</th>
                                            <th width="11%"><div align="center">Options</div></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $totalAmount=0;
                                        while($rList = $db->fetch_array($qList)):
                                        $amount = $db->getValue('inhouse_material_item','sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) )',array('im_id'=>$rList['im_id']));
                                        $totalAmount += $amount;
                                        $is_paid = $rList['is_paid'];
                                         $bgColor='';
                                        if($is_paid==1)
                                            $bgColor = 'bgcolor="#e6eb9c"';

                                        $manageItemLink = ( isset($arrFE[$rList['im_id']]) ) ? 'admin-inhouse-material-item-manage-fuel.php' : 'admin-inhouse-material-item-manage.php';
                                        $printItemLink = ( isset($arrFE[$rList['im_id']]) ) ? 'admin-inhouse-material-print-fuel-equip.php' : 'admin-inhouse-material-print.php';

                                    ?>
                                        <tr <?php echo $bgColor;?>>
                                            <td><?php echo functions::datearr($rList['im_date']);?></td>
                                            <td><?php echo ($rList['proj_id']) ? $db->getValue('project','proj_name',array('proj_id'=>$rList['proj_id'])) : $db->getValue('inhouse_material','payee',array('im_id'=>$rList['im_id']));?></td>
                                            <td><a id="costdetail<?php echo $rList['im_id']?>" class="label label-info thickbox" title="View Purchase Details" data-rel="tooltip" onclick="showThis(this.id,'admin-inhouse-material-item-view.php?im_id=<?php echo functions::encode($rList['im_id']);?>','In-house Material Item','1')"><?php echo functions::formatMoney($amount);?></a></td>
                                            <td>
                                                <div align="center">
                                                <a id="detail<?php echo $rList['im_id']?>" class="btn btn-mini btn-info thickbox" title="Manage Purchase item" data-rel="tooltip" onclick="showThis(this.id,'<?php echo $manageItemLink?>?im_id=<?php echo functions::encode($rList['im_id']);?>','Material Details')"><i class="halflings-icon white plus-sign"></i></a>
                                                <a id="edit<?php echo $rList['im_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Purchase" data-rel="tooltip" onclick="showThis(this.id,'admin-inhouse-material-edit.php?im_id=<?php echo functions::encode($rList['im_id']);?>','Material Details')"><i class="halflings-icon white pencil"></i></a>
                                                <?php if( $db->getValue('inhouse_material_item','count(*)',array('im_id'=>$rList['im_id']))==0 ){ ?>
                                                <a id="del<?php echo $rList['im_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Purchase" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?itemDel=<?php echo functions::encode($rList['im_id']);?>"><i class="halflings-icon white trash"></i></a>
                                                <?php }?>
                                                <a id="print<?php echo $rList['im_id']?>" class="btn btn-mini btn-success thickbox" title="Print this Purchase" data-rel="tooltip" onclick="showThis(this.id,'<?php echo $printItemLink?>?im_id=<?php echo functions::encode($rList['im_id']);?>','Warehouse Print','1')"><i class="halflings-icon white print"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile;?>
                                    </tbody>
                                </table>
                                <table class="table table-bordered table-hover">
                                    <tr>
                                        <td width="44%"><div align="right"><strong>Total Amount</strong></div></td>
                                        <td width="10%"><div align="left"><strong><?php echo functions::formatMoney($totalAmount);?></strong></div></td>
                                        <td width="11%">&nbsp;</td>
                                    </tr>
                                </table>
                                <div align="center"><?php #functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?',$search="");?></div>
                      </div>
                </div><!--/span-->
          </div><!--/row-->
</form>
<!-- body content: end here-->
<script>
    function delt(){
      if(confirm('Do you want to remove this P.O.?'))
        return true;
      else
        return false; 
    }
</script>
<?php require_once('templ_down.php');?>