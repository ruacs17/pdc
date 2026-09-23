<?php
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();

$tpe = (isset($_REQUEST['typ']) && !empty($_REQUEST['typ']) ) ? $_REQUEST['typ'] : '';
$selected = (isset($_REQUEST['sel']) && !empty($_REQUEST['sel']) ) ? $_REQUEST['sel'] : 0;
if($tpe=='class')
	$type='classification';
else if($tpe=='type')
	$type='type';
else if($tpe=='brand')
	$type='brand';
?>
<?php if($type){ ?>
	<option value="">-- Select --</option>
	<?php
	$qType = $db->query('SELECT * FROM equipment_type WHERE type_name="'.$type.'" ORDER BY type_desc ');
	while($rType = $db->fetch_array($qType)):
	?>
	<option value="<?php echo $rType['type_desc']?>" <?php if($selected && $selected==$rType['type_desc'])echo 'selected'; ?>><?php echo $rType['type_desc']?></option>
	<?php endwhile;?>
<?php }?>