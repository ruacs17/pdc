<?php require_once('templ_up.php');?>
<?php
$attendance_unconfirmed = $db->getValue('emp_attendance','count(*)',array('attendance_ready'=>0));
$payroll_unconfirmed = $db->getValue('emp_attendance','count(*)',array('attendance_ready'=>1,'confirmed'=>0));
?>
<!-- body content: start here-->
<div class="row-fluid">

</div><br>
<div class="row-fluid">
	<a id="emp" class="quick-button metro blue span3 thickbox" onclick="showThis(this.id,'employee_list.php?active=active','Employee List','1')">
		<i class="icon-group"></i>
		<p><strong>Employee</strong></p>
		<span style="font-size: 20px;"><?php echo $db->getValue('employee','count(*)',array(),'WHERE work_status IN ("OJT","Part Time","Contractual","Probationary","Regular")');?></span>
	</a>
	<a id="department" class="quick-button metro green span3" href="department.php">
		<i class="icon-sitemap"></i>
		<p><strong>Department</strong></p>
		<span style="font-size: 20px;"><?php echo $db->getValue('department','count(*)',array());?></span>
	</a>
	<a id="priceMonitor" class="quick-button metro purple span3" href="position.php">
		<i class="icon-user-md"></i>
		<p><strong>Position Title</strong></p>
		<span style="font-size: 20px;"><?php echo $db->getValue('dep_position','count(DISTINCT pos_name)',array());?></span>
	</a>
	<a class="quick-button metro orange span3" href="assignment.php">
		<i class="icon-folder-open"></i>
		<p><strong>Assignment</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
</div><br>


<div class="row-fluid">
	<a class="quick-button metro red span3" href="holiday.php">
		<i class="icon-calendar"></i>
		<p><strong>Holiday</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a class="quick-button metro blue span3" href="leave_file.php">
		<i class="icon-glass"></i>
		<p><strong>Leave</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a class="quick-button metro green span3" href="travel.php">
		<i class="icon-truck"></i>
		<p><strong>Travel</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a class="quick-button metro purple span3" href="overtime.php">
		<i class="icon-time"></i>
		<p><strong>Overtime</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
</div><br>
<div class="row-fluid">
	<a id="contrib" class="quick-button metro yellow span3 thickbox" onclick="showThis(this.id,'contribution_sss.php?','Contribution Reference','1')">
		<i class="icon-reorder"></i>
		<p><strong>Contribution Reference</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a id="signatory" class="quick-button metro green span3 thickbox" onclick="showThis(this.id,'signatory.php?','Default Signatory','1')">
		<i class="icon-pencil"></i>
		<p><strong>Default Signatory</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	
	<a class="quick-button metro purple span3" href="attendance.php?<?php echo ($attendance_unconfirmed) ? 'unc=unc' : ''; ?>">
		<i class="icon-check"></i>
		<p><strong>Attendance</strong></p>
		<div style="font-size: 15px;" align="right"><?php echo ($attendance_unconfirmed) ? 'Unconfirmed: <strong>'.$attendance_unconfirmed.'</strong>' : ''; ?></div>
	</a>
	<a class="quick-button metro red span3" href="payroll.php?<?php echo ($payroll_unconfirmed) ? 'unc=unc' : ''; ?>">
		<i class="icon-money"></i>
		<p><strong>Payroll</strong></p>
		<div style="font-size: 15px;" align="right"><?php echo ($payroll_unconfirmed) ? 'Unconfirmed: <strong>'.$payroll_unconfirmed.'</strong>' : ''; ?></div>
	</a>
</div><br>
<!--/row-fluid-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
