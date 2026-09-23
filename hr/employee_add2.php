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

$qItemCitezenship = $db->query('SELECT DISTINCT citizenship FROM employee ORDER BY citizenship');
$namesCitezenship='';
while($rItemC=$db->fetch_array($qItemCitezenship)):
    $string = preg_replace("/'/",'"',$rItemC['citizenship']);
    $namesCitezenship .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesCitezenship .= '"--"';

$qReligion = $db->query('SELECT DISTINCT religion FROM employee ORDER BY religion');
$namesReligion='';
while($rRel=$db->fetch_array($qReligion)):
    $string = preg_replace("/'/",'"',$rRel['religion']);
    $namesReligion .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesReligion .= '"--"';
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
    <link rel="stylesheet" href="../css/bootstrap-multiselect.css" type="text/css">
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

    $emp_no = ( isset($_POST['txtEmpNo']) ) ? strtoupper(trim($_POST['txtEmpNo'])) : '';
    $lname = ( isset($_POST['txtlName']) ) ? strtoupper(trim($_POST['txtlName'])) : '';
    $fname = ( isset($_POST['txtfName']) ) ? strtoupper(trim($_POST['txtfName'])) : '';
    $mname = ( isset($_POST['txtmName']) ) ? strtoupper(trim($_POST['txtmName'])) : '';
    $extname = ( isset($_POST['txtExtName']) ) ? strtoupper(trim($_POST['txtExtName'])) : '';
    $nickname = ( isset($_POST['txtNickName']) ) ? strtoupper(trim($_POST['txtNickName'])) : '';

    $bdate ='0000-00-00';
    $bdMon = ( isset($_POST['bdMon']) ) ? trim($_POST['bdMon']) : '';
    $bdDay = ( isset($_POST['bdDay']) ) ? trim($_POST['bdDay']) : '';
    $bdYear = ( isset($_POST['bdYear']) ) ? trim($_POST['bdYear']) : '';
    if( !empty($bdMon) && !empty($bdDay) && !empty($bdDay) ){
        $bdate = $bdYear.'-'.$bdMon.'-'.$bdDay;
    }

    $bplace = ( isset($_POST['txtBirthPlace']) ) ? strtoupper(trim($_POST['txtBirthPlace'])) : '';
    $gender = ( isset($_POST['selGender']) ) ? trim($_POST['selGender']) : '';
    $civil_status = ( isset($_POST['selCivilStat']) ) ? trim($_POST['selCivilStat']) : '';
    $citizenship = ( isset($_POST['txCitizenship']) ) ? strtoupper(trim($_POST['txCitizenship'])) : '';
    $religion = ( isset($_POST['txReligion']) ) ? strtoupper(trim($_POST['txReligion'])) : '';
    $height = ( isset($_POST['txHeight']) ) ? trim($_POST['txHeight']) : '';
    $weight = ( isset($_POST['txWeight']) ) ? trim($_POST['txWeight']) : '';
    $bloodtype = ( isset($_POST['txBloodType']) ) ? trim($_POST['txBloodType']) : '';
    $tin = ( isset($_POST['txTIN']) ) ? trim($_POST['txTIN']) : '';
    $pagibig = ( isset($_POST['txpagibig']) ) ? trim($_POST['txpagibig']) : '';
    $philhealth = ( isset($_POST['txphilhealth']) ) ? trim($_POST['txphilhealth']) : '';
    $sss = ( isset($_POST['txtSSS']) ) ? trim($_POST['txtSSS']) : '';
    $gsis = ( isset($_POST['txtGSIS']) ) ? trim($_POST['txtGSIS']) : '';

    $curr_province = ( isset($_POST['selCurProvince']) ) ? trim($_POST['selCurProvince']) : '';
    $curr_cityMun = ( isset($_POST['selCurCityMun']) ) ? trim($_POST['selCurCityMun']) : '';
    $curr_add = ( isset($_POST['selCurBrngy']) ) ? trim($_POST['selCurBrngy']) : '';
    $curr_street = ( isset($_POST['curStreet']) ) ? strtoupper(trim($_POST['curStreet'])) : '';
    $curr_zipcode = ( isset($_POST['curZipCode']) ) ? trim($_POST['curZipCode']) : '';
    $curr_tel = ( isset($_POST['curTelNo']) ) ? trim($_POST['curTelNo']) : '';
    $perm_province = ( isset($_POST['selProvincePerm']) ) ? trim($_POST['selProvincePerm']) : '';
    $perm_cityMun = ( isset($_POST['selCityMunPerm']) ) ? trim($_POST['selCityMunPerm']) : '';
    $perm_add = ( isset($_POST['selBrngyPerm']) ) ? trim($_POST['selBrngyPerm']) : '';
    $perm_street = ( isset($_POST['permStreet']) ) ? strtoupper(trim($_POST['permStreet'])) : '';
    $perm_zipcode = ( isset($_POST['permZipCode']) ) ? trim($_POST['permZipCode']) : '';
    $perm_tel = ( isset($_POST['permTelNo']) ) ? trim($_POST['permTelNo']) : '';
    $email = ( isset($_POST['txEmail']) ) ? trim($_POST['txEmail']) : '';
    $cell_no = ( isset($_POST['txCellphone']) ) ? trim($_POST['txCellphone']) : '';
    $selPosition = ( isset($_POST['selPosition']) ) ? $_POST['selPosition'] : array();

    $ctc_no = ( isset($_POST['txCTCNo']) ) ? trim($_POST['txCTCNo']) : '';
    $ctc_issue_place = ( isset($_POST['txCTCPlace']) ) ? strtoupper(trim($_POST['txCTCPlace'])) : '';
    $ctc_issue_date = '0000-00-00';
    $bdCTCMon = ( isset($_POST['bdCTCMon']) ) ? trim($_POST['bdCTCMon']) : '';
    $bdCTCDay = ( isset($_POST['bdCTCDay']) ) ? trim($_POST['bdCTCDay']) : '';
    $bdCTCYear = ( isset($_POST['bdCTCYear']) ) ? trim($_POST['bdCTCYear']) : '';
    if( !empty($bdCTCMon) && !empty($bdCTCDay) && !empty($bdCTCYear) ){
        $ctc_issue_date = $bdCTCYear.'-'.$bdCTCMon.'-'.$bdCTCDay;
    }

    $date_accomplished = '0000-00-00';
    $bdAccmplshdMon = ( isset($_POST['bdAccmplshdMon']) ) ? trim($_POST['bdAccmplshdMon']) : '';
    $bdAccmplshdDay = ( isset($_POST['bdAccmplshdDay']) ) ? trim($_POST['bdAccmplshdDay']) : '';
    $bdAccmplshdYear = ( isset($_POST['bdAccmplshdYear']) ) ? trim($_POST['bdAccmplshdYear']) : '';
    if( !empty($bdAccmplshdMon) && !empty($bdAccmplshdDay) && !empty($bdAccmplshdYear) ){
        $date_accomplished = $bdAccmplshdYear.'-'.$bdAccmplshdMon.'-'.$bdAccmplshdDay;
    }

    $ews_stat = ( isset($_POST['selWorkStat']) ) ? trim($_POST['selWorkStat']) : '';
    $ews_date = '0000-00-00';
    $selWorkMon = ( isset($_POST['selWorkMon']) ) ? trim($_POST['selWorkMon']) : '';
    $selWorkDay = ( isset($_POST['selWorkDay']) ) ? trim($_POST['selWorkDay']) : '';
    $selWorkYr = ( isset($_POST['selWorkYr']) ) ? trim($_POST['selWorkYr']) : '';
    if( !empty($selWorkMon) && !empty($selWorkDay) && !empty($selWorkYr) ){
        $ews_date = $selWorkYr.'-'.$selWorkMon.'-'.$selWorkDay;
    }

    $arrIns = array();
    if($lname && $fname){

        $arrIns = array('lname'=>$lname,'fname'=>$fname,'mname'=>$mname,'extname'=>$extname,'nickname'=>$nickname,'bdate'=>$bdate,
                        'bplace'=>$bplace,'gender'=>$gender,'civil_status'=>$civil_status,'citizenship'=>$citizenship,'religion'=>$religion,
                        'height'=>$height,'weight'=>$weight,'bloodtype'=>$bloodtype,'tin'=>$tin,'pagibig'=>$pagibig,'philhealth'=>$philhealth,'sss'=>$sss,'gsis'=>$gsis,
                        'curr_province'=>$curr_province,'curr_cityMun'=>$curr_cityMun,'curr_add'=>$curr_add,'curr_street'=>$curr_street,'curr_zipcode'=>$curr_zipcode,'curr_tel'=>$curr_tel,
                        'perm_province'=>$perm_province,'perm_cityMun'=>$perm_cityMun,'perm_add'=>$perm_add,'perm_street'=>$perm_street,'perm_zipcode'=>$perm_zipcode,'curr_tel'=>$perm_tel,
                        'email'=>$email,'cell_no'=>$cell_no,'ctc_no'=>$ctc_no,'ctc_issue_date'=>$ctc_issue_date,'ctc_issue_place'=>$ctc_issue_place,'date_accomplished'=>$date_accomplished,'work_status'=>$ews_stat,);
        $allow_update_emp_no=1;
        if( $emp_no ){
            if( $db->getValue('employee','count(*)',array('emp_no'=>$emp_no)) )
                $allow_update_emp_no=0;
        }

        if($allow_update_emp_no==0)
            functions::say('Employee number already exist! Update it next time.');
        else
            $arrIns = array_merge($arrIns,array('emp_no'=>$emp_no));

        $insertEmp = $db->insertPrint('employee',$arrIns);
        $db->query($insertEmp);
        $emp_id = $db->insert_id();

        if($emp_id){
            if(count($selPosition)){
                foreach($selPosition as $dpID):
                    $db->insert('emp_position',array('emp_id'=>$emp_id,'dp_id'=>$dpID));
                endforeach;
            }
            if($ews_stat && $ews_date)
                $db->insert('emp_work_status',array('ews_stat'=>$ews_stat,'ews_date'=>$ews_date,'emp_id'=>$emp_id));
            if($ctc_no)
                $db->insert('emp_ctc',array('ctc_issued_date'=>$ctc_issue_date,'ctc_issued_at'=>$ctc_issue_place,'emp_id'=>$emp_id));
            if( !empty($_FILES['image']) && $_FILES['image']['error'] == 0 ) {

                $uploaddir = '../img_emp/';
                $max_size = 2000 * 1024; // 500 KB
                // Generates random filename and extension 
                function tempnam_sfx($path, $suffix){
                    do {
                        $file = $path."/".mt_rand().$suffix;
                        $fp = @fopen($file, 'x');
                    }
                    while(!$fp);

                    fclose($fp);
                    return $file;
                }
                // Process image with GD library
                $verifyimg = getimagesize($_FILES['image']['tmp_name']);

                // Make sure the MIME type is an image
                $pattern = "#^(image/)[^\s\n<]+$#i";

                if( !preg_match($pattern, $verifyimg['mime']) ){
                    functions::say("Only image files are allowed!");
                }
                else if( $_FILES["image"]["size"] > $max_size ){
                    functions::say("Image reached the limit size!");
                }
                else{
                    // Rename both the image and the extension 
                    $uploadfile = tempnam_sfx($uploaddir, ".jpg");
                    // Upload the file to a secure directory with the new name and extension
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
                        $qPicIns = $db->insertPrint('cert_img',array('cert_name'=>basename($uploadfile),'cert_org_name'=>basename($_FILES['image']['name']),'mime_type'=>$_FILES['image']['type']));
                        $db->query($qPicIns);
                        $newCertID = $db->insert_id();
                        $db->update('employee',array('cert_id'=>$newCertID),array('emp_id'=>$emp_id));
                    }
                }
            }
            functions::say('Personal Information Saved!');
            #functions::sendTo('employee_add_family.php?eid='.functions::encode($emp_id));
        }
        else{
            functions::say('Fail to save.');
        }
    }
    else
        functions::say('Please fill up the form properly!');
}
?>
<style>
.hrDuty{
    width:75px;
}
</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span></h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post" enctype="multipart/form-data">
                <div align="center" style="padding-bottom: 15px;"><h2>PERSONAL INFORMATION</h2></div>
                <table border="0" width="98%" align="center">
                    <tr>
                        <td align="center">
                            <div align="center">
                                <?php
                                    echo '<img height="200" width="200" src="../img_emp/blank-pic.png">';
                                ?>
                                Select <strong>2x2</strong> image to upload: <input type="file" name="image">
                            </div><br>
                            <div>
                            </div>
                        </td>
                    </tr>               
                </table>
                <table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
                    <tr>
                        <td width="17%" height="30"><div align="right">Employee Number</div></td>
                        <td width="43%"><input type="text" name="txtEmpNo" id="txtEmpNo" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Position</div></td>
                        <td>
                            <select name="selPosition[]" id="selPosition" multiple="multiple" data-rel="chosen" style="width:490px;">
                                <?php 
                                $qPos = $db->select('dep_position','*',array(),'ORDER BY pos_name');
                                while($rPos = $db->fetch_array($qPos)):
                                    $dept = $db->getValue('department','dep_name',array('dep_id'=>$rPos['dep_id']));
                                    $depName = ($dept) ? '('.$dept.')' : '(no department)';
                                ?>
                                <option value="<?php echo $rPos['dp_id']?>"><?php echo $rPos['pos_name']; echo '&nbsp;&nbsp;'.$depName;?></option>
                                <?php endwhile;?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">First Name</div></td>
                        <td><input type="text" name="txtfName" id="txtfName" class="span6" value="" /></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Middle Name</div></td>
                        <td><input type="text" name="txtmName" id="txtmName" class="span6" value="" /></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Last Name</div></td>
                        <td><input type="text" name="txtlName" id="txtlName" class="span6" value="" /></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Suffix (JR, SR, III)</div></td>
                        <td><input type="text" name="txtExtName" id="txtExtName" class="span6" value="" /></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Nickname</div></td>
                        <td><input type="text" name="txtNickName" id="txtNickName" class="span6" value="" /></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Date of Birth</div></td>
                        <td>
                            <select name="bdMon" id="bdMon" style="width:80px;" class="drpPad">
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
                            <select name="bdDay" id="bdDay" style="width:80px;" class="drpPad">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo ($i<10) ? '0'.$i : $i;?>"><?php echo $i;?></option>
                                <?php endfor;?>
                            </select>
                            <select name="bdYear" id="bdYear" style="width:80px;" class="drpPad">
                                <option value="">Year</option>
                                <?php for($y=date('Y');$y>=1900;$y--):?>
                                <option value="<?php echo $y;?>"><?php echo $y;?></option>
                                <?php endfor;?>
                            </select>
                          </div>
                          <span class="help-inline warning" style="font-weight:bold;" id="msgBdate" name="msgBdate"></span>
                        </td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Birth Place</div></td>
                        <td><input type="text" name="txtBirthPlace" id="txtBirthPlace" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Gender</div></td>
                        <td>
                            <select name="selGender" id="selGender" style="width: 150px;">
                               <option value="">--Select--</option>
                               <option value="Male">Male</option>
                               <option value="Female">Female</option>
                            </select>
                            <span class="help-inline warning" style="font-weight:bold;" id="msgGender" name="msgGender"></span>
                        </td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Civil Status</div></td>
                        <td>
                            <select name="selCivilStat" id="selCivilStat" style="width: 150px;">
                               <option value="">--Select--</option>
                               <option value="Single">Single</option>
                               <option value="Married">Married</option>
                               <option value="Annulled">Annulled</option>
                               <option value="Widowed">Widowed</option>
                               <option value="Separated">Separated</option>
                            </select>
                            <span class="help-inline warning" style="font-weight:bold;" id="msgCivilStat" name="msgCivilStat"></span>
                        </td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Citizenship</div></td>
                        <td>
                            <input type="text" name="txCitizenship" id="txCitizenship" class="span6" value="" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesCitezenship;?>]'>
                            <span class="help-inline warning" style="font-weight:bold;" id="msgCitizenship" name="msgCitizenship"></span>
                        </td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Religion</div></td>
                        <td><input type="text" name="txReligion" id="txReligion" class="span6" value="" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesReligion;?>]'></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Height (m)</div></td>
                        <td><input type="text" name="txHeight" id="txHeight" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Weight (kg)</div></td>
                        <td><input type="text" name="txWeight" id="txWeight" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Blood Type</div></td>
                        <td><input type="text" name="txBloodType" id="txBloodType" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">TIN</div></td>
                        <td><input type="text" name="txTIN" id="txTIN" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">PAGIBIG No.</div></td>
                        <td><input type="text" name="txpagibig" id="txpagibig" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">PHILHEALTH No.</div></td>
                        <td><input type="text" name="txphilhealth" id="txphilhealth" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">SSS No.</div></td>
                        <td><input type="text" name="txtSSS" id="txtSSS" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">GSIS No.</div></td>
                        <td><input type="text" name="txtGSIS" id="txtGSIS" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Residential Address</div></td>
                        <td><br>
                            <table>
                                <tr>
                                    <td>
                                        <select name="selCurProvince" id="selCurProvince" onChange="getCityMun(this.value)">
                                            <option value="">Select Province</option>
                                            <?php 
                                            $qProvince = $db->select('refprovince','*',array(),'ORDER BY provDesc');
                                            while($rProv = $db->fetch_array($qProvince)): ?>
                                            <option value="<?php echo $rProv['provCode']?>"><?php echo $rProv['provDesc']?></option>
                                            <?php endwhile;?>
                                        </select>
                                    </td>
                                    <td>
                                        <div id="divCurCityMun">
                                            <select name="selCurCityMun" id="selCurCityMun">
                                                <option value="">Select City/Municipality</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div id="divCurBrngy">
                                            <select id="selCurBrngy" name="selCurBrngy">
                                                <option value="">Select Barangay</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><input type="text" name="curStreet" id="curStreet" value="" placeholder="Street" style="width: 350px;"></td>
                                    <td><input type="text" name="curZipCode" id="curZipCode"  value="" placeholder="Zip Code"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Telephone No.</div></td>
                        <td><input type="text" name="curTelNo" id="curTelNo" class="span6" value="" style="width: 150px;"></td>
                    </tr>
                    <tr>
                        <td height="60" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td><div align="right">Permanent Address</div></td>
                        <td><br>
                            <table>
                                <tr>
                                    <td>
                                        <select name="selProvincePerm" id="selProvincePerm" onChange="getCityMunPerm(this.value)" style="width: 300px;" class="form-control">
                                            <option value="">Select Province</option>
                                            <?php 
                                            $qProvince = $db->select('refprovince','*',array(),'ORDER BY provDesc');
                                            while($rProv = $db->fetch_array($qProvince)): ?>
                                            <option value="<?php echo $rProv['provCode']?>"><?php echo $rProv['provDesc']?></option>
                                            <?php endwhile;?>
                                        </select>
                                    </td>
                                    <td>
                                        <div id="divCityMunPerm">
                                            <select name="selCityMunPerm" id="selCurCityMunPerm" class="form-control">
                                                <option value="">Select City/Municipality</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div id="divBrngyPerm">
                                            <select id="selBrngyPerm" name="selBrngyPerm" class="form-control">
                                                <option value="">Select Barangay</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><input type="text" name="permStreet" id="permStreet" value="" placeholder="Street" style="width: 350px;"></td>
                                    <td><input type="text" name="permZipCode" id="permZipCode"  value="" placeholder="Zip Code"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Telephone No.</div></td>
                        <td><input type="text" name="permTelNo" id="permTelNo" class="span6" value="" style="width: 150px;"></td>
                    </tr>
                    <tr>
                        <td height="60" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Email Address</div></td>
                        <td><input type="text" name="txEmail" id="txEmail" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Cellphone No.</div></td>
                        <td><input type="text" name="txCellphone" id="txCellphone" class="span6" value="" onkeypress="return checkinput(this, event);"></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Community TAX Certificate No</div></td>
                        <td><input type="text" name="txCTCNo" id="txCTCNo" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">ISSUED AT</div></td>
                        <td><input type="text" name="txCTCPlace" id="txCTCPlace" class="span6" value=""></td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">ISSUED ON</div></td>
                        <td>
                            <select name="bdCTCMon" id="bdCTCMon" style="width:80px;" class="drpPad">
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
                            <select name="bdCTCDay" id="bdCTCDay" style="width:80px;" class="drpPad">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo ($i<10) ? '0'.$i : $i;?>"><?php echo $i;?></option>
                                <?php endfor;?>
                            </select>
                            <select name="bdCTCYear" id="bdCTCYear" style="width:80px;" class="drpPad">
                                <option value="">Year</option>
                                <?php for($y=date('Y');$y>=1900;$y--):?>
                                <option value="<?php echo $y;?>"><?php echo $y;?></option>
                                <?php endfor;?>
                            </select>
                          </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">DATE ACCOMPLISHED</div></td>
                        <td>
                            <select name="bdAccmplshdMon" id="bdAccmplshdMon" style="width:80px;" class="drpPad">
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
                            <select name="bdAccmplshdDay" id="bdAccmplshdDay" style="width:80px;" class="drpPad">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo ($i<10) ? '0'.$i : $i;?>"><?php echo $i;?></option>
                                <?php endfor;?>
                            </select>
                            <select name="bdAccmplshdYear" id="bdAccmplshdYear" style="width:80px;" class="drpPad">
                                <option value="">Year</option>
                                <?php for($y=date('Y');$y>=1900;$y--):?>
                                <option value="<?php echo $y;?>"><?php echo $y;?></option>
                                <?php endfor;?>
                            </select>
                          </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">WORK STATUS</div></td>
                        <td>
                            <select name="selWorkStat" id="selWorkStat" style="width: 150px;">
                               <option value="">--Select--</option>
                               <option value="Part Time">Part Time</option>
                               <option value="Contractual">Contractual</option>
                               <option value="Probationary">Probationary</option>
                               <option value="Regular">Regular</option>
                               <option value="Retired">Retired</option>
                               <option value="Resigned">Resigned</option>
                               <option value="AWOL">AWOL</option>
                            </select>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <select name="selWorkMon" id="selWorkMon" style="width:80px;">
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
                            <select name="selWorkDay" id="selWorkDay" style="width:80px;">
                                <option value="">Day</option>
                                <?php for($i=1;$i<=31;$i++):?>
                                <option value="<?php echo ($i<10) ? '0'.$i : $i;?>"><?php echo $i;?></option>
                                <?php endfor;?>
                            </select>
                            <select name="selWorkYr" id="selWorkYr" style="width:80px;">
                                <option value="">Year</option>
                                <?php for($y=date('Y');$y>=1900;$y--):?>
                                <option value="<?php echo $y;?>"><?php echo $y;?></option>
                                <?php endfor;?>
                            </select>
                          </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="30"><div align="right">Duty</div></td>
                        <td>
                            <table width="90%">
                                <tr>
                                    <td>&nbsp;</td>
                                    <td colspan="2"><div align="center">Morning</div></td>
                                    <td colspan="2"><div align="center">Afternoon</div></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td width="20%"><div align="center">In</div></td>
                                    <td width="20%"><div align="center">Out</div></td>
                                    <td width="20%"><div align="center">In</div></td>
                                    <td width="20%"><div align="center">Out</div></td>
                                </tr>
                                <tr>
                                    <td>Monday</td>
                                    <td>
                                        <div align="center">
                                            <select name="monAmIn" id="monAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==8 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="monAmOut" id="monAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==12 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="monPmIn" id="monPmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==13 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="monPmOut" id="monPmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==17 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Tuesday</td>
                                    <td>
                                        <div align="center">
                                            <select name="tueAmIn" id="tueAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==8 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="tueAmOut" id="tueAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==12 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="tuePmIn" id="tuePmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==13 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="tuePmOut" id="tuePmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==17 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Wednesday</td>
                                    <td>
                                        <div align="center">
                                            <select name="wedAmIn" id="wedAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==8 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="wedAmOut" id="wedAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==12 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="wedPmIn" id="wedPmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==13 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="wedPmOut" id="wedPmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==17 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Thurdays</td>
                                    <td>
                                        <div align="center">
                                            <select name="thuAmIn" id="thuAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==8 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="thuAmOut" id="thuAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==12 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="thuPmIn" id="thuPmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==13 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="thuPmOut" id="thuPmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==17 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Friday</td>
                                    <td>
                                        <div align="center">
                                            <select name="friAmIn" id="friAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==8 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="friAmOut" id="friAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==12 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="friPmIn" id="friPmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==13 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="friPmOut" id="friPmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==17 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Saturday</td>
                                    <td>
                                        <div align="center">
                                            <select name="satAmIn" id="satAmIn" class="hrDuty" data-rel="chosen" style="width:110px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==8 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="satAmOut" id="satAmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>" <?php if($hr==12 && $mn==0)echo 'selected="selected"';?>><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="satPmIn" id="satPmIn" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="" selected="selected">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>"><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div align="center">
                                            <select name="satPmOut" id="satPmOut" class="hrDuty" data-rel="chosen" style="width:100px;">
                                                <option value="" selected="selected">N/A</option>
                                                <?php
                                                for($hr=0; $hr<24;$hr++):
                                                    for($mn=0;$mn<60;$mn+=30):
                                                        $hrDisplay = ($hr<10) ? '0'.$hr : $hr;
                                                        $mnDisplay = ($mn<10) ? '0'.$mn : $mn;
                                                        $tmVal = $hrDisplay.':'.$mnDisplay.':00';
                                                        $hrDisplay = ($hrDisplay==0) ? 12 : $hrDisplay;
                                                        $timeDisp=$hrDisplay.':'.$mnDisplay.' AM';
                                                        if($hr>=12){
                                                            $tmphr = $hr - 12;
                                                            $tmphr = ($tmphr==0) ? 12 : $tmphr;
                                                            $tmphr = ($tmphr<10) ? '0'.$tmphr : $tmphr;
                                                            $timeDisp = $tmphr.':'.$mnDisplay.' PM';
                                                        }
                                                ?>
                                                <option value="<?php echo $tmVal?>"><?php echo $timeDisp;?></option>
                                                <?php endfor;
                                                endfor;?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>
                <div align="center">
                    <input type="submit" name="btnSave" id="btnSave" value=" SAVE " class="btn btn-primary">
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
<script type="text/javascript" src="../js/bootstrap-multiselect.js"></script>
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
function getXMLHTTP() { //fuction to return the xml http object
    var xmlhttp=false;  
    try{
        xmlhttp=new XMLHttpRequest();
    }
    catch(e)  {   
        try{      
            xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch(e){
            try{
                xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch(e1){
                xmlhttp=false;
            }
        }
    }
    return xmlhttp;
}
  
function getCityMun(provId) {    
    var strURL="findCityMun.php?provId="+provId;
    var req = getXMLHTTP();
    
    if (req) {
        req.onreadystatechange = function() {
            if (req.readyState == 4) {
                // only if "OK"
                if (req.status == 200) {            
                    document.getElementById('divCurCityMun').innerHTML=req.responseText;
                    document.getElementById('divCurBrngy').innerHTML='<select name="selCurBrngy" id="selCurBrngy"><option value="">Select Barangay</option></select>';            
                }else{
                    alert("Problem while using XMLHTTP:\n" + req.statusText);
                }
            }       
        }     
        req.open("GET", strURL, true);
        req.send(null);
    }   
}

function getBrngy(cityMunId) {   
    var strURL="findBrngy.php?cityMunId="+cityMunId;
    var req = getXMLHTTP();
    
    if(req){
        req.onreadystatechange = function() {
            if (req.readyState == 4) {
                // only if "OK"
                if (req.status == 200) {
                    document.getElementById('divCurBrngy').innerHTML=req.responseText;
                }else{
                    alert("Problem while using XMLHTTP:\n" + req.statusText);
                }
            }
        }     
    req.open("GET", strURL, true);
    req.send(null);
    }
}

function getCityMunPerm(provId) {
    var strURL="findCityMunPerm.php?provId="+provId;
    var reqs = getXMLHTTP();

    if (reqs) {
        reqs.onreadystatechange = function() {
            if (reqs.readyState == 4) {
                // only if "OK"
                if (reqs.status == 200) {//alert(reqs.status);
                    document.getElementById('divCityMunPerm').innerHTML=reqs.responseText;
                    document.getElementById('divBrngyPerm').innerHTML='<select name="selBrngyPerm" id="selBrngyPerm" class="form-control"><option value="">Select Barangay</option></select>';
                }else{
                    alert("Problem while using XMLHTTP:\n" + reqs.statusText);
                }
            }
        }     
        reqs.open("GET", strURL, true);
        reqs.send(null);
    }
}

function getBrngyPerm(cityMunId) {   
    var strURL="findBrngyPerm.php?cityMunId="+cityMunId;
    var reqss = getXMLHTTP();
    
    if(reqss){
        reqss.onreadystatechange = function() {
            if (reqss.readyState == 4) {
                // only if "OK"
                if(reqss.status == 200){
                    document.getElementById('divBrngyPerm').innerHTML=reqss.responseText;
                }else{
                    alert("Problem while using XMLHTTP:\n" + reqss.statusText);
                }
            }
        }
        reqss.open("GET", strURL, true);
        reqss.send(null);
    }
}
</script>
<!-- end: JavaScript-->
</body>
</html>