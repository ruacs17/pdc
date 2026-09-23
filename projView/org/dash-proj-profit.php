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
  $arr = array('incharge'=>$user_id);
  $proj_date = (isset($_REQUEST['pdt']) && !empty($_REQUEST['pdt']) ) ? functions::decode($_REQUEST['pdt']) : 0;
  $sort = (isset($_REQUEST['sort']) && !empty($_REQUEST['sort']) ) ? functions::decode($_REQUEST['sort']) : 0;

  if($proj_date)
  	$arr = array('left(date_contract,4)'=>$proj_date,'incharge'=>$user_id);

  $orderBy = "ORDER BY proj_name ASC";
  if($sort){
    if($sort=='cd')
      $orderBy = "ORDER BY date_contract DESC";
    elseif($sort=='ds')
      $orderBy = "ORDER BY date_start DESC";
    elseif($sort=='date_completion')
      $orderBy = "ORDER BY date_completion DESC";
    elseif($sort=='date_revise')
      $orderBy = "ORDER BY date_revise DESC";
    elseif($sort=='proj_cost')
      $orderBy = "ORDER BY proj_cost DESC";
    elseif($sort=='proj_name')
      $orderBy = "ORDER BY proj_name ASC";
  }
  
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
            <div class="row-fluid sortable">
              <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Project Report</h2>
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
                  <table width="80%" align="center" border="0" class="table table-bordered table-hover bootstrap-datatable datatable" style="font-size: 12px;">
                    <tr>
                        <th width="18%"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('proj_name')?>">Project Name</a></th>
                        <th width="7%"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('cd')?>">Contract Date</a></th>
                        <th width="7%"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('ds')?>">Date Started</a></th>
                        <th width="5%">Contract Duration<br>(Days)</th>
                        <th width="7%"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('date_completion')?>">Target Date Completion</a></th>
                        <th width="5%">Approved Time Extension</th>
                        <th width="7%"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('date_revise')?>">Revised Target Date of Completion</a></th>
                        <th width="9%">Actual Cost</th>
                        <th width="9%"><a href="?pdt=<?php echo functions::encode($proj_date)?>&sort=<?php echo functions::encode('proj_cost')?>">Contract Amount</a></th>
                        <th width="5%">Wt(%)</th>
                        <th width="5%">Days Elapse</th>
                        <th width="5%">Days Remain</th>
                    </tr>
                    <?php
					  	          $totalCost=0;
                      	$qProj = $db->select('project','*',$arr,$orderBy);
                        $allProjCost = $db->getValue('project','round(sum(proj_cost),2)',$arr);
                        $allWt=0;
						            while($rProj = $db->fetch_array($qProj)):
						            $totalCost += $rProj['proj_cost'];

                        $amount=0;
                        $po_amount=0;
                        $dateStart = $rProj['date_start'];
                        $dateCompletion = $rProj['date_completion'];  
                        $dateReviseCompletion = $rProj['date_revise_completion'];
                        $dateCompleted = $rProj['date_completed'];
                                            $non_po=$db->getValue('voucher_detail','sum(amount)',array('proj_id'=>$rProj['proj_id']));
                        $daysExtension = functions::date_diff($dateCompletion,$dateReviseCompletion);
                        $daysDuration = functions::date_diff($dateStart,$dateCompletion);

                        $daysElapsed=0;
                        $daysRemaining=0;
                                          
                        if($dateStart <= date('Y-m-d')){

                              if( isset($rProj['date_completed']) ){
                                    $daysElapsed = functions::date_diff($dateStart,$rProj['date_completed']);
                                    if($rProj['date_revise_completion'])
                                          $daysRemaining = functions::date_diff($rProj['date_completed'],$dateReviseCompletion);
                                    else if($rProj['date_completion'])
                                          $daysRemaining = functions::date_diff($rProj['date_completed'],$rProj['date_completion']);
                              }
                              else if( !isset($rProj['date_completed']) ){
                                    $daysElapsed = functions::date_diff($dateStart,date('Y-m-d'));
                                    if($rProj['date_revise_completion'])
                                          $daysRemaining = functions::date_diff(date('Y-m-d'),$dateReviseCompletion);
                                    else if($rProj['date_completion'])
                                          $daysRemaining = functions::date_diff(date('Y-m-d'),$rProj['date_completion']);
                              }
                        }
                        $qPO_amount = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) FROM po, po_item WHERE po.po_id=po_item.po_id AND po.proj_id="'.$db->clean($rProj['proj_id']).'"');
                        $po_amount = $db->result();
                        $amount = $non_po + $po_amount;
                        $bgColor='';
                        $consumedPercent=0;
                        $allWt += $wt = ($rProj['proj_cost'] / $allProjCost) * 100;

                        if($amount && $rProj['proj_cost'])
                            $consumedPercent = ($amount/$rProj['proj_cost']) * 100;
					        ?>
                    <tr>
                        <td><?php echo $rProj['proj_name'];?></td>
                        <td><?php echo functions::datearr($rProj['date_contract']);?> </i></td>
                        <td><?php echo functions::datearr($rProj['date_start']);?> </i></td>
                        <td><?php echo $daysDuration;?></td>
                        <td><?php echo functions::datearr($dateCompletion);?></td>
                        <td><?php echo $daysExtension;?></td>
                        <td><?php echo functions::datearr($dateReviseCompletion);?></td>
                        <td><?php echo functions::formatMoney($amount);?></td>
                        <td><?php echo functions::formatMoney($rProj['proj_cost']);?></td>
                        <td><?php echo functions::formatMoney($wt);?>%</td>
                        <td><?php echo $daysElapsed;?></td>
                        <td><?php echo $daysRemaining;?></td>
                    </tr>
                    <?php endwhile;?>
                    <tr>
                      <td height="30" colspan="7"><div align="right"><strong>Total</strong></div></td>
                      <td><strong><?php echo functions::formatMoney($totalCost);?></strong></td>
                      <td><?php echo $allWt;?>%</td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                      
                  </table>     

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
<script>function selDt(PiEwgD){window.location="dash-proj-profit.php?pdt="+PiEwgD}</script>
<!-- end: JavaScript-->

</body>
</html>

