<?php require_once('templ_up.php');?>
<?php
$arr = array();
$item='';
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $db->clean($_REQUEST['startrow']) : 0;
$rowdisplay=40;
$num_record=0;
if( isset($_POST['btnSearch']) ){
    $_SESSION['sp_proj'] = (isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? trim($_POST['selProj']) : "";
    functions::sendTo($_SERVER['PHP_SELF']);
    die();
}
$proj_id = (isset($_SESSION['sp_proj'])) ? $_SESSION['sp_proj'] : "";
$q = 'SELECT item,unit,brand,sum(qty) as qy FROM medicine_distribution md, medicine_distribution_detail mdd WHERE md.md_id=mdd.md_id AND proj_id="'.$db->clean($proj_id).'" GROUP BY item,unit,brand LIMIT '.$startrow.', '.$rowdisplay;
$_SESSION['im_query']=$q;
$qCount = $db->query('SELECT item,unit,brand FROM medicine_distribution md, medicine_distribution_detail mdd WHERE md.md_id=mdd.md_id AND proj_id="'.$db->clean($proj_id).'" GROUP BY item,unit,brand');
$num_record = $db->num_rows();
$qList = $db->query($q);
?>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>MEDICAL STOCKS PER PROJECT</h2>
        </div>
        <div class="box-content">
            <table width="200" cellspacing="0" cellpadding="0" border='0' align="right">
                <tr>
                    <td width="10" height='30'><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
                    <td width="90"> Low Stock Level</td>
                </tr>
            </table><br><br>
            <div align="right" style="display:none;"><a id="loc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'admin-inhouse-material-stock-location.php?','In-house Warehouse Stock Location')">Stock Location</a>&nbsp;&nbsp;<a id="itemPrint" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'admin-inhouse-material-stock-print.php?','Warehouse Stock Item Print','1')"><i class="halflings-icon white print"></i></a></div><br>
            <form method="post" action="?">
                <table border="0">
                    <tr>
                        <td style="padding: 10px 10px 5px 0px">
                            <div align="left">
                                <select name="selProj" id="selProj" data-rel="chosen" style="width:700px;">
                                    <option value="">--Select Project--</option>
                                    <?php $qProj = $db->select('project p, medicine_distribution md','p.proj_id,proj_name',array(),'WHERE p.proj_id=md.proj_id GROUP BY p.proj_id,proj_name ORDER BY proj_name');
                                          while($rProj = $db->fetch_array($qProj)):
                                    ?>
                                    <option value="<?php echo $rProj['proj_id']?>" <?php if($proj_id==$rProj['proj_id']){echo 'selected="selected"';} ?>><?php echo ($rProj['proj_name']);?></option>
                                    <?php endwhile;?>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div align="center">
                                <input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-small btn-primary">
                            </div>
                        </td>
                    </tr>
                    <tr><td colspan="2"><hr width="100%"></td></tr>
                </table>
            </form>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="35%">Item</th>
                        <th width="8%">Unit</th>
                        <th width="10%">Brand</th>
                        <th width="10%"><div align="center">Distributed</div></th>
                        <th width="10%"><div align="center">Consumed</div></th>
                        <th width="10%"><div align="center">Remaining</div></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $totalAmount=0;$count=0;
                while($rList = $db->fetch_array($qList)):
                    $count++;
                    $mf_id = $db->getValue('material_reference','mf_id',array('item'=>$rList['item'],'unit'=>$rList['unit'],'brand'=>$rList['brand']));
                    $least_required = $db->getValue('material_reference','least_required',array('mf_id'=>$mf_id));

                    $allocation = (is_numeric($rList['qy'])) ? $rList['qy'] : 0;
                    $cons = $db->getValue('medicine_request mrq, medicine_request_detail mrd','sum(qty)',array('item'=>$rList['item'],'unit'=>$rList['unit'],'brand'=>$rList['brand'],'proj_id'=>$proj_id),'AND mrq.mrq_id=mrd.mrq_id');
                    #echo $db->last_query;
                    $consumed = (is_numeric($cons)) ? $cons : 0;
                    $available = $allocation - $consumed;
                ?>
                    <tr>
                        <td><?php echo $rList['item']?></td>
                        <td><?php echo $rList['unit']?></td>
                        <td><?php echo $rList['brand']?></td>
                        <td><div align="center"><a id="alloctn<?php echo $count++?>" class="thickbox" style="cursor:pointer;" title="View Distribution Details" data-rel="tooltip" onclick="showThis(this.id,'stock-project-allocation-detail.php?mf_id=<?php echo functions::encode($mf_id);?>&proj_id=<?php echo functions::encode($proj_id)?>','Distribution Details','1')"><?php echo $allocation?></a></div></td>
                        <td><div align="center"><a id="s<?php echo $count++?>" class="thickbox" style="cursor:pointer;" title="View Consumption Details" data-rel="tooltip" onclick="showThis(this.id,'stock-project-consume-detail.php?mf_id=<?php echo functions::encode($mf_id);?>&proj_id=<?php echo functions::encode($proj_id)?>','Consumption Details','1')"><?php echo $consumed?></a></div></td>
                        <td><div align="center"><?php echo $available;?></div></td>
                    </tr>
                <?php endwhile;
                if($count==0){?>
                    <tr>
                        <td colspan="6"><div align="center">--Nothing to Report--</div></td>
                    </tr>
                <?php }?>
                </tbody>
            </table>
            <div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?',$search="txItem");?></div>
        </div>
    </div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>