<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
require_once('../class/MoneytoWords.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$ppe_id = (isset($_REQUEST['ppe_id']) && !empty($_REQUEST['ppe_id']) ) ? functions::decode($_REQUEST['ppe_id']) : 0;
$ppei_type='';$ppei_brand=''; $ppei_qty='';$ppei_size='';$issued_count='';$issued_date=date('Y-m-d');$replacement_date=date('Y-m-d');$issuance_mode='';$remark='';$end_user='';$returned='';
if( isset($_REQUEST['txItmDel']) && !empty($_REQUEST['txItmDel']) ){
    $txItmDel = functions::decode($_REQUEST['txItmDel']);
    $db->delete('ppe_item',array('ppei_id'=>$txItmDel));
    functions::sendTo($_SERVER['PHP_SELF'].'?ppe_id='.functions::encode($ppe_id));
}
$editTrue=0;
$txItmEdt='';

if( isset($_REQUEST['txItmEdt']) && !empty($_REQUEST['txItmEdt']) ){
    $txItmEdt = functions::decode($_REQUEST['txItmEdt']);
    $editTrue = $db->getValue('ppe_item','count(*)',array('ppei_id'=>$txItmEdt));
    $qvedt = $db->select('ppe_item','*',array('ppei_id'=>$txItmEdt));
    while($rvedt = $db->fetch_array($qvedt)):
        $ppei_type=$rvedt['ppei_type'];
        $ppei_brand=$rvedt['ppei_brand'];
        $ppei_qty=$rvedt['ppei_qty'];
        $ppei_size=$rvedt['ppei_size'];
        $issued_count=$rvedt['issued_count'];
        $issued_date=$rvedt['issued_date'];
        $replacement_date=$rvedt['replacement_date'];
        $issuance_mode=$rvedt['issuance_mode'];
        $remark=$rvedt['remark'];
    endwhile;
}


$address='';$emp_name='';$position='';$marital_status='';$date_hired='';$work_status='';
$emp_id = $db->getValue('ppe','emp_id',array('ppe_id'=>$ppe_id));
$empInfo = $db->select('employee','*',array('emp_id'=>$emp_id));
while($r = $db->fetch_array($empInfo)):
    $curStreet = $r['curr_street'];
    $curProvince = $db->getValue('refprovince','provDesc',array('provCode'=>$r['curr_province']));
    $curCity = $db->getValue('refcitymun','citymunDesc',array('citymunCode'=>$r['curr_cityMun']));
    $curBrngy = $db->getValue('refbrgy','brgyDesc',array('brgyCode'=>$r['curr_add']));
    $address = $curStreet.' '.strtoupper($curBrngy).', '.$curCity.', '.$curProvince;
    $emp_name = ucwords(strtolower($r['fname'].' '.$r['lname']));
    $work_status = $r['work_status'];
    $marital_status = $r['civil_status'];
    $dp_id = $db->getValue('emp_position','dp_id',array('emp_id'=>$emp_id));
    $position = $db->getValue('dep_position','pos_name',array('dp_id'=>$dp_id));
    $date_hired = $db->getValue('emp_work_status','ews_date',array('emp_id'=>$emp_id),'ORDER BY ews_date ASC');
endwhile;
$ppe_no='';$employer='';$employer_add='';$assigned_location='';$proj_name='';$prepared_date='';$prepared_date='';$prepared_by='';$prepared_by_title='';$received_date='';$received_by='';$received_by_title='';
$qPPE = $db->select('ppe','*',array('ppe_id'=>$ppe_id));
while( $rPPE = $db->fetch_array($qPPE)):
$ppe_no = $rPPE['ppe_no'];
$employer = $rPPE['employer'];
$employer_add = $rPPE['employer_add'];
$assigned_location = $rPPE['assigned_location'];
$proj_id = $rPPE['proj_id'];
$proj_name = $db->getValue('project','proj_name',array('proj_id'=>$proj_id));

$prepared_date = $rPPE['prepared_date'];
$prepared_byID = $rPPE['prepared_by'];
$prepared_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$prepared_byID)));
$prepared_by_title = $rPPE['prepared_by_title'];

$received_date = $rPPE['received_date'];
$received_byID = $rPPE['received_by'];
$received_by = strtoupper($db->getValue('employee','concat(fname," ",substring(mname,1,1),". ",lname)',array('emp_id'=>$received_byID)));
$received_by_title = $rPPE['received_by_title'];
endwhile;

$namesType='';
$qItemType = $db->query('SELECT DISTINCT ppei_type as "itm" FROM ppe_item ORDER BY ppei_type');
while($rType=$db->fetch_array($qItemType)):
    $string = preg_replace("/'/",'"',$rType['itm']);
    $namesType .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesType .= '"--"';

$namesBrand='';
$qItemBrand = $db->query('SELECT DISTINCT ppei_brand as "itm" FROM ppe_item ORDER BY ppei_brand');
while($rBrand=$db->fetch_array($qItemBrand)):
    $string = preg_replace("/'/",'"',$rBrand['itm']);
    $namesBrand .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesBrand .= '"--"';

$namesMode='';
$qItemMode = $db->query('SELECT DISTINCT issuance_mode as "itm" FROM ppe_item ORDER BY issuance_mode');
while($rMode=$db->fetch_array($qItemMode)):
    $string = preg_replace("/'/",'"',$rMode['itm']);
    $namesMode .='"'.$db->clean(trim(preg_replace('/\s\s+/', ' ', $string))).'",';
endwhile;
$namesMode .= '"--"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../img/favicon.png">
    <title>IPPE Manage</title>
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
    <script type="text/javascript" src="../js/datetimepicker_css.js"></script>
    <!-- end: CSS -->
    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <link id="ie-style" href="../css/ie.css" rel="stylesheet">
    <![endif]-->
    <!--[if IE 9]>
    <link id="ie9style" href="../css/ie9.css" rel="stylesheet">
    <![endif]-->
<?php
if( isset($_POST['btnAdd']) ){
    $ppei_brand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? strtoupper(trim($_POST['txBrand'])) : '';
    $ppei_type = ( isset($_POST['txPPEType']) && !empty($_POST['txPPEType']) ) ? strtoupper(trim($_POST['txPPEType'])) : '';
    $ppei_qty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? trim($_POST['txQty']) : '';
    $ppei_size = ( isset($_POST['txSize']) && !empty($_POST['txSize']) ) ? strtoupper(trim($_POST['txSize'])) : '';
    $issued_count = ( isset($_POST['txIssuedCount']) && !empty($_POST['txIssuedCount']) ) ? trim($_POST['txIssuedCount']) : '';
    $issued_date = ( isset($_POST['txDateIssued']) && !empty($_POST['txDateIssued']) ) ? trim($_POST['txDateIssued']) : '0000-00-00';
    $replacement_date = ( isset($_POST['txDateReplace']) && !empty($_POST['txDateReplace']) ) ? trim($_POST['txDateReplace']) : '0000-00-00';
    $issuance_mode = ( isset($_POST['txIssuanceMode']) && !empty($_POST['txIssuanceMode']) ) ? strtoupper(trim($_POST['txIssuanceMode'])) : '';
    $remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';

    if( !empty($ppei_type) && !empty($ppei_brand) ){
        $db->insert('ppe_item',array('ppe_id'=>$ppe_id,'ppei_brand'=>$ppei_brand,'ppei_type'=>$ppei_type,'ppei_qty'=>$ppei_qty,'ppei_size'=>$ppei_size,'issued_count'=>$issued_count,'issued_date'=>$issued_date,'replacement_date'=>$replacement_date,'issuance_mode'=>$issuance_mode,'remark'=>$remark));
        functions::sendTo($_SERVER['PHP_SELF'].'?ppe_id='.functions::encode($ppe_id));
    }
    else
        functions::say('Please fill up the form properly!');  
}

if( isset($_POST['btnSave']) ){
    $ppei_id_Edt = ( isset($_POST['txItmEdt']) && !empty($_POST['txItmEdt']) ) ? functions::decode($_POST['txItmEdt']) : 0;
    $ppei_brand = ( isset($_POST['txBrand']) && !empty($_POST['txBrand']) ) ? strtoupper(trim($_POST['txBrand'])) : '';
    $ppei_type = ( isset($_POST['txPPEType']) && !empty($_POST['txPPEType']) ) ? strtoupper(trim($_POST['txPPEType'])) : '';
    $ppei_qty = ( isset($_POST['txQty']) && !empty($_POST['txQty']) ) ? trim($_POST['txQty']) : '';
    $ppei_size = ( isset($_POST['txSize']) && !empty($_POST['txSize']) ) ? strtoupper(trim($_POST['txSize'])) : '';
    $issued_count = ( isset($_POST['txIssuedCount']) && !empty($_POST['txIssuedCount']) ) ? trim($_POST['txIssuedCount']) : '';
    $issued_date = ( isset($_POST['txDateIssued']) && !empty($_POST['txDateIssued']) ) ? trim($_POST['txDateIssued']) : '0000-00-00';
    $replacement_date = ( isset($_POST['txDateReplace']) && !empty($_POST['txDateReplace']) ) ? trim($_POST['txDateReplace']) : '0000-00-00';
    $issuance_mode = ( isset($_POST['txIssuanceMode']) && !empty($_POST['txIssuanceMode']) ) ? strtoupper(trim($_POST['txIssuanceMode'])) : '';
    $remark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';
    
    if( !empty($ppei_id_Edt) && !empty($ppei_brand) ){
        $db->update('ppe_item',array('ppei_brand'=>$ppei_brand,'ppei_type'=>$ppei_type,'ppei_qty'=>$ppei_qty,'ppei_size'=>$ppei_size,'issued_count'=>$issued_count,'issued_date'=>$issued_date,'replacement_date'=>$replacement_date,'issuance_mode'=>$issuance_mode,'remark'=>$remark),array('ppei_id'=>$ppei_id_Edt,'ppe_id'=>$ppe_id));
        functions::sendTo($_SERVER['PHP_SELF'].'?ppe_id='.functions::encode($ppe_id));
    }
    else
        functions::say('Please fill up the form properly!');
}
?>
    <style type="text/css">
    .padParLeft{padding-left:10px;}
    .padAmLeft{padding-left:60px;}
    </style>
</head>
<body bgcolor="#FFFFFF">
<!-- body content: start here-->
    <form method="post">
        <div class="row-fluid">
            <div class="box span12">
                <div class="box-header" data-original-title>
                    <h2><i class="halflings-icon white edit"></i><span class="break"></span>IPPE Item Manage</h2>
                </div>
                <div align="right"><br>
                    <a id="mrUpdate" href="admin-ppe-edit.php?ppe_id=<?php echo functions::encode($ppe_id);?>" class="btn btn-info"><i class="halflings-icon white pencil"></i> Update This IPPE</a>
                    <a id="mrPrint" href="admin-ppe-print.php?ppe_id=<?php echo functions::encode($ppe_id);?>" class="btn btn-info"><i class="halflings-icon white print"></i></a>&nbsp;
                </div><br>

                <div class="box-content">
                    <table width="100%" border="0" align="center" style="font-size: 12px;">
                        <tr>
                            <td>
                                <table width="100%" border="0" style="font-size: 12px;">
                                    <tr>
                                        <td height="15" align="center">
                                            <div>
                                                <strong>MEMORANDUM OF RECEIPT <br> IPPE No. <?php echo $ppe_no;?></strong>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I, <strong><u><?php echo $emp_name?></u></strong>, of legal age, <strong><u><?php echo $marital_status?></u></strong> and a resident of <strong><u><?php echo $address?></u></strong> has been employed as <strong><u><?php echo $position?></u></strong> for <strong><u><?php echo $work_status?></u></strong>  at <strong><u><?php echo $employer?></u></strong> with an office address in <strong><u><?php echo $employer_add?></u></strong>. I am currently assigned at <strong><u><?php echo $assigned_location?></u></strong> for the <strong><u><?php echo $proj_name?></u></strong> project.</td>
                                    </tr>
                                    <tr height="5">
                                        <td></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I certify that I received the Personal Protective Equipment (PPE) listed in the quantities indicated:
                                <input type="hidden" name="txItmEdt" id="txItmEdt" value="<?php echo functions::encode($txItmEdt);?>">
                                <br><br>
                                <table width="100%" border="0" align="center" class="table">
                                    <tr>
                                        <td width="40%"><div align="right"><strong>PPE Type</strong></div></td>
                                        <td><div align="left"><input type="text" name="txPPEType" id="txPPEType" value="<?php echo $ppei_type?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesType;?>]'></div></td>
                                    </tr>
                                    <tr>
                                        <td><div align="right"><strong>Brand</strong></div></td>
                                        <td><div align="left"><input type="text" name="txBrand" id="txBrand" value="<?php echo $ppei_brand?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesBrand;?>]'></div></td>
                                    </tr>
                                    <tr>
                                        <td><div align="right"><strong>Quantity</strong></div></td>
                                        <td><div align="left"><input type="text" name="txQty" id="txQty" value="<?php echo $ppei_qty?>"></div></td>
                                    </tr>
                                    <tr>
                                        <td><div align="right"><strong>Size</strong></div></td>
                                        <td><div align="left"><input type="text" name="txSize" id="txSize" value="<?php echo $ppei_size?>"></div></td>
                                    </tr>
                                    <tr>
                                        <td><div align="right"><strong>No. Of Times Issued</strong></div></td>
                                        <td><div align="left"><input type="text" name="txIssuedCount" id="txIssuedCount" value="<?php echo $issued_count?>"></div></td>
                                    </tr>
                                    <tr>
                                        <td><div align="right"><strong>Date Issued</strong></div></td>
                                        <td>
                                            <div align="left">
                                                <a href="javascript:NewCssCal('txDateIssued')">
                                                <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                                                </a>
                                                <input name="txDateIssued" type="text" class="span6 mytextbox" id="txDateIssued" value="<?php echo $issued_date?>" style="width: 90px;" readonly>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><div align="right"><strong>Replacement Date</strong></strong></div></td>
                                        <td>
                                            <div align="left">
                                                <a href="javascript:NewCssCal('txDateReplace')">
                                                <img src="../js/datepick/cal.gif" width="16" height="16" border="0" alt="Click Here to Pick up the timestamp">
                                                </a>
                                                <input name="txDateReplace" type="text" class="span6 mytextbox" id="txDateReplace" value="<?php echo $replacement_date?>" style="width: 90px;" readonly>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><div align="right"><strong>Mode of Issuance</strong></div></td>
                                        <td><div align="left"><input type="text" name="txIssuanceMode" id="txIssuanceMode" value="<?php echo $issuance_mode?>" autocomplete='off' data-provide="typeahead" data-items="10" data-source='[<?php echo $namesMode;?>]'></div></td>
                                    </tr>
                                    <tr>
                                        <td><div align="right"><strong>Remark</strong></div></td>
                                        <td><div align="left"><textarea name="txRemark" id="txRemark"><?php echo $remark?></textarea></div></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>
                                            <div align="left">
                                                <?php 
                                                if($editTrue)
                                                    echo '<input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary">';
                                                else
                                                    echo '<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="btn btn-primary">';
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
 
                                <table width="100%" border="1" style="font-size: 11px;">
                                    <tr>
                                        <th width="10%" scope="col"><div align="center">PPE Type</div></th>
                                        <th width="10%" scope="col"><div align="center">Brand</div></th>
                                        <th width="10%" scope="col"><div align="center">Quantity</div></th>
                                        <th width="10%" scope="col"><div align="center">Size</div></th>
                                        <th width="10%" scope="col"><div align="center">No. Of Times Issued</div></th>
                                        <th width="10%" scope="col"><div align="center">Date Issued</div></th>
                                        <th width="10%" scope="col"><div align="center">Replacement Date</div></th>
                                        <th width="10%" scope="col"><div align="center">Mode of Issuance</div></th>
                                        <th width="10%" scope="col"><div align="center">Remarks</div></th>
                                        <th width="10%" scope="col">&nbsp;</th>
                                    </tr>
                                    <?php
                                    $qItem = $db->select('ppe_item','*',array('ppe_id'=>$ppe_id),'ORDER BY ppei_type');
                                    while($rItem = $db->fetch_array($qItem)):
                                        $rID = $rItem['ppei_id'];
                                    ?>
                                    <tr>
                                        <td height="30"><div align="center"><?php echo $rItem['ppei_type'];?></div></td>
                                        <td><div align="center"><?php echo $rItem['ppei_brand'];?></div></td>
                                        <td><div align="center"><?php echo $rItem['ppei_qty'];?></div></td>
                                        <td><div align="center"><?php echo $rItem['ppei_size'];?></div></td>
                                        <td><div align="center"><?php echo $rItem['issued_count'];?></div></td>
                                        <td><div align="center"><?php echo functions::datearr($rItem['issued_date']);?></div></td>
                                        <td><div align="center"><?php echo functions::datearr($rItem['replacement_date']);?></div></td>
                                        <td><div align="center"><?php echo $rItem['issuance_mode'];?></div></td>
                                        <td><?php echo $rItem['remark'];?></td>
                                        <td>
                                            <div align="center">
                                                <a id="edit<?php echo $rID;?>" class="btn btn-mini btn-info" title="Update this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?ppe_id=<?php echo functions::encode($ppe_id);?>&txItmEdt=<?php echo functions::encode($rID);?>"><i class="halflings-icon white pencil"></i></a>
                                                <a id="del<?php echo $rID;?>" class="btn btn-mini btn-warning" onClick="return delt()" title="Remove this item" data-rel="tooltip" href="<?php echo $_SERVER['PHP_SELF']?>?ppe_id=<?php echo functions::encode($ppe_id);?>&txItmDel=<?php echo functions::encode($rID);?>"><i class="halflings-icon white trash"></i></a>
                                             </div>
                                        </td>
                                    </tr>
                                    <?php endwhile;?>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td height="20"><div align="center"><strong>---------- NOTHING FOLLOWS ----------</strong></div></td>
                        </tr>
                        <tr>
                            <td>That I promise to properly use, care, and maintain the PPE that I am being issued. I also understand that: 1) This equipment is for my personal use while on the job as an employee of PDC and that it will be stored at the warehouse at all times after work; 2) It is my responsibility to wear the equipment properly and to inspect and maintain my PPE in accordance with the manufacturer’s recommendations; 3) I am responsible for immediately notifying the Safety Officer  to replace any lost, stolen, damaged or worn PPE; 4) I am responsible for immediately notifying my supervisor of any new job hazards which my require a hazard assessment and/or additional PPE. </td>
                        </tr>
                        <tr>
                            <td height="20"><div align="center">&nbsp;</div></td>
                        </tr>
                        <tr>
                            <td align="center">
                                <table width="100%" border="0" style="font-size: 12px;">
                                    <tr>
                                        <td align="center" width="20%">Prepared and Issued by:</td>
                                        <td align="center" width="20%">Received and Inspected by: </td>
                                    </tr>
                                    <tr>
                                        <td height="30" align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $prepared_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
                                        <td align="center" valign="bottom"><div style="text-decoration:underline"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $received_by;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></div></td>
                                    </tr>
                                    <tr>
                                        <td height="10" align="center"><div><strong><?php echo $prepared_by_title;?></strong></div></td>
                                        <td align="center"><div><strong><?php echo $received_by_title;?></strong></div></td>
                                    </tr>
                                    <tr>
                                        <td height="20" align="center" valign="bottom">Date: <strong><?php echo functions::datearr($prepared_date);?></strong></td>
                                        <td align="center" valign="bottom">Date: <strong><?php echo functions::datearr($received_date);?></strong></td>
                                    </tr>
                                </table><br><br><br>
                            </td>
                        </tr>
                    </table><br><br><br>
                </div>
            </div>
        </div>
    </form>
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
<script>
function delt(){
    if(confirm('Do you want to remove this?'))
        return true;
    else
        return false; 
}
</script>
<!-- end: JavaScript-->
</body>
</html>