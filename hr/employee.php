<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
$searchVal='';
$qDisp = $db->select('employee','*',$arrVal,'ORDER BY lname');
if( isset($_POST['btnSearch']) ){
	$searchVal = ( isset($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
	functions::sendTo(functions::pageName().'?emp='.functions::encode($searchVal));
	die();
}
$searchVal = ( isset($_REQUEST['emp']) ) ? functions::decode($_REQUEST['emp']) : '';
$qDisp = $db->select('employee','*',array('emp_id'=>$searchVal));
?>
<!-- body content: start here-->
<div align="right">
	<a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'employee_add.php?','Employee Detail')">Add New Employee</a>&nbsp;&nbsp;
	<a id="adcd" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'employee_list.php?','Employee Detail','1')">Employee List</a>&nbsp;&nbsp;
	<a href="employee_export_menu.php?from=<?php echo functions::encode($_SERVER['REQUEST_URI']) ?>" class="btn btn-info btn-small">Employee List Download</a>&nbsp;&nbsp;
</div><br>
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
							<td style="padding-top: 7px;">
								<select name="txSearch" id="txSearch" data-rel="chosen" style="width:480px;">
									<option value="">--Select Employee--</option>
									<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
									while($rEU = $db->fetch_array($qEU)):
									?>
									<option value="<?php echo $rEU['emp_id']?>" <?php if($searchVal==$rEU['emp_id'])echo 'selected="selected"'; ?>><?php echo strtoupper($rEU['emp_no'].' - '.$rEU['lname'].', '.$rEU['fname']);?></option>
									<?php endwhile;?>
								</select>
								<input style="display:none;" type="text" name="txSearch_" id="txSearch_" value="<?php echo $searchVal?>"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-primary btn-small" name="btnSearch" id="btnSearch" value="Search"></td>
							<td>&nbsp;&nbsp; <a href="?" class="btn btn-info btn-small">&nbsp;&nbsp;Clear&nbsp;&nbsp;</a></td>
						</tr>
					</table>
				</div>
			</form>
			<table class="table table-bordered table-hover table-striped" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="10%" scope="col">ID No.</th>
						<th width="24%" scope="col">NAME</th>
						<th width="20%" scope="col">ADDRESS</th>
						<th width="25%" scope="col">POSITION</th>
						<th width="10%" scope="col"><div align="center">STATUS</div></th>
						<th width="14%" scope="col"><div align="center">OPTION</div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if($searchVal){
						$countResult=0;
						while($rDisp = $db->fetch_array($qDisp)):
							$countResult++;
							$curStreet = $rDisp['curr_street'];
							$curProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$rDisp['curr_province']));
							$curProvince = ($curProvince) ? ', '.$curProvince : '';
							$curCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$rDisp['curr_cityMun']));
							$curCity = ($curCity) ? ', '.$curCity : '';
							$curBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$rDisp['curr_add']));
							$current_address = $curStreet.' '.strtoupper($curBrngy).$curCity.$curProvince;
					?>
					<tr>
						<td><?php echo $rDisp['emp_no']?></td>
						<td style="font-size: 14px;"><?php echo $rDisp['lname'].' '.$rDisp['extname'].', '.$rDisp['fname'].' '.$rDisp['mname']?></td>
						<td><?php echo $current_address?></td>
						<td>
							<?php
							$qDep = $db->select('emp_position ep, dep_position dp','*',array('emp_id'=>$rDisp['emp_id']),'AND ep.dp_id=dp.dp_id ORDER BY dp.pos_name');
							$countDep = $db->num_rows($qDep);
							if($countDep>1){
							?>
							<table width="100%" class="table-bordered">
								<tr>
									<td><strong>Department</strong></td>
									<td><strong>Position</strong></td>
								</tr>
								<?php
								while($rDep = $db->fetch_array($qDep)):
								?>
								<tr>
									<td><?php echo $db->getValue('department','dep_name',array('dep_id'=>$rDep['dep_id']))?></td>
									<td><?php echo $rDep['pos_name']?></td>
								</tr>
								<?php endwhile;?>
							</table><br><br>
							<?php
							}else if($countDep==1){
								$rDep = $db->fetch_array($qDep);
								$depName = $db->getValue('department','dep_name',array('dep_id'=>$rDep['dep_id']));
							?>
							<?php if($depName) {?>Department: <div style="padding-left:15px;"><strong><?php echo $depName?></strong></div><br><?php } ?> Position: <strong><?php echo $rDep['pos_name']?></strong>
							<?php }?>
						</td>
						<td>
							<div align="center">
								<?php
								$qStat = $db->select('emp_work_status','*',array('emp_id'=>$rDisp['emp_id']),'ORDER BY ews_date DESC, ews_id DESC LIMIT 1');
								$rStat = $db->fetch_array($qStat);
								echo '<strong>'.$rStat['ews_stat'].'</strong>';
								echo '<div>'.functions::datearr($rStat['ews_date']).'</div>';
								echo ($rStat['project_based']) ? '<div>(<i>Project Based</i>)</div>' : '';
								
								$date_hired=$rStat['ews_date'];

								$datehired = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$rDisp['emp_id']),'ORDER BY ews_date, ews_id DESC LIMIT 1');
								$datetime1 = new DateTime($datehired);
								$datetime2 = new DateTime(date('Y-m-d'));
								$interval = $datetime1->diff($datetime2);
								$intrval_year = $interval->format('%y');
								$intrval_month = $interval->format('%m');
								$intrval_day = $interval->format('%d');
								$diffInMonths = ($intrval_year * 12) + $intrval_month;
								$display_yr=$display_mn=$display_day='';
								if($intrval_year){
									$display_yr = ($intrval_year > 1) ? $intrval_year.' Years, ' : $intrval_year.' Year, ';
								}
								if($intrval_month){
									$display_mn = ($intrval_month > 1) ? $intrval_month.' Months, ' : $intrval_month.' Month, ';
								}
								if($intrval_day){
									$display_day = ($intrval_day > 1) ? $intrval_day.' Days ' : $intrval_day.' Day ';
								}
								$intval = ($datehired=='0000-00-00' || $datehired=='') ? '----' : $display_yr.$display_mn.$display_day;
								?>
								<div align="left" style="padding-top:20px;">Date Hired: <?php echo functions::datearr($datehired); ?></div>
								<div align="left" style="padding-top:5px;">Length of Service: </div>
								<div align="left">Year: <?php echo $intrval_year ?></div>
								<div align="left">Month: <?php echo $intrval_month ?></div>
								<div align="left">Day: <?php echo $intrval_day ?></div>
							</div>
						</td>
						<td>
							<div align="center">
								<a id="cl<?php echo $rDisp['emp_id']?>" class="btn btn-mini btn-info thickbox" title="File 201 Checklist" data-rel="tooltip" onclick="showThis(this.id,'employee_checklist.php?eid=<?php echo functions::encode($rDisp['emp_id']);?>','Employee 201 Detail','1')"><i class="halflings-icon white list"></i></a>
								<a id="vw<?php echo $rDisp['emp_id']?>" class="btn btn-mini btn-info thickbox" title="Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_detail.php?eid=<?php echo functions::encode($rDisp['emp_id']);?>','Employee Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
								<a id="edit<?php echo $rDisp['emp_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify Employee Detail" data-rel="tooltip" onclick="showThis(this.id,'employee_edit.php?eid=<?php echo functions::encode($rDisp['emp_id']);?>','Employee Detail Update')"><i class="halflings-icon white pencil"></i></a>
							</div>
						</td>
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
<script>function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}</script>