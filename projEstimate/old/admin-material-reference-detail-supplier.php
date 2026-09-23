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
$arrVal=array();
$searchVal='';
$selAccredited='';$selEvaluated='';
$orderBy = (isset($_REQUEST['orderBy']) && !empty($_REQUEST['orderBy']) ) ? functions::decode($_REQUEST['orderBy']) : 'name';

$ascDes = (isset($_REQUEST['ascDes']) && !empty($_REQUEST['ascDes']) ) ? $_REQUEST['ascDes'] : 'DESC';
$sort_AscDesc="ASC";
if($ascDes==="DESC"){
    $ascDes="ASC";
    $sort_AscDesc="ASC";
}    
else if($ascDes==="ASC"){
    $ascDes="DESC";
    $sort_AscDesc="DESC";
}

if( isset($_REQUEST['srch']) ){
    $searchVal = ( isset($_REQUEST['srch']) ) ? $db->clean(trim($_REQUEST['srch'])) : '';
    $selAccredited = ( isset($_REQUEST['selAccredited']) ) ? $_REQUEST['selAccredited'] : '';
    $selEvaluated = ( isset($_REQUEST['selEvaluated']) ) ? $_REQUEST['selEvaluated'] : '';
    $qSearchAccredited = ($selAccredited) ? 'accredited="'.$db->clean($selAccredited).'" AND ' : '';
    $qSearchEvaluated = ($selEvaluated) ? 'evaluated="'.$db->clean($selEvaluated).'" AND ' : '';
    $qDisp = $db->query('SELECT * FROM supplier WHERE '.$qSearchEvaluated.$qSearchAccredited.' (name LIKE "%'.$db->clean($searchVal).'%" OR business_type LIKE "%'.$db->clean($searchVal).'%") ORDER BY name');
}
else if( isset($_POST['btnSearch']) ){
    $searchVal = ( isset($_POST['txSupName']) ) ? $_POST['txSupName'] : '';
    $selAccredited = ( isset($_POST['selAccredited']) ) ? $_POST['selAccredited'] : '';
    $selEvaluated = ( isset($_POST['selEvaluated']) ) ? $_POST['selEvaluated'] : '';
    $qSearchAccredited = ($selAccredited) ? 'accredited="'.$db->clean($selAccredited).'" AND ' : '';
    $qSearchEvaluated = ($selEvaluated) ? 'evaluated="'.$db->clean($selEvaluated).'" AND ' : '';
    $qDisp = $db->query('SELECT * FROM supplier WHERE '.$qSearchEvaluated.$qSearchAccredited.' (name LIKE "%'.$db->clean($searchVal).'%" OR business_type LIKE "%'.$db->clean($searchVal).'%") ORDER BY name');
}
else
    $qDisp = $db->select('supplier','*',$arrVal,'ORDER BY name');
$arrDisp=array();
while($rDisp = $db->fetch_array($qDisp)):
    $contact = ($rDisp['contactPhoneNo']) ? $rDisp['contactPhoneNo'].' <br>' : '';
    $contact .= ($rDisp['contactPhoneNo2']) ? $rDisp['contactPhoneNo2'].' <br>' : '';
    $contact .= ($rDisp['contactCellNo']) ? $rDisp['contactCellNo'].' <br>' : '';
    $contact .= ($rDisp['contactCellNo2']) ? $rDisp['contactCellNo2'].' <br>' : '';
    $arrDisp[] = array('supplierID'=>$rDisp['supplierID'],'name'=>$rDisp['name'],'address'=>$rDisp['address'],'contactPhoneNo'=>$contact,'business_type'=>$rDisp['business_type'],'accredited'=>$rDisp['accredited'],'evaluated'=>$rDisp['evaluated'],'vat'=>$rDisp['vat']);
endwhile;
if(count($arrDisp))
    functions::sortMultiArray($arrDisp,$orderBy,$sort_AscDesc);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Material Reference</title>
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
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Material Reference</h2>
        </div>
        <div class="box-content">
            <form method="post">
                <div align="center">
                    <table border="0">
                        <tr>
                            <td style="padding-top: 7px;"><input type="text" name="txSupName" id="txSupName" value="<?php echo $searchVal?>"></td>
                            <td style="padding-top: 7px;">&nbsp;
                                <select name="selAccredited" id="selAccredited" style="width: 150px;">
                                    <option value="">--Accreditation--</option>
                                    <option value="Accredited" <?php if($selAccredited=='Accredited')echo 'selected="selected"';?>>Accredited</option>
                                    <option value="Pre-Accredited" <?php if($selAccredited=="Pre-Accredited")echo 'selected="selected"';?>>Pre-Accredited</option>
                                    <option value="Not Accredited" <?php if($selAccredited=="Not Accredited")echo 'selected="selected"';?>>Not Accredited</option>
                                </select>&nbsp;
                            </td>
                            <td style="padding-top: 7px;">&nbsp;
                                <select name="selEvaluated" id="selEvaluated" style="width: 150px;">
                                    <option value="">--Evaluation--</option>
                                    <option value="Evaluated" <?php if($selEvaluated=='Evaluated')echo 'selected="selected"';?>>Evaluated</option>
                                    <option value="Passed" <?php if($selEvaluated=='Passed')echo 'selected="selected"';?>>Passed</option>
                                    <option value="Failed" <?php if($selEvaluated=='Failed')echo 'selected="selected"';?>>Failed</option>
                                </select>&nbsp;
                            </td>
                            <td>&nbsp;&nbsp;<input type="submit" class="btn btn-info" name="btnSearch" id="btnSearch" value="Search"></td>
                            <td>&nbsp;&nbsp;<input type="submit" class="btn btn-info" name="btnAll" id="btnAll" value="View All"></td>
                        </tr>
                    </table>
                </div>
            </form>
            <table class="table table-bordered table-hover" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th width="20%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('name').'&ascDes='.$ascDes?>">NAME</a></th>
                        <th width="20%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('address').'&ascDes='.$ascDes?>">ADDRESS</a></th>
                        <th width="15%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('contactPhoneNo').'&ascDes='.$ascDes?>">CONTACT #</a></th>
                        <th width="11%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('business_type').'&ascDes='.$ascDes?>">BUSINESS TYPE</a></th>
                        <th width="11%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('accredited').'&ascDes='.$ascDes?>">ACCREDITATION</a></th>
                        <th width="11%" scope="col"><a href="?srch=<?php echo $searchVal?>&orderBy=<?php echo functions::encode('evaluated').'&ascDes='.$ascDes?>">EVALUATION</a></th>
                        <th width="9%" scope="col"><div align="center">OPTIONS</div></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($arrDisp as $itm):
                    ?>
                    <tr>
                        <td><?php echo $itm['name']; echo ' <i>('.$itm['vat'].')</i>';?></td>
                        <td><?php echo $itm['address']?></td>
                        <td><?php echo $itm['contactPhoneNo'];?></td>
                        <td><?php echo $itm['business_type']?></td>
                        <td><?php echo ($itm['accredited']) ? $itm['accredited'] : '----';?></td>
                        <td><?php echo ($itm['evaluated']) ? $itm['evaluated'] : '----';?></td>
                        <td>
                            <div align="center">
                                <a id="vw<?php echo $itm['supplierID']?>" class="btn btn-mini btn-info thickbox" title="Supplier Detail" data-rel="tooltip" onclick="showThis(this.id,'supplier_view.php?sid=<?php echo functions::encode($itm['supplierID']);?>','Supplier Detail','1')"><i class="halflings-icon white zoom-in"></i></a>
                                <a id="edit<?php echo $itm['supplierID']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Supplier" data-rel="tooltip" onclick="showThis(this.id,'supplier_edit.php?sid=<?php echo functions::encode($itm['supplierID']);?>','Supplier Detail')"><i class="halflings-icon white pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php
                        endforeach;
                    ?>
                </tbody>
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
<script src="../js/wxh.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>

$(document).ready(function(){
    var res = false;
    $('#btnAdd,#btnSave').click(function(){
        $('#msgtxItem').html("");
        $('#msgtxQty').html("");
        $('#msgtxQtyDel').html("");
        $('#MsgSelClass').html("");
        $('#MsgSelType').html("");
        $('#MsgSelCat').html("");
          
        if( $('#selCat').val()=="" ){
            $('#MsgSelCat').html("Specify Category!");
            $('#selCat').focus();
            res=false;
        }
        else if( $('#selClass').val()=="" ){
            $('#MsgSelClass').html("Specify Classification!");
            $('#selClass').focus();
            res=false;
        }
        else if( $('#selType').val()=="" ){
            $('#MsgSelType').html("Specify Type!");
            $('#selType').focus();
            res=false;
        }
        else if( $('#txItem').val()=="" ){
            $('#msgtxItem').html("Specify Item!");
            $('#txItem').focus();
            res=false;
        }
        else if( $('#txQty').val()=="" ){
            $('#msgtxQty').html("Required!");
            $('#txQty').focus();
            res=false;
        }
        else if( $('#txCost').val()=="" ){
            $('#msgtxCost').html("Required!");
            $('#txCost').focus();
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