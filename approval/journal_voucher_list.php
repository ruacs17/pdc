<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
$txbMon='';$txSearch='';$txbYear='';$txProj='';
$arr = array();
if( isset($_POST['btnSearch']) ){
    $arr = array();
    $_SESSION['jvProj'] = ( isset($_POST['selProj']) && !empty($_POST['selProj']) ) ? $_POST['selProj'] : '';
    $_SESSION['jvMon'] = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $_SESSION['jvYear'] = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? $_POST['bdYear'] : '';
    functions::sendTo($_SERVER['PHP_SELF']);
}

#$q = $db->select('journal_voucher jv','*',$arr,$searchQ.'ORDER BY jv_date DESC');

$jvDel = ( isset($_REQUEST['jvDel']) ) ? functions::decode($_REQUEST['jvDel']) : '';
if($jvDel){
    $db->delete('journal_voucher',array('jv_id'=>$jvDel));
    functions::sendTo($_SERVER['PHP_SELF']);
}
$searchQ='';
$txSearch = ( isset($_POST['txSearch']) && !empty($_POST['txSearch']) ) ? $_POST['txSearch'] : '';
$txProj = ( isset($_SESSION['jvProj']) && !empty($_SESSION['jvProj']) ) ? $_SESSION['jvProj'] : '';
$txbMon = ( isset($_SESSION['jvMon']) ) ? $_SESSION['jvMon'] : date('m');
$txbYear = ( isset($_SESSION['jvYear']) ) ? $_SESSION['jvYear'] : date('Y');


if($txProj)
    $arr = array_merge($arr,array('proj_id'=>$txProj));

if($txbMon && $txbYear)
    $arr = array_merge($arr,array('LEFT(jv_date,7)'=>$txbYear.'-'.$txbMon));
else if($txbMon)
    $arr = array_merge($arr,array('SUBSTRING(jv_date,6,2)'=>$txbMon));
elseif($txbYear)
    $arr = array_merge($arr,array('LEFT(jv_date,4)'=>$txbYear)); 
if($txSearch) 
    $searchQ = (count($arr)>0) ? ' AND jv_no="'.$db->clean($txSearch).'"' : ''; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Journal Voucher</title>
    <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
    <link id="base-style" href="../css/loader.css" rel="stylesheet">
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
    <style>
        /* Style the header */
        .header {
          background: #CCC;
        }
        /* The sticky class is added to the header with JS when it reaches its scroll position */
        .sticky {
          position: fixed;
          top: 0;
          width: 97%
        }
        .hideit{
            display:none;
        }
        .brdrNone{
            border:none;
        }
    </style>
    <!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div id="spinner"></div>
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Journal Voucher</h2>
        </div>
        <div class="box-content">
            <form method="post">
                <div align="center">
                    <div align="right"><a id="poUnserved" href="#" class="btn btn-info btn-small btn-setting thickbox" onclick="showThis(this.id,'journal_voucher_manage.php?','Journal Voucher Form')">Add Journal Voucher</a></div>
                    <table border="0" align="center">
                        <tr>
                            <td colspan="3">Journal Number: <input type="text" name="txSearch" value="<?php echo $txSearch;?>">&nbsp;<input type="submit" name="btnSearch2" id="btnSearch2" value="Search" class="btn btn-small btn-primary">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="3"><hr width="100%"></td>
                        </tr>
                        <tr>
                            <td width="22%">
                                <div align="center">
                                    <select name="bdYear" id="bdYear" style="width:90px;">
                                        <option value="">All Year</option>
                                        <?php
                                            $qYr = $db->select('journal_voucher','DISTINCT LEFT(jv_date,4) as yr',array(),'ORDER BY jv_date DESC');
                                            while($rYr = $db->fetch_array($qYr)):
                                        ?>
                                        <option value="<?php echo $rYr['yr']?>" <?php if($txbYear==$rYr['yr'])echo 'selected="selected"';?>><?php echo $rYr['yr']?></option>
                                        <?php endwhile;?>
                                    </select>
                                    <select name="bdMon" id="bdMon" style="width:95px;">
                                        <option value="">All Month</option>
                                        <option value="01" <?php if($txbMon=='01')echo 'selected="selected"';?>>Jan</option>
                                        <option value="02" <?php if($txbMon=='02')echo 'selected="selected"';?>>Feb</option>
                                        <option value="03" <?php if($txbMon=='03')echo 'selected="selected"';?>>Mar</option>
                                        <option value="04" <?php if($txbMon=='04')echo 'selected="selected"';?>>Apr</option>
                                        <option value="05" <?php if($txbMon=='05')echo 'selected="selected"';?>>May</option>
                                        <option value="06" <?php if($txbMon=='06')echo 'selected="selected"';?>>Jun</option>
                                        <option value="07" <?php if($txbMon=='07')echo 'selected="selected"';?>>Jul</option>
                                        <option value="08" <?php if($txbMon=='08')echo 'selected="selected"';?>>Aug</option>
                                        <option value="09" <?php if($txbMon=='09')echo 'selected="selected"';?>>Sep</option>
                                        <option value="10" <?php if($txbMon=='10')echo 'selected="selected"';?>>Oct</option>
                                        <option value="11" <?php if($txbMon=='11')echo 'selected="selected"';?>>Nov</option>
                                        <option value="12" <?php if($txbMon=='12')echo 'selected="selected"';?>>Dec</option>
                                    </select>
                                </div>
                            </td>
                            <td width="55%">
                                <div align="left">
                                    <select name="selProj" id="selProj" data-rel="chosen" style="width:780px;">
                                        <option value="">All Project</option>
                                        <?php $qProj = $db->select('project p, journal_voucher jv','p.proj_id,proj_name',array(),'WHERE p.proj_id=jv.proj_id GROUP BY p.proj_id,proj_name ORDER BY proj_name');
                                            while($rProj = $db->fetch_array($qProj)):
                                        ?>
                                        <option value="<?php echo $rProj['proj_id']?>" <?php if($txProj==$rProj['proj_id'])echo 'selected="selected"';?>><?php echo strtoupper($rProj['proj_name']);?></option>
                                        <?php endwhile;?>
                                    </select>
                                </div>
                            </td>
                            <td width="8%">
                                <div align="center">
                                    <input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-small btn-primary">
                                </div>
                            </td>
                        </tr>
                        <tr><td colspan="3"><hr width="100%"></td></tr>
                    </table>
                    <table class="table table-bordered table-stripped" style="font-size:12px;">
                        <tr style="background-color:#E4E1E1">
                            <th width="15%"><div align="left">Date</div></th>
                            <th width="15%"><div align="left">No.</div></th>
                            <th width="60%"><div align="left">Project</div></th>
                            <th width="10%"><div align="center">Options</div></th>
                        </tr>
                        <?php
                        $q = $db->select('journal_voucher jv','*',$arr,$searchQ.'ORDER BY jv_date DESC');
                        while($r = $db->fetch_array($q)):
                            $rID = $r['jv_id'];
                        ?>
                        <tr>
                            <td><div aling="left"><?php echo functions::datearr($r['jv_date']); ?></div></td>
                            <td><div aling="left"><?php echo $r['jv_no']?></div></td>
                            <td><div aling="left"><?php echo $db->getValue('project','proj_name',array('proj_id'=>$r['proj_id'])); ?></div></td>
                            <td>
                                <div aling="left" style="padding-left:20px;">
                                    <a id="detail<?php echo $rID?>" class="btn btn-mini btn-info thickbox" title="Journal Detail" data-rel="tooltip" onclick="showThis(this.id,'journal_voucher_manage_item.php?jv=<?php echo functions::encode($rID);?>','Journal Details')"><i class="halflings-icon white plus-sign"></i></a>
                                    <a id="del<?php echo $rID;?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this Journal" data-rel="tooltip" href="?jvDel=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script type="text/javascript">$(window).ready(function(){$("#spinner").fadeOut("slow");});</script>
<script type="text/javascript">function delt(){if(confirm('Do you want to remove this record?')){return true;}else{return false;}}</script>
<script>
// When the user scrolls the page, execute myFunction
window.onscroll = function() {myFunction()};
window.onload = function(){header.classList.add("hideit");};

// Get the header
var header = document.getElementById("myHeader");

// Get the offset position of the navbar
var sticky = header.offsetTop;

// Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
function myFunction() {
    if (window.pageYOffset > sticky) {
        header.classList.add("sticky");
        header.classList.remove("hideit");
    } else {
        header.classList.remove("sticky");
        header.classList.add("hideit");
    }
}
</script>
<!-- end: JavaScript-->
</body>
</html>