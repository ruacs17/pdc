<?php require_once('templ_up.php');?>
<?php
$p_id=(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
$searchVal='';
if( isset($_REQUEST['rm']) && !empty($_REQUEST['rm']) ){
	$rm = functions::decode($_REQUEST['rm']);
	if( $db->getValue('emp_201','count(*)',array('e2r_id'=>$rm))==0 ){
		$db->delete('emp_201_reference',array('e2r_id'=>$rm));
	}
	$_SESSION['notif_success']='Checklist Removed!';
	functions::sendTo('?');
	die();
}
?>

<style>
	:root {
		--panel-bg: #ffffff;
		--border-subtle: #e7e0d8;
		--text-primary: #2c1d11;
		--text-muted: #78695c;
		
		/* Brown Theme Color Palette for Main Header Container */
		--theme-brown-header: linear-gradient(135deg, #4a2c1d 0%, #2b180d 100%);
	}

	/* Maximize Entire Width of Page */
	.page-full-wrapper {
		width: 100% !important;
		max-width: 100% !important;
		padding: 0 15px !important;
		box-sizing: border-box;
	}

	.card-panel {
		width: 100%;
		background: var(--panel-bg);
		border-radius: 16px;
		border: 1px solid var(--border-subtle);
		box-shadow: 0 10px 25px -5px rgba(61, 35, 20, 0.05);
		overflow: hidden;
		box-sizing: border-box;
		margin-bottom: 25px;
	}

	/* Target Custom Card Header */
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

	.emp-toolbar {
		display: flex;
		justify-content: flex-end;
		gap: 8px;
		margin-bottom: 15px;
	}

	/* Enhanced Modern Table Design */
	.table-wrapper-custom {
		overflow-x: auto;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		background: #ffffff;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
	}

	.table-modern {
		width: 100%;
		border-collapse: collapse;
		text-align: left;
		margin-bottom: 0 !important;
	}

	.table-modern th {
		background: #f8fafc;
		color: #475569;
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.6px;
		padding: 14px 20px;
		border-bottom: 2px solid #e2e8f0;
	}

	.table-modern td {
		padding: 14px 20px;
		border-bottom: 1px solid #f1f5f9;
		vertical-align: middle;
		transition: background-color 0.2s ease;
	}

	.table-modern tr:last-child td {
		border-bottom: none;
	}

	.table-modern tr:hover td {
		background-color: #faf6f0;
	}

	.checklist-name-cell {
		font-size: 14px;
		font-weight: 600;
		color: #2c1d11;
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.checklist-icon-bullet {
		width: 8px;
		height: 8px;
		background-color: #7c401e;
		border-radius: 50%;
		flex-shrink: 0;
	}

	/* Action Buttons Group Styling */
	.action-buttons-group {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		gap: 6px;
	}

	.action-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 32px;
		height: 32px;
		border-radius: 6px;
		color: #ffffff !important;
		text-decoration: none !important;
		transition: all 0.2s ease;
		border: none;
		cursor: pointer;
	}

	.action-btn-info {
		background-color: #0284c7;
	}
	.action-btn-info:hover {
		background-color: #0369a1;
		transform: translateY(-2px);
		box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);
	}

	.action-btn-warning {
		background-color: #d97706;
	}
	.action-btn-warning:hover {
		background-color: #b45309;
		transform: translateY(-2px);
		box-shadow: 0 4px 10px rgba(217, 119, 6, 0.3);
	}

	.action-btn-danger {
		background-color: #dc2626;
	}
	.action-btn-danger:hover {
		background-color: #b91c1c;
		transform: translateY(-2px);
		box-shadow: 0 4px 10px rgba(220, 38, 38, 0.3);
	}
</style>

<!-- Top Actions Bar -->
<div class="emp-toolbar">
	<a id="adc" href="#" class="btn btn-info btn-setting btn-small thickbox" onclick="showThis(this.id,'201_reference_manage.php?','Manage Reference')"><i class="icon-plus icon-white"></i> Add New CheckList</a>
</div>

<div class="page-full-wrapper">
	<div class="card-panel">
		<!-- Updated Custom Card Header -->
		<div class="card-header-custom">
			<h2><i class="halflings-icon white list-alt"></i> 201 Checklist References</h2>
		</div>
		<div class="card-body-custom">
			<form method="post" style="display:none;">
				<div align="center">
					<table border="0">
						<tr>
							<td style="padding-top: 7px;"><input type="text" name="txSearch" id="txSearch" value="<?php echo $searchVal?>"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info" name="btnSearch" id="btnSearch" value="Search"></td>
							<td>&nbsp;&nbsp;<input type="submit" class="btn btn-info" name="btnAll" id="btnAll" value="View All"></td>
						</tr>
					</table>
				</div>
			</form>

			<div class="table-wrapper-custom">
				<table id="tblist" class="table-modern">
					<thead>
						<tr>
							<th width="80%">Name</th>
							<th width="20%"><div align="right">Manage</div></th>
						</tr>
					</thead>
					<tbody>
					<?php
					$qDisp = $db->select('emp_201_reference','*',array(),'ORDER BY e2r_name');
					while($rDisp = $db->fetch_array($qDisp)):
						$rID = $rDisp['e2r_id'];
					?>
						<tr id="rw<?php echo $rID?>">
							<td>
								<div class="checklist-name-cell">
									<span class="checklist-icon-bullet"></span>
									<?php echo $rDisp['e2r_name'];?>
								</div>
							</td>
							<td>
								<div class="action-buttons-group">
									<a id="vw<?php echo $rID?>" class="action-btn action-btn-info thickbox" title="View Detail" data-rel="tooltip" onclick="showThis(this.id,'201_reference_view.php?eid=<?php echo functions::encode($rID);?>','201 View')"><i class="halflings-icon white zoom-in"></i></a>
									<a id="edit<?php echo $rID?>" class="action-btn action-btn-warning thickbox" title="Modify Checklist" data-rel="tooltip" onclick="showThis(this.id,'201_reference_manage.php?eid=<?php echo functions::encode($rID);?>','201 Manage')"><i class="halflings-icon white pencil"></i></a>
									<?php if( $db->getValue('emp_201','count(*)',array('e2r_id'=>$rID))==0 ){ ?>
									<a href="?rm=<?php echo functions::encode($rID) ?>" class="action-btn action-btn-danger" title="Remove Checklist" data-rel="tooltip" onClick="return delt()"><i class="halflings-icon white trash"></i></a>
									<?php } ?>
								</div>
							</td>
						</tr>
					<?php endwhile; ?>
					</tbody>
				</table>
			</div>
			
		</div>
	</div><!--/span-->
</div><!--/row-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>
<script>function delt(){if(confirm('Do you want to remove this checklist?'))return true; else return false;}</script>
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
<?php if(isset($_SESSION['notif_success'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_success'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_success']);} ?>