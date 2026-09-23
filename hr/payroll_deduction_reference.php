<?php require_once('templ_up.php');?>
<?php
$searchVal='';
$qDisp = $db->select('employee','*',array(),'ORDER BY lname');
if( isset($_POST['btnSearch']) ){
	$searchVal = ( isset($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
	$qDisp = $db->query('SELECT * FROM employee WHERE emp_no LIKE "%'.$db->clean($searchVal).'%" OR lname LIKE "%'.$db->clean($searchVal).'%" OR fname LIKE "%'.$db->clean($searchVal).'%" ORDER BY lname');
}
?>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Employee List</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center">
					<table border="0">
						<tr>
							<td style="padding-top: 7px;"><input type="text" name="txSearch" id="txSearch" value="<?php echo $searchVal?>"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info" name="btnSearch" id="btnSearch" value="Search"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info" name="btnAll" id="btnAll" value="Clear"></td>
						</tr>
					</table>
				</div>
			</form>
			<table class="table table-bordered table-hover" style="font-size: 12px;">
				<thead>
					<tr>
						<th width="20%" scope="col">ID No.</th>
						<th width="27%" scope="col">NAME</th>
						<th width="20%" scope="col">ADDRESS</th>
						<th width="15%" scope="col">CONTACT #</th>
						<th width="14%" scope="col"><div align="center">DETAILS</div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if($searchVal){
						$countResult=0;
						while($rDisp = $db->fetch_array($qDisp)):
							$countResult++;
					?>
					<tr>
						<td><?php echo $rDisp['emp_no']?></td>
						<td style="font-size: 14px;"><?php echo $rDisp['lname'].' '.$rDisp['extname'].', '.$rDisp['fname'].' '.$rDisp['mname']?></td>
						<td><?php echo $rDisp['bplace']?></td>
						<td><?php echo $rDisp['cell_no'];?></td>
						<td><div align="center"><a id="edit<?php echo $rDisp['emp_id']?>" class="btn btn-mini btn-info thickbox" title="Employee Deduction Manage" data-rel="tooltip" onclick="showThis(this.id,'payroll_deduction_reference_detail.php?eid=<?php echo functions::encode($rDisp['emp_id']);?>','Employee Deduction Manage')"><i class="halflings-icon white zoom-in"></i></a></div></td>
					</tr>
					<?php
						endwhile;
						if($countResult==0){
					?>
					<tr>
						<td colspan="5"><div align="center">---No Match Found!---</div></td>
					</tr>
					<?php }
					}?>
				</tbody>
			</table>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>function delt(){if(confirm('Do you want to remove this item?'))return true; else return false;}</script>