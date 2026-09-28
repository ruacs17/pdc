<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;

$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=40;
$arrVal=array();
if($p_id){
	$arrVal = array('proj_id'=>$p_id);
}
?>

<style type="text/css">
	:root {
		--bg-canvas: #fcfaf8;
		--panel-bg: #ffffff;
		--border-subtle: #e7e0d8;
		--text-primary: #2c1d11;
		--text-muted: #78695c;
		
		/* Brown Theme Color Palette */
		--theme-brown-dark: #3d2314;
		--theme-brown-header: linear-gradient(135deg, #4a2c1d 0%, #2b180d 100%);
		--theme-brown-primary: #7c401e;
		--theme-brown-hover: #5c2e14;
		--theme-brown-light: #f5ebe6;
		--theme-brown-badge-text: #612d11;
		--theme-brown-accent: #b45309;
	}

	body {
		background-color: var(--bg-canvas);
		font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
		color: var(--text-primary);
		margin: 0;
		padding: 0;
	}

	/* Full Width Container Setup */
	.group-assign-container {
		width: 100% !important;
		max-width: 100% !important;
		padding: 15px 20px 30px 20px;
		box-sizing: border-box;
	}

	/* Header & Action Bar */
	.top-action-bar {
		display: flex;
		justify-content: flex-end;
		margin-bottom: 20px;
		width: 100%;
	}

	.btn-modern-brown {
		background: var(--theme-brown-primary);
		color: #ffffff !important;
		font-weight: 600;
		font-size: 13px;
		padding: 10px 20px;
		border-radius: 8px;
		border: none;
		display: inline-flex;
		align-items: center;
		gap: 8px;
		text-decoration: none !important;
		box-shadow: 0 4px 12px rgba(124, 64, 30, 0.25);
		transition: all 0.2s ease;
	}

	.btn-modern-brown:hover {
		background: var(--theme-brown-hover);
		transform: translateY(-1px);
		box-shadow: 0 6px 16px rgba(92, 46, 20, 0.35);
	}

	/* Card Structure */
	.card-panel {
		width: 100%;
		background: var(--panel-bg);
		border-radius: 16px;
		border: 1px solid var(--border-subtle);
		box-shadow: 0 10px 25px -5px rgba(61, 35, 20, 0.05);
		overflow: hidden;
		box-sizing: border-box;
	}

	/* Group Assignment Header (Brown Gradient) */
	.card-header-custom {
		background: var(--theme-brown-header);
		padding: 20px 25px;
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

	/* Search Control Bar */
	.filter-card {
		background: #f7f2ed;
		padding: 18px 20px;
		border-radius: 12px;
		margin-bottom: 25px;
		border: 1px solid var(--border-subtle);
		width: 100%;
		box-sizing: border-box;
	}

	.filter-grid {
		display: flex;
		align-items: center;
		gap: 15px;
		width: 100%;
	}

	.filter-grid select {
		flex: 1;
		height: 42px !important;
		padding: 8px 14px !important;
		font-size: 13px !important;
		border-radius: 8px !important;
		border: 1px solid var(--border-subtle) !important;
		background-color: #ffffff !important;
		color: var(--text-primary) !important;
		outline: none;
	}

	.filter-grid select:focus {
		border-color: var(--theme-brown-primary) !important;
	}

	.btn-modern-search {
		background: var(--theme-brown-primary);
		color: #ffffff;
		font-weight: 600;
		padding: 10px 28px;
		border-radius: 8px;
		border: none;
		font-size: 13px;
		cursor: pointer;
		transition: background 0.2s;
		white-space: nowrap;
	}

	.btn-modern-search:hover {
		background: var(--theme-brown-hover);
	}

	/* Full-Width Data Table Styling */
	.table-wrapper {
		width: 100%;
		border-radius: 12px;
		overflow: hidden;
		border: 1px solid var(--border-subtle);
		box-sizing: border-box;
	}

	.custom-data-table {
		width: 100% !important;
		border-collapse: separate;
		border-spacing: 0;
		font-size: 13px;
		margin: 0;
	}

	.custom-data-table thead tr {
		background: #f4ebe2;
	}

	.custom-data-table thead th {
		color: var(--text-muted);
		font-weight: 700;
		text-transform: uppercase;
		font-size: 11px;
		letter-spacing: 0.5px;
		padding: 16px 20px;
		border-bottom: 1px solid var(--border-subtle);
		position: sticky;
		top: 0;
	}

	.custom-data-table tbody tr {
		transition: background 0.15s ease;
	}

	.custom-data-table tbody tr:hover {
		background-color: #f9f4ef !important;
	}

	.custom-data-table tbody td {
		padding: 16px 20px;
		border-bottom: 1px solid #f2e9e1;
		vertical-align: middle;
	}

	.custom-data-table tbody tr:last-child td {
		border-bottom: none;
	}

	/* Brown Theme Personnel Badge */
	.personnel-badge {
		background: var(--theme-brown-light);
		color: var(--theme-brown-badge-text);
		font-weight: 700;
		font-size: 12px;
		padding: 4px 14px;
		border-radius: 20px;
		display: inline-block;
		border: 1px solid #ebd8cb;
	}

	/* Brown Action Button */
	.btn-action-icon {
		background: var(--theme-brown-light);
		color: var(--theme-brown-primary);
		width: 34px;
		height: 34px;
		border-radius: 8px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		text-decoration: none !important;
		transition: all 0.2s ease;
		border: 1px solid #ebd8cb;
	}

	.btn-action-icon:hover {
		background: var(--theme-brown-primary);
		color: #ffffff;
		border-color: var(--theme-brown-primary);
		transform: scale(1.05);
	}

	.pagination-container {
		margin-top: 20px;
		display: flex;
		justify-content: center;
		width: 100%;
	}
</style>

<!-- body content: start here-->
<div class="group-assign-container">
	<div class="card-panel">
		<div class="card-header-custom">
			<h2><i class="halflings-icon white list-alt"></i> Project Assignment</h2>
		</div>
		<div class="card-body-custom">
			<form method="post" style="width:100%;">
				<div class="filter-card">
					<div class="filter-grid">
						<select name="selProj" id="selProj" data-rel="chosen" onChange="projSel(this.value)">
							<option value="">-- All Projects --</option>
							<?php 
							$qProj = $db->select('project','*',array(),'ORDER BY proj_name');
							while($rProj = $db->fetch_array($qProj)):?>
							<option value="<?php echo functions::encode($rProj['proj_id'])?>" <?php if($p_id==$rProj['proj_id'])echo 'selected="selected"';?>>
								<?php echo strtoupper($rProj['proj_name']);?><?php echo ($rProj['proj_desc']) ? ' ('.$rProj['proj_desc'].')' : '';?>
							</option>
							<?php endwhile;?>
						</select>
						<button type="submit" name="btnSearch" id="btnSearch" class="btn-modern-search">Search</button>
					</div>
				</div>
			</form>

			<div class="table-wrapper">
				<table class="custom-data-table table-hover table-striped">
					<thead>
						<tr>
							<th width="70%" scope="col">Project Name</th>
							<th width="15%" scope="col" style="text-align: center;">Assigned Personnel</th>
							<th width="15%" scope="col" style="text-align: center;">View</th>
						</tr>
					</thead>
					<tbody>
					<?php
					if(!is_numeric($startrow))
						$startrow=0;
					$qProj = $db->select('project','*',$arrVal,'ORDER BY date_start DESC LIMIT '.$startrow.', '.$rowdisplay);
					$num_record = $db->getValue('project','count(*)',$arrVal);

					while($rDisp = $db->fetch_array($qProj)):
						$rID = $rDisp['proj_id'];
						$member = $db->getValue('emp_site_assign','count(*)',array('proj_id'=>$rID));
					?>
						<tr id="rw<?php echo $rID;?>">
							<td style="font-weight: 600; color: var(--text-primary);">
								<?php echo $rDisp['proj_name']?>
							</td>
							<td style="text-align: center;">
								<span class="personnel-badge"><?php echo $member?></span>
							</td>
							<td style="text-align: center;">
								<a id="vw<?php echo $rID?>" class="btn-action-icon thickbox" title="Assignment Detail" data-rel="tooltip" onclick="showThis(this.id,'project-assign-detail.php?projid=<?php echo functions::encode($rID);?>','Assignment Detail')">
									<i class="halflings-icon zoom-in" style="margin-top:0;"></i>
								</a>
							</td>
						</tr>
					<?php endwhile;?>
					</tbody>
				</table>
			</div>

			<div class="pagination-container">
				<?php functions::pagination($rowdisplay,$page_display=10,$num_record,$startrow,$pagename=functions::pageName().'?',$search="");?>
			</div>
		</div>
	</div>
</div>
<!-- body content: end here-->

<?php require_once('templ_down.php');?>

<script>
function projSel(PiEwgD){
	if(PiEwgD)
		window.location="<?php echo functions::pageName()?>?pid="+PiEwgD
	else
		window.location="<?php echo functions::pageName()?>"
}
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
	$('#rw<?php echo $_SESSION['notif_id'] ?>').css('border','2px solid #7c401e');
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:"#d4a373"}, 4000);
	$("#rw<?php echo $_SESSION['notif_id'] ?>").animate({borderColor:""}, 4000);
	window.setTimeout(function(){$('#tblist').addClass('table-bordered');}, 5000);
});
</script>
<?php unset($_SESSION['notif_id']);} ?>