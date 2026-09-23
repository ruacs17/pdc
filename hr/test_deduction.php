<?php
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();

$fromPayroll = (isset($_REQUEST['pr']) && !empty($_REQUEST['pr']) ) ? $_REQUEST['pr'] : 0;
$eatid = (isset($_REQUEST['eatid']) && !empty($_REQUEST['eatid']) ) ? functions::decode($_REQUEST['eatid']) : 0;

$q = $db->select('emp_attendance','*',array());
while($r = $db->fetch_array($q)):
$eatid = $r['eat_id'];
    $qD = $db->select('emp_attendance_detail','emp_id,sum(absent_amount) as absnt',array('eat_id'=>$eatid),'GROUP BY emp_id');
    while($rD = $db->fetch_array($qD)):
        $totalAbsentAmount=0;
        $emp_id = $rD['emp_id'];
        $totalAbsentAmount = $rD['absnt'];
        $default_sss_contribution = $db->getValue('employee','sss_contribution',array('emp_id'=>$emp_id));
        $default_philhealth_contribution = $db->getValue('employee','philhealth_contribution',array('emp_id'=>$emp_id));
        $default_pagibig_contribution = $db->getValue('employee','pagibig_contribution',array('emp_id'=>$emp_id));
        if( $db->getValue('payroll_adjustment','count(*)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'SSS'))==0 )
            echo '<br>'.$db->insertPrint('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'SSS','adjustment_value'=>$default_sss_contribution,'adjustment_type'=>'deduction','adjustment_mode'=>'system')).';';
        #echo $db->last_query.'<br>';

        if( $db->getValue('payroll_adjustment','count(*)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'Philhealth'))==0 )
            echo '<br>'.$db->insertPrint('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'Philhealth','adjustment_value'=>$default_philhealth_contribution,'adjustment_type'=>'deduction','adjustment_mode'=>'system')).';';
        #echo $db->last_query.'<br>';
        if( $db->getValue('payroll_adjustment','count(*)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'Pagibig'))==0 )
            echo '<br>'.$db->insertPrint('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'Pagibig','adjustment_value'=>$default_pagibig_contribution,'adjustment_type'=>'deduction','adjustment_mode'=>'system')).';';
        #echo $db->last_query.'<br>';
        if( $db->getValue('payroll_adjustment','count(*)',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'absenses')) )
            echo '<br>'.$db->updatePrint('payroll_adjustment',array('adjustment_value'=>$totalAbsentAmount),array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'absenses','adjustment_mode'=>'system')).';';
        else
            echo '<br>'.$db->insertPrint('payroll_adjustment',array('emp_id'=>$emp_id,'eat_id'=>$eatid,'adjustment_name'=>'absenses','adjustment_value'=>$totalAbsentAmount,'adjustment_type'=>'deduction','adjustment_mode'=>'system')).';';
        #echo $db->last_query.'<br>';
        echo '<br>';
    endwhile;
endwhile;
?>