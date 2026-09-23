<?php require_once('templ_up.php');?>
<?php
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$po_id = (isset($_REQUEST['po_id']) && !empty($_REQUEST['po_id']) ) ? functions::decode($_REQUEST['po_id']) : 0;
$po_idDel = (isset($_REQUEST['po_idDel']) && !empty($_REQUEST['po_idDel']) ) ? functions::decode($_REQUEST['po_idDel']) : 0;
$arr = array();

$mon='';$txProj = '';$txPayee = '';$txbMon = '';$txbYear = ''; $txSearch='';

if( isset($_POST['btnViewAll']) ){
    functions::sendTo($_SERVER['PHP_SELF']);
}
if( isset($_POST['btnSearch']) ){
    $_SESSION['rqEmp'] = ( isset($_POST['txRequestedBy']) && !empty($_POST['txRequestedBy']) ) ? $_POST['txRequestedBy'] : '';
    $_SESSION['rqMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $_SESSION['rqYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    functions::sendTo($_SERVER['PHP_SELF']);
}
if($po_idDel){
    $items = $db->getValue('medicine_request_detail','count(*)',array('mrq_id'=>$po_idDel));
    if($items == 0){
        $db->delete('medicine_request',array('mrq_id'=>$po_idDel));
        functions::sendTo($_SERVER['PHP_SELF']);
    }
}

$emp_id = ( isset($_SESSION['rqEmp']) && !empty($_SESSION['rqEmp']) ) ? $_SESSION['rqEmp'] : '';
$txbMon = ( isset($_SESSION['rqMon']) ) ? $_SESSION['rqMon'] : date('m');
$txbYear = ( isset($_SESSION['rqYear']) ) ? $_SESSION['rqYear'] : date('Y');
if($emp_id)
    $arr = array_merge($arr,array('emp_id'=>$emp_id));

if($txbMon && $txbYear)
    $arr = array_merge($arr,array('LEFT(mrq_date,7)'=>$txbYear.'-'.$txbMon));
else if($txbMon)
    $arr = array_merge($arr,array('SUBSTRING(mrq_date,6,2)'=>$txbMon));
elseif($txbYear)
    $arr = array_merge($arr,array('LEFT(mrq_date,4)'=>$txbYear));  


if(count($arr)){
    $rowdisplay=1000;
    $startrow=0;
}

$qPO = $db->select('medicine_request','*',$arr,'ORDER BY mrq_date DESC LIMIT '.$startrow.', '.$rowdisplay);
$num_record = $db->getValue('medicine_request','count(*)',$arr);

if( isset($_POST['btnSearch2']) ){
    $txSearch = ( isset($_POST['txSearch']) && !empty($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
    $qPO = $db->query("SELECT * FROM medicine_request WHERE mrq_no LIKE '%".$db->clean($txSearch)."%' LIMIT 0, 100");
    $num_record = $db->num_rows($qPO);
}
$bgc_unserved='#e6eb9c';  $bgc_not_received='#f5ae00'; $bgc_del_inc='#ff889e';
?>
<!-- body content: start here-->
<div class="row-fluid">
        <div class="box span12">
                <div class="box-header" data-original-title>
                    <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Request List</h2>
                </div>
                <div class="box-content">
                    <form method="post">
                        <table border="0" align="right">
                            <tr>
                                <td><a id="poUnserved" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'request_manage.php?','Add Medicine Request')">Add Medicine Request</a>&nbsp;&nbsp;&nbsp;<a id="poMonitoring" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'po_monitoring.php?','P.O. Monitoring','1')" style="display:none;">Show Monitoring</a></td>
                            </tr>
                        </table><br><br>
                        <table border="0">
                            <tr>
                                <td colspan="3">Request No: <input type="text" name="txSearch" value="<?php echo $txSearch;?>">&nbsp;<input type="submit" name="btnSearch2" id="btnSearch2" value="Search" class="btn btn-primary">&nbsp;<input type="submit" name="btnViewAll" id="btnViewAll" value="View All" class="btn btn-primary">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3"><hr width="100%"></td>
                            </tr>
                            <tr>
                                <td width="15%">
                                    <div align="left">
                                        <select name="bdYear" id="bdYear" style="width:90px;">
                                            <option value="">All Year</option>
                                            <?php
                                                $qYr = $db->select('medicine_request','DISTINCT LEFT(mrq_date,4) as yr',array(),'ORDER BY mrq_date DESC');
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
                                <td width="40%">
                                    <div align="left">
                                        <select name="txRequestedBy" id="txRequestedBy" data-rel="chosen" style="width:590px;">
                                            <option value="">--All Employee--</option>
                                            <?php $qEU = $db->select('employee e, medicine_request mrq','e.emp_id,lname,fname',array(),'WHERE e.emp_id=mrq.emp_id GROUP BY e.emp_id,lname,fname ORDER BY lname,fname');
                                            while($rEU = $db->fetch_array($qEU)):
                                            ?>
                                            <option value="<?php echo $rEU['emp_id']?>" <?php if($emp_id==$rEU['emp_id']){echo 'selected="selected"';} ?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
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
                    <table class="table table-bordered table-hover" style="font-size:12px">
                        <thead>
                            <tr>
                                <th width="6%"><div>Request #</div></th>
                                <th width="9%">Request Date</th>
                                <th width="15%">Employee</th>
                                <th width="30%">Project</th>
                                <th width="10%"><div align="center">Options</div></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            while($rPO = $db->fetch_array($qPO)):
                                $items = $db->getValue('medicine_request_detail','count(*)',array('mrq_id'=>$rPO['mrq_id']));
                                $claimed = $rPO['claimed'];
                                $bgColor='';
                                if($claimed==0)
                                    $bgColor = 'bgcolor="'.$bgc_not_received.'"';

                        ?>
                            <tr <?php echo $bgColor;?>>
                                <td><div><?php echo $rPO['mrq_no'];?></div></td>
                                <td><?php echo functions::datearr($rPO['mrq_date']);?></td>
                                <td><?php echo $db->getValue('employee','concat(lname, ",",fname)',array('emp_id'=>$rPO['emp_id']));?></td>
                                <td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rPO['proj_id']));?></td>
                                <td>
                                    <div align="left" style="padding-left:20px;">
                                        <a id="detail<?php echo $rPO['mrq_id']?>" class="btn btn-mini btn-info thickbox" title="Manage Request item" data-rel="tooltip" onclick="showThis(this.id,'request_item_manage.php?mrq_id=<?php echo functions::encode($rPO['mrq_id']);?>','Request Details')"><i class="halflings-icon white plus-sign"></i></a>
                                        <a id="edit<?php echo $rPO['mrq_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Request" data-rel="tooltip" onclick="showThis(this.id,'request_manage.php?mrq_id=<?php echo functions::encode($rPO['mrq_id']);?>','Request Item Details')"><i class="halflings-icon white pencil"></i></a>
                                        <a id="print<?php echo $rPO['mrq_id']?>" class="btn btn-mini btn-success thickbox" title="Print this Request" data-rel="tooltip" onclick="showThis(this.id,'request_print.php?mrq_id=<?php echo functions::encode($rPO['mrq_id']);?>','Request Print Details','1')"><i class="halflings-icon white print"></i></a>
                                    <?php if($items==0){?>
                                        <a id="del<?php echo $rPO['mrq_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Request" data-rel="tooltip" href="?po_idDel=<?php echo functions::encode($rPO['mrq_id']);?>"><i class="halflings-icon white trash"></i></a>
                                    <?php }?>
                                    </div>
                                </td>
                            </tr>
                                <?php endwhile;?>
                        </tbody>
                    </table>
                    <div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?',$search="");?></div>
                </div>
        </div><!--/span-->
</div><!--/row-->
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