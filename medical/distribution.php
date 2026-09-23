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
    $_SESSION['md_proj'] = (isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : "";
    $_SESSION['md_yr'] = (isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : 0;
    $_SESSION['md_mn'] = (isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : 0;
    functions::sendTo($_SERVER['PHP_SELF']);
}
if($itemDel){
    $detail = $db->getValue('medicine_distribution_detail','count(*)',array('md_id'=>$itemDel));
    if($detail == 0){
        $db->delete('medicine_distribution',array('md_id'=>$itemDel));
        functions::sendTo($_SERVER['PHP_SELF']);
    }
}
$selProj = ( isset($_SESSION['md_proj']) ) ? $_SESSION['md_proj'] : "";
$txbYear = ( isset($_SESSION['md_yr']) ) ? $_SESSION['md_yr'] : date('Y');
$txbMon = ( isset($_SESSION['md_mn']) ) ? $_SESSION['md_mn'] : date('m');

if($txbMon && $txbYear)
    $arr = array('LEFT(md_date,7)'=>$txbYear.'-'.$txbMon);
elseif($txbYear)
    $arr = array('LEFT(md_date,4)'=>$txbYear);
elseif($txbMon)
    $arr = array('SUBSTRING(md_date,6,2)'=>$txbMon);

if($selProj)
    $arr = array_merge($arr,array('proj_id'=>$selProj));

if(count($arr)){
    $rowdisplay=1000;
    $startrow=0;
}
if($txSearchMR)
    $qList = $db->select('medicine_distribution','*',array('mr_no'=>$txSearchMR));
else
    $qList = $db->select('medicine_distribution','*',$arr,'ORDER BY md_date DESC');

$num_record = $db->getValue('medicine_distribution','count(*)',$arr);

$arrChargeList=array();
?>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Medicine Distribution</h2>
        </div>
        <div class="box-content">
            <div align="right">
                <a id="mrAdd" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'distribution_manage.php?','Create Distribution Form')">Make Distribution</a>
            </div><br>
            <form method="post">
                <table class="table" border="0">
                    <tr>
                        <td colspan="3" height="70">Distribution No: <input type="text" name="txSearchMR" id="txSearchMR" value="<?php echo $txSearchMR;?>">&nbsp;<input type="submit" name="btnSearchMR" id="btnSearchMR" value="Search" class="btn btn-primary"></td>
                    </tr>
                    <tr>
                        <td width="20%">
                            <div align="left">
                                <select name="bdYear" id="bdYear" style="width:90px;">
                                    <option value="">All Year</option>
                                    <?php
                                        $qYr = $db->select('medicine_distribution','DISTINCT LEFT(md_date,4) as yr',array(),'ORDER BY md_date DESC');
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
                                <select name="selProj" id="selProj" data-rel="chosen" style="width:600px;">
                                    <option value="">--All Project--</option>
                                    <?php $qProj = $db->select('project p, medicine_distribution md','p.proj_id,proj_name',array(),'WHERE p.proj_id=md.proj_id GROUP BY p.proj_id,proj_name ORDER BY proj_name');
                                          while($rProj = $db->fetch_array($qProj)):
                                    ?>
                                    <option value="<?php echo $rProj['proj_id']?>" <?php if($selProj==$rProj['proj_id']){echo 'selected="selected"';} ?>><?php echo ($rProj['proj_name']);?></option>
                                    <?php endwhile;?>
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
            </form>         
            <table class="table table-bordered table-hover" style="font-size:12px;">
                <thead>
                    <tr>
                        <th width="9%">Distribution Date</th>
                        <th width="10%">Reference No.</th>
                        <th width="35%">Project</th>
                        <th width="10%"><div align="center">Manage</div></th>
                    </tr>
                </thead>
                <tbody>
                <?php while($rList = $db->fetch_array($qList)):?>
                    <tr>
                        <td><?php echo functions::datearr($rList['md_date']);?></td>
                        <td><?php echo $rList['md_no'];?></td>
                        <td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rList['proj_id']));?></td>
                        <td>
                            <div align="left" style="padding-left: 35px;">
                                <a id="itm<?php echo $rList['md_id']?>" class="btn btn-mini btn-info thickbox" title="Manage Medical item" data-rel="tooltip" onclick="showThis(this.id,'distribution_item_manage.php?mdid=<?php echo functions::encode($rList['md_id']);?>','Manage Item')"><i class="halflings-icon white plus-sign"></i></a>
                                <a id="detail<?php echo $rList['md_id']?>" class="btn btn-mini btn-warning thickbox" title="Manage Medical Distribution" data-rel="tooltip" onclick="showThis(this.id,'distribution_manage.php?mdid=<?php echo functions::encode($rList['md_id']);?>','Manage Item')"><i class="halflings-icon white pencil"></i></a>
                                <?php if( $db->getValue('medicine_distribution_detail','count(*)',array('md_id'=>$rList['md_id']))==0 ){ ?>
                                <a id="del<?php echo $rList['md_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this MR" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?itemDel=<?php echo functions::encode($rList['md_id']);?>"><i class="halflings-icon white trash"></i></a>
                                <?php }?>
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
<!-- body content: end here-->
<script>
function delt(){
    if(confirm('Do you want to remove this Report?'))
        return true;
    else
        return false; 
    }
</script>
<?php require_once('templ_down.php');?>