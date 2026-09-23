<?php require_once('templ_up.php');?>
<?php
if( isset($_POST['btnSearch']) ){
	$_SESSION['month13Year'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	functions::sendTo($_SERVER['PHP_SELF']);
}
$txbYear = ( isset($_SESSION['month13Year']) ) ? $_SESSION['month13Year'] : '';
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
	.table-wrapper thead tr:nth-child(1) th { background: #DDD;position: sticky; top: 0px; }
	</style>
<!-- body content: start here-->
<div align="right"><?php if($txbYear){ ?><a id="adc" href="#" class="btn btn-info thickbox" title="Print 13 Month Payroll" data-rel="tooltip" onclick="showThis(this.id,'month13_print.php?','13th Month Payroll Print')"><i class="halflings-icon white print"></i></a><?php } ?></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>13th Month Payroll</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center">
					<div style="width:250px;">
						<table border="0" width="90%">
							<tr>
								<td width="20%" style="padding-top: 8px;">
									<div align="center">
										<select name="bdYear" id="bdYear" style="width:130px;">
											<option value="">--Select Year--</option>
											<?php
											$qYr = $db->select('emp_attendance','DISTINCT LEFT(date_start,4) as yr',array(),'ORDER BY date_start DESC');
											while($rYr = $db->fetch_array($qYr)):
											?>
											<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
											<?php endwhile;?>
										</select>
									</div>
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
					<tr style="background-color:#CCC">
						<th width="5%" scope="col">No.</th>
						<th width="10%" scope="col"><div align="left">Employee No</div></th>
						<th width="25%" scope="col"><div align="left">Name</div></th>
						<th width="10%" scope="col"><div align="right">Total Gross</div></th>
						<th width="10%" scope="col"><div align="right">Total Absent</div></th>
						<th width="10%" scope="col"><div align="right">Total Net</div></th>
						<th width="10%" scope="col"><div align="right">13 Month</div></th>
						<th width="5%" scope="col"><div align="center">&nbsp;</div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$countEmp=0;
					$qDisp = $db->select('emp_attendance ea, emp_payroll_detail epd, employee emp','emp.emp_no,emp.emp_id, lname, fname, sum(gross) as totalGross, sum(absent) as totalAbsent, sum(gross-absent) as totalNet,(sum(gross-absent)/12) as month13',array('att_year'=>$txbYear),'AND ea.eat_id=epd.eat_id AND emp.emp_id=epd.emp_id GROUP BY emp.emp_id ORDER BY lname,fname');
					#echo $db->last_query;
					$total13month=0;
					while($rDisp = $db->fetch_array($qDisp)):
						$countEmp++;
						$emp_id = $rDisp['emp_id'];
						$total13month += $rDisp['month13'];
					?>
					<tr>
						<td><?php echo $countEmp?></td>
						<td><div align="left"><?php echo $rDisp['emp_no']?></div></td>
						<td><?php echo $rDisp['lname'].', '.$rDisp['fname']; ?></td>
						<td><div align="right"><?php echo functions::formatMoney($rDisp['totalGross']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rDisp['totalAbsent']);?></div></td>
						<td><div align="right"><?php echo functions::formatMoney($rDisp['totalNet']);?></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($rDisp['month13']);?></strong></div></td>
						<td>
							<div align="center">
								<a id="vw<?php echo $countEmp?>" class="btn btn-mini btn-info thickbox" title="13th Month Payroll Detail" data-rel="tooltip" onclick="showThis(this.id,'month13_individual.php?empid=<?php echo functions::encode($emp_id);?>&yr=<?php echo functions::encode($txbYear)?>','13th Month Payroll Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
							</div>
						</td>
					</tr>
					<?php endwhile;?>
					<tr>
						<td colspan="7"><div align="right"><strong>Total: <?php echo functions::formatMoney($total13month);?></strong></div></td>
						<td></td>
					</tr>
				</tbody>
			</table>
			</div>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}
</script>
<?php require_once('templ_down.php');?>