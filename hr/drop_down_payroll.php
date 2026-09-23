<?php
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();

$year = isset($_GET['yr']) ? $_GET['yr'] : '';
$month = isset($_GET['mn']) ? $_GET['mn'] : '';
$arr = array('confirmed'=>1);
/*if( $year && $month )
	$arr = array_merge($arr,array('LEFT(date_start,7)'=>$year.'-'.$month));
else if($year)
	$arr = array_merge($arr,array('LEFT(date_start,4)'=>$year));
else if($month)
	$arr = array_merge($arr,array('SUBSTRING(date_start,6,2)'=>$month));*/
if($year && $year)
	$arr = array_merge($arr,array('att_year'=>$year,'att_month'=>$month));
else if($month)
	$arr = array_merge($arr,array('att_month'=>$month));
elseif($year)
	$arr = array_merge($arr,array('att_year'=>$year));
?>
<select name="selPayroll" id="selPayroll" data-rel="chosen" style="width:95%;font-size:12px;">
	<option value="">--Select Payroll--</option>
	<?php 
	$qEA = $db->select('emp_attendance','*',$arr,'ORDER BY date_start DESC');
	while($rEA = $db->fetch_array($qEA)):?>
	<option value="<?php echo functions::encode($rEA['eat_id'])?>" <?php #if($projID==$rEA['eat_id'])echo 'selected="selected"';?>><?php echo $rEA['payroll_no'].' <i>('.functions::datearr($rEA['date_start']).' - '.functions::datearr($rEA['date_end']).')</i>';?></option>
	<?php endwhile;?>
</select>
