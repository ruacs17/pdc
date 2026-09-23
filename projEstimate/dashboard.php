<?php require_once('templ_up.php');?>
            <!-- body content: start here-->
			<div class="row-fluid">
				<a class="quick-button metro purple span3" href="project_list.php">
					<i class="icon-building"></i>
					<p><strong>Projects</strong></p>
					<span style="font-size: 20px;"><?php echo $db->getValue('project','count(proj_id)',array('project'=>1));?></span>
				</a>
				<a id="projCIS" class="quick-button metro blue span3" href="project_ca.php">
					<i class="icon-book"></i>
					<p><strong>Estimate</strong></p>
					<span style="font-size: 20px;">&nbsp;</span>
				</a>
				<a id="projControl" class="quick-button metro purple span3" href="admin-material-reference.php">
					<i class="icon-money"></i>
					<p><strong>Price Monitor</strong></p>
					<span style="font-size: 20px;">&nbsp;</span>
				</a>
				<a id="projProf" class="quick-button metro blue span3" href="admin-equipment.php">
					<i class="icon-truck"></i>
					<p><strong>Property</strong></p>
					<span style="font-size: 20px;">&nbsp;</span>
				</a>
			</div><br>
            <!--/row-fluid-->
            <!-- body content: end here-->
<?php require_once('templ_down.php');?>