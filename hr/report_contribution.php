<?php require_once('templ_up.php');?>
<?php
$arr = array();
if( isset($_POST['btnSearch']) ){
	$_SESSION['rcEmpID'] = ( isset($_POST['txSearch']) && !empty($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
	functions::sendTo($_SERVER['PHP_SELF']);
}
$emp_id = ( isset($_SESSION['rcEmpID']) ) ? $_SESSION['rcEmpID'] : '';
$andwhere = 'WHERE';
if($emp_id){
	$arr = array_merge($arr,array('emp.emp_id'=>$emp_id));
	$andwhere = 'AND';
}
?>
	<style>
	/* Style the header */
	.header {
		background: #CCC;
	}
	/* The sticky class is added to the header with JS when it reaches its scroll position */
	.sticky {
		position: fixed;
		top: 0;
		width: 97%
	}
	.hideit{
		display:none;
	}
	.brdrNone{
		border:none;
	}
	</style>
<style type="text/css">
/*.table-wrapper{overflow-y: scroll;height: 700px;}*/
.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
</style>
<!-- body content: start here-->
<div align="right"><a id="vwMonitor" class="btn btn-info btn-mini thickbox" style="cursor:pointer;" title="Contribution Monitoring Detail" data-rel="tooltip" onclick="showThis(this.id,'report_contribution_monitor.php?','Contribution Monitor','1')">Contribution Monitoring</a></div>
<div align="right"><a id="adc" href="#" class="btn btn-info thickbox" style="display:none;" title="Print 13 Month Payroll" data-rel="tooltip" onclick="showThis(this.id,'month13_print.php?','Generate 13th Month Payroll')"><i class="halflings-icon white print"></i></a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>SSS, Philhealth, Pagibig Contribution</h2>
		</div>
		<div class="box-content">

			<form method="post">
				<div align="center">
					<div style="width:640px;">
						<table border="0" width="90%">
							<tr>
								<td width="20%" style="padding-top: 8px;">
									<select name="txSearch" id="txSearch" data-rel="chosen" style="width:480px;">
										<option value="">--All Contributor--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>" <?php if($emp_id==$rEU['emp_id'])echo 'selected="selected"'; ?>><?php echo strtoupper($rEU['emp_no'].' - '.$rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
								</td>
								<td width="7%"><div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="&nbsp;&nbsp;View&nbsp;&nbsp;" class="btn btn-primary btn-small"></div></td>
							</tr>
						</table>
					</div>
				</div>
			</form>
			<div class="table-wrapper">
			<table class="table table-bordered table-hover table-striped" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="10%" scope="col">Emp No.</th>
						<th width="35%" scope="col"><div align="left">Name</div></th>
						<th width="10%" scope="col"><div align="right">SSS</div></th>
						<th width="10%" scope="col"><div align="right">Philhealth</div></th>
						<th width="10%" scope="col"><div align="right">Pagibig</div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$countEmp=0;$linkID=0;
					$qDisp = $db->select('emp_attendance ea, emp_payroll_detail epd, employee emp','emp.emp_id,emp_no, lname, fname, sum(epd.sss) as totalSSS, sum(ph) as totalPH, sum(hdmf) as totalHDMF',$arr,$andwhere.' ea.eat_id=epd.eat_id AND emp.emp_id=epd.emp_id GROUP BY emp.emp_id ORDER BY lname,fname');
					#echo $db->last_query;
					#$total13month=0;
					while($rDisp = $db->fetch_array($qDisp)):
						$countEmp++;
						$emp_id = $rDisp['emp_id'];
						#$total13month += $rDisp['month13'];
					?>
					<tr>
						<td><?php echo $rDisp['emp_no']?></td>
						<td><?php echo $rDisp['lname'].', '.$rDisp['fname']; ?></td>
						<td><div align="right"><a id="vw<?php echo $linkID++?>" class="thickbox" style="cursor:pointer;" title="SSS Contribution Detail" data-rel="tooltip" onclick="showThis(this.id,'report_contribution_individual.php?empid=<?php echo functions::encode($emp_id);?>&type=<?php echo functions::encode('sss')?>','SSS Contribution Detail','1')"><?php echo functions::formatMoney($rDisp['totalSSS']);?></a></div></td>
						<td><div align="right"><a id="vw<?php echo $linkID++?>" class="thickbox" style="cursor:pointer;" title="Philhealth Contribution Detail" data-rel="tooltip" onclick="showThis(this.id,'report_contribution_individual.php?empid=<?php echo functions::encode($emp_id);?>&type=<?php echo functions::encode('ph')?>','Philhealth Contribution Detail','1')"><?php echo functions::formatMoney($rDisp['totalPH']);?></a></div></td>
						<td><div align="right"><a id="vw<?php echo $linkID++?>" class="thickbox" style="cursor:pointer;" title="Pagibig Contribution Detail" data-rel="tooltip" onclick="showThis(this.id,'report_contribution_individual.php?empid=<?php echo functions::encode($emp_id);?>&type=<?php echo functions::encode('hdmf')?>','Pagibig Contribution Detail','1')"><?php echo functions::formatMoney($rDisp['totalHDMF']);?></a></div></td>
					</tr>
					<?php endwhile;?>
					<?php if($countEmp==0){ ?>
					<tr>
						<td colspan="5"><div align="center">--Nothing To Report--</div></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			</div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>
function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}
// When the user scrolls the page, execute myFunction
window.onscroll = function() {myFunction()};
window.onload = function(){header.classList.add("hideit");};

// Get the header
var header = document.getElementById("myHeader");

// Get the offset position of the navbar
var sticky = header.offsetTop;
sticky = 200
// Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
function myFunction() {
	if (window.pageYOffset > sticky) {
		header.classList.add("sticky");
		header.classList.remove("hideit");
	}else{
		header.classList.remove("sticky");
		header.classList.add("hideit");
	}
}
</script>