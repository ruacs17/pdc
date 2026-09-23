<?php require_once('templ_up.php');?>
<?php

$arr = array();
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;

$itemDel = (isset($_REQUEST['itemDel']) && !empty($_REQUEST['itemDel']) ) ? functions::decode($_REQUEST['itemDel']) : 0;
$mon='';
$txProj = '';
$txPayee = '';
$txbMon = '';
$txbYear = ''; 
$txSearchMR = ( isset($_POST['btnSearchMR']) && isset($_POST['txSearchMR']) ) ? $_POST['txSearchMR'] : '';

if( isset($_POST['btnSearch']) ){
    $arr = array();
    $_SESSION['mr_eu'] = (isset($_POST['selUser']) && !empty($_POST['selUser']) ) ? $_POST['selUser'] : 0;
    $_SESSION['mr_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : 0;
    $_SESSION['mr_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : 0;
}
if($itemDel){
    $amount = $db->getValue('mr_item','count(*)',array('mr_id'=>$itemDel));
    if($amount == 0){
        $db->delete('mr',array('mr_id'=>$itemDel));
        functions::sendTo($_SERVER['PHP_SELF']);
    }
}

    $txUser = ( isset($_SESSION['mr_eu']) ) ? $_SESSION['mr_eu'] : '';
    $txbYear = ( isset($_SESSION['mr_yr']) ) ? $_SESSION['mr_yr'] : date('Y');
    $txbMon = ( isset($_SESSION['mr_mn']) ) ? $_SESSION['mr_mn'] : date('m');

    if($txbMon && $txbYear)
        $arr = array('LEFT(mr_date,7)'=>$txbYear.'-'.$txbMon);
    elseif($txbYear)
        $arr = array('LEFT(mr_date,4)'=>$txbYear);
    elseif($txbMon)
        $arr = array('SUBSTRING(mr_date,6,2)'=>$txbMon);

    if($txUser){
        if( $db->getValue('mr','count(*)',array('eu_id'=>$txUser)) )
            $arr = array_merge($arr,array('eu_id'=>$txUser));
    }

    if(count($arr)){
        $rowdisplay=1000;
        $startrow=0;
    }
    if($txSearchMR)
        $qList = $db->select('mr','*',array('mr_no'=>$txSearchMR));
    else
    $qList = $db->select('mr','*',$arr,'ORDER BY mr_date DESC');

    $num_record = $db->getValue('mr','count(*)',$arr);

$arrChargeList=array();
$qUser = $db->query('SELECT eu.eu_id,fname,mname,lname FROM mr, equip_user eu WHERE mr.eu_id=eu.eu_id GROUP BY eu_id,fname,mname,lname ORDER BY eu.lname,eu.fname');
while($rUser = $db->fetch_array($qUser)):
    $nme = $rUser['lname'].', '.$rUser['fname'].' '.$rUser['mname'];
    $arrChargeList[$nme]=$rUser['eu_id'];
endwhile;
?>
<!-- body content: start here-->
<form method="post">
    <div class="row-fluid">
        <div class="box span12">
            <div class="box-header" data-original-title>
                <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Memorandum of Receipt</h2>
            </div>
            <div class="box-content">
                <table width="200" cellspacing="0" cellpadding="0" border='0' align="right">
                    <tr>
                        <td width="10" height='30'><div style="background-color:#e6eb9c; width:20px;">&nbsp;</div></td>
                        <td width="90"> UnCleared</td>
                    </tr>
                </table><br><br>
                <table class="table" border="0">
                    <tr>
                        <td colspan="3" height="70">MR No: <input type="text" name="txSearchMR" id="txSearchMR" value="<?php echo $txSearchMR;?>">&nbsp;<input type="submit" name="btnSearchMR" id="btnSearchMR" value="Search" class="btn btn-primary"></td>
                    </tr>
                    <tr>
                        <td width="20%">
                            <div align="left">
                                <select name="bdYear" id="bdYear" style="width:90px;">
                                    <option value="">All Year</option>
                                    <?php
                                        $qYr = $db->select('mr','DISTINCT LEFT(mr_date,4) as yr',array(),'ORDER BY mr_date DESC');
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
                            </div>
                        </td>
                        <td width="35%" style="padding: 12px 0px 0px 0px">
                            <div align="left">
                                <select name="selUser" id="selUser" data-rel="chosen" style="width:650px;">
                                    <option value="">All User</option>
                                    <?php foreach($arrChargeList as $name => $id): ?>
                                    <option value="<?php echo $id?>" <?php if($txUser===$id)echo 'selected="selected"';?>><?php echo ucwords(strtolower($name));?></option>
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
                    <tr><td colspan="3"><hr width="100%"></td></tr>
                </table>          
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="9%">MR Date</th>
                            <th width="10%">MR No.</th>
                            <th width="35%">Project</th>
                            <th width="11%"><div align="center">Manage</div></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        while($rList = $db->fetch_array($qList)):
                    ?>
                        <tr>
                            <td><?php echo functions::datearr($rList['mr_date']);?></td>
                            <td><?php echo $rList['mr_no'];?></td>
                            <td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rList['proj_id']));?></td>
                            <td>
                                <div align="center">
                                <a id="detail<?php echo $rList['mr_id']?>" class="btn btn-mini btn-info thickbox" title="Manage MR item" data-rel="tooltip" onclick="showThis(this.id,'admin-mr-return-item-manage.php?mr_id=<?php echo functions::encode($rList['mr_id']);?>','Manage Item')">
                                    <i class="halflings-icon white plus-sign"></i>
                                </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile;?>
                    </tbody>
                </table>
            <div align="center"><?php #functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?',$search="");?></div>
            </div>
        </div><!--/span-->
    </div><!--/row-->
</form>
            <!-- body content: end here-->
<script>
function delt(){
  if(confirm('Do you want to remove this Leasing?'))
    return true;
  else
    return false; 
}
</script>
<?php require_once('templ_down.php');?>