<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;

$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$arrVal=array();
#$arrVal=array('project'=>'1');
if($p_id){
	#$arrVal = array('proj_id'=>$p_id,'project'=>'1');
	$arrVal = array('proj_id'=>$p_id);
}

if( isset($_REQUEST['pidDel']) && !empty($_REQUEST['pidDel']) ){
	$projDelID = functions::decode($_REQUEST['pidDel']);
	$db->delete('po',array('proj_id'=>$projDelID));
	$db->delete('voucher_detail',array('proj_id'=>$projDelID));
	$db->delete('project',array('proj_id'=>$projDelID));
}
$bgColorGreen='#2ecc71';
$bgColorYellow='#d4d400';
$bgColorOrange='#f5ae00';
$bgColorRed='#E94B4B';
?>
<!-- body content: start here-->
<form method="post">
	<div style="display: flex; justify-content: flex-end; margin-bottom: 15px; gap: 20px; flex-wrap: wrap;">
		<div style="background: #fff; border: 1px solid #ddd; padding: 8px 12px; border-radius: 6px; display: flex; gap: 15px; align-items: center; font-size: 11px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
			<span style="font-weight: bold; color: #444;"><i class="halflings-icon tag" style="opacity: 0.6;"></i> Cost Legend:</span>
			<div style="display: flex; align-items: center; gap: 5px;"><div style="background-color:<?php echo $bgColorGreen?>; width:14px; height:14px; border-radius:3px;"></div> &lt; 20%</div>
			<div style="display: flex; align-items: center; gap: 5px;"><div style="background-color:<?php echo $bgColorYellow?>; width:14px; height:14px; border-radius:3px;"></div> 20% - 30%</div>
			<div style="display: flex; align-items: center; gap: 5px;"><div style="background-color:<?php echo $bgColorOrange?>; width:14px; height:14px; border-radius:3px;"></div> 31% - 40%</div>
			<div style="display: flex; align-items: center; gap: 5px;"><div style="background-color:<?php echo $bgColorRed?>; width:14px; height:14px; border-radius:3px;"></div> &gt;= 41%</div>
		</div>
		<div style="background: #fff; border: 1px solid #ddd; padding: 8px 12px; border-radius: 6px; display: flex; gap: 15px; align-items: center; font-size: 11px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
			<span style="font-weight: bold; color: #444;"><i class="halflings-icon tag" style="opacity: 0.6;"></i> Time Legend:</span>
			<div style="display: flex; align-items: center; gap: 5px;"><div style="background-color:<?php echo $bgColorGreen?>; width:14px; height:14px; border-radius:3px;"></div> 0% - 25%</div>
			<div style="display: flex; align-items: center; gap: 5px;"><div style="background-color:<?php echo $bgColorYellow?>; width:14px; height:14px; border-radius:3px;"></div> 26% - 50%</div>
			<div style="display: flex; align-items: center; gap: 5px;"><div style="background-color:<?php echo $bgColorOrange?>; width:14px; height:14px; border-radius:3px;"></div> 51% - 75%</div>
			<div style="display: flex; align-items: center; gap: 5px;"><div style="background-color:<?php echo $bgColorRed?>; width:14px; height:14px; border-radius:3px;"></div> 76% - 100%</div>
		</div>
	</div>

	<div class="row-fluid">
		<div class="box span12">
			<div class="box-header" data-original-title style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
				<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Project List</h2>
			</div>
			
			<div style="padding: 15px 0;">
				<select name="selProj" id="selProj" data-rel="chosen" style="width:100%;font-size:12px;" onChange="projSel(this.value)">
					<option value="">--All Projects--</option>
					<?php 
					$qProj = $db->select('project','*',array(),'ORDER BY proj_name');
					while($rProj = $db->fetch_array($qProj)):?>
					<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($p_id==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
					<?php endwhile;?>
				</select>
			</div>

			<div class="box-content" style="padding-top: 5px;">
				<!-- Full-Width Card List Container -->
				<div style="display: flex; flex-direction: column; gap: 12px; width: 100%; box-sizing: border-box;" id="tblist">
					<?php
					if(!is_numeric($startrow))
						$startrow=0;
					$qProj = $db->select('project','*',$arrVal,'ORDER BY date_start DESC LIMIT '.$startrow.', '.$rowdisplay);
					$num_record = $db->getValue('project','count(*)',$arrVal);
					
					if($num_record > 0):
						while($rProj = $db->fetch_array($qProj)):
							$amount=0;
							$po_amount=0;
							$dateStart = $rProj['date_start'];
							$dateCompletion = $rProj['date_completion'];
							$dateReviseCompletion = $rProj['date_revise_completion'];
							$dateCompleted = $rProj['date_completed'];
							$projectStat = isset($rProj['status']) ? strtolower(trim($rProj['status'])) : '';
							$non_po=$db->getValue('voucher_detail','sum(amount)',array('proj_id'=>$rProj['proj_id']));
							
							$daysExtension = functions::date_diff($dateCompletion,$dateReviseCompletion);
							$daysDuration = functions::date_diff($dateStart,$dateCompletion);

							$daysElapsed=0;
							$daysRemaining=0;

							if($dateStart <= date('Y-m-d')){
								if( isset($rProj['date_completed']) ){
									$daysElapsed = functions::date_diff($dateStart,$rProj['date_completed']);

									if($rProj['date_revise_completion'])
										$daysRemaining = functions::date_diff($rProj['date_completed'],$rProj['date_revise_completion']);
									else if($rProj['date_completion'])
										$daysRemaining = functions::date_diff($rProj['date_completed'],$rProj['date_completion']);
								}
								else if( !isset($rProj['date_completed']) ){
									$daysElapsed = functions::date_diff($dateStart,date('Y-m-d'));
									if($rProj['date_revise_completion'])
										$daysRemaining = functions::date_diff(date('Y-m-d'),$rProj['date_revise_completion']);
									else if($rProj['date_completion'])
										$daysRemaining = functions::date_diff(date('Y-m-d'),$rProj['date_completion']);
								}
							}

							$qOverhead = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="overhead" AND cost_type="operating expenses"');
							$amount +=  $db->result($qOverhead);

							$qMaterials = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="materials"');
							$amount += $db->result($qMaterials,0);

							$qPO = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) as res FROM po_item poi, po p where p.po_id=poi.po_id AND p.proj_id="'.$db->clean($rProj['proj_id']).'"');
							$amount += $db->result($qPO,0);

							$qInhouseMaterial = $db->query('SELECT round( sum( (quantity * cost) - ( (quantity * cost) * (discount/100) ) ),2) as res FROM inhouse_material im, inhouse_material_item imi where im.im_id=imi.im_id AND im.proj_id="'.$db->clean($rProj['proj_id']).'"');
							$amount += $db->result($qInhouseMaterial,0);

							$qEquipment = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="equipment"');
							$amount += $db->result($qEquipment);

							$qInhouseRental = $db->query('SELECT sum( (duration * cost) - ( (duration * cost) * (discount/100) ) ) FROM inhouse_equip_leasing iel, inhouse_equip_leasing_item ieli WHERE iel.iel_id=ieli.iel_id AND proj_id="'.$db->clean($rProj['proj_id']).'"');
							$amount += $db->result($qInhouseRental,0);

							$qLabor = $db->query('select round(sum(amount),2) from voucher_detail vd, item_deduction itd WHERE vd.category_id=itd.item_id AND proj_id="'.$db->clean($rProj['proj_id']).'" AND itd.expense_type="labor"');
							$amount += $db->result($qLabor,0);

							// Card Background & Accent Border Color based on status/completion
							$cardBorderColor = '#d0d0d0';
							$cardBgColor = '#ffffff';
							if( $projectStat === 'done' || $projectStat === 'closed' || (isset($dateCompleted) && !empty($dateCompleted)) ){
								$cardBorderColor = $bgColorGreen;
								$cardBgColor = '#e8f8f0';
							}

							// Cost consumption percentage
							$consumedPercent = 0;
							if($amount && $rProj['proj_cost']){
								$consumedPercent = ($amount / $rProj['proj_cost']) * 100;
							}
							$barPercent = ($rProj['proj_cost'] > 0) ? min(100, $consumedPercent) : 0;

							// Determine single badge color for "Actual Spent"
							$progressBarBgColor = $bgColorGreen;
							if($consumedPercent >= 41){
								$progressBarBgColor = $bgColorRed;
							} else if($consumedPercent >= 31){
								$progressBarBgColor = $bgColorOrange;
							} else if($consumedPercent >= 20){
								$progressBarBgColor = $bgColorYellow;
							}

							// Multi-segmented widths for Cost Consumption Bar (Green: 0-20, Yellow: 20-30, Orange: 30-41, Red: 41+)
							$costGreenWidth = min(20, $barPercent);
							$costYellowWidth = ($barPercent > 20) ? min(10, $barPercent - 20) : 0;
							$costOrangeWidth = ($barPercent > 30) ? min(11, $barPercent - 30) : 0;
							$costRedWidth = ($barPercent > 41) ? ($barPercent - 41) : 0;

							// Calculate time progress percentage & multi-segmented widths (Green: 0-25, Yellow: 25-50, Orange: 50-75, Red: 75-100)
							$totalDuration = ($daysDuration + ($daysExtension > 0 ? $daysExtension : 0));
							$timePercent = ($totalDuration > 0) ? min(100, max(0, ($daysElapsed / $totalDuration) * 100)) : 0;

							$timeGreenWidth = min(25, $timePercent);
							$timeYellowWidth = ($timePercent > 25) ? min(25, $timePercent - 25) : 0;
							$timeOrangeWidth = ($timePercent > 50) ? min(25, $timePercent - 50) : 0;
							$timeRedWidth = ($timePercent > 75) ? ($timePercent - 75) : 0;
						?>
						<div id="rw<?php echo $rProj['proj_id']?>" style="background: <?php echo $cardBgColor; ?>; border: 1px solid #dcdcdc; border-left: 8px solid <?php echo $cardBorderColor; ?>; border-radius: 8px; padding: 18px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.06); display: flex; flex-direction: column; gap: 14px; width: 100%; box-sizing: border-box;">
							
							<!-- Card Header: Project Name, Status Badge, & Actions -->
							<div style="display: flex; justify-content: space-between; align-items: center; gap: 15px;">
								<div style="display: flex; flex-direction: column; gap: 4px; flex: 1;">
									<span style="font-size: 16px; font-weight: bold; color: #222;">
										<?php echo $rProj['proj_name'];?>
									</span>
									<?php if(!empty($rProj['proj_desc'])): ?>
										<span style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($rProj['proj_desc']); ?></span>
									<?php endif; ?>
								</div>

								<!-- Status Badge & Action Buttons Grouped to Align Vertically Across Cards -->
								<div style="display: flex; gap: 12px; align-items: center; flex-shrink: 0;">
									<?php if($projectStat === 'done'): ?>
										<span style="background: #2ecc71; color: #fff; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase;">Completed<?php echo !empty($dateCompleted) ? ': '.functions::datearr($dateCompleted) : ''; ?></span>
									<?php elseif($projectStat === 'closed'): ?>
										<span style="background: #e74c3c; color: #fff; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase;">Closed</span>
									<?php elseif(!empty($dateCompleted)): ?>
										<span style="background: #2ecc71; color: #fff; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase;">Completed: <?php echo functions::datearr($dateCompleted); ?></span>
									<?php else: ?>
										<span style="background: #3498db; color: #fff; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase;">In Progress</span>
									<?php endif; ?>

									<div style="display: flex; gap: 6px; align-items: center;">
										<a id="detail<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-info thickbox" title="Project Detail" data-rel="tooltip" onclick="showThis(this.id,'project_view.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Detail')"><i class="halflings-icon white zoom-in"></i></a>
										<a id="edit<?php echo $rProj['proj_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Project" data-rel="tooltip" onclick="showThis(this.id,'project_edit.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Detail')"><i class="halflings-icon white pencil"></i></a>
										<?php if($amount==0){?>
										<a id="del<?php echo $rProj['proj_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Project" data-rel="tooltip" href="project_list.php?pidDel=<?php echo functions::encode($rProj['proj_id']);?>"><i class="halflings-icon white trash"></i></a>
										<?php }?>
									</div>
								</div>
							</div>

							<!-- Card Divider -->
							<div style="border-top: 1px solid rgba(0,0,0,0.1); margin: 0;"></div>

							<!-- Card Body Grid Layout: Timeline, Financials, & Progress Tracking with Even Widths -->
							<div style="display: flex; gap: 20px; font-size: 12px; color: #555; flex-wrap: wrap;">
								
								<!-- Column 1: Schedule & Timeline -->
								<div style="flex: 1; min-width: 250px; display: flex; flex-direction: column; gap: 6px;">
									<div style="font-weight: bold; color: #444; margin-bottom: 2px; display: flex; align-items: center; gap: 4px;">
										<i class="halflings-icon calendar" style="opacity: 0.6; font-size: 11px;"></i> Timeline & Schedule:
									</div>
									<div style="display: flex; flex-direction: column; gap: 6px; background: rgba(255,255,255,0.85); border: 1px solid rgba(0,0,0,0.08); padding: 8px 10px; border-radius: 4px;">
										<div style="display: flex; gap: 8px;">
											<span style="color: #555; width: 145px; flex-shrink: 0;">Date Started:</span>
											<span style="font-weight: bold; color: #222;"><?php echo functions::datearr($rProj['date_start']);?></span>
										</div>
										<div style="display: flex; gap: 8px;">
											<span style="color: #555; width: 145px; flex-shrink: 0;">Contract Duration:</span>
											<span style="font-weight: bold; color: #222;"><?php echo number_format($daysDuration);?> Days</span>
										</div>
										<div style="display: flex; gap: 8px;">
											<span style="color: #555; width: 145px; flex-shrink: 0;">Target Completion:</span>
											<span style="font-weight: bold; color: #222;"><?php echo functions::datearr($dateCompletion);?></span>
										</div>
										<?php if(!empty($daysExtension)): ?>
										<div style="display: flex; gap: 8px;">
											<span style="color: #555; width: 145px; flex-shrink: 0;">Approved Time Extension:</span>
											<span style="font-weight: bold; color: #222;"><?php echo number_format($daysExtension);?> Days</span>
										</div>
										<div style="display: flex; gap: 8px; border-top: 1px dashed rgba(0,0,0,0.1); padding-top: 5px; margin-top: 2px;">
											<span style="color: #555; width: 145px; flex-shrink: 0;">Revised Target Date:</span>
											<span style="font-weight: bold; color: #222;"><?php echo functions::datearr($dateReviseCompletion);?></span>
										</div>
										<?php endif; ?>
									</div>
								</div>

								<!-- Column 2: Financials -->
								<div style="flex: 1; min-width: 250px; display: flex; flex-direction: column; gap: 6px;">
									<div style="font-weight: bold; color: #444; margin-bottom: 2px; display: flex; align-items: center; gap: 4px;">
										<i class="halflings-icon qrcode" style="opacity: 0.6; font-size: 11px;"></i> Financial Overview:
									</div>
									<div style="display: flex; flex-direction: column; gap: 8px; background: rgba(255,255,255,0.85); border: 1px solid rgba(0,0,0,0.08); padding: 8px 10px; border-radius: 4px;">
										<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; align-items: center;">
											<div>
												<div style="color: #777; font-size: 11px;">Contract Amount</div>
												<div style="font-weight: bold; color: #333; font-size: 13px;"><?php echo functions::formatMoney($rProj['proj_cost']);?></div>
											</div>
											<div>
												<div style="color: #777; font-size: 11px;">Actual Spent</div>
												<div>
													<a id="costdetail<?php echo $rProj['proj_id']?>" class="thickbox" title="Actual Cost Details" data-rel="tooltip" onclick="showThis(this.id,'project_cost_view.php?pid=<?php echo functions::encode($rProj['proj_id']);?>','Project Cost Details','1')" style="font-size: 12px; padding: 4px 10px; border-radius: 4px; background-color: <?php echo $progressBarBgColor; ?>; border: 1px solid rgba(0,0,0,0.15); color: #fff; font-weight: bold; display: inline-block; text-decoration: none; text-shadow: 0 1px 1px rgba(0,0,0,0.2); cursor: pointer;"><?php echo functions::formatMoney($amount);?></a>
												</div>
											</div>
										</div>

										<!-- Multi-Segmented Cost Consumption Progress Bar -->
										<div style="display: flex; flex-direction: column; gap: 3px; border-top: 1px dashed rgba(0,0,0,0.1); padding-top: 6px;">
											<div style="display: flex; justify-content: space-between; font-size: 10px; font-weight: bold; color: #555;">
												<span>Cost Consumption</span>
												<span><?php echo number_format($consumedPercent, 1); ?>%</span>
											</div>
											<div style="background: #e0e0e0; border-radius: 4px; height: 8px; width: 100%; overflow: hidden; position: relative; display: flex;">
												<?php if($costGreenWidth > 0): ?>
													<div style="background-color: <?php echo $bgColorGreen; ?>; width: <?php echo $costGreenWidth; ?>%; height: 100%;"></div>
												<?php endif; ?>
												<?php if($costYellowWidth > 0): ?>
													<div style="background-color: <?php echo $bgColorYellow; ?>; width: <?php echo $costYellowWidth; ?>%; height: 100%;"></div>
												<?php endif; ?>
												<?php if($costOrangeWidth > 0): ?>
													<div style="background-color: <?php echo $bgColorOrange; ?>; width: <?php echo $costOrangeWidth; ?>%; height: 100%;"></div>
												<?php endif; ?>
												<?php if($costRedWidth > 0): ?>
													<div style="background-color: <?php echo $bgColorRed; ?>; width: <?php echo $costRedWidth; ?>%; height: 100%;"></div>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</div>

								<!-- Column 3: Days Tracking -->
								<div style="flex: 1; min-width: 250px; display: flex; flex-direction: column; gap: 6px;">
									<div style="font-weight: bold; color: #444; margin-bottom: 2px; display: flex; align-items: center; gap: 4px;">
										<i class="halflings-icon time" style="opacity: 0.6; font-size: 11px;"></i> Progress Tracking:
									</div>
									<div style="display: flex; flex-direction: column; gap: 6px; background: rgba(255,255,255,0.85); border: 1px solid rgba(0,0,0,0.08); padding: 8px 10px; border-radius: 4px;">
										<div style="display: flex; justify-content: space-between; font-size: 11px;">
											<span style="color: #666;">Elapsed:</span>
											<strong style="color: #333;"><?php echo number_format($daysElapsed);?> days</strong>
										</div>
										<div style="display: flex; justify-content: space-between; font-size: 11px; border-top: 1px solid rgba(0,0,0,0.08); padding-top: 4px;">
											<span style="color: #666;">Remaining:</span>
											<strong style="color: #333;"><?php echo number_format($daysRemaining);?> days</strong>
										</div>

										<!-- Multi-Segmented Time Progress Bar (Equal 25% distribution) -->
										<div style="display: flex; flex-direction: column; gap: 3px; border-top: 1px dashed rgba(0,0,0,0.1); padding-top: 6px;">
											<div style="display: flex; justify-content: space-between; font-size: 10px; font-weight: bold; color: #555;">
												<span>Time Elapsed</span>
												<span><?php echo number_format($timePercent, 1); ?>%</span>
											</div>
											<div style="background: #e0e0e0; border-radius: 4px; height: 8px; width: 100%; overflow: hidden; position: relative; display: flex;">
												<?php if($timeGreenWidth > 0): ?>
													<div style="background-color: <?php echo $bgColorGreen; ?>; width: <?php echo $timeGreenWidth; ?>%; height: 100%;"></div>
												<?php endif; ?>
												<?php if($timeYellowWidth > 0): ?>
													<div style="background-color: <?php echo $bgColorYellow; ?>; width: <?php echo $timeYellowWidth; ?>%; height: 100%;"></div>
												<?php endif; ?>
												<?php if($timeOrangeWidth > 0): ?>
													<div style="background-color: <?php echo $bgColorOrange; ?>; width: <?php echo $timeOrangeWidth; ?>%; height: 100%;"></div>
												<?php endif; ?>
												<?php if($timeRedWidth > 0): ?>
													<div style="background-color: <?php echo $bgColorRed; ?>; width: <?php echo $timeRedWidth; ?>%; height: 100%;"></div>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</div>

							</div>

						</div>
						<?php endwhile;
					else: ?>
						<div style="text-align: center; padding: 40px; background: #fff; border: 1px solid #ddd; border-radius: 6px; width: 100%; box-sizing: border-box;" class="muted">
							<i class="halflings-icon warning-sign"></i> No projects found matching your query.
						</div>
					<?php endif; ?>
				</div>

				<div align="center" style="margin-top: 20px;"><?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=functions::pageName().'?',$search="");?></div>
			</div>
		</div><!--/span-->
	</div><!--/row-->
</form>
<!-- body content: end here-->
<script>
function projSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?pid="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
function delt(){
	if(confirm('Do you want to remove this Project?'))
		return true;
	else
		return false; 
}
</script>
<?php require_once('templ_down.php');?>
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
<?php if(isset($_SESSION['notif_id_list'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id_list'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id_list'] ?>").animate({borderColor:""}, 4000);
});
</script>
<?php unset($_SESSION['notif_id_list']);} ?>