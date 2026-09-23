<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/databaseHR.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new DatabaseHR();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$eid = (isset($_REQUEST['eid']) && !empty($_REQUEST['eid']) ) ? functions::decode($_REQUEST['eid']) : 0;
$txtEmpNo='';$txtfName='';$txtmName='';$txtlName='';$txtExtName='';$txtNickName='';$bdate='';$txtBirthPlace='';$selGender='';$selCivilStat='';$txCitizenship='';$cert_id='';$txReligion='';$txHeight='';$txWeight='';$txBloodType='';$txTIN='';$txpagibig='';$txphilhealth='';$txtSSS='';$selCurProvince='';$selCurCityMun='';$selCurBrngy='';$curStreet='';$curr_zipcode='';$curTelNo='';$selProvincePerm='';$selCityMunPerm='';$selBrngyPerm='';$permStreet='';$permZipCode='';$permTelNo='';$txEmail='';$txCellphone='';

$txtspfName='';$txtspmName='';$txtsplName='';$txtspExtName='';$txtExtName='';$txtspOccupation='';$txspbusname='';$txtspbusadd='';$txspTelNo='';$txtfrfName='';$txtfrmName='';$txtfrlName='';$txtfrExtName='';$txtmrfName='';$txtmrmName='';$txtmrlName='';
$current_address=''; $permanent_address='';
if($eid){
    $q = $db->select('employee','*',array('emp_id'=>$eid));
    while($r = $db->fetch_array($q)):
        $cert_id = $r['cert_id'];
        $txtEmpNo = $r['emp_no'];
        $txtfName = $r['fname'];
        $txtmName = $r['mname'];
        $txtlName = $r['lname'];
        $txtExtName = $r['extname'];
        $txtNickName = $r['nickname'];
        $bdate = $r['bdate'];
        $txtBirthPlace = $r['bplace'];
        $selGender = $r['gender'];
        $selCivilStat = $r['civil_status'];
        $txCitizenship = $r['citizenship'];
        $txReligion = $r['religion'];
        $txHeight = $r['height'];
        $txWeight = $r['weight'];
        $txBloodType = $r['bloodtype'];
        $txTIN = $r['tin'];
        $txpagibig = $r['pagibig'];
        $txphilhealth = $r['philhealth'];
        $txtSSS = $r['sss'];

        $curStreet = $r['curr_street'];
        $curProvince = $db->getValue('refProvince','provDesc',array('provCode'=>$r['curr_province']));
        $curCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$r['curr_cityMun']));
        $curBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$r['curr_add']));
        $current_address = $curStreet.' '.strtoupper($curBrngy).', '.$curCity.', '.$curProvince.' '.$r['curr_zipcode'];
        $curTelNo = $r['curr_tel'];

        $permStreet = $r['perm_street'];
        $permProvince = $db->getValue('refProvince','provDesc',array('provCode'=>$r['perm_province']));
        $permCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$r['perm_cityMun']));
        $permBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$r['perm_add']));
        $permanent_address = $permStreet.' '.strtoupper($permBrngy).', '.$permCity.', '.$permProvince.' '.$r['perm_zipcode'];
        $permTelNo = $r['perm_tel'];

        $txEmail = $r['email'];
        $txCellphone = $r['cell_no'];

        $txtspfName = $r['sp_fname'];
        $txtspmName = $r['sp_mname'];
        $txtsplName = $r['sp_lname'];
        $txtspExtName = $r['sp_extname'];
        $txtExtName = $r['extname'];
        $txtspOccupation = $r['sp_occupation'];
        $txspbusname = $r['sp_bus_name'];
        $txtspbusadd = $r['sp_bus_address'];
        $txspTelNo = $r['sp_tel_no'];
        $txtfrfName = $r['fr_fname'];
        $txtfrmName = $r['fr_mname'];
        $txtfrlName = $r['fr_lname'];
        $txtfrExtName = $r['fr_extname'];
        $txtmrfName = $r['mr_fname'];
        $txtmrmName = $r['mr_mname'];
        $txtmrlName = $r['mr_lname'];
    endwhile;
}
$fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$cert_id));
$file = ($fileName) ? $fileName : 'blank-pic.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Employee Add</title>
     <!-- end: Meta -->
    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end: Mobile Specific -->
    <!-- start: CSS -->
    <link id="bootstrap-style" href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
    <link id="base-style" href="../css/style.css" rel="stylesheet">
    <link id="base-style-responsive" href="../css/style-responsive.css" rel="stylesheet">
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
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
<table  width="700" border="0" align="center">
    <thead>
        <tr>
            <td>
                <?php
                require_once('../class/print_header.php');
                print_header('PERSONAL DATA SHEET');
                ?><br>
            </td>
        </tr>
    </thead>
    </tbody>
        <tr>
            <td>

<table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;">
    <tr>
        <td colspan="3" style="background-color:#E4E1E1"><div align="center"><strong>I. PERSONAL INFORMATION</strong></div></td>
    </tr>
    <tr>
        <td width="20%"><strong>LAST NAME</strong></td>
        <td width="45%"><?php echo $txtlName;?></td>
        <td rowspan="5"><div align="center"><img width="300" height="300" src="../img_emp/<?php echo $file;?>"></div></td>
    </tr>
    <tr>
        <td><strong>FIRST NAME</strong></td>
        <td><?php echo $txtfName;?></td>
    </tr>
    <tr>
        <td><strong>MIDDLE NAME</strong></td>
        <td><?php echo $txtmName;?></td>
    </tr>
    <tr>
        <td><strong>Suffix (JR, SR, III)</strong></td>
        <td><?php echo $txtExtName;?></td>
    </tr>
    <tr>
        <td><strong>NICK NAME</strong></td>
        <td><?php echo $txtNickName;?></td>
    </tr>
</table>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;">
    <tr>
        <td width="20%"><strong>DATE OF BIRTH</strong></td>
        <td><?php echo functions::datearr($bdate);?>&nbsp;&nbsp;&nbsp;&nbsp; (AGE: <?php echo functions::year_diff($bdate,date('Y-m-d'));?>)</td>
    </tr>
    <tr>
        <td><strong>PLACE OF BIRTH</strong></td>
        <td><?php echo $txtBirthPlace;?></td>
    </tr>
    <tr>
        <td><strong>GENDER</strong></td>
        <td><?php echo $selGender;?></td>
    </tr>
    <tr>
        <td><strong>CIVIL STATUS</strong></td>
        <td><?php echo $selCivilStat;?></td>
    </tr>
    <tr>
        <td><strong>CITIZENSHIP</strong></td>
        <td><?php echo $txCitizenship;?></td>
    </tr>
    <tr>
        <td><strong>HEIGHT (m)</strong></td>
        <td><?php echo $txHeight;?></td>
    </tr>
    <tr>
        <td><strong>WEIGHT (kg)</strong></td>
        <td><?php echo $txWeight;?></td>
    </tr>
    <tr>
        <td><strong>BLOOD TYPE</strong></td>
        <td><?php echo $txBloodType;?></td>
    </tr>
    <tr>
        <td><strong>TIN</strong></td>
        <td><?php echo $txTIN;?></td>
    </tr>
    <tr>
        <td><strong>PAG-IBIG ID</strong></td>
        <td><?php echo $txpagibig;?></td>
    </tr>
    <tr>
        <td><strong>PHILHEALTH No</strong></td>
        <td><?php echo $txphilhealth;?></td>
    </tr>
    <tr>
        <td><strong>SSS No</strong></td>
        <td><?php echo $txtSSS;?></td>
    </tr>
    <tr>
        <td><strong>PRESENT ADDRESS</strong></td>
        <td><?php echo $current_address;?></td>
    </tr>
    <tr>
        <td><strong>TELEPHONE No.</strong></td>
        <td><?php echo $curTelNo;?></td>
    </tr>
    <tr>
        <td><strong>HOME ADDRESS</strong></td>
        <td><?php echo $permanent_address;?></td>
    </tr>
    <tr>
        <td><strong>TELEPHONE No.</strong></td>
        <td><?php echo $permTelNo;?></td>
    </tr>
    <tr>
        <td><strong>E-MAIL ADDRESS</strong></td>
        <td><?php echo $txEmail;?></td>
    </tr>
    <tr>
        <td><strong>CELLPHONE No.</strong></td>
        <td><?php echo $txCellphone;?></td>
    </tr>
</table>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;">
    <tr>
        <td colspan="2" style="background-color:#E4E1E1"><div align="center"><strong>II. FAMILY BACKGROUND</strong></div></td>
    </tr>
    <tr>
        <td><strong>FATHER'S NAME</strong></td>
        <td><?php echo $txtfrfName.' '.$txtfrmName.' '.$txtfrlName; echo ($txtfrExtName) ? ', '.$txtfrExtName : '';?></td>
    </tr>
    <tr>
        <td><strong>MOTHER'S NAME</strong></td>
        <td><?php echo $txtmrfName.' '.$txtmrmName.' '.$txtmrlName;?></td>
    </tr>
    <tr>
        <td width="20%"><strong>SPOUSE'S NAME</strong></td>
        <td><?php echo $txtspfName.' '.$txtspmName.' '.$txtsplName; echo ($txtspExtName) ? ', '.$txtspExtName : '';?></td>
    </tr>
    <tr>
        <td><strong>OCCUPATION</strong></td>
        <td><?php echo $txtspOccupation;?></td>
    </tr>
    <tr>
        <td><strong>BUSINESS NAME</strong></td>
        <td><?php echo $txspbusname;?></td>
    </tr>
    <tr>
        <td><strong>BUS. ADDRESS</strong></td>
        <td><?php echo $txtspbusadd;?></td>
    </tr>
    <tr>
        <td><strong>TELEPHONE No.</strong></td>
        <td><?php echo $txspTelNo;?></td>
    </tr>
</table>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;">
    <tr>
        <td colspan="2"><strong>CHILDREN</strong></td>
    </tr>
    <tr>
        <td width="20%">&nbsp;</td>
        <td><strong>NAME</strong></td>
        <td width="25%"><strong>DATE OF BIRTH</strong></td>
    </tr>
    <?php
    $countChild=0;
    $qChild = $db->select('emp_child','*',array('emp_id'=>$eid));
    while($rChild = $db->fetch_array($qChild)):
        $countChild++;
    ?>
    <tr>
        <td>&nbsp;</td>
        <td><?php echo $countChild.'. '.$rChild['ec_fname'].' '.$rChild['ec_mname'].' '.$rChild['ec_lname']; echo ($rChild['ec_extname']) ? ', '.$rChild['ec_extname'] : '';?></td>
        <td><?php echo functions::datearr($rChild['ec_bdate']);?>&nbsp;&nbsp;&nbsp;&nbsp; (AGE: <?php echo functions::year_diff($rChild['ec_bdate'],date('Y-m-d'));?>)</td>
    </tr>
<?php endwhile;?>
</table>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered" style="font-size:12px;">
    <tr>
        <td colspan="8" style="background-color:#E4E1E1"><div align="center"><strong>III. EDUCATIONAL BACKGROUND</strong></div></td>
    </tr>
    <tr>
        <td colspan="8"><div align="center"></td>
    </tr>
    <tr style="background-color:#ececec">
        <td rowspan="2"><strong>LEVEL</strong></td>
        <td rowspan="2"><strong>NAME OF SCHOOL</strong></td>
        <td rowspan="2"><strong>DEGREE COURSE</strong></td>
        <td rowspan="2"><strong>YEAR GRADUATED</strong> <br><i>(if graduated)</i></td>
        <td rowspan="2"><strong>HIGHEST GRADE /<br>LEVEL /<br>UNITS EARNED</strong> <br><i>(if NOT graduated)</i></td>
        <td colspan="2"><strong>INCLUSIVE DATES OF ATTENDANCE</strong></td>
        <td><strong>SCHOLARSHIP /<br>ACADEMIC HONORS RECEIVED</strong></td>
    </tr>
    <tr style="background-color:#ececec">
        <td><strong>FROM</strong></td>
        <td><strong>TO</strong></td>
        <td></td>
    </tr>
    <?php
        $arrLevel = array('ELEMENTARY','SECONDARY','VOCATIONAL','COLLEGE','GRADUATE');
        foreach($arrLevel as $level):
        $qCol = $db->select('emp_education','*',array('emp_id'=>$eid,'ee_level'=>$level));
        while($rCol = $db->fetch_array($qCol)):
    ?>
    <tr>
        <td><?php echo $rCol['ee_level']?></td>
        <td><?php echo ucwords(strtolower($rCol['ee_school']))?></td>
        <td><?php echo ucwords(strtolower($rCol['ee_course']))?></td>
        <td><?php echo $rCol['ee_year_grad']?></td>
        <td><?php echo $rCol['ee_highest_level']?></td>
        <td><?php echo $rCol['ee_date_from']?></td>
        <td><?php echo $rCol['ee_date_to']?></td>
        <td><?php echo ucwords(strtolower($rCol['ee_honors']))?></td>
    </tr>
    <?php
    endwhile;
    endforeach;
    ?>
</table>

<table width="100%" align="center" border="0" class="table table-bordered" style="font-size: 12px;">
    <tr>
        <td colspan="6" style="background-color:#E4E1E1"><div align="center"><strong>IV. PRC ISSUED LICENSES</strong></div></td>
    </tr>
    <tr>
        <td colspan="6"><div align="center"></td>
    </tr>
    <tr style="background-color:#ececec">
        <td rowspan="2"><strong>RA 1080 (BOARD/BAR)<br>UNDER SPECIAL LAWS / CES / CESS</strong></td>
        <td rowspan="2"><strong>RATING</strong></td>
        <td rowspan="2"><strong>DATE OF EXAMINATION</strong></td>
        <td rowspan="2"><strong>PLACE OF EXAMINATION</strong></td>
        <td colspan="2"><strong>LICENSE</strong> (if applicable)</td>
    </tr>
    <tr style="background-color:#ececec">
        <td><strong>Number</strong></td>
        <td><strong>Date of Release</strong></td>
    </tr>
    <?php
        $q = $db->select('emp_prc_license','*',array('emp_id'=>$eid),'ORDER BY el_exam_date');
        while($r = $db->fetch_array($q)):
    ?>
    <tr>
        <td><?php echo $r['el_title']?></td>
        <td><?php echo $r['el_rating']?></td>
        <td><?php echo functions::datearr($r['el_exam_date'])?></td>
        <td><?php echo $r['el_exam_place']?></td>
        <td><?php echo $r['el_no']?></td>
        <td><?php echo functions::datearr($r['el_date_release'])?></td>
    </tr>
    <?php endwhile;?>
</table>
<table width="100%" align="center" border="0" class="table table-bordered" style="font-size: 12px;">
    <tr>
        <td colspan="5" style="background-color:#E4E1E1"><div align="center"><strong>V. WORK EXPERIENCES</strong> <i>(Start from your current work)</i></div></td>
    </tr>
    <tr>
        <td colspan="5"><div align="center"></td>
    </tr>
    <tr style="background-color:#ececec">
        <td colspan="2" width="40%"><div align="center"><strong>INCLUSIVE DATE(S)</strong></div></td>
        <td rowspan="2" width="20%"><strong>POSITION</strong></td>
        <td rowspan="2" width="25%"><strong>DEPARTMENT / AGENCY / OFFICE / COMPANY</strong></td>
        <td rowspan="2" width="10%"><strong>YEAR(S) IN SERVICE</strong></td>
    </tr>
    <tr style="background-color:#ececec">
        <td width="12%"><strong>From</strong></td>
        <td width="12%"><strong>To</strong></td>
    </tr>
    <?php
        $q = $db->select('emp_work_exp','*',array('emp_id'=>$eid),'ORDER BY ewe_date_from DESC');
        while($r = $db->fetch_array($q)):
    ?>
    <tr>
        <td><?php echo functions::datearr($r['ewe_date_from'])?></td>
        <td><?php echo functions::datearr($r['ewe_date_to'])?></td>
        <td><?php echo $r['ewe_position']?></td>
        <td><?php echo $r['ewe_company']?></td>
        <td><div align="center"><?php echo functions::year_diff($r['ewe_date_from'],$r['ewe_date_to']);?></div></td>
    </tr>
    <?php endwhile;?>
</table>
<table width="100%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1; font-size: 12px;">
    <tr>
        <td colspan="6" style="background-color:#E4E1E1"><div align="center"><strong>VI. RELEVANT TRAININGS </strong> <i>(with Certificate Attached)</i></div></td>
    </tr>
    <tr>
        <td colspan="6"><div align="center"></td>
    </tr>
    <tr>
        <td rowspan="2" width="25%"><strong>TITLE OF SEMINAR / CONFERENCE / <br>WORKSHOP / SHORT COURSES</strong></td>
        <td colspan="2" width="20%"><div align="center"><strong>INCLUSIVE DATE(S) OF ATTENDANCE</strong></div></td>
        <td rowspan="2" width="10%"><strong>NO. OF HOUR(S)</strong></td>
        <td rowspan="2" width="20%"><strong>CONDUCTED / SPONSORED BY</strong></td>
        <td rowspan="2" width="15%"><div align="center"><strong>CERTIFICATE</strong></div></td>
    </tr>
    <tr>
        <td width="10%"><strong>From</strong></td>
        <td width="10%"><strong>To</strong></td>
    </tr>
    <?php
        $q = $db->select('emp_training','*',array('emp_id'=>$eid),'ORDER BY et_date_from');
        while($r = $db->fetch_array($q)):
    ?>
    <tr>
        <td><?php echo $r['et_title']?></td>
        <td><?php echo functions::datearr($r['et_date_from'])?></td>
        <td><?php echo functions::datearr($r['et_date_to'])?></td>
        <td><div align="center"><?php echo $r['et_no_hour'];?></div></td>
        <td><?php echo $r['et_sponsored_by']?></td>
        <td>
            <?php
            $fileName = $db->getValue('cert_img','cert_name',array('cert_id'=>$r['cert_id']));
            $file = ($fileName) ? $fileName : 'blank-pic.png';
            ?>
            <div align="center">
<a id="vw<?php echo $r['et_id']?>" style="cursor: pointer;" class="thickbox" onclick="showThis(this.id,'employee_cert_view.php?crtID=<?php echo functions::encode($r['cert_id'])?>','View Certificate','1')"><img height="100" width="100" src="../img_emp/<?php echo $file;?>"></a>
            </div>
        </td>
    </tr>
    <?php endwhile;?>
</table>
            </td>
        </tr>
    </tbody>
</table>
<!-- body content: end here-->
<!-- start: JavaScript-->
<script src="../js/jquery-1.9.1.min.js"></script>
<script src="../js/jquery-migrate-1.0.0.min.js"></script>
<script src="../js/jquery-ui-1.10.0.custom.min.js"></script>
<script src="../js/jquery.ui.touch-punch.js"></script>
<script src="../js/modernizr.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.cookie.js"></script>
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
<!-- end: JavaScript-->
</body>
</html>