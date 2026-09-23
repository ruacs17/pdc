<?php
require_once('../class/databaseHR.php');
require_once('../class/functions.php');
$db = new DatabaseHR();
$provId = isset($_GET['provId']) ? $_GET['provId'] : 0;
$result = $db->select('refcitymun','*',array('provCode'=>$provId),'ORDER BY citymunDesc');
?>
<select name="selCityMunPerm" id="selCityMunPerm" onchange="getBrngyPerm(this.value)">
<option value="">Select City/Municipality</option>
<?php while ($row=$db->fetch_array($result)) { ?>
<option value="<?php echo $row['citymunCode']?>"><?php echo $row['citymunDesc']?></option>
<?php } ?>
</select>
