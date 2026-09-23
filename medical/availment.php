<?php require_once('templ_up.php');?>
<?php

if( isset($_POST['btnSearch']) ){
    $_SESSION['ramhd'] = ( isset($_POST['txHC']) && !empty($_POST['txHC']) ) ? $_POST['txHC'] : '';
    $_SESSION['raemp'] = ( isset($_POST['txRequestedBy']) && !empty($_POST['txRequestedBy']) ) ? $_POST['txRequestedBy'] : '';
    functions::sendTo($_SERVER['PHP_SELF']);
}
$qhdefault = $db->select('medicine_healthcare_duration','*',array('active_status'=>1),'ORDER BY active_status DESC,mhd_start');
$rhdefault = $db->fetch_array($qhdefault);
$mhd_id = ( isset($_SESSION['ramhd']) ) ? $_SESSION['ramhd'] : $rhdefault['mhd_id'];
$emp_id = ( isset($_SESSION['raemp']) ) ? $_SESSION['raemp'] : "";
$healthcare_amount = $db->getValue('medicine_healthcare_duration','mhd_amount',array('mhd_id'=>$mhd_id));

$arr = array('mhd_id'=>$mhd_id);
if($emp_id)
    $arr = array_merge($arr,array('e.emp_id'=>$emp_id));

$qRec = $db->select('medicine_healthcare_availment mha, employee e','e.emp_id,lname,fname,work_status,sum(mha_amount) as amnt',$arr,'AND mha.emp_id=e.emp_id GROUP BY mha.emp_id ORDER BY lname,fname DESC');

function position($emp_id){
    global $db;
    $countPos=0;$position='';
    $qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
    while($rPos = $db->fetch_array($qPos)):
        if($countPos)
            $position .= ' /<br>';
        $position .= $rPos['pos_name'];
        $countPos++;
    endwhile;
    return $position;
}
?>
<!-- body content: start here-->
<div class="row-fluid">
        <div class="box span12">
                <div class="box-header" data-original-title>
                    <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Healthcare Benefit Availment Monitoring</h2>
                </div>
                <div class="box-content">
                    <form method="post">
                        <table border="0" align="right">
                            <tr>
                                <td><a id="poUnserved" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'availment_manage.php?','Availment Details')">Add Availment</a></td>
                            </tr>
                        </table><br><br><br>
                        <table border="0" width="90%">
                            <tr>
                                <td width="15%">
                                    <div align="left">
                                        <select name="txHC" id="txHC" style="width:350px;" required>
                                            <?php $qhd = $db->select('medicine_healthcare_duration','*',array(),'ORDER BY mhd_start');
                                            while($rhd = $db->fetch_array($qhd)):
                                            ?>
                                            <option value="<?php echo $rhd['mhd_id']?>" <?php if($mhd_id==$rhd['mhd_id'])echo 'selected="selected"';?>><?php echo functions::datearr($rhd['mhd_start']).' - '.functions::datearr($rhd['mhd_end']).' ('.functions::formatMoney($rhd['mhd_amount']).')'; echo ($rhd['active_status']==1) ? ' - Active' : '';?></option>
                                            <?php endwhile;?>
                                        </select>
                                    </div>
                                </td>
                                <td width="40%">
                                    <div align="left">
                                        <select name="txRequestedBy" id="txRequestedBy" data-rel="chosen" style="width:390px;">
                                            <option value="">--All Employee--</option>
                                            <?php $qEU = $db->select('employee e, medicine_healthcare_availment mha','e.emp_id,lname,fname',array(),'WHERE e.emp_id=mha.emp_id GROUP BY e.emp_id,lname,fname ORDER BY lname,fname');
                                            while($rEU = $db->fetch_array($qEU)):
                                            ?>
                                            <option value="<?php echo $rEU['emp_id']?>" <?php if($emp_id==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
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
                                <th width="18%">Employee</th>
                                <th width="15%">Position</th>
                                <th width="8%">Work Status</th>
                                <th width="8%">Amount</th>
                                <th width="8%">Balance</th>
                                <th width="5%"><div align="center">View</div></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $count=0;
                        while($rRec = $db->fetch_array($qRec)):
                        ?>
                            <tr>
                                <td><?php echo $rRec['lname'].', '.$rRec['fname'];?></td>
                                <td><div align="left"><?php echo position($rRec['emp_id']); ?></div></td>
                                <td><div align="left"><?php echo $rRec['work_status'] ?></div></td>
                                <td><?php echo functions::formatMoney($rRec['amnt']);?></td>
                                <td><?php echo ($healthcare_amount) ? functions::formatMoney($healthcare_amount-$rRec['amnt']) : '--'; ?></td>
                                <td>
                                    <div align="center">
                                        <a id="detail<?php echo $count++?>" class="btn btn-mini btn-info thickbox" title="Availment Details" data-rel="tooltip" onclick="showThis(this.id,'availment_list.php?empid=<?php echo functions::encode($rRec['emp_id']);?>&mhd=<?php echo functions::encode($mhd_id);?>','Availment Details')"><i class="halflings-icon white zoom-in"></i></a>
                                    </div>
                                </td>
                            </tr>
                                <?php endwhile;?>
                        </tbody>
                    </table>
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