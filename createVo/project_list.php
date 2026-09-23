<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
if($p_id){
    $arrVal = array('proj_id'=>$p_id);
}
?>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Project List</h2>
        </div>
        <div align="left"><br>&nbsp;&nbsp;
            <select name="selProj" id="selProj" data-rel="chosen" style="width:650px;" onChange="projSel(this.value)">
                <option value="">--All Projects--</option>
                <?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
                while($rProj = $db->fetch_array($qProj)):
                ?>
                <option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($p_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
                <?php endwhile;?>
            </select>
        </div>
        <div class="box-content">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="30%">Project Name</th>
                        <th width="20%">Description</th>
                        <th width="10%"><div align="center">Options</div></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if(!is_numeric($startrow))
                    $startrow=0;
                $qProj = $db->select('project','*',$arrVal,'ORDER BY proj_name ASC LIMIT '.$startrow.', '.$rowdisplay);
                $num_record = $db->getValue('project','count(*)',$arrVal);
                while($rProj = $db->fetch_array($qProj)):
                ?>
                    <tr>
                        <td><?php echo $rProj['proj_name'];?></td>
                        <td><?php echo $rProj['proj_desc'];?></td>
                        <td><div align="center"><a id="edit<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Project" data-rel="tooltip" onclick="showThis(this.id,'project_edit.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Detail')"><i class="halflings-icon white pencil"></i></a></div></td>
                    </tr>
                <?php endwhile;?>
                </tbody>
            </table>
            <div align="center"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=$_SERVER['PHP_SELF'].'?',$search="");?></div>
        </div>
    </div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>function projSel(PiEwgD){window.location="project_list.php?pid="+PiEwgD}</script>
<?php require_once('templ_down.php');?>