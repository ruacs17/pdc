<?php require_once('authorize.php');
  $username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
  $user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
  
  $db = new Database();
  $name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
  #$logs = new Logs();
  #$logs->save('visit');

if( isset($_POST['btnCreate']) ){

  $lname = ( isset($_POST['lname']) && !empty($_POST['lname']) ) ? trim($_POST['lname']) : '';
  $fname = ( isset($_POST['fname']) && !empty($_POST['fname']) ) ? trim($_POST['fname']) : '';
  $mname = ( isset($_POST['mname']) && !empty($_POST['mname']) ) ? trim($_POST['mname']) : '';
  $marStat = ( isset($_POST['marStat']) && !empty($_POST['marStat']) ) ? trim($_POST['marStat']) : '';
  $empStat = ( isset($_POST['empStat']) && !empty($_POST['empStat']) ) ? trim($_POST['empStat']) : '';
  $txPosition = ( isset($_POST['txPosition']) && !empty($_POST['txPosition']) ) ? trim($_POST['txPosition']) : '';
  $txAddress = ( isset($_POST['txAddress']) && !empty($_POST['txAddress']) ) ? trim($_POST['txAddress']) : '';
  $txMobile = ( isset($_POST['txMobile']) && !empty($_POST['txMobile']) ) ? trim($_POST['txMobile']) : '';


  $txbMon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '00';
  $txbDay = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '00';
  $txbYear = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '0000';

  $txBdate = $txbYear.'-'.$txbMon.'-'.$txbDay;

  $txbHMon = ( isset($_POST['bdHMon']) && !empty($_POST['bdHMon']) ) ? $_POST['bdHMon'] : '00';
  $txbHDay = ( isset($_POST['bdHDay']) && !empty($_POST['bdHDay']) ) ? $_POST['bdHDay'] : '00';
  $txbHYear = ( isset($_POST['bdHYear']) && !empty($_POST['bdHYear']) ) ? $_POST['bdHYear'] : '0000';

  $txHdate = $txbHYear.'-'.$txbHMon.'-'.$txbHDay;  


  if( !empty($lname) && !empty($fname) && !empty($marStat) && !empty($txPosition) && !empty($empStat) ){
      $db->insert('equip_user',array('fname'=>$fname,'lname'=>$lname,'mname'=>$mname,'bdate'=>$txBdate,'address'=>$txAddress,'position'=>$txPosition,'mobile'=>$txMobile,'marital_status'=>$marStat,'date_hired'=>$txHdate,'emp_status'=>$empStat));
      functions::sendTo($_SERVER['PHP_SELF']);
  }

  
}


$qPosition = $db->query('SELECT DISTINCT position FROM equip_user');
$namesPosition='';
  while($rPosition=$db->fetch_array($qPosition)):
      $string = preg_replace("/'/",'"',$rPosition['position']);
      $namesPosition .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
  endwhile;
$namesPosition .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- start: Meta -->
    <meta charset="utf-8">
      <title>Equipment Add Classification</title>
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
              <script src="../js/inputInt.js"></script>
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
              <style>
              .tdSpace{padding: 12px 0px 4px 0px;}
              .tdElmntSpace{padding: 12px 0px 0px 30px;}
              </style>
  </head>
  <body>
            <!-- body content: start here-->
            <div class="row-fluid sortable">
                <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>MR User Creation</h2>
                    </div>
              <div class="box-content">
                  <ul class="nav tab-menu nav-tabs">
                    <li><a href="admin-mr-user-list.php" style="opacity:.9">Personnel List</a></li>
                    <li class="active"><a href="admin-mr-user-add.php">Add Personnel</a></li>
                  </ul>
              </div>
                    <div class="box-content">
                      <div align="center">
                      <form method="post">
                        <div style="width:550px" style="background-color: #f5f5f5;">
                        <table width="50%" border="1" cellspacing="0" cellpadding="0" class="table table-bordered" style="background-color: #f5f5f5;">
                          <tr>
                            <th width="35%" align="left" scope="row"><div align="left" class="tdSpace">Family Name</div></th>
                            <td>
                              <div align="center" class="tdElmntSpace"><input type="text" style="width: 300px;" name="lname" id="lname" value=""></div>
                              <span class="help-inline warning" id="msgLname" style="font-weight:bold;" name="msgLname"></span>
                            </td>
                          </tr>
                          <tr>
                            <th align="left" scope="row"><div align="left" class="tdSpace">First Name</div></th>
                            <td>
                              <div align="center" class="tdElmntSpace"><input type="text" style="width: 300px;" name="fname" id="fname" value=""></div>
                              <span class="help-inline warning" id="msgFname" style="font-weight:bold;" name="msgFname"></span>
                            </td>
                          </tr>
                          <tr>
                            <th align="left" scope="row" class="tdSpace"><div align="left" class="tdSpace">Middle Name</div></th>
                            <td>
                              <div align="center" class="tdElmntSpace"><input type="text" style="width: 300px;" name="mname" id="mname" value=""></div>
                            </td>
                          </tr>
                          <tr>
                            <th align="left" scope="row" class="tdSpace"><div align="left" class="tdSpace">Marital Status</div></th>
                            <td>
                              <div align="center" class="tdElmntSpace">
                                <select name="marStat" id="marStat" style="width: 300px;" >
                                  <option value="">--select--</option>
                                  <option value="Single">Single</option>
                                  <option value="Married">Married</option>
                                  <option value="Widow">Widow</option>
                                  <option value="Widower">Widower</option>
                                </select>
                              </div>
                              <span class="help-inline warning" id="msgStatus" style="font-weight:bold;" name="msgStatus"></span>
                            </td>
                          </tr>
                          <tr>
                            <th align="left" scope="row" class="tdSpace"><div align="left" class="tdSpace">Birth Date</div></th>
                            <td>
                              <div align="left" class="tdElmntSpace">
                              <select name="bdMon" id="bdMon" style="width:80px;">
                                <option value="">Month</option>
                                <option value="01">Jan</option>
                                <option value="02">Feb</option>
                                <option value="03">Mar</option>
                                <option value="04">Apr</option>
                                <option value="05">May</option>
                                <option value="06">Jun</option>
                                <option value="07">Jul</option>
                                <option value="08">Aug</option>
                                <option value="09">Sep</option>
                                <option value="10">Oct</option>
                                <option value="11">Nov</option>
                                <option value="12">Dec</option>
                              </select>
                              <select name="bdDay" id="bdDay" style="width:60px;">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo $i;?>"><?php echo $i;?></option>
                                <?php endfor;?>
                              </select>
                              <select name="bdYear" id="bdYear" style="width:70px;">
                                <option value="">Year</option>
                                <?php for($y=(date('Y')-10);$y>=1930;$y--):?>
                                <option value="<?php echo $y;?>"><?php echo $y;?></option>
                                <?php endfor;?>
                              </select>
                              </div>

                            </td>
                          </tr>
                          <tr>
                            <th align="left" scope="row" class="tdSpace"><div align="left" class="tdSpace">Position</div></th>
                            <td>
                              <div align="left" class="tdElmntSpace">
                                  <input type="text" name="txPosition" id="txPosition" style="width:300px;" value="" class="typeahead" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesPosition;?>]'/>
                              </div>
                              <span class="help-inline warning" id="msgPosition" style="font-weight:bold;" name="msgPosition"></span>
                            </td>
                          </tr>
                          <tr>
                            <th align="left" scope="row" class="tdSpace"><div align="left" class="tdSpace">Address</div></th>
                            <td>
                              <div align="left" class="tdElmntSpace">
                                  <input type="text" name="txAddress" id="txAddress" style="width:300px;" value=""/>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <th align="left" scope="row" class="tdSpace"><div align="left" class="tdSpace">Contact No.</div></th>
                            <td>
                              <div align="left" class="tdElmntSpace">
                                  <input type="text" name="txMobile" id="txMobile" style="width:300px;" value=""/>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <th align="left" scope="row" class="tdSpace"><div align="left" class="tdSpace">Hired Date</div></th>
                            <td>
                              <div align="left" class="tdElmntSpace">
                              <select name="bdHMon" id="bdHMon" style="width:80px;">
                                <option value="">Month</option>
                                <option value="01">Jan</option>
                                <option value="02">Feb</option>
                                <option value="03">Mar</option>
                                <option value="04">Apr</option>
                                <option value="05">May</option>
                                <option value="06">Jun</option>
                                <option value="07">Jul</option>
                                <option value="08">Aug</option>
                                <option value="09">Sep</option>
                                <option value="10">Oct</option>
                                <option value="11">Nov</option>
                                <option value="12">Dec</option>
                              </select>
                              <select name="bdHDay" id="bdHDay" style="width:60px;">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo $i;?>"><?php echo $i;?></option>
                                <?php endfor;?>
                              </select>
                              <select name="bdHYear" id="bdHYear" style="width:70px;">
                                <option value="">Year</option>
                                <?php for($y=date('Y');$y>=1900;$y--):?>
                                <option value="<?php echo $y;?>"><?php echo $y;?></option>
                                <?php endfor;?>
                              </select>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <th align="left" scope="row" class="tdSpace"><div align="left" class="tdSpace">Employee Status</div></th>
                            <td>
                              <div align="center" class="tdElmntSpace">
                                <select name="empStat" id="empStat" style="width: 300px;" >
                                  <option value="">--select--</option>
                                  <option value="Regular">Regular</option>
                                  <option value="Probationary">Probationary</option>
                                  <option value="Part Time">Part Time</option>
                                  <option value="Contractual">Contractual</option>
                                  <option value="Retired">Retired</option>
                                  <option value="Resigned">Resigned</option>
                                  <option value="AWOL">AWOL</option>
                                </select>
                              </div>
                              <span class="help-inline warning" id="msgEmpStatus" style="font-weight:bold;" name="msgEmpStatus"></span>
                            </td>
                          </tr>              
                          <tr>
                            <th align="left" scope="row" class="tdSpace"></th>
                            <td>
                              <div align="left" class="tdElmntSpace">
                                  <input type="submit" name="btnCreate" id="btnCreate" value="Create User" class="btn btn-primary">
                              </div>
                            </td>
                          </tr>
                        </table>
                      </div>







                      </div>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>

<script>
  $(document).ready(function(){
    $('#btnCreate').click(function(){
      var submitStatus = false;
      resetErrorMsg();
      if( $('#lname').val() == ""){
        $('#msgLname').html('Family Name required!');
        $('#lname').focus();
      }     
      else if( $('#fname').val() == ""){
        $('#msgFname').html('First Name required!');
        $('#fname').focus();
      }
      else if( $('#marStat').val() == "" ){
        $('#msgStatus').html('Marital Status required!');
        $('#marStat').focus();
      }
      else if( $('#txPosition').val() == "" ){
        $('#msgPosition').html('Position required!');
        $('#txPosition').focus();
      }
      else if( $('#empStat').val() == "" ){
        $('#msgEmpStatus').html('Employee Status required!');
        $('#empStat').focus();
      }
      else{
        if( confirm("Are all information correct?") )
          submitStatus=true;
      }
      return submitStatus;
    });
    function resetErrorMsg(){
      $('#msgPosition').html('');
      $('#msgStatus').html('');
      $('#msgFname').html('');
      $('#msgLname').html('');
      $('#msgEmpStatus').html('');
    }
  });
</script>

<!-- end: JavaScript-->
</body>
</html>