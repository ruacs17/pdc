<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$ppe_id = (isset($_REQUEST['ppe_id']) && !empty($_REQUEST['ppe_id']) ) ? functions::decode($_REQUEST['ppe_id']) : 0;
$address='';$emp_name='';$position='';$marital_status='';$date_hired='';$work_status='';
$emp_id = $db->getValue('ppe','emp_id',array('ppe_id'=>$ppe_id));
$empInfo = $db->select('employee','*',array('emp_id'=>$emp_id));
while($r = $db->fetch_array($empInfo)):
	$curStreet = $r['curr_street'];
	$curProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$r['curr_province']));
	$curCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$r['curr_cityMun']));
	$curBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$r['curr_add']));
	$address = $curStreet.' '.strtoupper($curBrngy).', '.$curCity.', '.$curProvince;
	$emp_name = ucwords(strtolower($r['fname'].' '.$r['lname']));
	$work_status = $r['work_status'];
	$marital_status = $r['civil_status'];
	$dp_id = $db->getValue('emp_position','dp_id',array('emp_id'=>$emp_id));
	$position = $db->getValue('dep_position','pos_name',array('dp_id'=>$dp_id));
	$date_hired = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id),'ORDER BY ews_date ASC');
endwhile;
$ppe_no='';$employer='';$employer_add='';$assigned_location='';$proj_name='';$prepared_date='';$prepared_date='';$prepared_by='';$prepared_by_title='';$received_date='';$received_by='';$received_by_title='';
$qPPE = $db->select('ppe','*',array('ppe_id'=>$ppe_id));
while( $rPPE = $db->fetch_array($qPPE)):
$ppe_no = $rPPE['ppe_no'];
$employer = $rPPE['employer'];
$employer_add = $rPPE['employer_add'];
$assigned_location = $rPPE['assigned_location'];
$proj_id = $rPPE['proj_id'];
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$proj_id));

$prepared_date = $rPPE['prepared_date'];
$prepared_byID = $rPPE['prepared_by'];
$prepared_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$prepared_byID)));
$prepared_by_title = $rPPE['prepared_by_title'];

$received_date = $rPPE['received_date'];
$received_byID = $rPPE['received_by'];
$received_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$received_byID)));
$received_by_title = $rPPE['received_by_title'];
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<link rel="shortcut icon" href="../img/favicon.png">
	<title>PPE Issuance Print</title>
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
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
	<table width="700" border="0" align="center" style="font-size: 14px;">
		<tr>
			<td>
			<?php
			require_once('../class/print_header.php');
			print_header('ISSUANCE OF PERSONAL PROTECTIVE EQUIPMENT');
			?>
			</td>
		</tr>
	</table><br>
	<table width="85%" border="0" align="center">
		<tr>
			<td>
				<table width="100%" border="0" style="font-size: 14px;">
					<tr>
						<td height="15" align="center"><div><strong>IPPE No. <?php echo $ppe_no;?></strong></div></td>
					</tr>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I, <strong><u><?php echo $emp_name?></u></strong>, of legal age, <strong><u><?php echo $marital_status?></u></strong> and a resident of <strong><u><?php echo $address?></u></strong> has been employed as <strong><u><?php echo $position?></u></strong> for <strong><u><?php echo $work_status?></u></strong>  at <strong><u><?php echo $employer?></u></strong> with an office address in <strong><u><?php echo $employer_add?></u></strong>. I am currently assigned at <strong><u><?php echo $assigned_location?></u></strong> for the <strong><u><?php echo $proj_name?></u></strong> project.</td>
					</tr>
					<tr height="5">
						<td></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I certify that I received the Personal Protective Equipment (PPE) listed in the quantities indicated:
				<table width="100%" border="1" style="font-size: 12px;">
					<tr>
						<th width="21%" scope="col"><div align="center">Item</div></th>
						<th width="10%" scope="col"><div align="center">Brand</div></th>
						<th width="7%" scope="col"><div align="center">Acquired</div></th>
						<th width="7%" scope="col"><div align="center">Returned</div></th>
						<th width="7%" scope="col"><div align="center">No. Of Times Issued</div></th>
						<th width="8%" scope="col"><div align="center">Date Issued</div></th>
						<th width="8%" scope="col"><div align="center">Replacement Date</div></th>
						<th width="14%" scope="col"><div align="center">Mode of Issuance</div></th>
						<th width="10%" scope="col"><div align="center">Remarks</div></th>
					</tr>
					<?php
					$qItem = $db->select('ppe_items','*',array('ppe_id'=>$ppe_id),'ORDER BY ppei_id');
					while($rItem = $db->fetch_array($qItem)):
					$rID = $rItem['ppei_id'];
					?>
					<tr>
						<td height="30"><div align="center"><?php echo $rItem['item'];?></div></td>
						<td><div align="center"><?php echo $rItem['brand'];?></div></td>
						<td><div align="center"><?php echo $rItem['quantity']; echo ($rItem['unit']) ? ' ('.$rItem['unit'].')' : '';?></div></td>
						<td><div align="center"><?php echo $rItem['return_qty'];?></div></td>
						<td><div align="center"><?php echo $rItem['issued_count'];?></div></td>
						<td><div align="center"><?php echo functions::datearr($rItem['issued_date']);?></div></td>
						<td><div align="center"><?php echo functions::datearr($rItem['replacement_date']);?></div></td>
						<td><div align="center"><?php echo $rItem['issuance_mode'];?></div></td>
						<td><div align="center"><?php echo $rItem['remark'];?></div></td>
					</tr>
					<?php endwhile;?>
				</table>
			</td>
		</tr>
		<tr>
			<td height="20"><div align="center"><strong>---------- NOTHING FOLLOWS ----------</strong></div></td>
		</tr>
		<tr>
			<td>That I promise to properly use, care, and maintain the PPE that I am being issued. I also understand that: 1) This equipment is for my personal use while on the job as an employee of PDC and that it will be stored at the warehouse at all times after work; 2) It is my responsibility to wear the equipment properly and to inspect and maintain my PPE in accordance with the manufacturer’s recommendations; 3) I am responsible for immediately notifying the Safety Officer  to replace any lost, stolen, damaged or worn PPE; 4) I am responsible for immediately notifying my supervisor of any new job hazards which my require a hazard assessment and/or additional PPE. </td>
		</tr>
		<tr>
			<td height="20"><div align="center">&nbsp;</div></td>
		</tr>
		<tr>
			<td align="center">
				<table width="100%" border="0" style="font-size: 12px;">
					<tr>
						<td align="center" width="20%">Prepared and Issued by:</td>
						<td align="center" width="20%">Received and Inspected by: </td>
					</tr>
					<tr>
						<td height="30" align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $prepared_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
						<td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $received_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
					</tr>
					<tr>
						<td height="10" align="center"><div><strong><?php echo $prepared_by_title;?></strong></div></td>
						<td align="center"><div><strong><?php echo $received_by_title;?></strong></div></td>
					</tr>
					<tr>
						<td height="20" align="center" valign="bottom">Date: <strong><?php echo functions::datearr($prepared_date);?></strong></td>
						<td align="center" valign="bottom">Date: <strong><?php echo functions::datearr($received_date);?></strong></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script>
$(document).ready(function(){
	window.print();
	setTimeout("closePrint()",200);
});
function closePrint(){
	window.location="admin-ppe-item-manage.php?ppe_id=<?php echo functions::encode($ppe_id)?>";
}
</script>
<!-- end: JavaScript-->
</body>
</html>