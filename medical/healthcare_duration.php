<?php require_once('templ_up.php');?>
<?php
$mhdDel = (isset($_REQUEST['mhdDel']) && !empty($_REQUEST['mhdDel']) ) ? functions::decode($_REQUEST['mhdDel']) : 0;
if($mhdDel){
    $db->delete('medicine_healthcare_duration',array('mhd_id'=>$mhdDel));
    functions::sendTo($_SERVER['PHP_SELF']);
}
?>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Healthcare Benifit Setting</h2>
        </div>
        <div class="box-content">
            <div align="right">
                <a id="mrAdd" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'healthcare_duration_manage.php?','Healthcare Benifit Manage')">Add Duration</a>
            </div><br>
            <form method="post">
                <table class="table">
                    <tr>
                        <th>Date Start</th>
                        <th>Date End</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>&nbsp;</th>
                    </tr>
                    <?php
                    $q = $db->select('medicine_healthcare_duration','*',array());
                    while($r = $db->fetch_array($q)):
                    ?>
                    <tr>
                        <td><?php echo functions::datearr($r['mhd_start'])?></td>
                        <td><?php echo functions::datearr($r['mhd_end'])?></td>
                        <td><?php echo functions::formatMoney($r['mhd_amount'])?></td>
                        <td><?php echo ($r['active_status']==1) ? 'Active' : '';?></td>
                        <td>
                            <div align="right">
                                <?php if( $db->getValue('medicine_healthcare_availment','count(*)',array('mhd_id'=>$r['mhd_id']))==0 ){ ?>
                                <a id="del<?php echo $r['mhd_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Record" data-rel="tooltip" href="?mhdDel=<?php echo functions::encode($r['mhd_id']);?>"><i class="halflings-icon white trash"></i></a>
                                <?php } ?>
                                <a id="edit<?php echo $r['mhd_id'];?>" class="btn btn-mini btn-warning thickbox" title="Update this item" data-rel="tooltip" onclick="showThis(this.id,'healthcare_duration_manage.php?mhd=<?php echo functions::encode($r['mhd_id']);?>','Manage Duration')"><i class="halflings-icon white pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </form>
        </div>
    </div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){
    if(confirm('Do you want to remove this Record?'))
        return true;
    else
        return false; 
    }
</script>
<?php require_once('templ_down.php');?>