<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="12" ){
  header("Location: ../");
  die();
}
  $username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
  $user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  $db = new Database();
  $name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
  #$logs = new Logs();
  #$logs->save('visit');
  $selMon = date('m');
  $selYear = date('Y');
  $selDay='';
  $project_id = ( isset($_REQUEST['prjID']) && !empty($_REQUEST['prjID']) ) ? functions::decode($_REQUEST['prjID']) : '';
  $mnth = ( isset($_REQUEST['mnth']) && !empty($_REQUEST['mnth']) ) ? functions::decode($_REQUEST['mnth']) : '';
  $account_type = ( isset($_REQUEST['at']) && !empty($_REQUEST['at']) ) ? functions::decode($_REQUEST['at']) : '';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Account Statement Detail</title>
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
  </head>
  <body>
            <!-- body content: start here-->
            <div class="row-fluid">
                <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Income Statement Add Form</h2>
                    </div>
                    <div class="box-content">

                    <form class="form-horizontal" method="post">
                    <input type="hidden" name="prjID" id="prjID" value="<?php echo functions::encode($project_id)?>">
                      <br><br><br>
                      <table width="50%" align="center" border="0" class="table table-bordered table-hover" >
                        <tr style="background-color:#E4E1E1">
                          <th width="25%"><div align="center">Transaction Date</div></th>
                          <th width="25%"><div align="center">Account</div></th>
                          <th width="25%"><div align="center">Account</div></th>
                          <th width="25%"><div align="center">Amount</div></th>
                        </tr>
                        <?php 
                          $qShow = $db->select('project_income','*',array('proj_id'=>$project_id,'left(pi_date,7)'=>$mnth),'ORDER BY pi_date');
                          while($rShow = $db->fetch_array($qShow)):
						              $atID = $db->getValue('account_statement','account_type',array('project_income'=>$rShow['pi_id']));
                        ?>
                        <tr>
                          <td><div align="center"><?php echo $rShow['name']?></div></td>
                          <td><div align="center"><?php echo functions::datearr($rShow['pi_date'])?></div></td>
                          <td><div align="center"><?php echo $db->getValue('voucher_type','vt_name',array('vt_id'=>$atID));?></div></td>
                          <td><div align="center"><?php echo functions::formatMoney($rShow['amount'])?></div></td>
                        </tr>
                        <?php endwhile;?>
                        <tr>
                          <td colspan="4">&nbsp;</td>
                        </tr>
                        <tr>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td><div align="center"><strong><?php echo functions::formatMoney($db->getValue('project_income','sum(amount)',array('proj_id'=>$project_id,'left(pi_date,7)'=>$mnth)))?></strong></div></td>
                        </tr>
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
  $('#btnSave').click(function(){
  $('#msgDate').html("");
  $('#msgAmount').html("");

    if( $('#bdYear').val()=="" || $('#bdMon').val()=="" || $('#bdDay').val()=="" ){
      $('#msgDate').html("Date Required!");
      res=false;
    }
    else if( $('#txTran_amount').val()=="" ){
      $('#txTran_amount').focus();
      $('#msgAmount').html("Amount Required!");
      res=false;
    }
    /*else if( $('#txVoType').val()=="" ){
      $('#txVoType').focus();
      $('#msgAccountType').html("Specify Deposit Account!");
      res=false;
    }*/
    else
      res=true;

    return res;
  });
});
    function delt(){
      if(confirm('Do you want to remove this Item?'))
        return true;
      else
        return false; 
    }
</script>
<!-- end: JavaScript-->
</body>
</html>