<?php
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$pc_id = isset($_GET['pcid']) ? $_GET['pcid'] : 0;
if($pc_id)
	$result = $db->select('project','*',array('pc_id'=>$pc_id),'ORDER BY proj_name');
else
	$result = $db->select('project','*',array(),'ORDER BY proj_name');
?>
<select id="selProj" name="selProj" style="width:270px;" >
<option value="">--select--</option>
<?php while ($row=$db->fetch_array($result)) { ?>
<option value="<?php echo $row['proj_id']?>"><?php echo $row['proj_name']?></option>
<?php } ?>
</select>
