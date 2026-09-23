<?php require_once('templ_up.php');?>
<?php
function monthDays($month=0,$year=0){
	$list = array();
	$month = ($month) ? $month : date('m');
	$year = ($year) ? $year : date('Y');
	for($d=1; $d<=31; $d++):
		$time = mktime(12,0,0,$month,$d,$year);
		if( date('m',$time)==$month )
			$list[]=date('Y-m-d',$time);
	endfor;
	return $list;
}
$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');
$mn = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';
$actFrom='';
$actTo='';
if( isset($_POST['btnSearch']) ){
	$year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? functions::decode($_POST['bdYear']) : date('Y');
	$selMonth = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
	$_SESSION['as_acTypeFrom'] = ( isset($_POST['txVoTypeFrom']) && !empty($_POST['txVoTypeFrom']) ) ? $_POST['txVoTypeFrom'] : '';
	$_SESSION['as_acTypeTo'] = ( isset($_POST['txVoTypeTo']) && !empty($_POST['txVoTypeTo']) ) ? $_POST['txVoTypeTo'] : '';
	$_SESSION['as_selMonthft']=$selMonth;
	$_SESSION['as_selYrft']=$year;
	functions::sendTo(functions::pageName());
	die();
}
$selMonth = ( isset($_SESSION['as_selMonthft']) && !empty($_SESSION['as_selMonthft']) ) ? $_SESSION['as_selMonthft'] : $mn;
$year = ( isset($_SESSION['as_selYrft']) && !empty($_SESSION['as_selYrft']) ) ? $_SESSION['as_selYrft'] : $yr;
$account_typeFrom = ( isset($_SESSION['as_acTypeFrom']) && !empty($_SESSION['as_acTypeFrom']) ) ? $_SESSION['as_acTypeFrom'] : '';
$account_typeTo = ( isset($_SESSION['as_acTypeTo']) && !empty($_SESSION['as_acTypeTo']) ) ? $_SESSION['as_acTypeTo'] : '';
if($account_typeFrom)
	$actFrom = ' AND vt_id="'.$db->clean($account_typeFrom).'"';
if($account_typeTo)
	$actTo = ' AND acs.account_type="'.$db->clean($account_typeTo).'"';
?>
<!-- body content: start here-->
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Fund Transfer Statement</h2>
		</div>
		<form method="post">
			<div align="center"><br>&nbsp;&nbsp;
				<select name="bdYear" id="bdYear">
					<option value="">--Select Year--</option>
					<?php for($y=(date('Y')+1);$y>=2015;$y--):?>
					<option value="<?php echo functions::encode($y);?>" <?php if($year==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
					<?php endfor;?>
				</select>
				<select name="bdMon" id="bdMon" style="width:95px;">
					<option value="">All Month</option>
					<option value="01" <?php if($selMonth=='01')echo 'selected="selected"';?>>Jan</option>
					<option value="02" <?php if($selMonth=='02')echo 'selected="selected"';?>>Feb</option>
					<option value="03" <?php if($selMonth=='03')echo 'selected="selected"';?>>Mar</option>
					<option value="04" <?php if($selMonth=='04')echo 'selected="selected"';?>>Apr</option>
					<option value="05" <?php if($selMonth=='05')echo 'selected="selected"';?>>May</option>
					<option value="06" <?php if($selMonth=='06')echo 'selected="selected"';?>>Jun</option>
					<option value="07" <?php if($selMonth=='07')echo 'selected="selected"';?>>Jul</option>
					<option value="08" <?php if($selMonth=='08')echo 'selected="selected"';?>>Aug</option>
					<option value="09" <?php if($selMonth=='09')echo 'selected="selected"';?>>Sep</option>
					<option value="10" <?php if($selMonth=='10')echo 'selected="selected"';?>>Oct</option>
					<option value="11" <?php if($selMonth=='11')echo 'selected="selected"';?>>Nov</option>
					<option value="12" <?php if($selMonth=='12')echo 'selected="selected"';?>>Dec</option>
				</select>
				<select name="txVoTypeFrom" id="txVoTypeFrom">
					<option value="">--Source Account--</option>
					<?php 
					$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
					while($rVtype = $db->fetch_array($qVtype)):
					?>
					<option value="<?php echo $rVtype['vt_id']?>" <?php if($account_typeFrom==$rVtype['vt_id'])echo 'selected="selected"';?> ><?php echo $rVtype['vt_name']?></option>
					<?php endwhile;?>
				</select>
				<select name="txVoTypeTo" id="txVoTypeTo">
					<option value="">--Destination Account--</option>
					<?php 
					$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
					while($rVtype = $db->fetch_array($qVtype)):
					?>
					<option value="<?php echo $rVtype['vt_id']?>" <?php if($account_typeTo==$rVtype['vt_id'])echo 'selected="selected"';?> ><?php echo $rVtype['vt_name']?></option>
					<?php endwhile;?>
				</select>
				<input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-small btn-primary">
			</div>
		</form>
		<div class="box-content">
			<table class="table table-bordered table-hover table-striped" style="font-size:12px;">
				<thead>
					<tr style="background-color:#CCC;">
						<th width="10%">Date</th>
						<th width="12%">Check Number</th>
						<th width="30%">Source Account</th>
						<th width="30%">DESTINATION</th>
						<th width="20%"><div align="center">AMOUNT TRANSFERRED</div></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$balance=0;$totalDebit=0;$monthlyDebit=0;
				for($m = 1; $m <= 12; $m++):
					$monthlyCredit=0;$monthlyDebit=0;
					$time = mktime(0,0,0,$m,1,$year);
					$monthName = date('F',$time);
					$days = monthDays($m,$year);

					if($selMonth==$m || $selMonth == ''){
					echo '
					<tr>
						<td colspan="5"><div align="left"><strong>Month Of '.$monthName.'</strong></div></td>
					</tr>';
					}
					foreach($days as $day):
						$q = $db->query("SELECT vd.vd_date,v.cheque_id, v.cheque_date, v.vt_id,vp.vtype,vd.item,vd.amount,vd.vd_id,acs.account_type FROM voucher v, voucher_particular vp, voucher_detail vd, account_statement acs WHERE v.voucher_id=vp.voucher_id AND vd.vp_id=vp.vp_id AND vp.vtype='transfer' AND acs.tag_id=vd.vd_id AND vd.vd_date='".$day."'".$actFrom.$actTo);
						while($r = $db->fetch_array($q)):
							$amountDebit=0;
							$amountDebit += $r['amount'];
							$monthlyDebit += $amountDebit;
							$totalDebit += $amountDebit;
							$balance += $amountDebit;
							$balance = ($balance) ? round($balance,2) : $balance;
							if($selMonth==$m || $selMonth == ''){
								$balanceDisplay = $balance;
								echo '
								<tr>
									<td>'.functions::datearr($day).'</td>
									<td>'.$r['cheque_id'].'</td>
									<td>'.$db->getValue('voucher_type','vt_name',array('vt_id'=>$r['vt_id'])).'</td>
									<td>'.$db->getValue('voucher_type','vt_name',array('vt_id'=>$r['account_type'])).'</td>
									<td><div align="right">'.functions::formatMoney($amountDebit).'</div></td>
								</tr>';
							}
						endwhile;
					endforeach; #end for each day
					if($selMonth==$m || $selMonth == ''){
						$balanceDisplay = $balance;
						echo '
						<tr>
							<td>&nbsp;</td>
							<td colspan="3"><div align="right"><strong> End of '.$monthName.' '.$year.'</strong></div></td>
							<td><div align="right"><strong>'.functions::formatMoney($monthlyDebit).'</strong></div></td>
						</tr>';
						echo '
						<tr>
							<td>&nbsp;</td>
							<td colspan="3"><div align="right"><strong>Cumulative report until '.$monthName.' '.$year.'</strong></div></td>
							<td><div align="right"><strong>'.functions::formatMoney($totalDebit).'</strong></div></td>
						</tr>';
					}
				endfor; #end for each month
				$balanceDisplay = $balance;
				?>
					<tr>
						<td colspan="5"><hr width="100%" style="color:#FF0000"></td>
					</tr>
					<tr>
						<td colspan="4"><div align="right"><strong>End of Year <?php echo $year?></strong></div></td>
						<td><div align="right"><strong><?php echo functions::formatMoney($totalDebit);?></strong></div></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>