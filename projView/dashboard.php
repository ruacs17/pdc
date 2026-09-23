<?php require_once('templ_up.php');?>
<?php
$arrVal = array('project'=>'1','user_id'=>$user_id,'incharge_type'=>'project_incharge');
$projects = $db->getValue('project proj, project_incharge projin','count(proj.proj_id)',$arrVal,'AND proj.proj_id=projin.proj_id');
?>
<!-- body content: start here-->
<div class="row-fluid">
	<a class="quick-button metro purple span3" href="project_list.php">
		<i class="icon-building"></i>
		<p><strong>Projects</strong></p>
		<span style="font-size: 20px;"><?php echo $projects;?></span>
	</a>
	 <a id="projProf" class="quick-button metro blue span3 thickbox" onclick="showThis(this.id,'dash-proj-cost-control.php?','Project Cost Control','1')">
		<i class="icon-list-alt"></i>
		<p><strong>Project Cost Control</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	 <a id="projCIS" class="quick-button metro purple span3" href="project_list.php">
		<i class="icon-list-alt"></i>
		<p><strong>Comparative Income Statement</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a id="projControl" class="quick-button metro blue span3 thickbox" onclick="showThis(this.id,'dash-proj-profit.php?','Project Detail','1')">
		<i class="icon-money"></i>
		<p><strong>Project Profit</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
</div><br>
<!--/row-fluid-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>