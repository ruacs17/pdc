<?php
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$db->query('DELETE FROM emp_timein');
$count=0;
$qEmp = $db->select('employee','*',array());
while($rEmp = $db->fetch_array($qEmp)):
    $count++;
    $emp_id = $rEmp['emp_id'];
    $amIn = '07:00:00';
    $amOut = '12:00:00';
    $pmIn = '13:00:00';
    $pmOut = '17:00:00';
    $db->insert('emp_timein',array('am_in'=>$amIn,'am_out'=>$amOut,'pm_in'=>$pmIn,'pm_out'=>$pmOut,'emp_id'=>$emp_id,'eti_day'=>'Monday','day_no'=>'1'));
    $db->insert('emp_timein',array('am_in'=>$amIn,'am_out'=>$amOut,'pm_in'=>$pmIn,'pm_out'=>$pmOut,'emp_id'=>$emp_id,'eti_day'=>'Tuesday','day_no'=>'2'));
    $db->insert('emp_timein',array('am_in'=>$amIn,'am_out'=>$amOut,'pm_in'=>$pmIn,'pm_out'=>$pmOut,'emp_id'=>$emp_id,'eti_day'=>'Wednesday','day_no'=>'3'));
    $db->insert('emp_timein',array('am_in'=>$amIn,'am_out'=>$amOut,'pm_in'=>$pmIn,'pm_out'=>$pmOut,'emp_id'=>$emp_id,'eti_day'=>'Thursday','day_no'=>'4'));
    $db->insert('emp_timein',array('am_in'=>$amIn,'am_out'=>$amOut,'pm_in'=>$pmIn,'pm_out'=>$pmOut,'emp_id'=>$emp_id,'eti_day'=>'Friday','day_no'=>'5'));
    $db->insert('emp_timein',array('am_in'=>$amIn,'am_out'=>$amOut,'emp_id'=>$emp_id,'eti_day'=>'Saturday','day_no'=>'6'));
endwhile;
echo $count;
?>