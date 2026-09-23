<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$fromED = (isset($_REQUEST['fromED']) && !empty($_REQUEST['fromED']) ) ? $_REQUEST['fromED'] : '';
$txtEmpNo='';$txtfName='';$txtmName='';$txtlName='';$txtExtName='';$txtNickName='';$bdMon='';$bdDay='';$bdYear='';
$txtBirthPlace='';$selGender='';$selCivilStat='';$txCitizenship='';$cert_id='';$txtSSSContrib='';$txtPHContrib='';$txtPagIbigContrib='';
$txReligion='';$txHeight='';$txWeight='';$txBloodType='';$txTIN='';$txpagibig='';$txphilhealth='';$txtSSS='';$txtGSIS='';$selCurProvince='';$selCurCityMun='';$selCurBrngy='';$curStreet='';$curr_zipcode='';$curTelNo='';$selProvincePerm='';$selCityMunPerm='';$selBrngyPerm='';$permStreet='';$permZipCode='';$permTelNo='';$txEmail='';$txCellphone='';
$bdAccmplshdMon='';$bdAccmplshdDay='';$bdAccmplshdYear='';

$arrPosition=array();

function address($street='',$brgy='',$city='',$province=''){
	$street = ($street) ? trim($street) : '';
	$brgy = ($brgy) ? trim($brgy) : '';
	$city = ($city) ? trim($city) : '';
	$province = ($province) ? trim($province) : '';

	$adrs = $street;
	$adrs .= ( $adrs && $brgy ) ? ', '.$brgy : $brgy;
	$adrs .= ( $adrs && $city  ) ? ', '.$city : $city;
	$adrs .= ( $adrs && $province  ) ? ', '.$province : $province;
	return $adrs;
}
// function updateAddress($emp_id){
// 	global $db;
// 	$q = $db->prepareQ('SELECT 
// 		curr_street,
// 		(SELECT brgyDesc FROM refbrgy WHERE emp.curr_add=brgyCode) as curr_brgy, 
// 		(SELECT citymunDesc FROM refcitymun WHERE emp.curr_cityMun=cityMunCode) as curr_city, 
// 		(SELECT provDesc FROM refprovince WHERE emp.curr_province=provCode) as curr_province,
// 		perm_street,
// 		(SELECT brgyDesc FROM refbrgy WHERE emp.perm_add=brgyCode) as perm_brgy, 
// 		(SELECT citymunDesc FROM refcitymun WHERE emp.perm_cityMun=cityMunCode) as perm_city, 
// 		(SELECT provDesc FROM refprovince WHERE emp.perm_province=provCode) as perm_province,
// 		emp_id
// 		FROM employee emp WHERE emp_id=?',array($emp_id));
// 	while($r = $db->fetch_array($q)):
// 		$curr_address = address($r['curr_street'],$r['curr_brgy'],$r['curr_city'],$r['curr_province']);
// 		$perm_address = address($r['perm_street'],$r['perm_brgy'],$r['perm_city'],$r['perm_province']);
// 		$db->update('employee',array('curr_address'=>$curr_address,'perm_address'=>$perm_address),array('emp_id'=>$r['emp_id']));
// 	endwhile;
// }



$q = $db->select('employee','*',array('emp_id'=>$eid));
$r = $db->fetch_array($q);
$cert_id = $r['cert_id'];
$txtEmpNo = $r['emp_no'];
$txtfName = $r['fname'];
$txtmName = $r['mname'];
$txtlName = $r['lname'];
$txtExtName = $r['extname'];
$txtNickName = $r['nickname'];
$bdate = $r['bdate'];

$txtBirthPlace = $r['bplace'];
$selGender = $r['gender'];
$selCivilStat = $r['civil_status'];
$txCitizenship = $r['citizenship'];
$txReligion = $r['religion'];
$txHeight = $r['height'];
$txWeight = $r['weight'];
$txBloodType = $r['bloodtype'];
$txTIN = $r['tin'];
$txpagibig = $r['pagibig'];
$pagibig_autodeduct = $r['pagibig_autodeduct'];
$txtPagIbigContrib = functions::formatMoney($r['pagibig_contribution']);
$txphilhealth = $r['philhealth'];
$philhealth_autodeduct = $r['philhealth_autodeduct'];
$txtPHContrib = functions::formatMoney($r['philhealth_contribution']);
$txtSSS = $r['sss'];
$sss_autodeduct = $r['sss_autodeduct'];
$txtSSSContrib = functions::formatMoney($r['sss_contribution']);
$txtGSIS = $r['gsis'];
$gsis_autodeduct = $r['gsis_autodeduct'];
$selCurProvince = $r['curr_province'];
$selCurCityMun = $r['curr_cityMun'];
$selCurBrngy = $r['curr_add'];
$curStreet = $r['curr_street'];
$curZipCode = $r['curr_zipcode'];
$curTelNo = $r['curr_tel'];
$selProvincePerm = $r['perm_province'];
$selCityMunPerm = $r['perm_cityMun'];
$selBrngyPerm = $r['perm_add'];
$permStreet = $r['perm_street'];
$permZipCode = $r['perm_zipcode'];
$permTelNo = $r['perm_tel'];
$txEmail = $r['email'];
$txCellphone = $r['cell_no'];

$date_accomplished = $r['date_accomplished'];

$qSelPos = $db->select('emp_position','*',array('emp_id'=>$eid));
while($rSelPos = $db->fetch_array($qSelPos)):
	$arrPosition[$rSelPos['dp_id']]=1;
endwhile;
$arrSiteAssign=array();
$qSelSite = $db->select('emp_site_assign','*',array('emp_id'=>$eid));
while($rSelSite = $db->fetch_array($qSelSite)):
	$arrSiteAssign[$rSelSite['proj_id']]=1;
endwhile;

if( isset($_POST['btnSave']) && !empty($eid) ){
	$emp_no = ( isset($_POST['txtEmpNo']) ) ? strtoupper(trim($_POST['txtEmpNo'])) : '';
	$fname = ( isset($_POST['txtfName']) ) ? strtoupper(trim($_POST['txtfName'])) : '';
	$mname = ( isset($_POST['txtmName']) ) ? strtoupper(trim($_POST['txtmName'])) : '';
	$lname = ( isset($_POST['txtlName']) ) ? strtoupper(trim($_POST['txtlName'])) : '';
	$extname = ( isset($_POST['txtExtName']) ) ? strtoupper(trim($_POST['txtExtName'])) : '';
	$nickname = ( isset($_POST['txtNickName']) ) ? strtoupper(trim($_POST['txtNickName'])) : '';

	$bdate = ( isset($_POST['txBdateDate']) && !empty($_POST['txBdateDate']) ) ? trim($_POST['txBdateDate']) : NULL;
	$bplace = ( isset($_POST['txtBirthPlace']) ) ? strtoupper(trim($_POST['txtBirthPlace'])) : '';
	$gender = ( isset($_POST['selGender']) ) ? trim($_POST['selGender']) : '';
	$civil_status = ( isset($_POST['selCivilStat']) ) ? trim($_POST['selCivilStat']) : '';
	$citizenship = ( isset($_POST['txCitizenship']) ) ? strtoupper(trim($_POST['txCitizenship'])) : '';
	$religion = ( isset($_POST['txReligion']) ) ? strtoupper(trim($_POST['txReligion'])) : '';
	$height = ( isset($_POST['txHeight']) ) ? trim($_POST['txHeight']) : '';
	$weight = ( isset($_POST['txWeight']) ) ? trim($_POST['txWeight']) : '';
	$bloodtype = ( isset($_POST['txBloodType']) ) ? trim($_POST['txBloodType']) : '';
	$tin = ( isset($_POST['txTIN']) ) ? trim($_POST['txTIN']) : '';
	$pagibig = ( isset($_POST['txpagibig']) ) ? trim($_POST['txpagibig']) : '';
	$pagibig_contribution = ( isset($_POST['txtPagIbigContrib']) && !empty($_POST['txtPagIbigContrib']) ) ? functions::moneyToDouble($_POST['txtPagIbigContrib']) : 0;
	$pagibig_autodeduct = ( isset($_POST['chkPAG']) ) ? 1 : 0;
	$philhealth = ( isset($_POST['txphilhealth']) ) ? trim($_POST['txphilhealth']) : '';
	$philhealth_contribution = ( isset($_POST['txtPHContrib']) && !empty($_POST['txtPHContrib']) ) ? functions::moneyToDouble($_POST['txtPHContrib']) : 0;
	$philhealth_autodeduct = ( isset($_POST['chkPH']) ) ? 1 : 0;
	$sss = ( isset($_POST['txtSSS']) ) ? trim($_POST['txtSSS']) : '';
	$sss_contribution = ( isset($_POST['txtSSSContrib']) && !empty($_POST['txtSSSContrib']) ) ? functions::moneyToDouble($_POST['txtSSSContrib']) : 0;
	$sss_autodeduct = ( isset($_POST['chkSSS']) ) ? 1 : 0;
	$gsis = ( isset($_POST['txtGSIS']) ) ? trim($_POST['txtGSIS']) : '';
	$gsis_autodeduct = ( isset($_POST['chkGSIS']) ) ? 1 : 0;

	$curr_province = ( isset($_POST['selCurProvince']) ) ? trim($_POST['selCurProvince']) : '';
	$curr_cityMun = ( isset($_POST['selCurCityMun']) ) ? trim($_POST['selCurCityMun']) : '';
	$curr_add = ( isset($_POST['selCurBrngy']) ) ? trim($_POST['selCurBrngy']) : '';
	$curr_street = ( isset($_POST['curStreet']) ) ? strtoupper(trim($_POST['curStreet'])) : '';
	$curr_zipcode = ( isset($_POST['curZipCode']) ) ? trim($_POST['curZipCode']) : '';
	$curr_tel = ( isset($_POST['curTelNo']) ) ? trim($_POST['curTelNo']) : '';
	$perm_province = ( isset($_POST['selProvincePerm']) ) ? trim($_POST['selProvincePerm']) : '';
	$perm_cityMun = ( isset($_POST['selCityMunPerm']) ) ? trim($_POST['selCityMunPerm']) : '';
	$perm_add = ( isset($_POST['selBrngyPerm']) ) ? trim($_POST['selBrngyPerm']) : '';
	$perm_street = ( isset($_POST['permStreet']) ) ? strtoupper(trim($_POST['permStreet'])) : '';
	$perm_zipcode = ( isset($_POST['permZipCode']) ) ? trim($_POST['permZipCode']) : '';
	$perm_tel = ( isset($_POST['permTelNo']) ) ? trim($_POST['permTelNo']) : '';
	$email = ( isset($_POST['txEmail']) ) ? trim($_POST['txEmail']) : '';
	$cell_no = ( isset($_POST['txCellphone']) ) ? trim($_POST['txCellphone']) : '';
	$selPosition = ( isset($_POST['selPosition']) ) ? $_POST['selPosition'] : array();
	$selProjAssign = ( isset($_POST['selProjAssign']) ) ? $_POST['selProjAssign'] : array();
	$date_accomplished = ( isset($_POST['txAccmplshdDate']) && functions::valid_date($_POST['txAccmplshdDate']) ) ? trim($_POST['txAccmplshdDate']) : NULL;

	if( !empty($_FILES['image']) && $_FILES['image']['error'] == 0 ) {
        
		$uploaddir = '../img_emp/';
		$max_size = 2000 * 1024; // 500 KB
		// Generates random filename and extension 
		function tempnam_sfx($path, $suffix){
			do{
				$file = $path."/".mt_rand().$suffix;
				$fp = @fopen($file, 'x');
			}
			while(!$fp);

			fclose($fp);
			return $file;
		}
		// Process image with GD library
		$verifyimg = getimagesize($_FILES['image']['tmp_name']);

		// Make sure the MIME type is an image
		$pattern = "#^(image/)[^\s\n<]+$#i";

		if( !preg_match($pattern, $verifyimg['mime']) ){
			functions::say("Only image files are allowed!");
		}
		else if( $_FILES["image"]["size"] > $max_size ){
			functions::say("Image reached the limit size!");
		}
		else{
			// Rename both the image and the extension
			$uploadfile = tempnam_sfx($uploaddir, ".jpg");
			// Upload the file to a secure directory with the new name and extension
			if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
				$oldFileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
				if($oldFileName)
					unlink('../img_emp/'.$oldFileName);
				$db->query('UPDATE employee SET cert_id=NULL WHERE cert_id="'.$db->clean($cert_id).'"');
				$db->delete('cert_img',array('cert_id'=>$cert_id));
				$qPicIns = $db->insertPrint('cert_img',array('cert_name'=>basename($uploadfile),'cert_org_name'=>basename($_FILES['image']['name']),'mime_type'=>$_FILES['image']['type']));
				$db->query($qPicIns);
				$newCertID = $db->insert_id();
				$db->update('employee',array('cert_id'=>$newCertID),array('emp_id'=>$eid));
			}
			else
				functions::say('upload fail.');
		}
	}
	if($lname && $fname){
		$arrUpdate = array('lname'=>$lname,'fname'=>$fname,'mname'=>$mname,'extname'=>$extname,'nickname'=>$nickname,'bdate'=>$bdate,
							'bplace'=>$bplace,'gender'=>$gender,'civil_status'=>$civil_status,'citizenship'=>$citizenship,'religion'=>$religion,
							'height'=>$height,'weight'=>$weight,'bloodtype'=>$bloodtype,'tin'=>$tin,'pagibig'=>$pagibig,'pagibig_contribution'=>$pagibig_contribution,'pagibig_autodeduct'=>$pagibig_autodeduct,'philhealth'=>$philhealth,'philhealth_contribution'=>$philhealth_contribution,'philhealth_autodeduct'=>$philhealth_autodeduct,'sss'=>$sss,'sss_contribution'=>$sss_contribution,'sss_autodeduct'=>$sss_autodeduct,'gsis'=>$gsis,'gsis_autodeduct'=>$gsis_autodeduct,
							'curr_province'=>$curr_province,'curr_cityMun'=>$curr_cityMun,'curr_add'=>$curr_add,'curr_street'=>$curr_street,'curr_zipcode'=>$curr_zipcode,'curr_tel'=>$curr_tel,
							'perm_province'=>$perm_province,'perm_cityMun'=>$perm_cityMun,'perm_add'=>$perm_add,'perm_street'=>$perm_street,'perm_zipcode'=>$perm_zipcode,'perm_tel'=>$perm_tel,
							'email'=>$email,'cell_no'=>$cell_no,'date_accomplished'=>$date_accomplished);

		$allow_update_emp_no=1;
		if( $emp_no ){
			$org_emp_no = $db->getValue('employee','emp_no',array('emp_id'=>$eid));
			if($emp_no != $org_emp_no){
				if( $db->getValue('employee','count(*)',array('emp_no'=>$emp_no)) )
					$allow_update_emp_no=0;
			}
		}
		if( $allow_update_emp_no==0 ){
			functions::say('Employee number already exist! Update it next time.');
		}
		else
			$arrUpdate = array_merge($arrUpdate,array('emp_no'=>$emp_no));

		if($eid){
			#updateAddress($eid);
			$curr_brgy=$db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$curr_add));
			$curr_city=$db->getValue('refcitymun','citymunDesc',array('cityMunCode'=>$curr_cityMun));
			$curr_province=$db->getValue('refprovince','provDesc',array('provCode'=>$curr_province));

			$perm_brgy=$db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$perm_add));
			$perm_city=$db->getValue('refcitymun','citymunDesc',array('cityMunCode'=>$perm_cityMun));
			$perm_province=$db->getValue('refprovince','provDesc',array('provCode'=>$perm_province));

			$curr_address = address($curr_street,$curr_brgy,$curr_city,$curr_province);
			$perm_address = address($perm_street,$perm_brgy,$perm_city,$perm_province);
			$db->update('employee',array('curr_address'=>$curr_address,'perm_address'=>$perm_address),array('emp_id'=>$eid));

			#die();
			//Removing the excluded position
			$qSelPos = $db->select('emp_position','*',array('emp_id'=>$eid));
			while($rSelPos = $db->fetch_array($qSelPos)):
				if( !isset($selPosition[$rSelPos['dp_id']]) )
					$db->delete('emp_position',array('emp_id'=>$eid,'dp_id'=>$rSelPos['dp_id']));
			endwhile;
			//Inserting new position
			foreach($selPosition as $dpID):
				$arrField = array('emp_id'=>$eid,'dp_id'=>$dpID);
				if( $db->getValue('emp_position','count(*)',$arrField)==0 )
					$db->insert('emp_position',$arrField);
			endforeach;

			// //Removing the excluded assignment
			// $qSelSite = $db->select('emp_site_assign','*',array('emp_id'=>$eid));
			// while($rSelSite = $db->fetch_array($qSelSite)):
			// 	if( !isset($selProjAssign[$rSelSite['proj_id']]) )
			// 		$db->delete('emp_site_assign',array('emp_id'=>$eid,'proj_id'=>$rSelSite['proj_id']));
			// endwhile;
			//Inserting new assignment
			foreach($selProjAssign as $prjID):
				$arrField = array('emp_id'=>$eid,'proj_id'=>$prjID);
				if( $db->getValue('emp_site_assign','count(*)',$arrField)==0 )
					$db->insert('emp_site_assign',$arrField);
			endforeach;

			$db->update('employee',$arrUpdate,array('emp_id'=>$eid));

			$_SESSION['notif_success']='Personal Information Saved!';
			functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
			die();
		}
		else{
			functions::say('Fail to save.');
		}
	}
}
$qItemCitezenship = $db->query('SELECT DISTINCT citizenship FROM employee ORDER BY citizenship');
$namesCitezenship='';
while($rItemC=$db->fetch_array($qItemCitezenship)):
	$string = preg_replace("/'/",'"',$rItemC['citizenship']);
	$namesCitezenship .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesCitezenship .= '"--"';

$qReligion = $db->query('SELECT DISTINCT religion FROM employee ORDER BY religion');
$namesReligion='';
while($rRel=$db->fetch_array($qReligion)):
	$string = preg_replace("/'/",'"',$rRel['religion']);
	$namesReligion .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesReligion .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Update</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/inputInt.js"></script>
	<link rel="stylesheet" href="../css/bootstrap-multiselect.css" type="text/css">
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
		</div>
		<?php if(empty($fromED)){ ?>
		<div class="box-content">
			<div align="right" class="nav tab-menu nav-tabs" style="padding-top: 3px;"><?php require_once('employee_options.php');?></div>
		</div>
		<?php } ?>
		<div class="box-content">
			<form class="form-horizontal" method="post" enctype="multipart/form-data" onSubmit="return ask()">
				<div align="center" style="padding-bottom: 15px;"><h2>PERSONAL INFORMATION</h2></div>
				<table border="0" width="98%" align="center">
					<tr>
						<td align="center">
							<div align="center">
								<?php
								$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
								if( $fileName && file_exists('../img_emp/'.$fileName)){
									$file = '../img_emp/'.$fileName;
									echo '<img height="200" width="200" src="'.$file.'">';
								}
								else
									echo '<img height="200" width="200" src="../img_emp/blank-pic.png">';
								?>
								Select <strong>2x2</strong> image to upload: <input type="file" name="image">
							</div>
						</td>
					</tr>
				</table>
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
					<tr>
						<td width="17%" height="30"><div align="right">Employee Number</div></td>
						<td width="43%"><input type="text" name="txtEmpNo" id="txtEmpNo" class="span6" value="<?php echo $txtEmpNo?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Position</div></td>
						<td>
							<select name="selPosition[]" id="selPosition" multiple="multiple" data-rel="chosen" style="width:490px;">
								<?php 
								$qPos = $db->select('dep_position','*',array(),'ORDER BY pos_name');
								while($rPos = $db->fetch_array($qPos)):
								$dept = $db->getValue('department','dep_name',array('dep_id'=>$rPos['dep_id']));
								$depName = ($dept) ? '('.$dept.')' : '(no department)';
								?>
								<option value="<?php echo $rPos['dp_id']?>" <?php if( isset($arrPosition[$rPos['dp_id']]) )echo 'selected="selected"';?>><?php echo $rPos['pos_name']; echo '&nbsp;&nbsp;'.$depName;?></option>
								<?php endwhile;?>
							</select>
						</td>
					</tr>
					<tr style="display:none;">
						<td height="30"><div align="right">Project / Site Assignment</div></td>
						<td>
							<select name="selProjAssign[]" id="selProjAssign" multiple="multiple" data-rel="chosen" style="width:790px;">
								<?php 
								$qProj = $db->select('project','*',array(),'ORDER BY proj_name');
								while($rProj = $db->fetch_array($qProj)):
								?>
								<option value="<?php echo $rProj['proj_id']?>" <?php if( isset($arrSiteAssign[$rProj['proj_id']]) )echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?></option>
								<?php endwhile;?>
							</select>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">First Name</div></td>
						<td><input type="text" name="txtfName" id="txtfName" class="span6" value="<?php echo $txtfName?>" required/></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Middle Name</div></td>
						<td><input type="text" name="txtmName" id="txtmName" class="span6" value="<?php echo $txtmName?>" /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Last Name</div></td>
						<td><input type="text" name="txtlName" id="txtlName" class="span6" value="<?php echo $txtlName?>"  required/></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Suffix (JR, SR, III)</div></td>
						<td><input type="text" name="txtExtName" id="txtExtName" class="span6" value="<?php echo $txtExtName?>" /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Nickname</div></td>
						<td><input type="text" name="txtNickName" id="txtNickName" class="span6" value="<?php echo $txtNickName?>" /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Date of Birth</div></td>
						<td>
							<a href="javascript:NewCssCal('txBdateDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
							<input name="txBdateDate" type="text" class="span6 mytextbox" id="txBdateDate" value="<?php echo $bdate; ?>" style="width: 90px;" readonly>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Birth Place</div></td>
						<td><input type="text" name="txtBirthPlace" id="txtBirthPlace" class="span6" value="<?php echo $txtBirthPlace?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Gender</div></td>
						<td>
							<select name="selGender" id="selGender" style="width: 150px;" required>
								<option value="">--Select--</option>
								<option value="Male" <?php if($selGender=='Male')echo 'selected="selected"';?>>Male</option>
								<option value="Female" <?php if($selGender=='Female')echo 'selected="selected"';?>>Female</option>
							</select>
							<span class="help-inline warning" style="font-weight:bold;" id="msgGender" name="msgGender"></span>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Civil Status</div></td>
						<td>
							<select name="selCivilStat" id="selCivilStat" style="width: 150px;" required>
								<option value="">--Select--</option>
								<option value="Single" <?php if($selCivilStat=='Single')echo 'selected="selected"';?>>Single</option>
								<option value="Married" <?php if($selCivilStat=='Married')echo 'selected="selected"';?>>Married</option>
								<option value="Annulled" <?php if($selCivilStat=='Annulled')echo 'selected="selected"';?>>Annulled</option>
								<option value="Widowed" <?php if($selCivilStat=='Widowed')echo 'selected="selected"';?>>Widowed</option>
								<option value="Separated" <?php if($selCivilStat=='Separated')echo 'selected="selected"';?>>Separated</option>
							</select>
							<span class="help-inline warning" style="font-weight:bold;" id="msgCivilStat" name="msgCivilStat"></span>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Citizenship</div></td>
						<td>
							<input type="text" name="txCitizenship" id="txCitizenship" class="span6" value="<?php echo $txCitizenship;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCitezenship;?>]'>
							<span class="help-inline warning" style="font-weight:bold;" id="msgCitizenship" name="msgCitizenship"></span>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Religion</div></td>
						<td><input type="text" name="txReligion" id="txReligion" class="span6" value="<?php echo $txReligion;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReligion;?>]'></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Height (m)</div></td>
						<td><input type="text" name="txHeight" id="txHeight" class="span6" value="<?php echo $txHeight;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Weight (kg)</div></td>
						<td><input type="text" name="txWeight" id="txWeight" class="span6" value="<?php echo $txWeight;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Blood Type</div></td>
						<td><input type="text" name="txBloodType" id="txBloodType" class="span6" value="<?php echo $txBloodType;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">TIN</div></td>
						<td><input type="text" name="txTIN" id="txTIN" class="span6" value="<?php echo $txTIN;?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">PAGIBIG No.</div></td>
						<td><input type="text" name="txpagibig" id="txpagibig" style="width: 150px;" class="span6" value="<?php echo $txpagibig;?>">&nbsp;&nbsp; Additional Contribution: <input type="text" name="txtPagIbigContrib" id="txtPagIbigContrib" style="width: 144px;" class="span6" value="<?php echo $txtPagIbigContrib?>" onkeyup="FormatCurrency(this);">&nbsp;&nbsp; <input type="checkbox" name="chkPAG" id="chkPAG" <?php if($pagibig_autodeduct==1){echo 'checked';} ?>>&nbsp;&nbsp;<i id="chkPAGDesc" style="cursor:pointer;">Payroll Auto-deduction</i></td>
					</tr>
					<tr>
						<td height="30"><div align="right">PHILHEALTH No.</div></td>
						<td><input type="text" name="txphilhealth" id="txphilhealth" style="width: 150px;" class="span6" value="<?php echo $txphilhealth;?>">&nbsp;&nbsp; <input type="checkbox" name="chkPH" id="chkPH" <?php if($philhealth_autodeduct==1){echo 'checked';} ?>>&nbsp;&nbsp;<i id="chkPHDesc" style="cursor:pointer;">Payroll Auto-deduction</i></td>
					</tr>
					<tr>
						<td height="30"><div align="right">SSS No.</div></td>
						<td><input type="text" name="txtSSS" id="txtSSS" style="width: 150px;" class="span6" value="<?php echo $txtSSS;?>">&nbsp;&nbsp; <input type="checkbox" name="chkSSS" id="chkSSS" <?php if($sss_autodeduct==1){echo 'checked';} ?>>&nbsp;&nbsp;<i id="chkSSSDesc" style="cursor:pointer;">Payroll Auto-deduction</i></td>
					</tr>
					<tr>
						<td height="30"><div align="right">GSIS No.</div></td>
						<td><input type="text" name="txtGSIS" id="txtGSIS" style="width: 150px;" class="span6" value="<?php echo $txtGSIS;?>">&nbsp;&nbsp; <input type="checkbox" name="chkGSIS" id="chkGSIS" <?php if($gsis_autodeduct==1){echo 'checked';} ?>>&nbsp;&nbsp;<i id="chkGSISDesc" style="cursor:pointer;">Payroll Auto-deduction</i></td>
					</tr>
					<tr>
						<td height="30" colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Residential Address</div></td>
						<td><br>
							<table>
								<tr>
									<td>
										<select name="selCurProvince" id="selCurProvince" onChange="getCityMun(this.value)">
											<option value="">Select Province</option>
											<?php 
											$qProvince = $db->select('refprovince','*',array(),'ORDER BY provDesc');
											while($rProv = $db->fetch_array($qProvince)): ?>
											<option value="<?php echo $rProv['provCode']?>" <?php if($rProv['provCode']==$selCurProvince)echo 'selected="selected"';?>><?php echo $rProv['provDesc']?></option>
											<?php endwhile;?>
										</select>
									</td>
									<td>
										<div id="divCurCityMun">
											<select name="selCurCityMun" id="selCurCityMun">
												<option value="">Select City/Municipality</option>
												<?php 
												$qCityMun = $db->select('refcitymun','*',array('provCode'=>$selCurProvince),'ORDER BY citymunDesc');
												while($rCityMun = $db->fetch_array($qCityMun)): ?>
												<option value="<?php echo $rCityMun['citymunCode']?>" <?php if($rCityMun['citymunCode']==$selCurCityMun)echo 'selected="selected"';?>><?php echo $rCityMun['citymunDesc']?></option>
												<?php endwhile;?>
											</select>
										</div>
									</td>
									<td>
										<div id="divCurBrngy">
											<select id="selCurBrngy" name="selCurBrngy">
												<option value="">Select Barangay</option>
												<?php 
												$qBrgy = $db->select('refbrgy','*',array('citymunCode'=>$selCurCityMun),'ORDER BY brgyDesc');
												while($rBrgy = $db->fetch_array($qBrgy)): ?>
												<option value="<?php echo $rBrgy['brgyCode']?>" <?php if($rBrgy['brgyCode']==$selCurBrngy)echo 'selected="selected"';?>><?php echo $rBrgy['brgyDesc']?></option>
												<?php endwhile;?>
											</select>
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="2"><input type="text" name="curStreet" id="curStreet" value="<?php echo $curStreet?>" placeholder="Street" style="width: 350px;"></td>
									<td><input type="text" name="curZipCode" id="curZipCode"  value="<?php echo $curZipCode?>" placeholder="Zip Code"></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Telephone No.</div></td>
						<td><input type="text" name="curTelNo" id="curTelNo" class="span6" value="<?php echo $curTelNo?>" style="width: 150px;"></td>
					</tr>
					<tr>
						<td height="60" colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td><div align="right">Permanent Address</div></td>
						<td><br>
							<table>
								<tr>
									<td>
										<select name="selProvincePerm" id="selProvincePerm" onChange="getCityMunPerm(this.value)" style="width: 300px;" class="form-control">
											<option value="">Select Province</option>
											<?php
											$qProvince = $db->select('refprovince','*',array(),'ORDER BY provDesc');
											while($rProv = $db->fetch_array($qProvince)): ?>
											<option value="<?php echo $rProv['provCode']?>" <?php if($rProv['provCode']==$selProvincePerm)echo 'selected="selected"';?>><?php echo $rProv['provDesc']?></option>
											<?php endwhile;?>
										</select>
									</td>
									<td>
										<div id="divCityMunPerm">
											<select name="selCityMunPerm" id="selCityMunPerm" class="form-control">
												<option value="">Select City/Municipality</option>
												<?php 
												$qCityMun = $db->select('refcitymun','*',array('provCode'=>$selProvincePerm),'ORDER BY citymunDesc');
												while($rCityMun = $db->fetch_array($qCityMun)): ?>
												<option value="<?php echo $rCityMun['citymunCode']?>" <?php if($rCityMun['citymunCode']==$selCityMunPerm)echo 'selected="selected"';?>><?php echo $rCityMun['citymunDesc']?></option>
												<?php endwhile;?>
											</select>
										</div>
									</td>
									<td>
										<div id="divBrngyPerm">
											<select id="selBrngyPerm" name="selBrngyPerm" class="form-control">
												<option value="">Select Barangay</option>
												<?php 
												$qBrgy = $db->select('refbrgy','*',array('citymunCode'=>$selCityMunPerm),'ORDER BY brgyDesc');
												while($rBrgy = $db->fetch_array($qBrgy)): ?>
												<option value="<?php echo $rBrgy['brgyCode']?>" <?php if($rBrgy['brgyCode']==$selBrngyPerm)echo 'selected="selected"';?>><?php echo $rBrgy['brgyDesc']?></option>
												<?php endwhile;?>
											</select>
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="2"><input type="text" name="permStreet" id="permStreet" value="<?php echo $permStreet?>" placeholder="Street" style="width: 350px;"></td>
									<td><input type="text" name="permZipCode" id="permZipCode"  value="<?php echo $permZipCode?>" placeholder="Zip Code"></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Telephone No.</div></td>
						<td><input type="text" name="permTelNo" id="permTelNo" class="span6" value="<?php echo $permTelNo?>" style="width: 150px;"></td>
					</tr>
					<tr>
						<td height="60" colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td height="30"><div align="right">Email Address</div></td>
						<td><input type="text" name="txEmail" id="txEmail" class="span6" value="<?php echo $txEmail?>"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">Cellphone No.</div></td>
						<td><input type="text" name="txCellphone" id="txCellphone" class="span6" value="<?php echo $txCellphone?>" onkeypress="return checkinput(this, event);"></td>
					</tr>
					<tr>
						<td height="30"><div align="right">DATE ACCOMPLISHED</div></td>
						<td>
							<a href="javascript:NewCssCal('txAccmplshdDate')"><img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp"></a>
							<input name="txAccmplshdDate" type="text" class="span6 mytextbox" id="txAccmplshdDate" value="<?php echo $date_accomplished?>" style="width: 90px;" readonly>
						</td>
					</tr>
				</table>
				<div align="center"><input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-small btn-primary"></div>
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
<script>
$(document).ready(function(){
	$('#chkPHDesc').click(function(){
		if( $('#chkPH').is(':checked') )
			$("#chkPH").prop( "checked", false );
		else
			$("#chkPH").prop( "checked", true );
	});
	$('#chkSSSDesc').click(function(){
		if( $('#chkSSS').is(':checked') )
			$("#chkSSS").prop( "checked", false );
		else
			$("#chkSSS").prop( "checked", true );
	});
	$('#chkPAGDesc').click(function(){
		if( $('#chkPAG').is(':checked') )
			$("#chkPAG").prop( "checked", false );
		else
			$("#chkPAG").prop( "checked", true );
	});
	$('#chkGSISDesc').click(function(){
		if( $('#chkGSIS').is(':checked') )
			$("#chkGSIS").prop( "checked", false );
		else
			$("#chkGSIS").prop( "checked", true );
	});
});
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
  
function getCityMun(provId){
	var strURL="findCityMun.php?provId="+provId;
	var req = getXMLHTTP();

	if(req){
		req.onreadystatechange = function(){
			if(req.readyState == 4){
				// only if "OK"
				if (req.status == 200){
					document.getElementById('divCurCityMun').innerHTML=req.responseText;
					document.getElementById('divCurBrngy').innerHTML='<select name="selCurBrngy" id="selCurBrngy"><option value="">Select Barangay</option></select>';            
				}
				else{
					alert("Problem while using XMLHTTP:\n" + req.statusText);
				}
			}
		}
		req.open("GET", strURL, true);
		req.send(null);
	}
}

function getBrngy(cityMunId){
	var strURL="findBrngy.php?cityMunId="+cityMunId;
	var req = getXMLHTTP();

	if(req){
		req.onreadystatechange = function(){
			if(req.readyState == 4){
				// only if "OK"
				if (req.status == 200){
					document.getElementById('divCurBrngy').innerHTML=req.responseText;
				}else{
					alert("Problem while using XMLHTTP:\n" + req.statusText);
				}
			}
		}
		req.open("GET", strURL, true);
		req.send(null);
	}
}

function getCityMunPerm(provId){
	var strURL="findCityMunPerm.php?provId="+provId;
	var reqs = getXMLHTTP();

	if(reqs){
		reqs.onreadystatechange = function(){
			if(reqs.readyState == 4){
				// only if "OK"
				if(reqs.status == 200){
					document.getElementById('divCityMunPerm').innerHTML=reqs.responseText;
					document.getElementById('divBrngyPerm').innerHTML='<select name="selBrngyPerm" id="selBrngyPerm" class="form-control"><option value="">Select Barangay</option></select>';
				}else{
					alert("Problem while using XMLHTTP:\n" + reqs.statusText);
				}
			}
		}
		reqs.open("GET", strURL, true);
		reqs.send(null);
	}
}

function getBrngyPerm(cityMunId){
	var strURL="findBrngyPerm.php?cityMunId="+cityMunId;
	var reqss = getXMLHTTP();

	if(reqss){
		reqss.onreadystatechange = function(){
			if(reqss.readyState == 4){
				// only if "OK"
				if(reqss.status == 200){
					document.getElementById('divBrngyPerm').innerHTML=reqss.responseText;
				}else{
					alert("Problem while using XMLHTTP:\n" + reqss.statusText);
				}
			}
		}
		reqss.open("GET", strURL, true);
		reqss.send(null);
	}
}
function ask(){
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false;
}
</script>
<script>document.getElementById("spinner").style.display = "none";//$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "error",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>
<!-- end: JavaScript-->
</body>
</html>