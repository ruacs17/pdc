<?php
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();

$asSType = (isset($_REQUEST['typ']) && !empty($_REQUEST['typ']) ) ? $_REQUEST['typ'] : '';
$selected = (isset($_REQUEST['sel']) && !empty($_REQUEST['sel']) ) ? $_REQUEST['sel'] : 0;

if($asSType)
	$qEU = $db->select('stockshare_personnel sp, employee emp','*',array('sp_type'=>$asSType),'AND sp.emp_id=emp.emp_id ORDER BY lname');
else
	$qEU = $db->query('SELECT * FROM stockshare_personnel sp, employee emp WHERE sp.emp_id=emp.emp_id ORDER BY lname');
?>
<option value="">--select--</option>
<?php
while($rEU = $db->fetch_array($qEU)):
?>
<option value="<?php echo $rEU['emp_id']?>" <?php if($selected && $selected==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
<?php endwhile;?>