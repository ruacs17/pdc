<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$delID = (isset($_REQUEST['delID']) && !empty($_REQUEST['delID']) ) ? functions::decode($_REQUEST['delID']) : 0;
if($delID){
	if( $db->getValue('emp_attendance','count(*)',array('eas_id'=>$delID)) == 0){
		$_SESSION['notif_warning']='Group Removed!';
		$db->delete('emp_assignment',array('eas_id'=>$delID));
	}
	functions::sendTo(functions::pageName());
	die();
}

if( isset($_POST['btnSearch']) ){
	$arr = array();
	$_SESSION['asProj'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? functions::decode($_POST['selProj']) : '';
	functions::sendTo(functions::pageName());
	die();
}
$arr = array();
$projID = ( isset($_SESSION['asProj']) && !empty($_SESSION['asProj']) ) ? $_SESSION['asProj'] : '';

if($projID)
	$arr = array_merge($arr,array('p.proj_id'=>$projID));
?>
<style type="text/css">
	:root {
		--bg-canvas: #fcfaf8;
		--panel-bg: #ffffff;
		--border-subtle: #e7e0d8;
		--text-primary: #2c1d11;
		--text-muted: #78695c;
		
		/* Brown Theme Color Palette */
		--theme-brown-header: linear-gradient(135deg, #4a2c1d 0%, #2b180d 100%);
		--theme-brown-primary: #7c401e;
		--theme-brown-hover: #5c2e14;
		--theme-brown-light: #f5ebe6;
		--theme-brown-accent: #b45309;
		--theme-brown-active-row: #f3e5dc;
	}

	body {
		background-color: var(--bg-canvas);
		font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
		color: var(--text-primary);
		margin: 0;
		padding: 0;
	}

	/* Maximize Entire Width of Page */
	.page-full-wrapper {
		width: 100% !important;
		max-width: 100% !important;
		/*padding: 0 15px !important;*/
		box-sizing: border-box;
	}

	.card-panel {
		width: 100%;
		background: var(--panel-bg);
		border-radius: 16px;
		border: 1px solid var(--border-subtle);
		box-shadow: 0 10px 25px -5px rgba(61, 35, 20, 0.05);
		overflow: visible;
		box-sizing: border-box;
		margin-bottom: 25px;
	}

	.card-header-custom {
		background: var(--theme-brown-header);
		padding: 18px 25px;
		color: #ffffff;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}

	.card-header-custom h2 {
		margin: 0;
		font-size: 18px;
		font-weight: 700;
		display: flex;
		align-items: center;
		gap: 10px;
		color: #ffffff;
	}

	.card-body-custom {
		padding: 25px;
		width: 100%;
		box-sizing: border-box;
	}

	.filter-box {
		background: #f7f2ed;
		padding: 15px 20px;
		border-radius: 12px;
		border: 1px solid var(--border-subtle);
		margin-bottom: 25px;
	}

	.filter-flex {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 12px;
	}

	.btn-modern-add {
		background: var(--theme-brown-primary);
		color: #ffffff !important;
		font-weight: 700;
		padding: 8px 18px;
		border-radius: 8px;
		border: none;
		font-size: 13px;
		cursor: pointer;
		transition: all 0.2s ease;
		box-shadow: 0 4px 12px rgba(124, 64, 30, 0.2);
		text-decoration: none;
		display: inline-flex;
		align-items: center;
		gap: 8px;
	}

	.btn-modern-add:hover {
		background: var(--theme-brown-hover);
		color: #fff;
		transform: translateY(-1px);
	}

	.btn-filter-compact {
		background: var(--theme-brown-primary);
		color: #fff;
		font-weight: 600;
		padding: 6px 14px;
		border-radius: 6px;
		border: none;
		font-size: 12px;
		cursor: pointer;
		transition: background 0.2s;
		height: 30px;
	}

	.btn-filter-compact:hover {
		background: var(--theme-brown-hover);
	}

	/* Full-Width Stacked Layout for Group Cards */
	.groups-grid {
		display: flex;
		flex-direction: column;
		gap: 15px;
		width: 100%;
		box-sizing: border-box;
	}

	.group-card {
		background: #ffffff;
		border: 1px solid var(--border-subtle);
		border-radius: 12px;
		padding: 18px 22px;
		box-shadow: 0 4px 12px rgba(61, 35, 20, 0.03);
		transition: all 0.2s ease;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 20px;
		width: 100%;
		box-sizing: border-box;
	}

	.group-card:hover {
		transform: translateY(-1px);
		box-shadow: 0 6px 16px rgba(61, 35, 20, 0.06);
		border-color: #dcd0c4;
	}

	/* Expanded main info column */
	.group-info-main {
		display: flex;
		flex-direction: column;
		gap: 6px;
		flex: 3;
		min-width: 0;
	}

	.group-title {
		font-size: 16px;
		font-weight: 700;
		color: var(--theme-brown-primary);
		margin: 0;
		display: flex;
		align-items: center;
		gap: 8px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.project-label {
		font-size: 12px;
		color: var(--text-muted);
		font-weight: 600;
		text-transform: uppercase;
		display: flex;
		align-items: center;
		gap: 6px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.group-type-col {
		flex: 0 0 160px;
		display: flex;
		align-items: center;
	}

	.group-meta-col {
		flex: 0 0 120px;
		display: flex;
		align-items: center;
		gap: 8px;
		font-size: 13px;
		font-weight: 600;
		color: var(--text-primary);
		white-space: nowrap;
	}

	.member-count-badge {
		background: var(--theme-brown-light);
		color: var(--theme-brown-primary);
		padding: 2px 8px;
		border-radius: 6px;
		font-weight: 700;
	}

	.badge-type-admin {
		background: var(--theme-brown-light);
		color: var(--theme-brown-primary);
		padding: 4px 10px;
		border-radius: 6px;
		font-weight: 600;
		font-size: 11px;
		text-transform: uppercase;
		display: inline-block;
	}

	.badge-type-labor {
		background: #f1f5f9;
		color: #475569;
		padding: 4px 10px;
		border-radius: 6px;
		font-weight: 600;
		font-size: 11px;
		text-transform: uppercase;
		display: inline-block;
	}

	.group-actions {
		display: flex;
		gap: 6px;
		justify-content: flex-end;
		flex: 0 0 auto;
	}
</style>

<!-- body content: start here-->
<div class="page-full-wrapper">
	<div class="card-panel">
		<div class="card-header-custom">
			<h2><i class="halflings-icon white list-alt"></i> Group Assignment for Attendance Encoding</h2>
			<a id="adc" href="#" class="btn-modern-add thickbox" style="cursor: pointer;text-decoration: none;" onclick="showThis(this.id,'assignment_manage.php?','Employee Assignment')">
				<i class="halflings-icon white plus" style="margin-top:0;"></i> Add Employee Assignment
			</a>
		</div>
		<div class="card-body-custom">
			<form method="post">
				<div class="filter-box">
					<div class="filter-flex">
						<div style="flex: 1; min-width: 50px;">
							<select name="selProj" id="selProj" data-rel="chosen" style="width:90%;">
								<option value="">--All Projects / Department--</option>
								<?php 
								$qProj = $db->select('project','*',array(),'WHERE proj_id IN (SELECT DISTINCT proj_id FROM emp_assignment) ORDER BY proj_name');
								while($rProj = $db->fetch_array($qProj)):?>
								<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($projID==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?></option>
								<?php endwhile;?>
							</select>
						</div>
						<div>
							<input type="submit" name="btnSearch" id="btnSearch" value="Select" class="btn-filter-compact">
						</div>
					</div>
				</div>
			</form>

			<div class="groups-grid" id="tblist">
				<?php
				if($projID)
					$qDisp = $db->select('emp_assignment p','*',array('proj_id'=>$projID),'ORDER BY proj_id');
				else
					$qDisp = $db->query('SELECT * FROM emp_assignment ea, project p WHERE ea.proj_id=p.proj_id ORDER BY proj_name');
				
				while($rDisp = $db->fetch_array($qDisp)):
					$rID = $rDisp['eas_id'];
					$member = $db->getValue('emp_assign_detail','count(*)',array('eas_id'=>$rID));
				?>
					<div class="group-card" id="rw<?php echo $rID;?>">
						<div class="group-info-main">
							<div class="project-label">
								<i class="halflings-icon briefcase" style="opacity: 0.7;"></i> 
								<?php echo strtoupper($db->getValue('project','proj_name',array('proj_id'=>$rDisp['proj_id'])));?>
							</div>
							<h3 class="group-title">
								<i class="halflings-icon user" style="color: var(--theme-brown-primary);"></i> 
								<?php echo $rDisp['eas_name']?>
							</h3>
						</div>
						<div class="group-type-col">
							<?php if($rDisp['worker_type']=='admin'){ ?>
								<span class="badge-type-admin">Office Personnel</span>
							<?php } else { ?>
								<span class="badge-type-labor">Labor Group</span>
							<?php } ?>
						</div>
						<div class="group-meta-col">
							Members: <span class="member-count-badge"><?php echo $member?></span>
						</div>
						<div class="group-actions">
							<a id="vw<?php echo $rID?>" class="btn btn-mini btn-info thickbox" title="Group Detail" data-rel="tooltip" onclick="showThis(this.id,'assignment_group_select.php?easid=<?php echo functions::encode($rID);?>','Group Detail')"><i class="halflings-icon white zoom-in"></i></a>
							<?php if( $db->getValue('emp_attendance','count(*)',array('eas_id'=>$rID)) == 0){?>
							<a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Group" data-rel="tooltip" href="?delID=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
							<?php }?>
						</div>
					</div>
				<?php endwhile;?>
			</div>
		</div>
	</div>
</div>
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>
function delt(){if(confirm('Do you want to remove this Group?'))return true; else return false;}
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
<?php if(isset($_SESSION['notif_id'])){ ?>
<script src="../js/jcentr.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#rw<?php echo $_SESSION['notif_id'] ?>').centerView();
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','3px solid green');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#87EAC1"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>