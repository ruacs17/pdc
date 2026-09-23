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
$fromED = (isset($_REQUEST['fromED']) && !empty($_REQUEST['fromED']) ) ? $_REQUEST['fromED'] : '';
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$educEdt = (isset($_REQUEST['eeEdt']) && !empty($_REQUEST['eeEdt']) ) ? functions::decode($_REQUEST['eeEdt']) : 0;
$educDel = (isset($_REQUEST['eeDlt']) && !empty($_REQUEST['eeDlt']) ) ? functions::decode($_REQUEST['eeDlt']) : 0;
$selLevel='';$txSchool='';$txCourse='';$selYrGrad='';$txHighestLevel='';$selDateFrom='';$selDateTo='';$txHonors='';
if($educEdt){
	$q = $db->select('emp_education','*',array('emp_id'=>$eid));
	while($r = $db->fetch_array($q)):
		$selLevel = $r['ee_level'];
		$txSchool = $r['ee_school'];
		$txCourse = $r['ee_course'];
		$selYrGrad = $r['ee_year_grad'];
		$txHighestLevel = $r['ee_highest_level'];
		$selDateFrom = $r['ee_date_from'];
		$selDateTo = $r['ee_date_to'];
		$txHonors = $r['ee_honors'];
	endwhile;
}
if($educDel){
	$_SESSION['notif_warning']='Information Removed!';
	$db->delete('emp_education',array('ee_id'=>$educDel,'emp_id'=>$eid));
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
if( isset($_POST['btnSave']) ){

	$ee_level = ( isset($_POST['selLevel']) ) ? strtoupper(trim($_POST['selLevel'])) : '';
	$ee_school = ( isset($_POST['txSchool']) ) ? strtoupper(trim($_POST['txSchool'])) : '';
	$ee_course = ( isset($_POST['txCourse']) ) ? strtoupper(trim($_POST['txCourse'])) : '';
	$ee_year_grad = ( isset($_POST['selYrGrad']) ) ? strtoupper(trim($_POST['selYrGrad'])) : '';
	$ee_highest_level = ( isset($_POST['txHighestLevel']) ) ? strtoupper(trim($_POST['txHighestLevel'])) : '';
	$ee_date_from = ( isset($_POST['selDateFrom']) ) ? strtoupper(trim($_POST['selDateFrom'])) : '';
	$ee_date_to = ( isset($_POST['selDateTo']) ) ? strtoupper(trim($_POST['selDateTo'])) : '';
	$ee_honors = ( isset($_POST['txHonors']) ) ? strtoupper(trim($_POST['txHonors'])) : '';

	$arrInsert = array('emp_id'=>$eid,'ee_level'=>$ee_level,'ee_school'=>$ee_school,'ee_course'=>$ee_course,'ee_year_grad'=>$ee_year_grad,'ee_highest_level'=>$ee_highest_level,'ee_date_from'=>$ee_date_from,'ee_date_from'=>$ee_date_from,'ee_date_to'=>$ee_date_to,'ee_honors'=>$ee_honors);
	if($educEdt){
		$_SESSION['notif_id_list']=$educEdt;
		$db->update('emp_education',$arrInsert,array('emp_id'=>$eid,'ee_id'=>$educEdt));
		$_SESSION['notif_success']='Changes Saved!';
	}
	else{
		$ins = $db->insert('emp_education',$arrInsert);
		if($ins){
			$_SESSION['notif_id_list']=$ins;
			$_SESSION['notif_success']='Information Added!';
		}
			
	}
	functions::sendTo(functions::pageName().'?eid='.functions::encode($eid).'&fromED='.$fromED);
	die();
}
$qSchool = $db->query('SELECT DISTINCT ee_school as "itm" FROM emp_education ORDER BY ee_school');
$namesSchool='';
while($rItemC=$db->fetch_array($qSchool)):
	$string = preg_replace("/'/",'"',$rItemC['itm']);
	$namesSchool .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesSchool .= '"--"';
$qCourse = $db->query('SELECT DISTINCT ee_course as "itm" FROM emp_education ORDER BY ee_school');
$namesCourse='';
while($rItemC=$db->fetch_array($qCourse)):
	$string = preg_replace("/'/",'"',$rItemC['itm']);
	$namesCourse .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesCourse .= '"--"';
$qHighestLevel = $db->query('SELECT DISTINCT ee_highest_level as "itm" FROM emp_education ORDER BY ee_school');
$namesHighestLevel='';
while($rItemC=$db->fetch_array($qHighestLevel)):
	$string = preg_replace("/'/",'"',$rItemC['itm']);
	$namesHighestLevel .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesHighestLevel .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Employee Education Manage</title>
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
	<style type="text/css">body{font-size: 12px;}</style>
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
			<form class="form-horizontal" method="post" onSubmit="return ask()">
				<div align="center" style="padding-bottom: 15px;"><h2>EDUCATIONAL BACKGROUND</h2></div>
				<table id="tblist" width="80%" align="center" border="0" class="table <?php if(!isset($_SESSION['notif_id_list'])){echo 'table-bordered';} ?> table-hover table-striped" style="background-color:#E4E1E1; font-size: 12px;">
					<thead>
					<tr>
						<td rowspan="2"><strong>LEVEL</strong></td>
						<td rowspan="2"><strong>NAME OF SCHOOL</strong></td>
						<td rowspan="2"><strong>DEGREE COURSE</strong></td>
						<td rowspan="2" width="10%"><div align="center"><strong>YEAR GRADUATED</strong> <br><i>(if graduated)</i></div></td>
						<td rowspan="2"><strong>HIGHEST GRADE /<br>LEVEL /<br>UNITS EARNED</strong> <br><i>(if NOT graduated)</i></td>
						<td colspan="2" width="10%"><div align="center"><strong>INCLUSIVE DATES OF ATTENDANCE</strong></div></td>
						<td rowspan="2"><strong>SCHOLARSHIP /<br>ACADEMIC HONORS RECEIVED</strong></td>
						<td rowspan="2" width="7%"><div align="center"><strong>Option</strong></div></td>
					</tr>
					<tr>
						<td><div align="center"><strong>FROM</strong></div></td>
						<td><div align="center"><strong>TO</strong></div></td>
					</tr>
					</thead>
					<tbody>
					<?php
					$arrLevel = array('ELEMENTARY','SECONDARY','VOCATIONAL','COLLEGE','GRADUATE');
					foreach($arrLevel as $level):
						$qCol = $db->select('emp_education','*',array('emp_id'=>$eid,'ee_level'=>$level));
						while($rCol = $db->fetch_array($qCol)):
							$rID=$rCol['ee_id'];
					?>
						<tr id="rw<?php echo $rID?>" style="background-color:#ececec">
							<td><?php echo $rCol['ee_level'];?></td>
							<td><?php echo $rCol['ee_school']?></td>
							<td><?php echo $rCol['ee_course']?></td>
							<td><div align="center"><?php echo $rCol['ee_year_grad']?></div></td>
							<td><?php echo $rCol['ee_highest_level']?></td>
							<td><div align="center"><?php echo $rCol['ee_date_from']?></div></td>
							<td><div align="center"><?php echo $rCol['ee_date_to']?></div></td>
							<td><?php echo $rCol['ee_honors']?></td>
							<td>
								<div align="center">
									<a href="?eid=<?php echo functions::encode($eid)?>&eeEdt=<?php echo functions::encode($rCol['ee_id'])?>&fromED=<?php echo $fromED;?>" title="Modify" class="btn btn-warning btn-mini"><i class="halflings-icon white pencil"></i></a>
									<a href="?eid=<?php echo functions::encode($eid)?>&eeDlt=<?php echo functions::encode($rCol['ee_id'])?>&fromED=<?php echo $fromED;?>" onClick="if(confirm('Do you want to remove this information?')){return true;}else{return false;}" title="Remove" class="btn btn-danger btn-mini"><i class="halflings-icon white trash"></i></a>
								</div>
							</td>
						</tr>
					<?php endwhile;
					endforeach;
					?>
					</tbody>
				</table><br><br><br>
				<table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1;">
					<tr>
						<td width="17%" height="30"><div align="right">LEVEL</div></td>
						<td width="43%">
							<select name="selLevel" id="selLevel" required>
								<option value="">--select--</option>
								<option value="Elementary" <?php if($selLevel=='ELEMENTARY')echo 'selected="selected"';?>>Elementary</option>
								<option value="Secondary" <?php if($selLevel=='SECONDARY')echo 'selected="selected"';?>>Secondary</option>
								<option value="Vocational" <?php if($selLevel=='VOCATIONAL')echo 'selected="selected"';?>>Vocational</option>
								<option value="College" <?php if($selLevel=='COLLEGE')echo 'selected="selected"';?>>College</option>
								<option value="Graduate" <?php if($selLevel=='GRADUATE')echo 'selected="selected"';?>>Graduate Studies</option>
							</select>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">NAME OF SCHOOL</div></td>
						<td><input type="text" name="txSchool" id="txSchool" class="span6" value="<?php echo $txSchool;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesSchool;?>]'/ required></td>
					</tr>
					<tr>
						<td height="30"><div align="right">DEGREE COURSE</div></td>
						<td><input type="text" name="txCourse" id="txCourse" class="span6" value="<?php echo $txCourse;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCourse;?>]' /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">YEAR GRADUATED <br><i>(if graduated)</i></div></td>
						<td>
							<select name="selYrGrad" id="selYrGrad" style="width:80px;" class="drpPad">
								<option value="">Year</option>
								<?php for($y=date('Y');$y>=1960;$y--):?>
								<option value="<?php echo $y;?>" <?php if($selYrGrad==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
								<?php endfor;?>
							</select>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">HIGHEST GRADE / LEVEL / UNITS EARNED <br><i>(if NOT graduated)</i></div></td>
						<td><input type="text" name="txHighestLevel" id="txHighestLevel" class="span6" value="<?php echo $txHighestLevel;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesHighestLevel;?>]' /></td>
					</tr>
					<tr>
						<td height="30"><div align="right">INCLUSIVE DATES OF ATTENDANCE (FROM)</div></td>
						<td>
							<select name="selDateFrom" id="selDateFrom" style="width:80px;">
								<option value="">Year</option>
								<?php for($y=date('Y');$y>=1960;$y--):?>
								<option value="<?php echo $y;?>" <?php if($selDateFrom==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
								<?php endfor;?>
							</select>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">INCLUSIVE DATES OF ATTENDANCE (TO)</div></td>
						<td>
							<select name="selDateTo" id="selDateTo" style="width:80px;" class="drpPad">
								<option value="">Year</option>
								<?php for($y=date('Y');$y>=1960;$y--):?>
								<option value="<?php echo $y;?>" <?php if($selDateTo==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
								<?php endfor;?>
							</select>
						</td>
					</tr>
					<tr>
						<td height="30"><div align="right">SCHOLARSHIP /<br>ACADEMIC HONORS RECEIVED</div></td>
						<td><textarea name="txHonors" id="txHonors"><?php echo $txHonors;?></textarea></td>
					</tr>
				</table>
				<div align="center">
					<input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary btn-small">
					<?php if ($educEdt): ?><a class="btn btn-small" href="?eid=<?php echo functions::encode($eid)?>&fromED=<?php echo $fromED;?>">Cancel</a><?php endif ?>
				</div>
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
<script src="../js/showPage.js"></script>
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
<script type="text/javascript">
function ask(){
	if(confirm('Do you want to save this information?'))
		return true;
	else
		return false;
}
</script>
<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>
<!-- end: JavaScript-->
</body>
</html>