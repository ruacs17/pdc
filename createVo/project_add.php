<?php require_once('templ_up.php');?>
<?php
$msg='';
$txProj_name=""; $txProj_desc=""; $txUnder="";
if( isset($_POST['btnCreate']) ){
    $insertID=0;
    $arrInsert = array();

    $txProj_name = ( isset($_POST['txProj_name']) && !empty($_POST['txProj_name']) ) ? $_POST['txProj_name'] : '';
    $txProj_desc = ( isset($_POST['txProj_desc']) && !empty($_POST['txProj_desc']) ) ? $_POST['txProj_desc'] : '';
    $txUnder = ( isset($_POST['txUnder']) && !empty($_POST['txUnder']) ) ? $_POST['txUnder'] : '';
	
    $arrInsert = array('proj_name'=>$txProj_name);

    if($txProj_desc)
        $arrInsert = array_merge($arrInsert,array('proj_desc'=>($txProj_desc)));

    if($txUnder)
        $arrInsert = array_merge($arrInsert,array('owned_by'=>$txUnder));

    if( $txProj_name ){

        if($txUnder=="")
            $msg='Please select admin';
        else if( $db->getValue('project','count(proj_id)',array('proj_name'=>$txProj_name))==0 ){
            $ins =  $db->insertPrint('project',$arrInsert);
            $db->query($ins);
            $insertID = $db->insert_id();
            if($insertID){
                functions::sendTo('project_list.php?pid='.functions::encode($insertID));
            }
  		}
        else
            $msg='Project name already exist.';
    }
}
?>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Create Form</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">           
                <table width="60%" align="center" border="0">
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Project Name</label>
                                <div class="controls"><input type="text" class="span6" name="txProj_name" id="txProj_name" value="<?php echo $txProj_name;?>"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Project Description</label>
                                <div class="controls"><input type="text" class="span6" name="txProj_desc" id="txProj_desc" value="<?php echo $txProj_desc;?>" /></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Under </label>
                                <div class="controls">
                                    <select name="txUnder" id="txUnder" style="width:300px;">
                                        <option value="">--select--</option>
                                        <?php
                                        $qUnder = $db->query('SELECT * FROM project WHERE project="0"');
                                        while($rUnder = $db->fetch_array($qUnder)):
                                        ?>
                                        <option value="<?php echo $rUnder['proj_id']?>" <?php if($txUnder==$rUnder['proj_id'])echo 'selected="selected"';?>><?php echo $rUnder['proj_name']?></option>
                                        <?php endwhile;?>
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="controls">
                                <input type="submit" name="btnCreate" id="btnCreate" value="Create" class="btn btn-primary">
                                <button class="btn">Cancel</button>
                            </div>
                        </td>
                    </tr>            
                </table>
                <?php
                if($msg)
                    echo '<span class="label label-warning">'.$msg.'</span>';
                ?>
            </form>
        </div>
    </div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>