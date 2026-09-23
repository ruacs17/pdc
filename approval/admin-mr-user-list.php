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
$eu_id=(isset($_REQUEST['eu_id']) && !empty($_REQUEST['eu_id']) ) ? functions::decode($_REQUEST['eu_id']) : 0;
$eu_idDel=(isset($_REQUEST['eu_idDel']) && !empty($_REQUEST['eu_idDel']) ) ? functions::decode($_REQUEST['eu_idDel']) : 0;
if($eu_idDel){
    $db->delete('equip_user',array('eu_id'=>$eu_idDel));
    functions::sendTo($_SERVER['PHP_SELF']);
}
$startrow=( isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay=20;
$arrVal=array();
if($eu_id){
    $arrVal = array('eu_id'=>$eu_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>MR Personnel</title>
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
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>MR Personnel</h2>
        </div>
        <div class="box-content">
            <ul class="nav tab-menu nav-tabs">
                <li class="active"><a href="admin-mr-user-list.php" style="opacity:.9">Personnel List</a></li>
                <li><a href="admin-mr-user-add.php">Add Personnel</a></li>
            </ul>
        </div>
        <div class="box-content">
            <div align="center">
                <form method="post">
                    <div align="left"><br>&nbsp;&nbsp;
                        <select name="selProj" id="selProj" data-rel="chosen" style="width:650px;" onChange="userSel(this.value)">
                            <option value="">--All Personnel--</option>
                            <?php $qUser = $db->select('equip_user','*',array(),'ORDER BY lname');
                                  while($rUser = $db->fetch_array($qUser)):
                            ?>
                            <option value="<?php echo functions::encode($rUser['eu_id'])?>" <?php if($eu_id==$rUser['eu_id'])echo 'selected="selected"';?>><?php echo strtoupper($rUser['lname'].', '.$rUser['fname']);?></option>
                            <?php endwhile;?>
                        </select>
                    </div>
                    <div class="box-content">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="20%">Name</th>
                                    <th width="8%">Birth Date</th>
                                    <th width="8%">Marital Status</th>
                                    <th width="8%">Contact No.</th>
                                    <th width="8%">Address</th>
                                    <th width="8%">Position</th>
                                    <th width="8%">Date Hired</th>
                                    <th width="8%">Status</th>
                                    <th width="10%"><div align="center">Options</div></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $qUser = $db->select('equip_user','*',$arrVal,'ORDER BY lname');
                            $num_record = $db->getValue('equip_user','count(*)',$arrVal);
                            while($rUser = $db->fetch_array($qUser)):
                                $allowDel=1;
                                if( $db->getValue('mr','count(*)',array('eu_id'=>$rUser['eu_id'])) )
                                    $allowDel=0;
                                if( $db->getValue('mr_item','count(*)',array('end_user'=>$rUser['eu_id'])) )
                                    $allowDel=0;
                                if( $db->getValue('stockshare','count(*)',array('eu_id'=>$rUser['eu_id'])) )
                                    $allowDel=0;
                                if( $db->getValue('po_fuel','count(*)',array('requester'=>$rUser['eu_id'])) )
                                    $allowDel=0;
                                if( $db->getValue('equip_fuel','count(*)',array('requester'=>$rUser['eu_id'])) )
                                    $allowDel=0;
                                if( $db->getValue('inhouse_material','count(*)',array('check_by'=>$rUser['eu_id'])) )
                                    $allowDel=0;
                                if( $db->getValue('inhouse_material','count(*)',array('deliver_by'=>$rUser['eu_id'])) )
                                    $allowDel=0;
                                if( $db->getValue('inhouse_material','count(*)',array('receive_by'=>$rUser['eu_id'])) )
                                    $allowDel=0;

                                $bgColor='';
                                $consumedPercent=0;
                            ?>
                                <tr <?php echo $bgColor;?>>
                                    <td><?php echo strtoupper($rUser['lname'].', '.$rUser['fname']);?></td>
                                    <td><?php echo functions::datearr($rUser['bdate']);?></td>
                                    <td><?php echo strtoupper($rUser['marital_status']);?></td>
                                    <td><?php echo $rUser['mobile'];?></td>
                                    <td><?php echo $rUser['address'];?></td>
                                    <td><?php echo $rUser['position'];?></td>
                                    <td><?php echo functions::datearr($rUser['date_hired']);?></td>
                                    <td><?php echo strtoupper($rUser['emp_status']);?></td>
                                    <td>
                                        <div align="center">
                                            <a id="edit<?php echo $rUser['eu_id']?>" class="btn btn-mini btn-warning thickbox" title="Modify this Account" data-rel="tooltip" onclick="showThis(this.id,'admin-mr-user-edit.php?eu_id=<?php echo functions::encode($rUser['eu_id']);?>','Information Update')"><i class="halflings-icon white pencil"></i></a>
                                            <?php if($allowDel==1){?>
                                            <a id="del<?php echo $rUser['eu_id'];?>" class="btn btn-mini btn-danger" onClick="return delt()" title="Remove this User" data-rel="tooltip" href="?eu_idDel=<?php echo functions::encode($rUser['eu_id']);?>"><i class="halflings-icon white trash"></i></a>
                                            <?php }?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile;?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
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
function userSel(PiEwgD){
    if(PiEwgD)
        window.location="<?php echo $_SERVER['PHP_SELF']?>?eu_id="+PiEwgD 
    else 
        window.location="<?php echo $_SERVER['PHP_SELF']?>"
}
function delt(){
    if(confirm('Do you want to remove this Personnel?'))
        return true;
    else
        return false; 
}
</script>
<!-- end: JavaScript-->
</body>
</html>