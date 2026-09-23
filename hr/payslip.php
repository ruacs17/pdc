<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arr = array();

if( isset($_POST['btnSearch']) ){
	$_SESSION['prID'] = ( isset($_POST['selPayroll']) && !empty($_POST['selPayroll']) ) ? functions::decode($_POST['selPayroll']) : '';
	$_SESSION['prMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['prYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
	functions::sendTo(functions::pageName());
}
if( isset($_REQUEST['selPayroll']) ){
	$_SESSION['prID'] = ( isset($_REQUEST['selPayroll']) && !empty($_REQUEST['selPayroll']) ) ? functions::decode($_REQUEST['selPayroll']) : '';
	functions::sendTo(functions::pageName());
}

$selPID = ( isset($_SESSION['prID']) && !empty($_SESSION['prID']) ) ? $_SESSION['prID'] : '';
$txbMon = ( isset($_SESSION['prMon']) ) ? $_SESSION['prMon'] : date('m');
$txbYear = ( isset($_SESSION['prYear']) ) ? $_SESSION['prYear'] : date('Y');
$arr = array('confirmed'=>1);
/*if($txbMon && $txbYear)
	$arr = array_merge($arr,array('LEFT(date_start,7)'=>$txbYear.'-'.$txbMon));
else if($txbMon)
	$arr = array_merge($arr,array('SUBSTRING(date_start,6,2)'=>$txbMon));
elseif($txbYear)
	$arr = array_merge($arr,array('LEFT(date_start,4)'=>$txbYear));
*/
if($txbMon && $txbYear)
	$arr = array_merge($arr,array('att_year'=>$txbYear,'att_month'=>$txbMon));
else if($txbMon)
	$arr = array_merge($arr,array('att_month'=>$txbMon));
elseif($txbYear)
	$arr = array_merge($arr,array('att_year'=>$txbYear));
/*if($selPID)
	$arr = array_merge($arr,array('eat_id'=>$selPID));*/
?>
<!-- body content: start here-->
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
<link href="../css/select2.min.css" rel="stylesheet">
<div align="right" style="display:none;"><a id="adc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'attendance_upload.php?','Attendance Upload')">Upload Attendance Log</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="adcd" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'attendance_report_add.php?','Create Attendance Report')">Create Attendance Report</a></div><br>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Payroll</h2>
		</div>
		<div class="box-content">
			<form method="post">
				<div align="center">
					<table border="0" width="95%">
						<tr>
							<td style="padding-top: 8px;">
								<div align="center">
									<select name="bdYear" id="bdYear" onChange="showPayroll(this.value,document.getElementById('bdMon').value)" style="width:95px;">
										<option value="">--Year--</option>
										<?php
										$qYr = $db->select('emp_attendance','DISTINCT LEFT(date_start,4) as yr',array(),'ORDER BY date_start DESC');
										while($rYr = $db->fetch_array($qYr)):
										?>
										<option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
							<td style="padding-top: 8px;">
								<div id="divMonth" align="center">
									<select name="bdMon" id="bdMon" onChange="showPayroll(document.getElementById('bdYear').value,this.value)" style="width:95px;">
										<option value="">--Month--</option>
										<option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
										<option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
										<option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
										<option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
										<option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
										<option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
										<option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
										<option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
										<option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
										<option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
										<option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
										<option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
									</select>
								</div>
							</td>
							<td width="70%">
								<div id="divPayroll">
									<select name="selPayroll" id="selPayroll" style="width:95%;font-size:12px;">
										<option value="">--Select Payroll--</option>
										<?php 
										$qEA = $db->select('emp_attendance','*',$arr,'ORDER BY date_start DESC');
										while($rEA = $db->fetch_array($qEA)):?>
										<option value="<?php echo functions::encode($rEA['eat_id'])?>" <?php if($selPID==$rEA['eat_id'])echo 'selected="selected"';?>><?php echo $rEA['payroll_no'].' <i>('.functions::datearr($rEA['date_start']).' - '.functions::datearr($rEA['date_end']).')</i>';?></option>
										<?php endwhile;?>
									</select>
								</div>
							</td>
							<td width="7%"><div align="center"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary btn-small"></div></td>
						</tr>
					</table>
				</div>
			</form>
			<table class="table table-bordered table-hover" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC">
						<th scope="col">Payroll No.</th>
						<th scope="col">Date Range</th>
						<th scope="col">Project / Department</th>
						<th scope="col">Note</th>
						<?php if(empty($selPID)){ ?><th scope="col">View</th><?php } ?>
					</tr>
				</thead>
				<tbody>
				<?php
				$arrList = $arr;
				if($selPID)
					$arrList = array_merge($arrList,array('eat_id'=>$selPID));
				$qDisp = $db->select('emp_attendance','*',$arrList,'ORDER BY date_start DESC,proj_id,payroll_no');
				$countAtt=0;
				while($rDisp = $db->fetch_array($qDisp)):
					$rID = $rDisp['eat_id'];
					$dateRangeDisp='';
					$date_from=$rDisp['date_start'];
					$date_to=$rDisp['date_end'];
					$days = functions::date_diff($date_from,$date_to,$includeDay1=1);
					$dtfArr = explode('-',$date_from);
					$monthFrom = isset($dtfArr[1]) ? $dtfArr[1] :'00';
					$dayFrom =  isset($dtfArr[2]) ? $dtfArr[2] :'00';
					$yrFrom =  isset($dtfArr[0]) ? $dtfArr[0] :'0000';
					$dttArr = explode('-',$date_to);
					$monthTo = isset($dttArr[1]) ? $dttArr[1] :'00';
					$dayTo =  isset($dttArr[2]) ? $dttArr[2] :'00';
					$yrTo =  isset($dttArr[0]) ? $dttArr[0] :'0000';
					if($yrFrom==$yrTo){
						if($monthFrom==$monthTo){
							$dateMonthName = date('M', strtotime($yrFrom.'-'.$monthFrom.'-'.'01'));
						$dateRangeDisp = $dateMonthName.' '.$dayFrom.' - '.$dayTo.', '.$yrFrom;
					}
					else
						$dateRangeDisp = functions::datearr($date_from).' - '.functions::datearr($date_to);
					}
					else
					$dateRangeDisp = functions::datearr($date_from).' - '.functions::datearr($date_to);

				?>
					<tr>
						<td><?php echo $rDisp['payroll_no'];?></td>
						<td><?php echo $dateRangeDisp.' (<i>'.$days.' days</i>)';?></td>
						<td><?php echo $db->getValue('project','proj_name',array('proj_id'=>$rDisp['proj_id'])); ?></td>
						<td><?php echo $rDisp['note'];?></td>
						<?php if(empty($selPID)){ ?>
						<td width="5%">
							<div align="center">
								<a id="vw<?php echo $countAtt++?>" class="btn btn-mini btn-info" title="View Payroll Detail" href="?selPayroll=<?php echo functions::encode($rDisp['eat_id']); ?>" ><i class="halflings-icon white zoom-in"></i></a>
							</div>
						</td>
						<?php } ?>
					</tr>
				<?php endwhile;?>
				</tbody>
			</table>
			<?php if($selPID){ ?>
			<br>
			<div align="center"><h2>--- Payroll List ---</h2></div>
			<div class="table-wrapper">
			<table class="table table-bordered table-hover table-striped" style="font-size: 12px;">
				<thead>
					<tr style="background-color:#CCC">
						<th width="5%" scope="col">No.</th>
						<th width="10%" scope="col"><div align="left">Employee No</div></th>
						<th width="25%" scope="col"><div align="left">Name</div></th>
						<th width="10%" scope="col"><div align="right">Basic Salary</div></th>
						<th width="10%" scope="col"><div align="right">Gross Pay</div></th>
						<th width="10%" scope="col"><div align="right">Deduction</div></th>
						<th width="10%" scope="col"><div align="right">Net</div></th>
						<th width="5%" scope="col"><div align="center">&nbsp;</div></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$countEmp=0;
					$qDisp = $db->select('emp_attendance ea, emp_payroll_detail epd, employee emp','ea.*,epd.*,emp.emp_no,emp.lname,emp.fname',array('ea.eat_id'=>$selPID),'AND ea.eat_id=epd.eat_id AND emp.emp_id=epd.emp_id ORDER BY lname,fname');
					#echo $db->last_query;
					$totalNet=0;
					while($rDisp = $db->fetch_array($qDisp)):
						$countEmp++;
						$emp_id = $rDisp['emp_id'];
						$totalNet += $rDisp['net_pay'];
						$basic_salary_display='';
						if($rDisp['salary_type']=='flexible')
							$basic_salary_display = functions::formatMoney($rDisp['salary']);
						else if($rDisp['salary_type']=='fixed'){
							$sal_daily = $db->getValue('emp_salary','es_daily',array('emp_id'=>$emp_id),'AND es_date <= "'.$rDisp['date_end'].'" ORDER BY es_date DESC LIMIT 1');
							$sal_daily = ($sal_daily) ? $sal_daily : 0;
							$basic_salary_display = functions::formatMoney($sal_daily).' <i>(Daily)</i>';
						}
					?>
					<tr>
						<td><?php echo $countEmp?></td>
						<td><div align="left"><?php echo $rDisp['emp_no']?></div></td>
						<td><?php echo $rDisp['lname'].', '.$rDisp['fname']; ?></td>
						<td><div align="right"><?php echo $basic_salary_display;?></div></td>
						<td><div align="right" title="<?php echo functions::formatMoney($rDisp['gross'],'~');?>"><?php echo functions::formatMoney($rDisp['gross']);?></div></td>
						<td><div align="right" title="<?php echo functions::formatMoney($rDisp['total_deduction'],'~');?>"><?php echo functions::formatMoney($rDisp['total_deduction']);?></div></td>
						<td><div align="right" title="<?php echo functions::formatMoney($rDisp['net_pay'],'~');?>"><strong><?php echo functions::formatMoney($rDisp['net_pay']);?></strong></div></td>
						<td>
							<div align="center">
								<a id="vw<?php echo $countEmp?>" class="btn btn-mini btn-info thickbox" title="Payroll Detail" data-rel="tooltip" onclick="showThis(this.id,'payslip_individual2.php?empid=<?php echo functions::encode($emp_id);?>&eatid=<?php echo functions::encode($rDisp['eat_id'])?>','Payroll Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
							</div>
						</td>
					</tr>
					<?php endwhile;?>
					<tr>
						<td colspan="7"><div align="right" title="<?php echo functions::formatMoney($totalNet,'~');?>"><strong>Total: <?php echo functions::formatMoney($totalNet);?></strong></div></td>
						<td></td>
					</tr>
				</tbody>
			</table>
			</div>
			<?php }//if($selPID) ?>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<script>
function delt(){if(confirm('Do you want to remove this?'))return true; else return false;}
</script>

<?php require_once('templ_down.php');?>

<script src="../js/select2.js"></script>
<script type="text/javascript">

function getXMLHTTP() { //fuction to return the xml http object
	var xmlhttp=false;
	try{
		xmlhttp=new XMLHttpRequest();
	}
	catch(e){
		try{
			xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch(e){
			try{
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch(e1){
				xmlhttp=false;
			}
		}
	}
	return xmlhttp;
}
  
function showPayroll(yr,mon) {
	var strURL="drop_down_payroll.php?yr="+yr+"&mn="+mon;
	var req = getXMLHTTP();

	if (req){
		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				// only if "OK"
				if (req.status == 200) {
					document.getElementById('divPayroll').innerHTML=req.responseText;
					$("#selPayroll").select2();
				}else{
					alert("Problem while using XMLHTTP:\n" + req.statusText);
				}
			}
		}
		req.open("GET", strURL, true);
		req.send(null);
	}
}
$("#selPayroll").select2();
</script>
<script>
// When the user scrolls the page, execute myFunction
window.onscroll = function() {myFunction()};
window.onload = function(){header.classList.add("hideit");};

// Get the header
var header = document.getElementById("myHeader");

// Get the offset position of the navbar
var sticky = header.offsetTop;
sticky = 380 //bigger number, deeper display
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