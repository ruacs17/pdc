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
$mha_id = (isset($_REQUEST['mha']) && !empty($_REQUEST['mha']) ) ? functions::decode($_REQUEST['mha']) : 0;
$empid = (isset($_REQUEST['empid']) && !empty($_REQUEST['empid']) ) ? functions::decode($_REQUEST['empid']) : 0;
$emp_readonly='';

function dateRange($start_date, $end_date, $date_from_user){
    // Convert to timestamp
    $start_ts = strtotime($start_date);
    $end_ts = strtotime($end_date);
    $user_ts = strtotime($date_from_user);
    // Check that user date is between start & end
    return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
}


$q = $db->select('medicine_healthcare_availment','*',array('mha_id'=>$mha_id));
$r = $db->fetch_array($q);

$xdate = explode('-',$r['mha_date']);
$mon = isset($xdate[1]) ? $xdate[1] : "";
$day = isset($xdate[2]) ? $xdate[2] : "";
$year = isset($xdate[0]) ? $xdate[0] : "";
$emp_id = $r['emp_id'];
$confirmed_by = $r['confirmed_by'];
$mha_purpose = $r['mha_purpose'];
$mha_amount = $r['mha_amount'];
$mhd_id = $r['mhd_id'];
if($empid){
    $emp_readonly='readonly';
    $emp_id = $empid;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>HEALTHCARE AVAILMENT</title>
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
    <?php
    if( isset($_POST['btnSave']) ){
        $mhd_id = ( isset($_POST['txHC']) && !empty($_POST['txHC']) ) ? $_POST['txHC'] : '';
        $mon = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
        $day = ( isset($_POST['bdDay']) && !empty($_POST['bdDay']) ) ? $_POST['bdDay'] : '';
        $year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
        $mha_date = ( $mon && $day && $year ) ? $year.'-'.$mon.'-'.$day : NULL;
        $emp_id = ( isset($_POST['txEmpID']) && !empty($_POST['txEmpID']) ) ? $_POST['txEmpID'] : '';
        $confirmed_by = ( isset($_POST['txConfirmedBy']) && !empty($_POST['txConfirmedBy']) ) ? $_POST['txConfirmedBy'] : '';
        $mha_purpose = ( isset($_POST['txPurpose']) && !empty($_POST['txPurpose']) ) ? $_POST['txPurpose'] : '';
        $mha_amount = ( isset($_POST['txAmount']) && !empty($_POST['txAmount']) ) ? functions::moneyToDouble($_POST['txAmount']) : 0;

        $mhd_start = $db->getValue('medicine_healthcare_duration','mhd_start',array('mhd_id'=>$mhd_id));
        $mhd_end = $db->getValue('medicine_healthcare_duration','mhd_end',array('mhd_id'=>$mhd_id));


        if( $mha_date && $emp_id && $confirmed_by && $mha_amount && $mhd_id ){

            if( dateRange($mhd_start, $mhd_end, $mha_date)==0 ){
                functions::say('Date Availment error!');
            }
            else if($mha_id){
                $db->update('medicine_healthcare_availment',array('mhd_id'=>$mhd_id,'mha_date'=>$mha_date,'emp_id'=>$emp_id,'confirmed_by'=>$confirmed_by,'mha_purpose'=>$mha_purpose,'mha_amount'=>$mha_amount),array('mha_id'=>$mha_id));
                functions::say('Changes saved!');
                functions::sendTo($_SERVER['PHP_SELF'].'?mha='.functions::encode($mha_id));
            }
            else{
                $mha_id = $db->insert('medicine_healthcare_availment',array('mhd_id'=>$mhd_id,'mha_date'=>$mha_date,'emp_id'=>$emp_id,'confirmed_by'=>$confirmed_by,'mha_purpose'=>$mha_purpose,'mha_amount'=>$mha_amount));
                if($mha_id){
                    functions::say('Record Successfully Added!');
                    if($empid)
                        functions::sendTo($_SERVER['PHP_SELF'].'?empid='.functions::encode($empid));
                    else
                        functions::sendTo($_SERVER['PHP_SELF']);
                }
            }
        }
        else{
            functions::say('Please fill up the form properly!');
        }
    }
    ?>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid sortable">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Healthcare Availment Form</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <input type="hidden" name="poid" id="poid" value="<?php echo functions::encode($po_id);?>">
                <table width="65%" align="center" border="0" style="background-color:#E4E1E1">
                    <tr><td>&nbsp;</td></tr>
                    <tr>
                        <td><br>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Healthcare Period</label>
                                <div class="controls">
                                    <select name="txHC" id="txHC" style="width:300px;" required>
                                        <?php $qhd = $db->select('medicine_healthcare_duration','*',array('active_status'=>1),'ORDER BY mhd_start');
                                        while($rhd = $db->fetch_array($qhd)):
                                        ?>
                                        <option value="<?php echo $rhd['mhd_id']?>" <?php if($mhd_id==$rhd['mhd_id'])echo 'selected="selected"';?>><?php echo functions::datearr($rhd['mhd_start']).' - '.functions::datearr($rhd['mhd_end']).' ('.functions::formatMoney($rhd['mhd_amount']).')';?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <span class="help-inline warning" style="font-weight:bold;" id="msgConfirmedBy" name="msgConfirmedBy"></span>
                              </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Availment Date</label>
                                <div class="controls">
                                    <select name="bdMon" id="bdMon" style="width:80px;" required>
                                        <option value="">Month</option>
                                        <option value="01" <?php if($mon=='01')echo 'selected="selected"';?>>Jan</option>
                                        <option value="02" <?php if($mon=='02')echo 'selected="selected"';?>>Feb</option>
                                        <option value="03" <?php if($mon=='03')echo 'selected="selected"';?>>Mar</option>
                                        <option value="04" <?php if($mon=='04')echo 'selected="selected"';?>>Apr</option>
                                        <option value="05" <?php if($mon=='05')echo 'selected="selected"';?>>May</option>
                                        <option value="06" <?php if($mon=='06')echo 'selected="selected"';?>>Jun</option>
                                        <option value="07" <?php if($mon=='07')echo 'selected="selected"';?>>Jul</option>
                                        <option value="08" <?php if($mon=='08')echo 'selected="selected"';?>>Aug</option>
                                        <option value="09" <?php if($mon=='09')echo 'selected="selected"';?>>Sep</option>
                                        <option value="10" <?php if($mon=='10')echo 'selected="selected"';?>>Oct</option>
                                        <option value="11" <?php if($mon=='11')echo 'selected="selected"';?>>Nov</option>
                                        <option value="12" <?php if($mon=='12')echo 'selected="selected"';?>>Dec</option>
                                    </select>
                                    <select name="bdDay" id="bdDay" style="width:60px;" required>
                                        <option value="">Day</option>
                                        <?php for($i=1;$i<=31;$i++):?>
                                        <option value="<?php echo ($i<10)? '0'.$i : $i;?>" <?php if($i==$day)echo 'selected="selected"';?>><?php echo $i;?></option>
                                        <?php endfor;?>
                                    </select>
                                    <select name="bdYear" id="bdYear" style="width:70px;" required>
                                        <option value="">Year</option>
                                        <?php for($y=(date('Y') + 1);$y>=2012;$y--):?>
                                        <option value="<?php echo $y;?>" <?php if($y==$year)echo 'selected="selected"';?>><?php echo $y;?></option>
                                        <?php endfor;?>
                                    </select>
                                    <span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Availed By</label>
                                <div class="controls">
                                    <select name="txEmpID" id="txEmpID" data-rel="chosen" style="width:400px;" <?php echo $emp_readonly ?> required>
                                        <option value="">--select--</option>
                                        <?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
                                        while($rEU = $db->fetch_array($qEU)):
                                        ?>
                                        <option value="<?php echo $rEU['emp_id']?>" <?php if($emp_id==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <span class="help-inline warning" style="font-weight:bold;" id="msgEmpID" name="msgEmpID"></span>
                              </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="textarea2">Amount</label>
                                <div class="controls">
                                    <input type="text" style="width:120px;" name="txAmount" id="txAmount" value="<?php echo ($mha_amount) ? functions::formatMoney($mha_amount) : '';?>" onkeyup="FormatCurrency(this);" required>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="textarea2">Purpose</label>
                                <div class="controls">
                                    <textarea name="txPurpose" required><?php echo $mha_purpose ?></textarea>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="inputSuccess">Confirmed By</label>
                                <div class="controls">
                                    <select name="txConfirmedBy" id="txConfirmedBy" data-rel="chosen" style="width:400px;" required>
                                        <option value="">--select--</option>
                                        <?php $qEU = $db->select('employee','*',array(),'ORDER BY lname,fname');
                                        while($rEU = $db->fetch_array($qEU)):
                                        ?>
                                        <option value="<?php echo $rEU['emp_id']?>" <?php if($confirmed_by==$rEU['emp_id'])echo 'selected="selected"';?>><?php echo strtoupper($rEU['lname'].', '.$rEU['fname']);?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <span class="help-inline warning" style="font-weight:bold;" id="msgConfirmedBy" name="msgConfirmedBy"></span>
                              </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="controls">
                                <input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary">
                            </div>
                        </td>
                    </tr> 
                    <tr><td>&nbsp;</td></tr>           
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
        $('#msgBdate').html("");
        $('#msgEmpID').html("");
        $('#msgConfirmedBy').html("");
        
        if( $('#bdYear').val()=="" || $('#bdMon').val()=="" || $('#bdDay').val()=="" ){
            $('#msgBdate').html("Date Required!");
            res=false;
        }
        else if( $('#txEmpID').val()=="" ){
            $('#txEmpID').focus();
            $('#msgEmpID').html("Availed By Required!");
            res=false;
        }
        else if( $('#txConfirmedBy').val()=="" ){
            $('#msgConfirmedBy').html("Confirmed By Required!");
            $('#txConfirmedBy').focus();
            res=false;
        }
        else
            res=true;

        return res;
    });
});
</script>
<!-- end: JavaScript-->
</body>
</html>