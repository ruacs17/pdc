<?php require_once('templ_up.php');?>
<?php
function position($emp_id){
	global $db;
	$countPos=0;$position='';
	$qPos = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$emp_id),'AND ep.dp_id=dp.dp_id');
	while($rPos = $db->fetch_array($qPos)):
		if($countPos)
			$position .= ' /<br>';
		$position .= $rPos['pos_name'];
		$countPos++;
	endwhile;
	return $position;
}
$searchVal='';
$qDisp = $db->query('SELECT * FROM employee WHERE emp_id IN (SELECT DISTINCT emp_id FROM payroll_deduction_reference) ORDER BY lname');
if( isset($_POST['btnSearch']) ){
	$searchVal = ( isset($_POST['txSearch']) ) ? trim($_POST['txSearch']) : '';
	$qDisp = $db->query('SELECT * FROM employee WHERE (emp_no LIKE "%'.$db->clean($searchVal).'%" OR lname LIKE "%'.$db->clean($searchVal).'%" OR fname LIKE "%'.$db->clean($searchVal).'%") AND emp_id IN (SELECT DISTINCT emp_id FROM payroll_deduction_reference) ORDER BY lname');
}
if( isset($_POST['btnAll']) ){
	functions::sendTo(functions::pageName());
	die();
}
?>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Payroll Deduction / Add-on of Employee List</h2>
		</div>
		<div class="box-content">
			<div align="right"><a id="adc" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'payroll_deduction_emp_list_enroll.php?','Enroll Employee Deduction')">Enroll Employee</a></div><br>
			<form method="post">
				<div align="center">
					<table border="0">
						<tr>
							<td style="padding-top: 7px;"><input type="text" name="txSearch" id="txSearch" value="<?php echo $searchVal?>" placeholder="Search Employee"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-primary btn-small" name="btnSearch" id="btnSearch" value="Search"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info btn-small" name="btnAll" id="btnAll" value="Show All"></td>
						</tr>
					</table>
				</div>
			</form>
			<table class="table table-bordered table-hover table-striped" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="8%" scope="col">ID No.</th>
						<th width="35%" scope="col">NAME</th>
						<th width="30%" scope="col">POSITION</th>
						<th width="10%" scope="col">STATUS</th>
						<th width="7%" scope="col"><div align="center">DEDUCTION</div></th>
						<th width="7%" scope="col"><div align="center">ADD-ON</div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$countResult=0;
					while($rDisp = $db->fetch_array($qDisp)):
					$countResult++;
					?>
					<tr>
						<td><?php echo $rDisp['emp_no']?></td>
						<td><?php echo $rDisp['lname'].' '.$rDisp['extname'].', '.$rDisp['fname'].' '.$rDisp['mname']?></td>
						<td><?php echo position($rDisp['emp_id'])?></td>
						<td><?php echo $rDisp['work_status'];?></td>
						<td><div align="center"><a id="deduct<?php echo $rDisp['emp_id']?>" class="btn btn-mini btn-info thickbox" title="Deduction Manage" data-rel="tooltip" onclick="showThis(this.id,'payroll_deduction_reference_detail.php?eid=<?php echo functions::encode($rDisp['emp_id']);?>','Employee Adjustment Manage','1')"><i class="halflings-icon white minus-sign"></i></a></div></td>
						<td><div align="center"><a id="addon<?php echo $rDisp['emp_id']?>" class="btn btn-mini btn-info thickbox" title="Add-on Manage" data-rel="tooltip" onclick="showThis(this.id,'payroll_addon_reference_detail.php?eid=<?php echo functions::encode($rDisp['emp_id']);?>','Employee Adjustment Manage','1')"><i class="halflings-icon white plus-sign"></i></a></div></td>
					</tr>
					<?php
					endwhile;
					if($countResult==0){
					?>
					<tr>
						<td colspan="6"><div align="center">---Nothing to Report!---</div></td>
					</tr>
					<?php }?>
				</tbody>
			</table>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}</script>