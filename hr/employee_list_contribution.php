<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
function empDetail($rEmp,$dep_id=0,&$count=0){
	global $db;
	$empCount=1;
	$emp_status=$rEmp['work_status'];
	$assign_ebr = $db->select('emp_benefit_ref','*',array('emp_id'=>$rEmp['emp_id'],'benefit_type'=>'sss'),'ORDER BY date_start DESC LIMIT 1');
	$ebrr = $db->fetch_array($assign_ebr);
    ?>
	<tr>
		<td><div align="center"><?php echo $count++; ?></div></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['emp_no'] ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['lname']; echo ($rEmp['extname']) ? ', '.$rEmp['extname'] : ''; ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['fname'] ?></td>
		<td><div align="left" style="padding-left:3px"><?php echo $rEmp['mname'] ?></td>
		<td>
			<div align="left" style="padding-left:3px">
				<?php
				$q = $db->select('emp_position ep, dep_position dp','*',array('dep_id'=>$dep_id,'emp_id'=>$rEmp['emp_id']),'AND dp.dp_id=ep.dp_id');
				$cntPos = $db->num_rows($q);
				$countPos=1;
				$position='';
				while($r = $db->fetch_array($q)):
					$position .= $r['pos_name'];
					if($countPos < $cntPos)
						$position.=' / ';
					$countPos++;
				endwhile;
				echo $position;
				?>
			</div>
		</td>
		<td><div align="left" style="padding-left:3px"><?php echo $emp_status; ?></div></td>
		<td><div align="right" style="padding-right:3px"><?php echo functions::formatMoney($rEmp['salary'])?></div></td>
		<td><div align="center"><?php echo functions::formatMoney($ebrr['sal_from']).' - '.functions::formatMoney($ebrr['sal_to']) ?></div></td>
		<td><div align="right" style="padding-right:3px"><?php echo functions::formatMoney($ebrr['ee_share']) ?></div></td>
		<td><div align="right" style="padding-right:3px"><?php echo functions::formatMoney($ebrr['er_share']) ?></div></td>
		<td><div align="right" style="padding-right:3px"><?php echo functions::formatMoney($ebrr['ec']) ?></div></td>
		<td><div align="right" style="padding-right:3px"><?php echo functions::formatMoney($rEmp['sss_contribution'])?></div></td>
		<td>
			<div align="center" style="padding-top:3px;padding-bottom:3px;">
				<a id="edit<?php echo functions::encode($count.$rEmp['emp_id'].$dep_id)?>" class="btn btn-mini btn-warning thickbox" title="Modify Contribution Reference" data-rel="tooltip" onclick="showThis(this.id,'payroll_deduction_reference_benefit.php?eid=<?php echo functions::encode($rEmp['emp_id']);?>&benType=<?php echo functions::encode('sss');?>','Employee Detail Update')"><i class="halflings-icon white pencil"></i></a>
			</div>
		</td>
	</tr>
	<?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee SSS Deduction List</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<link id="base-style" href="../css/loader.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<!-- start: Favicon -->
	<link rel="shortcut icon" href="../img/favicon.png">
	<!-- end: Favicon -->
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
	.padright{padding-right: 5px;}
	.padleft{padding-left: 5px;}
	</style>
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post">
				<div align="center" style="padding-bottom: 15px;"><h2>SSS ASSIGNED DEDUCTION</h2></div>
				<table class="table-hover" border="1" style="font-size: 12px;">
					<thead>
						<tr bgcolor="#CCC">
							<th width="2%" scope="col">No</th>
							<th width="4%" scope="col">ID</th>
							<th width="7%" scope="col">LAST NAME</th>
							<th width="7%" scope="col">FIRST NAME</th>
							<th width="4%" scope="col">MIDDLE NAME</th>
							<th width="15%" scope="col">POSITION</th>
							<th width="4%" scope="col">STATUS</th>
							<th width="5%" scope="col">MONTHLY</th>
							<th width="9%" scope="col">RANGE</th>
							<th width="5%" scope="col">EE SHARE</th>
							<th width="5%" scope="col">ER SHARE</th>
							<th width="5%" scope="col">EC</th>
							<th width="5%" scope="col"><div style="font-size:10px;">DEDUCTION PER PAYROLL</div></th>
							<th width="4%" scope="col"><div align="center">&nbsp;</div></th>
						</tr>
						<tr bgcolor="#CCC" class="header" id="myHeader">
							<th width="2%" scope="col">No</th>
							<th width="3%" scope="col">ID</th>
							<th width="7%" scope="col">LAST NAME</th>
							<th width="7%" scope="col">FIRST NAME</th>
							<th width="6%" scope="col">MIDDLE NAME</th>
							<th width="14%" scope="col">POSITION</th>
							<th width="4%" scope="col">STATUS</th>
							<th width="5%" scope="col">MONTHLY</th>
							<th width="8%" scope="col">RANGE</th>
							<th width="5%" scope="col">EE SHARE</th>
							<th width="5%" scope="col">ER SHARE</th>
							<th width="5%" scope="col">EC</th>
							<th width="5%" scope="col"><div style="font-size:10px;">DEDUCTION PER PAYROLL</div></th>
							<th width="1%" scope="col"><div align="center">&nbsp;</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$countEmpEntireDep=0;
					$empDepPosCount=0;
					$empSecPosCount=0;
					$qDep = $db->select('department','*',array(),'WHERE dep_head is NULL ORDER BY dep_name LIMIT 1');
					while($rDep = $db->fetch_array($qDep)):
						echo '<tr><td colspan="14">&nbsp;</td></tr>';
						echo '<tr><td colspan="14" bgcolor="#CCFF00"><strong>'.$rDep['dep_name'].'</strong></td></tr>';
						$empDepPosCount++;

						$qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',array('dp.dep_id'=>$rDep['dep_id']),'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id AND (work_status="Regular" || work_status="Probationary" || work_status="Contractual") GROUP BY emp.emp_id,dp.dep_id ORDER BY lname, fname');
						while($rEmp = $db->fetch_array($qEmp)):
							empDetail($rEmp,$rDep['dep_id'],$empDepPosCount);
						endwhile;
						$countEmpEntireDep += $empDepPosCount-1;

						$empDepPosCount=0;

						$qSec = $db->select('department','*',array('dep_head'=>$rDep['dep_id']),'ORDER BY dep_name');
						while($rSec = $db->fetch_array($qSec)):
							$empSecPosCount++;
							echo '<tr><td colspan="14">&nbsp;</td></tr>';
							echo '<tr><td colspan="14" style="padding-left:35px;" bgcolor="#009900"><strong><i>'.$rSec['dep_name'].'</i></strong></td></tr>';

							$qEmp = $db->select('employee emp, emp_position ep, dep_position dp','emp.*,dp.dep_id',array('dp.dep_id'=>$rSec['dep_id']),'AND emp.emp_id=ep.emp_id AND ep.dp_id=dp.dp_id GROUP BY emp.emp_id,dp.dep_id ORDER BY lname, fname');
							while($rEmp = $db->fetch_array($qEmp)):
								empDetail($rEmp,$rSec['dep_id'],$empSecPosCount);
							endwhile;
							$countEmpEntireDep += $empSecPosCount-1;
							$empSecPosCount=0;

						endwhile;//while($rSec = $db->fetch_array($qSec)):
						echo '<tr><td colspan="14" style="padding-left:10px;"><div align="center"><strong><i>'.$rDep['dep_name'].' : ('.$countEmpEntireDep.')'.'</i></strong></div></td></tr>';
						$countEmpEntireDep=0;
						echo '<tr><td colspan="14">&nbsp;</td></tr>';
					endwhile;//while($Dep = $db->fetch_array($qDep)):

					echo '<tr><td colspan="14" style="padding-left:35px;" bgcolor="#FF0000"><div style="color:#FFF"><strong>No Department</strong></div></td></tr>';
					$countNoPos=1;
					$qNoPos = $db->query('SELECT * FROM employee WHERE emp_id NOT IN (SELECT DISTINCT emp_id FROM emp_position) ORDER BY lname,fname');
					while($rNoPos = $db->fetch_array($qNoPos)):
						empDetail($rNoPos,0,$countNoPos);
					endwhile;
					?>
					</tbody>
				</table>
			</form>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
<script src='../js/fullcalendar.min.js'></script>
<script src='../js/jquery.dataTables.min.js'></script>
<script src="../js/excanvas.js"></script>
<script src="../js/jquery.flot.js"></script>
<script src="../js/jquery.flot.pie.js"></script>
<script src="../js/jquery.flot.stack.js"></script>
<script src="../js/jquery.flot.resize.min.js"></script>
<script src="../js/jquery.chosen.min.js"></script>
<script src="../js/jquery.uniform.min.js"></script>
<script src="../js/jquery.cleditor.min.js"></script>
<script src="../js/jquery.noty.js"></script>
<script src="../js/jquery.elfinder.min.js"></script>
<script src="../js/jquery.raty.min.js"></script>
<script src="../js/jquery.iphone.toggle.js"></script>
<script src="../js/jquery.uploadify-3.1.min.js"></script>
<script src="../js/jquery.gritter.min.js"></script>
<script src="../js/jquery.imagesloaded.js"></script>
<script src="../js/jquery.masonry.min.js"></script>
<script src="../js/jquery.knob.modified.js"></script>
<script src="../js/jquery.sparkline.min.js"></script>
<script src="../js/counter.js"></script>
<script src="../js/retina.js"></script>
<script src="../js/custom.js"></script>
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script>
// When the user scrolls the page, execute myFunction
window.onscroll = function() {myFunction()};
window.onload = function(){header.classList.add("hideit");};

// Get the header
var header = document.getElementById("myHeader");

// Get the offset position of the navbar
var sticky = header.offsetTop;
sticky = 80
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
<!-- end: JavaScript-->
</body>
</html>