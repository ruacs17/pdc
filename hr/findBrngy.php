<?php
require_once('../class/databaseHR.php');
require_once('../class/functions.php');
$db = new DatabaseHR();
$cityMunId = isset($_GET['cityMunId']) ? $_GET['cityMunId'] : 0;
$result = $db->select('refbrgy','*',array('citymunCode'=>$cityMunId),'ORDER BY brgyDesc');
?>
<select id="selCurBrngy" name="selCurBrngy">
<option value="">Select Barangay</option>
<?php while ($row=$db->fetch_array($result)) { ?>
<option value="<?php echo $row['brgyCode']?>"><?php echo $row['brgyDesc']?></option>
<?php } ?>
</select>
