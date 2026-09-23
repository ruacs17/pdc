<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$equip_id = (isset($_REQUEST['vdidVw']) && !empty($_REQUEST['vdidVw']) ) ? functions::decode($_REQUEST['vdidVw']) : 0;
$eqpid = $equip_id;
$qEatID = $db->select('equip_registration_signatory','*',array());
$rEatID = $db->fetch_array($qEatID);
$prepared_id = $rEatID['prepared_id'];
$prepared_by = $rEatID['prepared_by'];
$prepared_by_title = $rEatID['prepared_by_title'];

$noted_id = $rEatID['noted_id'];
$noted_by = $rEatID['noted_by'];
$noted_by_title = $rEatID['noted_by_title'];

$approved_id = $rEatID['approved_id'];
$approved_by = $rEatID['approved_by'];
$approved_by_title = $rEatID['approved_by_title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>Vehicle Registration Print</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	 <meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<!-- end: CSS -->
	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<link id="ie-style" href="../css/ie.css" rel="stylesheet">
	<![endif]-->
	<!--[if IE 9]>
	<link id="ie9style" href="../css/ie9.css" rel="stylesheet">
	<![endif]-->
	<style type="text/css">
	.padParLeft{padding-left:10px;}
	.padAmLeft{padding-left:60px;}
	</style>
	<style type="text/css">
		body {
			/*background: rgb(204,204,204);*/
			background: white;
			font-size: 12px;
			font-family: Tahoma;
		}
		table{border-collapse: collapse;}
		page[size="ltr"] {
			background: white;
			/*width: 21.6cm;
			height: 29.7cm;
			display: block;
			margin: 0 auto;
			margin-bottom: 0.5cm;
			box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);*/
		}
		@media print {
			body, page[size="ltr"] {
				margin: 0;
				box-shadow: 0;color:red;
				-webkit-print-color-adjust: exact;
			}
		}
		.spce {
		line-height: 150%;
		}
		.subitem{padding-left:10px;}
		.titlehead{background-color:red !important; font-weight: bold;}
		.padright{padding-right: 3px;}
		.desig{font-size:10px;}
		.padtop{padding-top:0px;}
	</style>
	<link href="../css/printerfoot.css" rel="stylesheet">
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<div align="center">
<page size="ltr">
	<table width="90%" border="0" align="center">
		<thead>
			<tr>
				<td>
					<?php
					require_once('../class/print_header.php');
					print_header('MOTOR VEHICLE RENEWAL OF REGISTRATION SCHEDULE AND MONITORING PLAN');
					?>
					<div style="padding-top:12px;"></div>
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td height="500" valign="top">
					<table id="tblist" style="font-size:12px;" border="1">
						<thead>
							<tr style="background-color:#e3dfed !important;">
								<th valign="middle" class="padtop"><div align="center">Make</div></th>
								<th valign="middle" class="padtop" width="30%"><div align="center">Property</div></th>
								<th valign="middle" class="padtop"><div align="center">Plate No</div></th>
								<th valign="middle" class="padtop"><div align="center">Prop. Code No</div></th>
								<th valign="middle" class="padtop" style="background-color:#f4d9ae !important;" width="7%"><div align="center">Date of Latest Renewal</div></th>
								<th valign="middle" class="padtop" style="background-color:#f24b3f !important;" width="7%"><div align="center">Due Date of Renewal</div></th>
								<th valign="middle" class="padtop" style="background-color:#fdfe60 !important;" width="7%"><div align="center">Renewal Schedule <br><i style="font-size:9px;">(2 Months before the due date)</i></div></th>
								<th valign="middle" class="padtop" style="background-color:#f8d5b5 !important;" width="7%"><div align="center">Date Actual Renewed</div></th>
								<th valign="middle" class="padtop"><div align="center">Location</div></th>
								<th valign="middle" class="padtop"><div align="center">Assigned Driver</div></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$countRec=0;
							if($equip_id)
								$qEquip = $db->query('SELECT * FROM equipment eq, equip_registration er WHERE eq.equip_id=er.equip_id AND eq.reg_renew IS NOT NULL AND eq.status NOT IN ("Trade In","Sold","Inactive","Unserviceable") AND "'.date('Y-m-d').'" BETWEEN er.date_due AND er.date_validity AND eq.equip_id="'.$db->clean($equip_id).'"');
							else
								$qEquip = $db->query('SELECT * FROM equipment eq, equip_registration er WHERE eq.equip_id=er.equip_id AND eq.reg_renew IS NOT NULL AND eq.status NOT IN ("Trade In","Sold","Inactive","Unserviceable") AND "'.date('Y-m-d').'" BETWEEN er.date_due AND er.date_validity ORDER BY er.date_due');
							while($r = $db->fetch_array($qEquip)):
								$countRec++;
								$equip_id=$r['equip_id'];
								$erig_id = $r['erig_id'];
								$reg_renew = $r['reg_renew'];
								$date_acquired = $r['date_acquired'];
								$acquired_year = date('Y',strtotime($date_acquired));
								$stat = $r['stat'];
								$date_due=$r['date_due'];
								$date_due_start = $r['date_due_start'];
								$date_actual = $r['date_actual'];
								$reg_term = $r['reg_term'];
								$latest_renewal = $db->getValue('equip_registration','date_actual',array('equip_id'=>$equip_id),'AND date_due < "'.$date_due.'" ORDER BY date_actual DESC');
								$x = $db->last_query;
							?>
							<tr>
								<td><div align="center"><?php echo $r['type'] ?></div></td>
								<td><div align="left" style="padding:0px 1px 0px 1px;"><?php echo $r['name'] ?></div></td>
								<td><div align="center"><?php echo $r['plate_no'] ?></div></td>
								<td><div align="center"><?php echo $r['inventory_id'] ?></div></td>
								<td><div align="center"><?php echo functions::datearr($latest_renewal); #echo $x;?></div></td>
								<td><div align="center"><?php echo functions::datearr($date_due)?></div></td>
								<td><div align="center"><?php echo functions::datearr($date_due_start);?></div></td>
								<td><div align="center"><?php echo functions::datearr($date_actual)?></div></td>
								<td>
									<div align="center">
										<?php
										$mr_emp='';
										$loc = $db->query('SELECT * FROM `mr_item` mi, mr WHERE mi.mr_id=mr.mr_id AND equip_id="'.$db->clean($equip_id).'" AND mr.mr_date <= "'.$date_due.'" ORDER BY mr.mr_date DESC LIMIT 1');
										$rLoc = $db->fetch_array($loc);
										$mr_emp = $rLoc['mr_emp'];
										if($db->num_rows($loc)==0){
											$location=$r['location']."<br><i>(in possession)</>";
										}
										else if($rLoc['mr_status']=='Returned'){
											$location=$r['location']."<br><i>(in possession)</>";
										}
										elseif($rLoc['mr_status']=='Unreturned'){
											$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
											$mr_emp = $rLoc['mr_emp'];
										}
										else{
											$location = !empty($rLoc['assigned_location']) ? $rLoc['assigned_location'] : 'Unspecified' ;
											$mr_emp = $rLoc['mr_emp'];
										}
										echo $location;
										?>
									</div>
								</td>
								<td><div align="center"><?php echo $db->getValue('employee','concat(lname," ",fname)',array('emp_id'=>$mr_emp)) ?></div></td>
							</tr>
							<?php
							endwhile;
							if($countRec==0){
								echo '<tr><td colspan="10"><div align="center">--Nothing to Report--</div></td></tr>';
							}
							?>
							<tr>
								<td colspan="10" style="border-bottom:solid #FFF;border-left:solid #FFF;border-right:solid #FFF;">
									<div style="padding-top:12px;"></div>
									<table width="100%" border="0" style="font-size: 12px;">
										<tr>
											<td width="33%"><div align="center">Prepared By</div></td>
											<td width="33%"><div align="center">Noted and Reviewed By</div></td>
											<td width="33%"><div align="center">Approved By</div></td>
										</tr>
										<tr>
											<td height="55" valign="bottom">
												<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $prepared_by;?>&nbsp;</strong></div>
												<div align="center" class="desig"><?php echo $prepared_by_title;?></div>
											</td>
											<td height="55" valign="bottom">
												<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $noted_by;?>&nbsp;</strong></div>
												<div align="center" class="desig"><?php echo $noted_by_title;?></div>
											</td>
											<td valign="bottom">
												<div align="center" style="text-decoration:underline"><strong>&nbsp;<?php echo $approved_by;?>&nbsp;</strong></div>
												<div align="center" class="desig"><?php echo $approved_by_title;?></div>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>

				</td>
			</tr>
		</tbody>
	</table>
	<footer>
		<div align="right" style="font-size:12px;display:none;">18AED.FRM024.00-10/18</div>
	</footer>
</page>
</div>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="admin-equipment-registration-monitor.php?vdidVw=<?php echo functions::encode($eqpid)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>