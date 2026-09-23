<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="9" ){
  header("Location: ../");
  die();
}
  $username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
  $user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  $db = new Database();
  $name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
  #$logs = new Logs();
  #$logs->save('visit');
  $arrVal = array('incharge'=>$user_id);
  $proj_date = (isset($_REQUEST['pdt']) && !empty($_REQUEST['pdt']) ) ? functions::decode($_REQUEST['pdt']) : '';
  if($proj_date)
  	$arrVal = array('left(date_contract,4)'=>$proj_date,'incharge'=>$user_id);
  
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Project Report</title>
            <!-- end: Meta -->
            <!-- start: Mobile Specific -->
            <meta name="viewport" content="width=device-width, initial-scale=1">
              <!-- end: Mobile Specific -->
              <!-- start: CSS -->
              <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
              <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
              <link id="base-style" href="../css/style.css" rel="stylesheet">
              <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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

  </head>
  <body>
            <!-- body content: start here-->
      <div class="row-fluid">
        
        <div class="box span12">
          <div class="box-header">
            <h2><i class="halflings-icon white th"></i><span class="break"></span>Project Cost Control Report</h2>
          </div>
          <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
              <li><a href="dash-proj-cost-control2.php?pdt=<?php echo functions::encode($proj_date);?>">Cost View</a></li>
              <li class="active"><a href="#">Deductions</a></li>
            </ul>
          </div>
                <div class="box-content" align="center">
                    <div>
                    <table width="29%" border="0">
                      <tr>
                        <td width="32%" valign="middle">Contract Year: </td>
                        <td width="68%" valign="middle">
                            <select name="proj_yr" id="proj_yr" onChange="selDt(this.value)">
                            <option value="">-- All Projects --</option>
                            <?php
                                $qYr = $db->query('SELECT DISTINCT left(date_contract,4) as dt FROM `project` where incharge="'.$db->clean($user_id).'" AND date_contract IS NOT NULL ORDER BY date_contract DESC ');
                                while($rYr = $db->fetch_array($qYr)):
                            ?>
                             <option value="<?php echo functions::encode($rYr['dt'])?>" <?php if($proj_date==$rYr['dt'])echo 'selected="selected"';?>><?php echo $rYr['dt']?></option>
                             <?php endwhile;?>
                             </select>
                        </td>
                      </tr>
                    </table>
                  </div>  

                                <table class="table table-striped" border="0" style="font-size:13px; font-family:Tahoma;">
                                    <?php
                                        $qProj = $db->select('project','*',$arrVal,'ORDER BY proj_name');
                                        $num_record = $db->getValue('project','count(*)',$arrVal);
                                        $totalProj_cost=0; $totalVat=0; $totalEwt=0; $totalMpF=0; $totalGenover=0; $totalProfit=0; $totalTechnical=0; $totalConsultancy=0; $totalBudget=0;
                                        $totalCommitment=0; $totalFinders=0;
                                        while($rProj = $db->fetch_array($qProj)):
                                        $amount=0;
                                        $po_amount=0;
                                        $dateStart = $rProj['date_start'];
                                        $dateCompletion = $rProj['date_completion'];  
                                        $dateReviseCompletion = $rProj['date_revise_completion'];
                                        $dateCompleted = $rProj['date_completed'];
                                        $totalProj_cost += $proj_cost = $rProj['proj_cost'];
                                        $totalVat += $totalVat = $vat12 = ($proj_cost / 1.12) * .12;
                                        $totalEwt += $ewt = ($proj_cost / 1.12) * .02;
                                        $totalMpF += $mpFee = $proj_cost * .008;
                                        $totalGenover += $genover = $proj_cost * .12;
                                        $totalProfit += $profit = $proj_cost * .10;
                                        $totalCommitment += $commitment = $proj_cost * ($rProj['commitment'] / 100);
                                        $totalConsultancy += $consultancy = $proj_cost * ($rProj['consultancy'] / 100);
                                        $totalFinders += $finders = $proj_cost * ($rProj['finders'] / 100);
                                        $totalTechnical += $technical = $proj_cost * ($rProj['technical'] / 100);
                                        
                                        $totalBudget += $budget = $proj_cost - ($vat12 + $ewt + $mpFee + $genover + $profit + $commitment + $consultancy + $finders + $technical);
                                    ?>
                                      <tr>
                                          <td colspan="11"><strong><?php echo $rProj['proj_name'];?></strong></td>
                                        </tr>
                                        <tr>
                                            <td width="5%"><strong>Project Cost</strong></td>
                                            <td width="5%"><strong>VAT<br>(12%)</strong></td>
                                            <td width="5%"><strong>EWT<br>(2%)</strong></td>
                                            <td width="5%"><strong>MPFee<br>(0.80%)</strong></td>
                                            <td width="5%"><strong>GenOver<br>(12%)</strong></td>
                                            <td width="5%"><strong>Profit<br>(10%)</strong></td>
                                            <td width="5%"><strong>Commit<br>(<?php echo $rProj['commitment'];?>%)</strong></td>
                                            <td width="5%"><strong>Consultancy<br>(<?php echo $rProj['consultancy'];?>%)</strong></td>
                                            <td width="5%"><strong>Finders<br>Fee<br>(<?php echo $rProj['finders'];?>%)</strong></td>
                                            <td width="5%"><strong>Technical<br>Fee(<?php echo $rProj['technical'];?>%)</strong></td>
                                            <td width="5%"><strong>Total Budget<br>For Operation</strong></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo functions::formatMoney($rProj['proj_cost']);?> </i></td>
                                            <td><?php echo functions::formatMoney($vat12);?></td>
                                            <td><?php echo functions::formatMoney($ewt);?></td>
                                            <td><?php echo functions::formatMoney($mpFee);?></td>
                                            <td><?php echo functions::formatMoney($genover);?></td>
                                            <td><?php echo functions::formatMoney($profit);?></td>
                                            <td><?php echo functions::formatMoney($commitment);?></td>
                                            <td><?php echo functions::formatMoney($consultancy);?></td>
                                            <td><?php echo functions::formatMoney($finders);?></td>
                                            <td><?php echo functions::formatMoney($technical);?></td>
                                            <td><?php echo functions::formatMoney($budget);?></td>
                                        </tr>
                                      <tr>
                                          <td colspan="11" height="50" valign="center">&nbsp;</td>
                                        </tr>
                                    <?php endwhile;?>
                                      <tr>
                                          <td colspan="11"><strong>Total of <?php echo $proj_date?></strong></td>
                                        </tr>
                                        <tr>
                                            <td width="5%"><strong>Project Cost</strong></td>
                                            <td width="5%"><strong>VAT<br>(12%)</strong></td>
                                            <td width="5%"><strong>EWT<br>(2%)</strong></td>
                                            <td width="5%"><strong>MPFee<br>(0.80%)</strong></td>
                                            <td width="5%"><strong>GenOver<br>(12%)</strong></td>
                                            <td width="5%"><strong>Profit<br>(10%)</strong></td>
                                            <td width="5%"><strong>Commit<br>(0%)</strong></td>
                                            <td width="5%"><strong>Consultancy</strong></td>
                                            <td width="5%"><strong>Finders<br>Fee</strong></td>
                                            <td width="5%"><strong>Technical<br>Fee</strong></td>
                                            <td width="5%"><strong>Total Budget<br>For Operation</strong></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo functions::formatMoney($totalProj_cost);?> </i></td>
                                            <td><?php echo functions::formatMoney($totalVat);?></td>
                                            <td><?php echo functions::formatMoney($totalEwt);?></td>
                                            <td><?php echo functions::formatMoney($totalMpF);?></td>
                                            <td><?php echo functions::formatMoney($totalGenover);?></td>
                                            <td><?php echo functions::formatMoney($totalProfit);?></td>
                                            <td><?php echo functions::formatMoney($totalCommitment);?></td>
                                            <td><?php echo functions::formatMoney($totalConsultancy);?></td>
                                            <td><?php echo functions::formatMoney($totalFinders);?></td>
                                            <td><?php echo functions::formatMoney($totalTechnical);?></td>
                                            <td><?php echo functions::formatMoney($totalBudget);?></td>
                                        </tr>
                                </table>    

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
<script>function selDt(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?pdt="+PiEwgD}</script>
<!-- end: JavaScript-->
</body>
</html>