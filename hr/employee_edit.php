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

$q = $db->select('employee','*',array('emp_id'=>$eid));
$r = $db->fetch_array($q);
$cert_id = $r['cert_id'] ?? NULL;
$txtEmpNo = $r['emp_no'] ?? NULL;
$txtfName = $r['fname'] ?? NULL;
$txtmName = $r['mname'] ?? NULL;
$txtlName = $r['lname'] ?? NULL;
$txtExtName = $r['extname'] ?? NULL;
$txtNickName = $r['nickname'] ?? NULL;
$bdate = $r['bdate'] ?? NULL;
$txtBirthPlace = $r['bplace'] ?? NULL;
$selGender = $r['gender'] ?? NULL;
$selCivilStat = $r['civil_status'] ?? NULL;
$txCitizenship = $r['citizenship'] ?? NULL;
$txReligion = $r['religion'] ?? NULL;
$txHeight = $r['height'] ?? NULL;
$txWeight = $r['weight'] ?? NULL;
$txBloodType = $r['bloodtype'] ?? NULL;
$txTIN = $r['tin'] ?? NULL;
$txpagibig = $r['pagibig'] ?? NULL;
$pagibig_autodeduct = $r['pagibig_autodeduct'] ?? NULL;
$txtPagIbigContrib = isset($r['pagibig_contribution'])  ? functions::formatMoney($r['pagibig_contribution']) : '';
$txphilhealth = $r['philhealth'] ?? NULL;
$philhealth_autodeduct = $r['philhealth_autodeduct'] ?? NULL;
$txtPHContrib = isset($r['philhealth_contribution'])  ? functions::formatMoney($r['philhealth_contribution']) : '';
$txtSSS = $r['sss'] ?? NULL;
$sss_autodeduct = $r['sss_autodeduct'] ?? NULL;
$txtSSSContrib = isset($r['sss_contribution']) ? functions::formatMoney($r['sss_contribution']) : '';
$txtGSIS = $r['gsis'] ?? NULL;
$gsis_autodeduct = $r['gsis_autodeduct'] ?? NULL;
$selCurProvince = $r['curr_province'] ?? NULL;
$selCurCityMun = $r['curr_cityMun'] ?? NULL;
$selCurBrngy = $r['curr_add'] ?? NULL;
$curStreet = $r['curr_street'] ?? NULL;
$curZipCode = $r['curr_zipcode'] ?? NULL;
$curTelNo = $r['curr_tel'] ?? NULL;
$selProvincePerm = $r['perm_province'] ?? NULL;
$selCityMunPerm = $r['perm_cityMun'] ?? NULL;
$selBrngyPerm = $r['perm_add'] ?? NULL;
$permStreet = $r['perm_street'] ?? NULL;
$permZipCode = $r['perm_zipcode'] ?? NULL;
$permTelNo = $r['perm_tel'] ?? NULL;
$txEmail = $r['email'] ?? NULL;
$txCellphone = $r['cell_no'] ?? NULL;
$date_accomplished = $r['date_accomplished'] ?? NULL;

$qSelPos = $db->select('emp_position','*',array('emp_id'=>$eid));
while($rSelPos = $db->fetch_array($qSelPos)):
	$arrPosition[$rSelPos['dp_id']]=1;
endwhile;
$arrSiteAssign=array();
$qSelSite = $db->select('emp_site_assign','*',array('emp_id'=>$eid));
while($rSelSite = $db->fetch_array($qSelSite)):
	$arrSiteAssign[$rSelSite['proj_id']]=1;
endwhile;

// UPDATED: Triggers if REQUEST_METHOD is POST OR btnSave is set
if( ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_POST['btnSave'])) && !empty($eid) ){
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
		$max_size = 2000 * 1024;
		function tempnam_sfx($path, $suffix){
			do{
				$file = $path."/".mt_rand().$suffix;
				$fp = @fopen($file, 'x');
			}
			while(!$fp);

			fclose($fp);
			return $file;
		}
		$verifyimg = getimagesize($_FILES['image']['tmp_name']);
		$pattern = "#^(image/)[^\s\n<]+$#i";

		if( !preg_match($pattern, $verifyimg['mime']) ){
			functions::say("Only image files are allowed!");
		}
		else if( $_FILES["image"]["size"] > $max_size ){
			functions::say("Image reached the limit size!");
		}
		else{
			$uploadfile = tempnam_sfx($uploaddir, ".jpg");
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
			$curr_brgy=$db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$curr_add));
			$curr_city=$db->getValue('refcitymun','citymunDesc',array('cityMunCode'=>$curr_cityMun));
			$curr_province=$db->getValue('refprovince','provDesc',array('provCode'=>$curr_province));

			$perm_brgy=$db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$perm_add));
			$perm_city=$db->getValue('refcitymun','citymunDesc',array('cityMunCode'=>$perm_cityMun));
			$perm_province=$db->getValue('refprovince','provDesc',array('provCode'=>$perm_province));

			$curr_address = address($curr_street,$curr_brgy,$curr_city,$curr_province);
			$perm_address = address($perm_street,$perm_brgy,$perm_city,$perm_province);
			$db->update('employee',array('curr_address'=>$curr_address,'perm_address'=>$perm_address),array('emp_id'=>$eid));

			$qSelPos = $db->select('emp_position','*',array('emp_id'=>$eid));
			while($rSelPos = $db->fetch_array($qSelPos)):
				if( !isset($selPosition[$rSelPos['dp_id']]) )
					$db->delete('emp_position',array('emp_id'=>$eid,'dp_id'=>$rSelPos['dp_id']));
			endwhile;
			foreach($selPosition as $dpID):
				$arrField = array('emp_id'=>$eid,'dp_id'=>$dpID);
				if( $db->getValue('emp_position','count(*)',$arrField)==0 )
					$db->insert('emp_position',$arrField);
			endforeach;

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
	<meta charset="utf-8">
	<title>Employee Update - Portal</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/inputInt.js"></script>
	<link rel="stylesheet" href="../css/bootstrap-multiselect.css" type="text/css">
	<script type="text/javascript" src="../js/datetimepicker_css.js"></script>
	<link rel="shortcut icon" href="../img/favicon.png">
	<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>

	<style>
		:root {
			--accent-color: #0f172a;
			--brand-blue: #3b82f6;
			--bg-canvas: #f8fafc;
			--panel-bg: #ffffff;
			--border-subtle: #e2e8f0;
			--text-primary: #0f172a;
			--text-muted: #64748b;
		}

		body {
			background-color: var(--bg-canvas);
			font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
			color: var(--text-primary);
			padding: 20px 10px;
		}

		.profile-container {
			max-width: 1100px;
			margin: 0 auto;
		}

		.banner-header {
			background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
			border-radius: 16px 16px 0 0;
			padding: 30px;
			color: white;
			display: flex;
			align-items: center;
			justify-content: space-between;
			box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
		}

		.banner-title h2 {
			margin: 0;
			font-size: 22px;
			font-weight: 700;
			color: #ffffff;
			letter-spacing: 0.5px;
		}

		.banner-title p {
			margin: 5px 0 0 0;
			font-size: 13px;
			color: #94a3b8;
		}

		.main-card {
			background: var(--panel-bg);
			border-radius: 0 0 16px 16px;
			padding: 30px;
			box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
			border: 1px solid var(--border-subtle);
			border-top: none;
		}

		.avatar-wrapper {
			display: flex;
			align-items: center;
			gap: 25px;
			background: #f1f5f9;
			padding: 20px;
			border-radius: 12px;
			margin-bottom: 30px;
		}

		.avatar-wrapper img {
			border-radius: 12px;
			box-shadow: 0 4px 12px rgba(0,0,0,0.1);
			background: #ffffff;
			padding: 4px;
		}

		.section-badge {
			display: inline-block;
			background: #e0f2fe;
			color: #0369a1;
			font-weight: 700;
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 1px;
			padding: 4px 10px;
			border-radius: 20px;
			margin-bottom: 15px;
		}

		.grid-2col {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 20px;
		}

		@media (max-width: 768px) {
			.grid-2col {
				grid-template-columns: 1fr;
			}
		}

		.form-group-custom {
			margin-bottom: 15px;
		}

		.form-group-custom label {
			display: block;
			font-size: 12px;
			font-weight: 600;
			color: var(--text-muted);
			text-transform: uppercase;
			margin-bottom: 6px;
		}

		input[type="text"], select {
			width: 100% !important;
			height: 42px !important;
			padding: 8px 14px !important;
			font-size: 14px !important;
			border-radius: 8px !important;
			border: 1px solid var(--border-subtle) !important;
			background-color: #ffffff !important;
			box-sizing: border-box !important;
			transition: all 0.2s ease !important;
		}

		input[type="text"]:focus, select:focus {
			border-color: var(--brand-blue) !important;
			box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
			outline: none !important;
		}

		.address-card-group {
			background: #fafafa;
			border: 1px dashed var(--border-subtle);
			padding: 20px;
			border-radius: 12px;
			margin-bottom: 20px;
		}

		.btn-save-floating {
			position: sticky;
			bottom: 20px;
			background: #ffffff;
			padding: 15px 25px;
			border-radius: 12px;
			box-shadow: 0 10px 30px rgba(0,0,0,0.15);
			border: 1px solid var(--border-subtle);
			display: flex;
			justify-content: flex-end;
			align-items: center;
			margin-top: 30px;
			z-index: 10;
		}

		.btn-modern-primary {
			background: var(--brand-blue);
			color: #ffffff;
			font-weight: 600;
			border: none;
			padding: 12px 30px;
			border-radius: 8px;
			cursor: pointer;
			font-size: 14px;
			transition: background 0.2s;
		}

		.btn-modern-primary:hover {
			background: #2563eb;
		}

		.chzn-container-multi .chzn-choices {
			border-radius: 8px !important;
			border: 1px solid var(--border-subtle) !important;
			padding: 4px !important;
		}

		.checkbox-custom {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			font-size: 12px;
			color: var(--text-muted);
			margin-left: 10px;
			cursor: pointer;
		}

		/* Modal Overlay with Blur */
		.modal-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100vw;
			height: 100vh;
			background: rgba(15, 23, 42, 0.4);
			backdrop-filter: blur(8px);
			-webkit-backdrop-filter: blur(8px);
			display: flex;
			align-items: center;
			justify-content: center;
			z-index: 9999;
			opacity: 0;
			visibility: hidden;
			transition: opacity 0.25s ease, visibility 0.25s ease;
		}

		.modal-overlay.active {
			opacity: 1;
			visibility: visible;
		}

		/* Modal Card */
		.modal-card {
			background: #ffffff;
			width: 100%;
			max-width: 420px;
			border-radius: 16px;
			padding: 24px;
			box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
			transform: scale(0.95);
			transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
			text-align: center;
		}

		.modal-overlay.active .modal-card {
			transform: scale(1);
		}

		/* Icon Styling */
		.modal-icon-wrapper {
			width: 48px;
			height: 48px;
			background: #eff6ff;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 16px auto;
		}

		.modal-icon {
			width: 24px;
			height: 24px;
			color: #3b82f6;
		}

		/* Text Content */
		.modal-title {
			margin: 0 0 8px 0;
			font-size: 18px;
			font-weight: 700;
			color: #0f172a;
		}

		.modal-description {
			margin: 0 0 24px 0;
			font-size: 14px;
			color: #64748b;
			line-height: 1.5;
		}

		/* Actions Footer */
		.modal-actions {
			display: flex;
			gap: 12px;
		}

		.btn-modal {
			flex: 1;
			padding: 10px 16px;
			border-radius: 8px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.2s ease;
			border: none;
		}

		.btn-modal-cancel {
			background: #f1f5f9;
			color: #475569;
		}

		.btn-modal-cancel:hover {
			background: #e2e8f0;
			color: #1e293b;
		}

		.btn-modal-confirm {
			background: #3b82f6;
			color: #ffffff;
			box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
		}

		.btn-modal-confirm:hover {
			background: #2563eb;
		}
	</style>
</head>
<body>

<div class="profile-container">
	<?php if(empty($fromED)){ ?>
	<div class="box-content" style="padding-top: 40px;">
		<div align="right" class="nav tab-menu nav-tabs" style="padding-top: 3px;"><?php require_once('employee_options.php');?></div>
	</div>
	<?php } ?>

	<div class="banner-header">
		<div class="banner-title">
			<h2>Employee File Update</h2>
			<p>Manage official employee information and records</p>
		</div>
	</div>

	<div class="main-card">
		<form method="post" id="empForm" enctype="multipart/form-data">
			
			<div class="avatar-wrapper">
				<?php
				$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
				if( $fileName && file_exists('../img_emp/'.$fileName)){
					$file = '../img_emp/'.$fileName;
					echo '<img height="120" width="120" src="'.$file.'">';
				}
				else
					echo '<img height="120" width="120" src="../img_emp/blank-pic.png">';
				?>
				<div>
					<span class="section-badge">Profile Photo</span>
					<div style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Upload standard 2x2 employee identification image:</div>
					<input type="file" name="image">
				</div>
			</div>

			<span class="section-badge">01. Company Information</span>
			<div class="grid-2col" style="margin-bottom: 25px;">
				<div class="form-group-custom">
					<label>Employee ID Number</label>
					<input type="text" name="txtEmpNo" id="txtEmpNo" value="<?php echo $txtEmpNo?>">
				</div>
				<div class="form-group-custom">
					<label>Designated Position(s)</label>
					<select name="selPosition[]" id="selPosition" multiple="multiple" data-rel="chosen">
						<?php 
						$qPos = $db->select('dep_position','*',array(),'ORDER BY pos_name');
						while($rPos = $db->fetch_array($qPos)):
						$dept = $db->getValue('department','dep_name',array('dep_id'=>$rPos['dep_id']));
						$depName = ($dept) ? '('.$dept.')' : '(no department)';
						?>
						<option value="<?php echo $rPos['dp_id']?>" <?php if( isset($arrPosition[$rPos['dp_id']]) )echo 'selected="selected"';?>><?php echo $rPos['pos_name']; echo '&nbsp;&nbsp;'.$depName;?></option>
						<?php endwhile;?>
					</select>
				</div>
				<div style="display:none;">
					<select name="selProjAssign[]" id="selProjAssign" multiple="multiple" data-rel="chosen">
						<?php 
						$qProj = $db->select('project','*',array(),'ORDER BY proj_name');
						while($rProj = $db->fetch_array($qProj)):
						?>
						<option value="<?php echo $rProj['proj_id']?>" <?php if( isset($arrSiteAssign[$rProj['proj_id']]) )echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?></option>
						<?php endwhile;?>
					</select>
				</div>
			</div>

			<span class="section-badge">02. Personal Profile</span>
			<div class="grid-2col" style="margin-bottom: 25px;">
				<div class="form-group-custom">
					<label>First Name</label>
					<input type="text" name="txtfName" id="txtfName" value="<?php echo $txtfName?>" required/>
				</div>
				<div class="form-group-custom">
					<label>Middle Name</label>
					<input type="text" name="txtmName" id="txtmName" value="<?php echo $txtmName?>" />
				</div>
				<div class="form-group-custom">
					<label>Last Name</label>
					<input type="text" name="txtlName" id="txtlName" value="<?php echo $txtlName?>" required/>
				</div>
				<div class="form-group-custom">
					<label>Suffix (JR, SR, III)</label>
					<input type="text" name="txtExtName" id="txtExtName" value="<?php echo $txtExtName?>" />
				</div>
				<div class="form-group-custom">
					<label>Nickname</label>
					<input type="text" name="txtNickName" id="txtNickName" value="<?php echo $txtNickName?>" />
				</div>
				<div class="form-group-custom">
					<label>Date of Birth</label>
					<div style="display: flex; gap: 8px; align-items: center;">
						<input name="txBdateDate" type="text" id="txBdateDate" value="<?php echo $bdate; ?>" readonly>
						<a href="javascript:NewCssCal('txBdateDate')"><img src="../js/datepick/cal.gif" width="18" height="18" border="0" alt="Pick Date"></a>
					</div>
				</div>
				<div class="form-group-custom">
					<label>Place of Birth</label>
					<input type="text" name="txtBirthPlace" id="txtBirthPlace" value="<?php echo $txtBirthPlace?>">
				</div>
				<div class="form-group-custom">
					<label>Gender</label>
					<select name="selGender" id="selGender" required>
						<option value="">--Select--</option>
						<option value="Male" <?php if($selGender=='Male')echo 'selected="selected"';?>>Male</option>
						<option value="Female" <?php if($selGender=='Female')echo 'selected="selected"';?>>Female</option>
					</select>
					<span id="msgGender" name="msgGender"></span>
				</div>
				<div class="form-group-custom">
					<label>Civil Status</label>
					<select name="selCivilStat" id="selCivilStat" required>
						<option value="">--Select--</option>
						<option value="Single" <?php if($selCivilStat=='Single')echo 'selected="selected"';?>>Single</option>
						<option value="Married" <?php if($selCivilStat=='Married')echo 'selected="selected"';?>>Married</option>
						<option value="Annulled" <?php if($selCivilStat=='Annulled')echo 'selected="selected"';?>>Annulled</option>
						<option value="Widowed" <?php if($selCivilStat=='Widowed')echo 'selected="selected"';?>>Widowed</option>
						<option value="Separated" <?php if($selCivilStat=='Separated')echo 'selected="selected"';?>>Separated</option>
					</select>
					<span id="msgCivilStat" name="msgCivilStat"></span>
				</div>
				<div class="form-group-custom">
					<label>Citizenship</label>
					<input type="text" name="txCitizenship" id="txCitizenship" value="<?php echo $txCitizenship;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCitezenship;?>]'>
					<span id="msgCitizenship" name="msgCitizenship"></span>
				</div>
				<div class="form-group-custom">
					<label>Religion</label>
					<input type="text" name="txReligion" id="txReligion" value="<?php echo $txReligion;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReligion;?>]'>
				</div>
				<div class="form-group-custom">
					<label>Height (m) / Weight (kg) / Blood Type</label>
					<div style="display: flex; gap: 10px;">
						<input type="text" name="txHeight" id="txHeight" value="<?php echo $txHeight;?>" placeholder="Height">
						<input type="text" name="txWeight" id="txWeight" value="<?php echo $txWeight;?>" placeholder="Weight">
						<input type="text" name="txBloodType" id="txBloodType" value="<?php echo $txBloodType;?>" placeholder="Blood">
					</div>
				</div>
			</div>

			<span class="section-badge">03. Government Identifiers</span>
			<div class="grid-2col" style="margin-bottom: 25px;">
				<div class="form-group-custom">
					<label>TIN Number</label>
					<input type="text" name="txTIN" id="txTIN" value="<?php echo $txTIN;?>">
				</div>
				<div class="form-group-custom">
					<label>
						PAGIBIG No.
						<span class="checkbox-custom">
							<input type="checkbox" name="chkPAG" id="chkPAG" <?php if($pagibig_autodeduct==1){echo 'checked';} ?>>
							<span id="chkPAGDesc">Auto-Deduct</span>
						</span>
					</label>
					<div style="display: flex; gap: 10px;">
						<input type="text" name="txpagibig" id="txpagibig" value="<?php echo $txpagibig;?>" placeholder="Pagibig No.">
						<input type="text" name="txtPagIbigContrib" id="txtPagIbigContrib" value="<?php echo $txtPagIbigContrib?>" placeholder="Add'l Contrib" onkeyup="FormatCurrency(this);">
					</div>
				</div>
				<div class="form-group-custom">
					<label>
						PhilHealth No.
						<span class="checkbox-custom">
							<input type="checkbox" name="chkPH" id="chkPH" <?php if($philhealth_autodeduct==1){echo 'checked';} ?>>
							<span id="chkPHDesc">Auto-Deduct</span>
						</span>
					</label>
					<input type="text" name="txphilhealth" id="txphilhealth" value="<?php echo $txphilhealth;?>">
				</div>
				<div class="form-group-custom">
					<label>
						SSS No.
						<span class="checkbox-custom">
							<input type="checkbox" name="chkSSS" id="chkSSS" <?php if($sss_autodeduct==1){echo 'checked';} ?>>
							<span id="chkSSSDesc">Auto-Deduct</span>
						</span>
					</label>
					<input type="text" name="txtSSS" id="txtSSS" value="<?php echo $txtSSS;?>">
				</div>
				<div class="form-group-custom">
					<label>
						GSIS No.
						<span class="checkbox-custom">
							<input type="checkbox" name="chkGSIS" id="chkGSIS" <?php if($gsis_autodeduct==1){echo 'checked';} ?>>
							<span id="chkGSISDesc">Auto-Deduct</span>
						</span>
					</label>
					<input type="text" name="txtGSIS" id="txtGSIS" value="<?php echo $txtGSIS;?>">
				</div>
			</div>

			<span class="section-badge">04. Contact & Address Records</span>
			<div class="address-card-group">
				<label style="font-weight:700; font-size:13px; color:var(--text-primary); margin-bottom:10px; display:block;">RESIDENTIAL ADDRESS</label>
				<div class="grid-2col" style="gap: 10px; margin-bottom: 10px;">
					<select name="selCurProvince" id="selCurProvince" onChange="getCityMun(this.value)">
						<option value="">Select Province</option>
						<?php 
						$qProvince = $db->select('refprovince','*',array(),'ORDER BY provDesc');
						while($rProv = $db->fetch_array($qProvince)): ?>
						<option value="<?php echo $rProv['provCode']?>" <?php if($rProv['provCode']==$selCurProvince)echo 'selected="selected"';?>><?php echo $rProv['provDesc']?></option>
						<?php endwhile;?>
					</select>
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
					<input type="text" name="curZipCode" id="curZipCode" value="<?php echo $curZipCode?>" placeholder="Zip Code">
				</div>
				<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
					<input type="text" name="curStreet" id="curStreet" value="<?php echo $curStreet?>" placeholder="Street Address">
					<input type="text" name="curTelNo" id="curTelNo" value="<?php echo $curTelNo?>" placeholder="Tel No.">
				</div>
			</div>

			<div class="address-card-group">
				<label style="font-weight:700; font-size:13px; color:var(--text-primary); margin-bottom:10px; display:block;">PERMANENT ADDRESS</label>
				<div class="grid-2col" style="gap: 10px; margin-bottom: 10px;">
					<select name="selProvincePerm" id="selProvincePerm" onChange="getCityMunPerm(this.value)">
						<option value="">Select Province</option>
						<?php 
						$qProvince = $db->select('refprovince','*',array(),'ORDER BY provDesc');
						while($rProv = $db->fetch_array($qProvince)): ?>
						<option value="<?php echo $rProv['provCode']?>" <?php if($rProv['provCode']==$selProvincePerm)echo 'selected="selected"';?>><?php echo $rProv['provDesc']?></option>
						<?php endwhile;?>
					</select>
					<div id="divCityMunPerm">
						<select name="selCityMunPerm" id="selCityMunPerm">
							<option value="">Select City/Municipality</option>
							<?php 
							$qCityMun = $db->select('refcitymun','*',array('provCode'=>$selProvincePerm),'ORDER BY citymunDesc');
							while($rCityMun = $db->fetch_array($qCityMun)): ?>
							<option value="<?php echo $rCityMun['citymunCode']?>" <?php if($rCityMun['citymunCode']==$selCityMunPerm)echo 'selected="selected"';?>><?php echo $rCityMun['citymunDesc']?></option>
							<?php endwhile;?>
						</select>
					</div>
					<div id="divBrngyPerm">
						<select id="selBrngyPerm" name="selBrngyPerm">
							<option value="">Select Barangay</option>
							<?php 
							$qBrgy = $db->select('refbrgy','*',array('citymunCode'=>$selCityMunPerm),'ORDER BY brgyDesc');
							while($rBrgy = $db->fetch_array($qBrgy)): ?>
							<option value="<?php echo $rBrgy['brgyCode']?>" <?php if($rBrgy['brgyCode']==$selBrngyPerm)echo 'selected="selected"';?>><?php echo $rBrgy['brgyDesc']?></option>
							<?php endwhile;?>
						</select>
					</div>
					<input type="text" name="permZipCode" id="permZipCode" value="<?php echo $permZipCode?>" placeholder="Zip Code">
				</div>
				<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
					<input type="text" name="permStreet" id="permStreet" value="<?php echo $permStreet?>" placeholder="Street Address">
					<input type="text" name="permTelNo" id="permTelNo" value="<?php echo $permTelNo?>" placeholder="Tel No.">
				</div>
			</div>

			<div class="grid-2col">
				<div class="form-group-custom">
					<label>Email Address</label>
					<input type="text" name="txEmail" id="txEmail" value="<?php echo $txEmail?>">
				</div>
				<div class="form-group-custom">
					<label>Cellphone Number</label>
					<input type="text" name="txCellphone" id="txCellphone" value="<?php echo $txCellphone?>" onkeypress="return checkinput(this, event);">
				</div>
				<div class="form-group-custom">
					<label>Date Accomplished</label>
					<div style="display: flex; gap: 8px; align-items: center;">
						<input name="txAccmplshdDate" type="text" id="txAccmplshdDate" value="<?php echo $date_accomplished?>" readonly>
						<a href="javascript:NewCssCal('txAccmplshdDate')"><img src="../js/datepick/cal.gif" width="18" height="18" border="0" alt="Pick Date"></a>
					</div>
				</div>
			</div>

			<div class="btn-save-floating">
				<input type="submit" name="btnSave" id="btnSave" value="Save Changes" class="btn-modern-primary">
			</div>
		</form>
	</div>
</div>

<!-- Modern Confirmation Modal -->
<div id="confirmModalOverlay" class="modal-overlay">
  <div class="modal-card">
    <div class="modal-icon-wrapper">
      <svg class="modal-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </div>
    <div class="modal-content">
      <h3 class="modal-title">Save Changes?</h3>
      <p class="modal-description">Are you sure you want to update this employee record? All changes will be saved to the database.</p>
    </div>
    <div class="modal-actions">
      <button type="button" id="btnModalCancel" class="btn-modal btn-modal-cancel">Cancel</button>
      <button type="button" id="btnModalConfirm" class="btn-modal btn-modal-confirm">Yes, Save</button>
    </div>
  </div>
</div>

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
<script src="../js/showPage.js"></script>

<script>
$(document).ready(function(){
	// Checkbox toggles
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

	// Modern Modal Form Interception
	var formToSubmit = null;

	$('#empForm').on('submit', function(e) {
		var $form = $(this);
		if (!$form.data('confirmed')) {
			e.preventDefault();
			formToSubmit = this;
			$('#confirmModalOverlay').addClass('active');
		}
	});

	// Handle Modal Confirm Button with requestSubmit
	$('#btnModalConfirm').on('click', function() {
		if (formToSubmit) {
			var $form = $(formToSubmit);
			$form.data('confirmed', true);

			// Append hidden btnSave input if it doesn't already exist
			if ($form.find('input[name="btnSave"]').length === 0) {
				$form.append('<input type="hidden" name="btnSave" value="Save Changes">');
			}

			$('#confirmModalOverlay').removeClass('active');

			// Trigger native form submission reliably
			if (typeof formToSubmit.requestSubmit === 'function') {
				formToSubmit.requestSubmit();
			} else {
				formToSubmit.submit();
			}
		}
	});

	$('#btnModalCancel, #confirmModalOverlay').on('click', function(e) {
		if (e.target === this) {
			$('#confirmModalOverlay').removeClass('active');
			if (formToSubmit) {
				$(formToSubmit).data('confirmed', false);
			}
			formToSubmit = null;
		}
	});
});

function getXMLHTTP() {
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
</script>

<script>
if(document.getElementById("spinner")) {
	document.getElementById("spinner").style.display = "none";
}
</script>

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

</body>
</html>