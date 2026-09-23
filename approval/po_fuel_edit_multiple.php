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

$txRefID=''; $mon=''; $day=''; $year=''; $txPayee=''; $txItemCat=''; $txTerms=''; $txProjDetail=''; $txInvoice='';
$arrEquip = ( isset($_SESSION['po_arr_equip']) && is_array($_SESSION['po_arr_equip']) ) ? $_SESSION['po_arr_equip'] : array();
$arrProj = ( isset($_SESSION['po_arr_proj']) && is_array($_SESSION['po_arr_proj']) ) ? $_SESSION['po_arr_proj'] : array();
$projID=0;
$txRefID = ( isset($_SESSION['RefID']) && !empty($_SESSION['RefID']) ) ? trim($_SESSION['RefID']) : '';

$txRequestRefID = ( isset($_REQUEST['refID']) && !empty($_REQUEST['refID']) ) ? functions::decode($_REQUEST['refID']) : '';
if( $txRequestRefID ){
	if( $db->getValue('po','count(vp_id)',array('ref_id'=>$txRequestRefID)) ){
		functions::say('P.O. has already been attached to a voucher. Just detach it to Edit!');
		unset($_SESSION['RefID']);
		functions::sendTo(functions::pageName());
		die();
	}
	else if( $po_id = $db->getValue('po','po_id',array('ref_id'=>$txRequestRefID)) ){
		$_SESSION['RefID']=$txRequestRefID;
		unset($_SESSION['po_arr_equip']);
		$qEquips = $db->select('po p, po_fuel_equipment pfe','equip_id,other_equip',array('ref_id'=>$txRequestRefID),'AND p.po_id=pfe.po_id');
		while($rEquips = $db->fetch_array($qEquips)):
			if($rEquips['equip_id'])
				$arrEquip = functions::insert_array($arrEquip,$rEquips['equip_id']);
			else if($rEquips['other_equip'])
				$arrEquip = functions::insert_array($arrEquip,$rEquips['other_equip']);
			endwhile;
			$_SESSION['po_arr_equip']=$arrEquip;
	}
	functions::sendTo(functions::pageName());
	die();
}

if( isset($_POST['btnSearch']) && isset($_POST['txRefID']) && !empty($_POST['txRefID']) ){
	$txRefID = ( isset($_POST['txRefID']) && !empty($_POST['txRefID']) ) ? trim($_POST['txRefID']) : '';
	$po_id = $db->getValue('po','po_id',array('ref_id'=>$txRefID));

	if($po_id){
		$_SESSION['RefID']=$txRefID;
		if( $db->getValue('view_po_payment','sum(paid)',array('po_id'=>$po_id)) > 0 ){
			functions::say('P.O. has a payment made. Cannot Edit!');
			unset($_SESSION['RefID']);
			functions::sendTo(functions::pageName());
			die();
		}
		else if( $db->getValue('po','count(vp_id)',array('ref_id'=>$txRefID)) ){
			functions::say('P.O. has already been attached to a voucher. Cannot Edit!');
			unset($_SESSION['RefID']);
			functions::sendTo(functions::pageName());
			die();
		}
		unset($_SESSION['po_arr_equip']);
		$qEquips = $db->select('po p, po_fuel_equipment pfe','equip_id,other_equip',array('ref_id'=>$txRefID),'AND p.po_id=pfe.po_id');
		while($rEquips = $db->fetch_array($qEquips)):
			if($rEquips['equip_id'])
				$arrEquip = functions::insert_array($arrEquip,$rEquips['equip_id']);
			else if($rEquips['other_equip'])
				$arrEquip = functions::insert_array($arrEquip,$rEquips['other_equip']);
		endwhile;
		$_SESSION['po_arr_equip']=$arrEquip;
	}
	else
		functions::say('P.O. not Exist!');
	functions::sendTo(functions::pageName());
	die();
}

$txBdate = $db->getValue('po','po_date',array('ref_id'=>$txRefID));

$txInvoice = $db->getValue('po','invoice',array('ref_id'=>$txRefID),'ORDER BY invoice DESC');
$txPayee = $db->getValue('po','supplierID',array('ref_id'=>$txRefID));
$txItemCat = $db->getValue('po','category_id',array('ref_id'=>$txRefID));
$txProjDetail = $db->getValue('po','proj_detail',array('ref_id'=>$txRefID));
$txPreparedBy = $db->getValue('po','purchaser',array('ref_id'=>$txRefID));
$txApprove = $db->getValue('po','approved_by',array('ref_id'=>$txRefID));
$txSupplier = $db->getValue('po','supplierID',array('ref_id'=>$txRefID));
$txReceive = $db->getValue('po','received',array('ref_id'=>$txRefID));
$po_id = $db->getValue('po','po_id',array('ref_id'=>$txRefID));
$requested_by = $db->getValue('po_fuel','requested_by',array('po_id'=>$po_id));
$equip_id = $db->getValue('po_fuel','equip_id',array('po_id'=>$po_id));
$other_equip = $db->getValue('po_fuel','other_equip',array('po_id'=>$po_id));
$dep_id = $db->getValue('po_fuel','dep_id',array('po_id'=>$po_id));
$txRemarks = $db->getValue('po','remarks',array('ref_id'=>$txRefID));
$txPaymentTerm = $db->getValue('po','payment_term',array('ref_id'=>$txRefID));
$txConforme = $db->getValue('po','conforme',array('ref_id'=>$txRefID));

if(count($arrProj)==0){
	$qProjs = $db->select('po p, po_fuel pf','proj_id,pf.payee as pay',array('ref_id'=>$txRefID),'AND p.po_id=pf.po_id');
	while($rProjs = $db->fetch_array($qProjs)):
		if($rProjs['proj_id'])
			$arrProj = functions::insert_array($arrProj,$rProjs['proj_id']);
		else if($rProjs['pay'])
			$arrProj = functions::insert_array($arrProj,$rProjs['pay']);
	endwhile;
}

if( isset($_REQUEST['dp']) && !empty($_REQUEST['dp']) ){
	$arrProj = functions::delete_array($arrProj,functions::decode($_REQUEST['dp']));
	$_SESSION['po_arr_proj'] = $arrProj;
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnAddProj']) && !empty($_POST['btnAddProj']) ){
	if( isset($_POST['selProj']) && !empty($_POST['selProj']) ){
		$arrProj = functions::insert_array($arrProj,functions::decode($_POST['selProj']));
		$_SESSION['po_arr_proj'] = $arrProj;
	}
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnAddOtherProj']) && !empty($_POST['btnAddOtherProj']) ){
	if( isset($_POST['txPayee']) && !empty($_POST['txPayee']) ){
		$arrProj = functions::insert_array($arrProj,$_POST['txPayee']);
		$_SESSION['po_arr_proj'] = $arrProj;
	}
	functions::sendTo(functions::pageName());
	die();
}

#array Equipment
if( isset($_REQUEST['dpe']) && !empty($_REQUEST['dpe']) ){
	$arrEquip = functions::delete_array($arrEquip,functions::decode($_REQUEST['dpe']));
	$_SESSION['po_arr_equip'] = $arrEquip;
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnAddEquip']) && !empty($_POST['btnAddEquip']) ){
	if( isset($_POST['selEquip']) && !empty($_POST['selEquip']) ){
		$arrEquip = functions::insert_array($arrEquip,functions::decode($_POST['selEquip']));
		$_SESSION['po_arr_equip'] = $arrEquip;
	}
	functions::sendTo(functions::pageName());
	die();
}
if( isset($_POST['btnAddOtherEquip']) && !empty($_POST['btnAddOtherEquip']) ){
	if( isset($_POST['txEquip']) && !empty($_POST['txEquip']) ){
		$arrEquip = functions::insert_array($arrEquip,$_POST['txEquip']);
		$_SESSION['po_arr_equip'] = $arrEquip;
	}
	functions::sendTo(functions::pageName());
	die();
}

$qPayee = $db->query('SELECT DISTINCT payee FROM po_fuel WHERE payee <> "" ');
$namesPayee='';
while($rPayee=$db->fetch_array($qPayee)):
	$string = preg_replace("/'/",'"',$rPayee['payee']);
	$namesPayee .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesPayee .= '"--"';

$qOtherEquip = $db->query('SELECT DISTINCT other_equip FROM po_fuel WHERE other_equip <> "" ');
$namesOtherEquip='';
while($rOtherEquip=$db->fetch_array($qOtherEquip)):
	$string = preg_replace("/'/",'"',$rOtherEquip['other_equip']);
	$namesOtherEquip .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesOtherEquip .= '"--"';

$qItem = $db->select('po','DISTINCT payment_term',array());
$namesPaymentTerm='';
while($rItem=$db->fetch_array($qItem)):
	$string = preg_replace("/'/",'"',$rItem['payment_term']);
	$namesPaymentTerm .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesPaymentTerm .= '"--"';
$qConfrme = $db->select('po','DISTINCT conforme',array());
$namesConfrme='';
while($rConfrme=$db->fetch_array($qConfrme)):
	$string = preg_replace("/'/",'"',$rConfrme['conforme']);
	$namesConfrme .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesConfrme .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- start: Meta -->
	<meta charset="utf-8">
	<title>Multiple P.O. Edit</title>
	<!-- end: Meta -->
	<!-- start: Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- end: Mobile Specific -->
	<!-- start: CSS -->
	<link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
	<link id="base-style" href="../css/style.css" rel="stylesheet">
	<link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
	<script src="../js/formatCurrency.js"></script>
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
<?php
if( isset($_POST['btnSave']) && $txRefID ){
	$insertID=0;
	$arrInsert = array();

	$proj=0;
	$chkProj = $arrProj;
	$numOfProj=0;
	if(is_array($chkProj)){
		$proj=1;
		$numOfProj = count($arrProj);
	}

	$eqp=0;
	$chkEqp = $arrEquip;
	$numOfEquip=0;
	if(is_array($arrEquip)){
		$eqp=1;
		$numOfEquip = count($arrEquip);
	}

	$supplierID = ( isset($_POST['txSupplier']) && !empty($_POST['txSupplier']) ) ? $_POST['txSupplier'] : '';
	$txRefID = ( isset($_POST['txRefID']) && !empty($_POST['txRefID']) ) ? $_POST['txRefID'] : '';
	$txInvoice = ( isset($_POST['txInvoice']) && !empty($_POST['txInvoice']) ) ? $_POST['txInvoice'] : '';
	$txBdate = ( isset($_POST['txPODate']) && functions::valid_date($_POST['txPODate']) ) ? $_POST['txPODate'] : NULL;
	$txPreparedBy = ( isset($_POST['txPreparedBy']) && !empty($_POST['txPreparedBy']) ) ? $_POST['txPreparedBy'] : '';
	$txApprove = ( isset($_POST['txApprove']) && !empty($_POST['txApprove']) ) ? $_POST['txApprove'] : '';
	$txProjDetail = ( isset($_POST['txProjDetail']) && !empty($_POST['txProjDetail']) ) ? $_POST['txProjDetail'] : '';
	$txItemCat = ( isset($_POST['txItemCat']) && !empty($_POST['txItemCat']) ) ? $_POST['txItemCat'] : 0;
	$txReceive = ( isset($_POST['txReceive']) && !empty($_POST['txReceive']) ) ? $_POST['txReceive'] : 0;
	$requested_by = ( isset($_POST['txRequestBy']) && !empty($_POST['txRequestBy']) ) ? $_POST['txRequestBy'] : NULL;

	$remarks = ( isset($_POST['txRemarks']) && !empty($_POST['txRemarks']) ) ? trim($_POST['txRemarks']) : '';
	$txPaymentTerm = ( isset($_POST['txPaymentTerm']) && !empty($_POST['txPaymentTerm']) ) ? trim($_POST['txPaymentTerm']) : '';
	$dep_id = ( isset($_POST['selDep']) && !empty($_POST['selDep']) ) ? trim($_POST['selDep']) : NULL;
	$txConforme = ( isset($_POST['txConforme']) && !empty($_POST['txConforme']) ) ? trim($_POST['txConforme']) : "";

	if( $numOfProj && $numOfEquip && $supplierID && $requested_by && $txBdate && $txPreparedBy && $txRefID && $txItemCat && $txApprove){

		$allowed=1;
		if($txInvoice){
			$oldInvoice = $db->getValue('po','invoice',array('ref_id'=>$txRefID),'ORDER BY invoice DESC');
			$newInvoice = $txInvoice;
			if($oldInvoice != $newInvoice){
				if( $db->getValue('po','count(*)',array('invoice'=>$newInvoice),'AND ref_id <> "'.$db->clean($txRefID).'"') ){//Check if invoice exist other than this po.
					#$allowed=0;
					functions::say("Warning: Invoice already used!");
				}
			}
		}

		if($allowed==1){
			#check for project, determining which project to remove
			$delProj=array();
			$qProjs = $db->query('SELECT p.po_id as ppo_id ,proj_id,pf.payee as pay FROM po p, po_fuel pf WHERE p.po_id=pf.po_id AND ref_id="'.$db->clean($txRefID).'"');
			while($rProjs = $db->fetch_array($qProjs)):
				$onDBProj = ($rProjs['proj_id']) ? ($rProjs['proj_id']) : $rProjs['pay'];
				if($onDBProj){
					if( functions::search_array($onDBProj,$arrProj) == false)
						$delProj[] = $rProjs['ppo_id'];
				}
			endwhile;

			if( count($delProj) ){
				foreach($delProj as $dp):
					$db->delete('po',array('po_id'=>$dp));
					$db->delete('po_fuel',array('po_id'=>$dp));
				endforeach;
			}
			#insert new project
			foreach($arrProj as $ap):
				if( $db->getValue('project','count(*)',array('proj_id'=>$ap)) ){ //if project is registered.
					if( $db->getValue('po','count(*)',array('proj_id'=>$ap,'ref_id'=>$txRefID))==0 && !empty($ap) ){
						$po_id = $db->insert('po',array('proj_id'=>$ap,'supplierID'=>$supplierID,'dep_id'=>$dep_id,'category_id'=>$txItemCat,'po_date'=>$txBdate,'purchaser'=>$txPreparedBy,'invoice'=>$txInvoice,'approved_by'=>$txApprove,'proj_detail'=>$txProjDetail,'ref_id'=>$txRefID,'po_type'=>'fuel','remarks'=>$remarks,'delivery_date'=>$delivery_date,'payment_term'=>$txPaymentTerm,'conforme'=>$txConforme));
						$db->insert('po_fuel',array('requested_by'=>$requested_by,'po_id'=>$po_id));
					}
				}
				else{ #if project is not registered or it is outsider.
					if( $db->getValue('po p, po_fuel pf','count(*)',array('payee'=>$ap,'ref_id'=>$txRefID),'AND p.po_id=pf.po_id')== 0 && !empty($ap) ){
						$po_id = $db->insert('po',array('supplierID'=>$supplierID,'dep_id'=>$dep_id,'category_id'=>$txItemCat,'po_date'=>$txBdate,'purchaser'=>$txPreparedBy,'invoice'=>$txInvoice,'approved_by'=>$txApprove,'proj_detail'=>$txProjDetail,'ref_id'=>$txRefID,'po_type'=>'fuel','conforme'=>$txConforme));
						$db->insert('po_fuel',array('requested_by'=>$requested_by,'po_id'=>$po_id,'payee'=>$txPayee));
					}
				}
			endforeach;

			#updating the po_no
			$count=1;
			$qpono = $db->select('po','*',array('ref_id'=>$txRefID),'AND po_no=""');
			while($rpono = $db->fetch_array($qpono)):
				$temp_pono = $txRefID.'-'.$count;
				while( $db->getValue('po','count(*)',array('ref_id'=>$txRefID,'po_no'=>$temp_pono)) > 0 ){
					$count++;
					$temp_pono = $txRefID.'-'.$count;
				}
				$db->update('po',array('po_no'=>$temp_pono),array('po_id'=>$rpono['po_id'],'ref_id'=>$txRefID));
			endwhile;

			$qUpdateEachPO = $db->select('po','*',array('ref_id'=>$txRefID));
			while($rUEPO = $db->fetch_array($qUpdateEachPO)):
				$db->update('po',array('supplierID'=>$supplierID,'dep_id'=>$dep_id,'category_id'=>$txItemCat,'po_date'=>$txBdate,'purchaser'=>$txPreparedBy,'invoice'=>$txInvoice,'approved_by'=>$txApprove,'proj_detail'=>$txProjDetail,'conforme'=>$txConforme),array('po_id'=>$rUEPO['po_id']));
				$db->update('po_fuel',array('requested_by'=>$requested_by),array('po_id'=>$rUEPO['po_id']));
				require_once('../class/class-po-history.php');
				PO_history::poModify($rUEPO['po_id'],$user_id);
			endwhile;

			$_SESSION['notif_success']='Changes Saved!';
			functions::sendTo('po_fuel_list_multiple.php?ref_id='.functions::encode($txRefID));
			die();
		}
	}
	else
		functions::say("Please fill up the form properly!");
}
?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white edit"></i><span class="break"></span>Purchase Order Form</h2>
		</div>
		<div class="box-content">
			<form class="form-horizontal" method="post" id="poForm" name="poForm">
				<table width="60%" align="center" border="0" style="background-color:#E4E1E1">
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">P.O. Number</label>
								<div class="controls">
									<input type="text" name="txRefID" id="txRefID" style="width:300px;" value="<?php echo $txRefID;?>" />
									<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-small btn-primary">
									<span class="help-inline warning" id="msgInvoice" style="font-weight:bold;" name="msgInvoice"></span>
								</div>
							</div>
						</td>
					</tr>
<?php if($txRefID){?>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Project</label>
								<div class="controls">
									<table border="0" width="99%">
										<tr>
											<td>
												<table border="0">
													<tr>
														<td>
															<select name="selProj" id="selProj" data-rel="chosen" style="width:550px;">
																<option value="">--select--</option>
																<?php $qProj = $db->select('project','*',array(),'ORDER BY proj_name');
																while($rProj = $db->fetch_array($qProj)):
																?>
																<option value="<?php echo functions::encode($rProj['proj_id'])?>"><?php echo ucwords(strtolower($rProj['proj_name']));?></option>
																<?php endwhile;?>
															</select>
														</td>
														<td><input type="submit" name="btnAddProj" id="btnAddProj" value="Add" class="btn btn-small btn-primary"></td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td>
												<table border="0">
													<tr>
														<td style="padding: 10px 5px 4px 4px"><input type="text" name="txPayee" id="txPayee" style="width:525px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPayee;?>]'/></td>
														<td><input type="submit" name="btnAddOtherProj" id="btnAddOtherProj" value="Add" class="btn btn-small btn-primary"></td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td>
												<table border="1" width="100%">
													<?php
													foreach($arrProj as $projID):
													$projName = $db->getValue('project','proj_name',array('proj_id'=>$projID));
													?>
													<tr>
														<td style="padding: 5px 0px 5px 3px"><?php echo ($projName) ? $projName : $projID;?></td>
														<td><div align="center"><a id="del<?php echo $projID;?>" class="btn btn-mini btn-danger" title="Remove this Project" data-rel="tooltip" href="<?php echo functions::pageName()?>?dp=<?php echo functions::encode($projID);?>"><i class="halflings-icon white trash"></i></a></div></td>
													</tr>
													<?php endforeach;?>
												</table>
											</td>
										</tr>
									</table>
									<span class="help-inline warning" id="msgProject" style="font-weight:bold;" name="msgProject"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Equipment</label>
								<div class="controls">
									<table border="0" width="99%">
										<tr>
											<td>
												<table border="0">
													<tr>
														<td>
															<select name="selEquip" id="selEquip" data-rel="chosen" style="width:550px;">
																<option value="">-- Select Equipment --</option>
																<?php $qEquip = $db->select('equipment','*',array(),'ORDER BY type');
																while($rEquip = $db->fetch_array($qEquip)):
																?>
																<option value="<?php echo functions::encode($rEquip['equip_id'])?>"><?php echo strtolower($rEquip['inventory_id']).' '.ucwords(strtolower($rEquip['equip_desc'])).' '.$rEquip['plate_no'].' '.$rEquip['serial_no'];?></option>
																<?php endwhile;?>
															</select>
														</td>
														<td><input type="submit" name="btnAddEquip" id="btnAddEquip" value="Add" class="btn btn-small btn-primary"></td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td>
												<table border="0">
													<tr>
														<td style="padding: 10px 5px 4px 4px"><input type="text" name="txEquip" id="txEquip" style="width:500px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesOtherEquip;?>]'/></td>
														<td><input type="submit" name="btnAddOtherEquip" id="btnAddOtherEquip" value="Add" class="btn btn-small btn-primary"></td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td>
												<table border="1" width="100%">
													<?php 
													$countEqp=0;
													foreach($arrEquip as $equiID):
													$equipName = $db->getValue('equipment','name',array('equip_id'=>$equiID));
													?>
													<tr>
														<td style="padding: 5px 0px 5px 3px"><?php echo ($equipName) ? $equipName : $equiID;?></td>
														<td><div align="center"><a id="del<?php echo $countEqp++;?>" class="btn btn-mini btn-danger" title="Remove this Equipment" data-rel="tooltip" href="<?php echo functions::pageName()?>?dpe=<?php echo functions::encode($equiID);?>"><i class="halflings-icon white trash"></i></a></div></td>
													</tr>
													<?php endforeach;?>
												</table>
											</td>
										</tr>
									</table>
									<span class="help-inline warning" id="msgEquip" style="font-weight:bold;" name="msgEquip"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">P.O Date</label>
								<div class="controls">
									<input type="text" style="width: 90px;" placeholder="YYYY-MM-DD" name="txPODate" id="txPODate" value="<?php echo $txBdate ?>">
									<span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Payee / Supplier</label>
								<div class="controls">
									<select name="txSupplier" id="txSupplier" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
										while($rSup = $db->fetch_array($qSup)):
										?>
										<option value="<?php echo $rSup['supplierID']?>" <?php if($rSup['supplierID']==$txSupplier)echo 'selected="selected"';?>><?php echo ($rSup['name']);?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgPayee" style="font-weight:bold;" name="msgPayee"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Department</label>
								<div class="controls">
									<select name="selDep" id="selDep" data-rel="chosen" style="width:500px;">
										<option value="">--select--</option>
										<?php $qDep = $db->select('department','*',array(),'ORDER BY dep_desc');
										while($rDep = $db->fetch_array($qDep)):
										?>
										<option value="<?php echo $rDep['dep_id']?>" <?php if($dep_id==$rDep['dep_id'])echo 'selected="selected"';?>><?php echo $rDep['dep_desc'].' ('.$rDep['dep_name'].')';?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Category</label>
								<div class="controls">
									<select name="txItemCat" id="txItemCat" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php 
										$qCat = $db->select('item_deduction','*',array(),'ORDER BY name');
										while($rCat = $db->fetch_array($qCat)):
										?>
										<option value="<?php echo $rCat['item_id']?>" <?php if($txItemCat==$rCat['item_id'])echo 'selected="selected"';?>><?php echo $rCat['name']?></option>
										<?php endwhile;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Purpose</label>
								<div class="controls">
									<input type="text" name="txProjDetail" id="txProjDetail" style="width:300px;" value="<?php echo $txProjDetail;?>" />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Invoice #</label>
								<div class="controls">
									<input type="text" name="txInvoice" id="txInvoice" style="width:300px;" value="<?php echo $txInvoice?>" />
									<span class="help-inline warning" id="msgInvoice" style="font-weight:bold;" name="msgInvoice"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Requested By</label>
								<div class="controls">
									<select name="txRequestBy" id="txRequestBy" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
										while($rEU = $db->fetch_array($qEU)):
										?>
										<option value="<?php echo $rEU['emp_id']?>"<?php if($requested_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgRequest" style="font-weight:bold;" name="msgRequest"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Served By</label>
								<div class="controls">
									<select name="txPreparedBy" id="txPreparedBy" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php $qPrep = $db->select('users','*',array('status'=>'active'),'ORDER BY lname');
										while($rPrep = $db->fetch_array($qPrep)):
										?>
										<option value="<?php echo $rPrep['user_id']?>" <?php if($txPreparedBy==$rPrep['user_id'])echo 'selected="selected"';?>><?php echo strtoupper($rPrep['lname'].', '.$rPrep['fname']);?></option>
										<?php endwhile;?>
									</select>
									<span class="help-inline warning" id="msgPrepare" style="font-weight:bold;" name="msgPrepare"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Approval</label>
								<div class="controls">
									<select name="txApprove" id="txApprove" data-rel="chosen" style="width:400px;">
										<option value="">--select--</option>
										<?php $qApprove = $db->select('users','*',array('status'=>'active'),'ORDER BY lname');
										while($rApprove = $db->fetch_array($qApprove)):
										?>
										<option value="<?php echo $rApprove['user_id']?>" <?php if($txApprove==$rApprove['user_id'])echo 'selected="selected"';?>><?php echo strtoupper($rApprove['lname'].', '.$rApprove['fname']);?></option>
										<?php endwhile;?>
										</select>
									<span class="help-inline warning" id="msgApprove" style="font-weight:bold;" name="msgApprove"></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Conforme</label>
								<div class="controls">
									<input type="text" name="txConforme" id="txConforme" style="width:300px;" value="<?php echo $txConforme?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesConfrme;?>]' />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group">
								<label class="control-label" for="inputSuccess">Terms of Payment</label>
								<div class="controls">
									<input type="text" name="txPaymentTerm" id="txPaymentTerm" style="width:300px;" value="<?php echo $txPaymentTerm;?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPaymentTerm;?>]' />
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="control-group hidden-phone">
								<label class="control-label" for="textarea2">Remarks</label>
								<div class="controls">
									<textarea id="txRemarks" name="txRemarks" rows="2"><?php echo $txRemarks?></textarea>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="controls">
								<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-small btn-primary">
								<button class="btn btn-small">Cancel</button>
							</div>
						</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
<?php }#if($txRefID)?>
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
<script>
$(document).ready(function(){
    var res = false;

	<?php if($txReceive==1){?>
	$('#bdMonDelvry').show();
	$('#bdDayDelvry').show();
	$('#bdYearDelvry').show();
	<?php }else{?>
	$('#bdMonDelvry').hide();
	$('#bdDayDelvry').hide();
	$('#bdYearDelvry').hide();
	<?php }?>
	$('#txReceive').change(function(){
		if( $('#txReceive').val()=="1" ){
			$('#bdMonDelvry').show();
			$('#bdDayDelvry').show();
			$('#bdYearDelvry').show();
		}
		else{
			$('#bdMonDelvry').hide();
			$('#bdDayDelvry').hide();
			$('#bdYearDelvry').hide();
		}
	});

	$('#btnAddProj').click(function(){
		if( $('#selProj').val()=="" ){
			$('#msgProject').html("Please select Project!");
			res=false;
		}
		else
		res=true;
		return res;
	});

	$('#btnSave').click(function(){
		$('#msgBdate').html("");
		$('#msgPayee').html("");
		$('#msgProject').html("");
		$('#msgPrepare').html("");
		$('#msgApprove').html("");
		$('#msgEquip').html("");
		$('#msgRequest').html("");
		$('#msgCat').html("");
		var prj = "<?php echo count($arrProj)?>";
		var eqp = "<?php echo count($arrEquip)?>";

		if( prj==0 ){
			$('#msgProject').html("Project Required!");
			res=false;
		}  
		else if( eqp==0 ){
			$('#msgEquip').html("Equipment Required!");
			res=false;
		}
		else if( $('#txPODate').val()=="" ){
			$('#msgBdate').html("Date Required!");
			res=false;
		}
		else if( $('#txSupplier').val()=="" ){
			$('#txSupplier').focus();
			$('#msgPayee').html("Supplier Required!");
			res=false;
		}
		else if( $('#txItemCat').val()=="" ){
			$('#txItemCat').focus();
			$('#msgCat').html("Category Required!");
			res=false;
		}
		else if( $('#txRequestBy').val()=="" ){
			$('#txRequestBy').focus();
			$('#msgRequest').html("Requested By Required!");
			res=false;
		}
		else if( $('#txPreparedBy').val()=="" ){
			$('#msgPrepare').html("Purchase Required!");
			$('#txPreparedBy').focus();
			res=false;
		}
		else if( $('#txApprove').val()=="" ){
			$('#msgApprove').html("Approval Required!");
			$('#txApprove').focus();
			res=false;
		}
		else{
			if(confirm('Do you want to submit this information?'))
				res=true;
			else
				res=false;
		}
		return res;
	});
	$('#txPODate').datepicker({
		numberOfMonths:1,
		dateFormat:'yy-mm-dd',
		changeYear: true,
		changeMonth: true,
		showButtonPanel: true,
		showOtherMonths: true,
		navigationAsDateFormat: true,
		hideIfNoPrevNext: true,
		yearRange:'2010:<?php echo date('Y')+10 ?>'
	});
});
</script>
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>
<!-- end: JavaScript-->
</body>
</html>