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

if( isset($_POST['btnAdd']) ){
    function addChild($baseParent='',$parentName='',$childName,$rate=0,$unit='day'){
        global $db;
        $baseItemID=$db->getValue('elm_rate','id',array('itemDesc'=>$baseParent,'itemParent'=>'0'));
        $itemBaseSel = $db->getValue('elm_rate','itemBase',array('id'=>$baseItemID));
        $parentItemID = $db->getValue('elm_rate','id',array('itemDesc'=>$parentName,'itemParent'=>$baseItemID));
        $itemDesc = $childName;
        $parentItemNo = $db->getValue('elm_rate','itemNo',array('id'=>$parentItemID));

        if($parentItemID){
            $itemNo = $db->getValue('elm_rate','count(itemNo)',array('itemParent'=>$parentItemID));
            $itemBase = $db->getValue('elm_rate','itemBase',array('id'=>$parentItemID));
            $parentItemNo = $db->getValue('elm_rate','itemNo',array('id'=>$parentItemID));
            $finalItemNo = $parentItemNo.'.'.($db->getValue('elm_rate','COUNT(*)',array('itemParent'=>$parentItemID)) + 1);
            $db->insert('elm_rate',array('itemBase'=>$itemBase,'itemParent'=>$parentItemID,'itemNo'=>$finalItemNo,'itemDesc'=>$itemDesc,'itemRate'=>$rate,'itemUnit'=>$unit));
        }
    }
  
    $arrDetails=array();
    $selClass = ( isset($_POST['selClass']) && !empty($_POST['selClass']) ) ? trim($_POST['selClass']) : '';
    $selCat = ( isset($_POST['selCat']) && !empty($_POST['selCat']) ) ? trim($_POST['selCat']) : '';
    $selType = ( isset($_POST['selType']) && !empty($_POST['selType']) ) ? trim($_POST['selType']) : '';
    $txInventoryNo = ( isset($_POST['txInventoryNo']) && !empty($_POST['txInventoryNo']) ) ? trim($_POST['txInventoryNo']) : '';
    $txDesc = ( isset($_POST['txDesc']) && !empty($_POST['txDesc']) ) ? trim($_POST['txDesc']) : '';
    $txModel = ( isset($_POST['txModel']) && !empty($_POST['txModel']) ) ? trim($_POST['txModel']) : '';
    $txBrand = ( isset($_POST['selBrand']) && !empty($_POST['selBrand']) ) ? trim($_POST['selBrand']) : '';
    $txSerialNo = ( isset($_POST['txSerialNo']) && !empty($_POST['txSerialNo']) ) ? trim($_POST['txSerialNo']) : '';
    $txPlateNo = ( isset($_POST['txPlateNo']) && !empty($_POST['txPlateNo']) ) ? trim($_POST['txPlateNo']) : '';
    $txMVNo = ( isset($_POST['txMVNo']) && !empty($_POST['txMVNo']) ) ? trim($_POST['txMVNo']) : '';
    $txEngineNo = ( isset($_POST['txEngineNo']) && !empty($_POST['txEngineNo']) ) ? trim($_POST['txEngineNo']) : '';
    $txChassisNo = ( isset($_POST['txChassisNo']) && !empty($_POST['txChassisNo']) ) ? trim($_POST['txChassisNo']) : '';
    $txQuantity = ( isset($_POST['txQuantity']) && !empty($_POST['txQuantity']) ) ? trim($_POST['txQuantity']) : 1;
    $txLiterHour = ( isset($_POST['txLiterHour']) && !empty($_POST['txLiterHour']) ) ? trim($_POST['txLiterHour']) : NULL;
    $txLiterKm = ( isset($_POST['txLiterKm']) && !empty($_POST['txLiterKm']) ) ? trim($_POST['txLiterKm']) : NULL;
    $txUnit = ( isset($_POST['txUnit']) && !empty($_POST['txUnit']) ) ? trim($_POST['txUnit']) : 'unit';
    $txSupplier = ( isset($_POST['txSupplier']) && !empty($_POST['txSupplier']) ) ? $_POST['txSupplier'] : NULL;
    
    $txProposedNoYr = ( isset($_POST['txProposedNoYr']) && !empty($_POST['txProposedNoYr']) ) ? trim($_POST['txProposedNoYr']) : '';
    $txRate = ( isset($_POST['txRate']) && !empty($_POST['txRate']) ) ? functions::moneyToDouble($_POST['txRate']) : '';
    $txUsageLimit = ( isset($_POST['txUsageLimit']) && !empty($_POST['txUsageLimit']) ) ? trim($_POST['txUsageLimit']) : '';
    $txWarranty = ( isset($_POST['txWarranty']) && !empty($_POST['txWarranty']) ) ? trim($_POST['txWarranty']) : '';

    $txPower = ( isset($_POST['txPower']) && !empty($_POST['txPower']) ) ? trim($_POST['txPower']) : '';
    $txLocation = ( isset($_POST['txLocation']) && !empty($_POST['txLocation']) ) ? trim($_POST['txLocation']) : '';
    $txGrossWt = ( isset($_POST['txGrossWt']) && !empty($_POST['txGrossWt']) ) ? trim($_POST['txGrossWt']) : '';
    $txNetWt = ( isset($_POST['txNetWt']) && !empty($_POST['txNetWt']) ) ? trim($_POST['txNetWt']) : '';
    $txShipWt = ( isset($_POST['txShipWt']) && !empty($_POST['txShipWt']) ) ? trim($_POST['txShipWt']) : '';
    $txPrice = ( isset($_POST['txPrice']) && !empty($_POST['txPrice']) ) ? functions::moneyToDouble($_POST['txPrice']) : '';
    $txRemark = ( isset($_POST['txRemark']) && !empty($_POST['txRemark']) ) ? trim($_POST['txRemark']) : '';
    $txStatus = ( isset($_POST['txStatus']) && !empty($_POST['txStatus']) ) ? trim($_POST['txStatus']) : '';

    $txDesc = str_replace(array("\r", "\n"), ' ', $txDesc);

    $arrDetails = array('classification'=>$selClass,'category'=>$selCat,'type'=>$selType,'liter_km'=>$txLiterKm,'liter_hr'=>$txLiterHour,'name'=>$txDesc,'equip_desc'=>$txDesc,'quantity'=>$txQuantity,'unit'=>$txUnit,'status'=>$txStatus);

    if($txSupplier)
        $arrDetails = array_merge($arrDetails,array('supplierID'=>$txSupplier));

    if($txInventoryNo)
        $arrDetails = array_merge($arrDetails,array('inventory_id'=>$txInventoryNo));

    if($txRate)
        $arrDetails = array_merge($arrDetails,array('rate'=>$txRate));

    if($txModel)
        $arrDetails = array_merge($arrDetails,array('model'=>$txModel));

    if($txBrand)
        $arrDetails = array_merge($arrDetails,array('brand'=>$txBrand));

    if($txSerialNo)
        $arrDetails = array_merge($arrDetails,array('serial_no'=>$txSerialNo));

    if($txPlateNo)
        $arrDetails = array_merge($arrDetails,array('plate_no'=>$txPlateNo));

    if($txMVNo)
        $arrDetails = array_merge($arrDetails,array('mvFileNo'=>$txMVNo));

    if($txEngineNo)
        $arrDetails = array_merge($arrDetails,array('engine_no'=>$txEngineNo));

    if($txChassisNo)
        $arrDetails = array_merge($arrDetails,array('chassis_no'=>$txChassisNo));

    if($txPower)
        $arrDetails = array_merge($arrDetails,array('power'=>$txPower));

    if($txLocation)
        $arrDetails = array_merge($arrDetails,array('location'=>$txLocation));

    if($txGrossWt)
        $arrDetails = array_merge($arrDetails,array('gross_wt'=>$txGrossWt));

    if($txNetWt)
        $arrDetails = array_merge($arrDetails,array('net_wt'=>$txNetWt));

    if($txShipWt)
        $arrDetails = array_merge($arrDetails,array('shipping_wt'=>$txShipWt));

    if($txPrice)
        $arrDetails = array_merge($arrDetails,array('price'=>$txPrice));

    if($txUsageLimit)
        $arrDetails = array_merge($arrDetails,array('limit_minutes'=>($txUsageLimit * 60)));

    if($txRemark)
        $arrDetails = array_merge($arrDetails,array('remarks'=>$txRemark));

    $txAcquiredMon = ( isset($_POST['txAcquiredMon']) && !empty($_POST['txAcquiredMon']) ) ? $_POST['txAcquiredMon'] : '';
    $txAcquiredDay = ( isset($_POST['txAcquiredDay']) && !empty($_POST['txAcquiredDay']) ) ? $_POST['txAcquiredDay'] : '';
    $txAcquiredYear = ( isset($_POST['txAcquiredYear']) && !empty($_POST['txAcquiredYear']) ) ? $_POST['txAcquiredYear'] : '';
    $txDateAcquired = $txAcquiredYear.'-'.$txAcquiredMon.'-'.$txAcquiredDay;
    if($txAcquiredMon && $txAcquiredDay && $txAcquiredYear){
        $arrDetails = array_merge($arrDetails,array('date_acquired'=>$txDateAcquired));
        if( $txProposedNoYr > 0)
            $arrDetails = array_merge($arrDetails,array('proposed_life'=>($txAcquiredYear + $txProposedNoYr).'-'.$txAcquiredMon.'-'.$txAcquiredDay));
        if( $txWarranty > 0)
            $arrDetails = array_merge($arrDetails,array('warranty'=>($txAcquiredYear + $txWarranty).'-'.$txAcquiredMon.'-'.$txAcquiredDay));
    }

    $txOutMon = ( isset($_POST['txOutMon']) && !empty($_POST['txOutMon']) ) ? $_POST['txOutMon'] : '';
    $txOutDay = ( isset($_POST['txOutDay']) && !empty($_POST['txOutDay']) ) ? $_POST['txOutDay'] : '';
    $txOutYear = ( isset($_POST['txOutYear']) && !empty($_POST['txOutYear']) ) ? $_POST['txOutYear'] : '';
    if($txStatus=="Unserviceable" || $txStatus=="Sold"){
        if( $txOutYear && $txOutMon && $txOutDay )
            $arrDetails = array_merge($arrDetails,array('out_date'=>$txOutYear.'-'.$txOutMon.'-'.$txOutDay));
    }

    if( $txDesc &&  $selType && $selClass ){
        $qInsert = $db->insertPrint('equipment',$arrDetails);
        $db->query($qInsert);
        $equip_id = $db->insert_id();

        if( !empty($_FILES['image']) && $_FILES['image']['error'] == 0 && $equip_id ) {
            $uploaddir = '../img_equip/';
            $max_size = 6000 * 3072; // 500 KB
            // Generates random filename and extension 
            function tempnam_sfx($path, $suffix){
                do{
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
            /*else if( $_FILES["image"]["size"] > $max_size ){
                functions::say("Image reached the limit size!");
            }*/
            else{
                // Rename both the image and the extension 
                $uploadfile = tempnam_sfx($uploaddir, ".jpg");
                // Upload the file to a secure directory with the new name and extension
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
                    $oldFileName = $db->getValue('images','name',array('equip_id'=>$equip_id));
                    if($oldFileName){
                        if(file_exists($oldFileName))
                            unlink('../img_equip/'.$oldFileName);
                    }
                    $db->delete('images',array('equip_id'=>$equip_id));
                    $db->insert('images',array('name'=>basename($uploadfile),'original_name'=>basename($_FILES['image']['name']),'mime_type'=>$_FILES['image']['type'],'equip_id'=>$equip_id));
                }
                else{
                    functions::say("Image upload failed!");
                }
            }
        }
      functions::sendTo('admin-equipment-view.php?vdidVw='.functions::encode($equip_id));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Property Add</title>
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
    <style>.tdSpace{padding: 12px 0px 4px 0px;}</style>
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>EQUIPMENT ADD</h2>
        </div>
        <div class="box-content">
            <div align="center">
                <form method="post" enctype="multipart/form-data">
                    <table width="80%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <th width="35%" align="right" scope="row">&nbsp;</th>
                            <td width="2%">&nbsp;</td>
                            <td width="50%">&nbsp;</td>
                            <td width="13%">&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Property Classification</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="selClass" id="selClass" style="width:250px;" required>
                                    <option value="">-- Select --</option>
                                    <?php
                                    $qType = $db->query('SELECT * FROM equipment_type WHERE type_name="classification" ORDER BY type_desc ');
                                    while($rType = $db->fetch_array($qType)):
                                    ?>
                                     <option value="<?php echo $rType['type_desc']?>"><?php echo $rType['type_desc']?></option>
                                     <?php endwhile;?>
                                </select>
                                <a id="AddClass" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-equipment-add-class.php?','Add Equipment Classification')" title="Add New Equipment Classification" data-rel="tooltip"><i class="halflings-icon plus-sign"></i></a>
                                <span class="help-inline warning" id="MsgSelClass" style="font-weight:bold;" name="MsgSelClass"></span>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Property Category</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="selCat" id="selCat" style="width:250px;" required>
                                    <option value="">-- Select --</option>
                                    <option value="Fixed Assets">Fixed Assets</option>
                                    <option value="Consumable Tools and Supplies">Consumable Tools and Supplies</option>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Property Type</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="selType" id="selType" style="width:250px;" required>
                                    <option value="">-- Select --</option>
                                    <?php
                                    $qType = $db->query('SELECT * FROM equipment_type WHERE type_name="type" ORDER BY type_desc ');
                                    while($rType = $db->fetch_array($qType)):
                                    ?>
                                    <option value="<?php echo $rType['type_desc']?>"><?php echo $rType['type_desc']?></option>
                                    <?php endwhile;?>
                                </select>
                                <a id="AddType" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-equipment-add-type.php?','Add Equipment Type')" title="Add New Equipment Type" data-rel="tooltip"><i class="halflings-icon plus-sign"></i></a>
                                <span class="help-inline warning" id="MsgSelType" style="font-weight:bold;" name="MsgSelType"></span>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Brand/Made</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="selBrand" id="selBrand" style="width:250px;">
                                    <option value="">-- Select --</option>
                                    <?php
                                    $qBrand = $db->query('SELECT * FROM equipment_type WHERE type_name="brand" ORDER BY type_desc ');
                                    while($rBrand = $db->fetch_array($qBrand)):
                                    ?>
                                    <option value="<?php echo $rBrand['type_desc']?>"><?php echo $rBrand['type_desc']?></option>
                                    <?php endwhile;?>
                                </select>
                                <a id="AddBrand" class="thickbox" style="cursor:pointer" onclick="showThis(this.id,'admin-equipment-add-brand.php?','Add Equipment Brand / Made')" title="Add New Brand" data-rel="tooltip"><i class="halflings-icon plus-sign"></i></a>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Property Picture</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">Select image to upload <input type="file" name="image"></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Property Code</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txInventoryNo" id="txInventoryNo" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Description</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <textarea type="text" name="txDesc" id="txDesc" style="width:300px"></textarea>
                                <span class="help-inline warning" id="MsgtxDesc" style="font-weight:bold;" name="MsgtxDesc"></span>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Model</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txModel" id="txModel" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Serial Number</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txSerialNo" id="txSerialNo" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Plate Number</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txPlateNo" id="txPlateNo" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">MV File Number</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txMVNo" id="txMVNo" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Engine Number</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txEngineNo" id="txEngineNo" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Chassis Number</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txChassisNo" id="txChassisNo" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Power / Capacity</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txPower" id="txPower" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Gross Weight</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txGrossWt" id="txGrossWt" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Net Weight</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txNetWt" id="txNetWt" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Shipping Weight</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txShipWt" id="txShipWt" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Average Liter/Hour</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txLiterHour" id="txLiterHour" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Average Liter/Km</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txLiterKm" id="txLiterKm" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Acquisition Cost</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" class="span6"  name="txPrice" id="txPrice" autocomplete='off' value="" onkeyup="FormatCurrency(this);"></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Location</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txLocation" id="txLocation" value="" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Date Acquired</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="txAcquiredMon" id="txAcquiredMon" style="width:80px;">
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
                                <select name="txAcquiredDay" id="txAcquiredDay" style="width:60px;">
                                    <option value="">Day</option>
                                    <?php for($i=1;$i<=31;$i++):?>
                                    <option value="<?php echo ($i<10) ? '0'.$i : $i;?>"><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                                <select name="txAcquiredYear" id="txAcquiredYear" style="width:70px;">
                                    <option value="">Year</option>
                                    <?php for($y=(date('Y') + 2);$y>=2000;$y--):?>
                                    <option value="<?php echo $y;?>"><?php echo $y;?></option>
                                    <?php endfor;?>
                                </select>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Warranty (No. of Years)</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txWarranty" id="txWarranty" value="" onkeypress="return checkinput(this, event);"/></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Useful Life (No. of Years)</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txProposedNoYr" id="txProposedNoYr" value="" onkeypress="return checkinput(this, event);"/></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Rate Per Hour</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txRate" id="txRate" value="" onkeyup="FormatCurrency(this);"/></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Maintenance Period (No. of Hours)</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><input type="text" name="txUsageLimit" id="txUsageLimit" value="" onkeypress="return checkinput(this, event);"/></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Quantity</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="txQuantity" id="txQuantity" data-rel="chosen" style="width:80px;">
                                    <?php for($i=1;$i<=100;$i++):?>
                                    <option value="<?php echo $i;?>"><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                                &nbsp;
                                <select name="txUnit" id="txUnit" style="width:80px;">
                                    <option value="unit">Unit</option>
                                    <option value="piece">Piece</option>
                                    <option value="set">Set</option>
                                    <option value="pair">Pair</option>
                                </select>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Status</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="txStatus" id="txStatus" style="width:200px;">
                                    <option value="">--Select--</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="Good Condition">Good Condition</option>
                                    <option value="For Repair">For Repair</option>
                                    <option value="For Rehab">For Rehab</option>
                                    <option value="Unserviceable">Unserviceable</option>
                                    <option value="Sold">Sold</option>
                                </select>
                                <select name="txOutMon" id="txOutMon" style="width:80px;">
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
                                <select name="txOutDay" id="txOutDay" style="width:60px;">
                                    <option value="">Day</option>
                                    <?php for($i=1;$i<=31;$i++):?>
                                    <option value="<?php echo ($i<10)? '0'.$i : $i;?>"><?php echo $i;?></option>
                                    <?php endfor;?>
                                </select>
                                <select name="txOutYear" id="txOutYear" style="width:70px;">
                                    <option value="">Year</option>
                                    <?php for($y=(date('Y') + 2);$y>=2000;$y--):?>
                                    <option value="<?php echo $y;?>"><?php echo $y;?></option>
                                    <?php endfor;?>
                                </select>
                                <span class="help-inline warning" id="MsgStatus" style="font-weight:bold;" name="MsgStatus"></span>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Remarks</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace"><textarea type="text" name="txRemark" id="txRemark" style="width:300px"></textarea></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th align="right" scope="row">Supplier</th>
                            <td>&nbsp;</td>
                            <td class="tdSpace">
                                <select name="txSupplier" id="txSupplier" data-rel="chosen" style="width:500px;">
                                    <option value="">--select--</option>
                                    <?php $qSup = $db->select('supplier','*',array(),'ORDER BY name');
                                          while($rSup = $db->fetch_array($qSup)):
                                    ?>
                                    <option value="<?php echo $rSup['supplierID']?>"><?php echo ucwords(strtolower($rSup['name']));?></option>
                                    <?php endwhile;?>
                                </select>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="tdSpace" colspan="4" align="center"><input type="submit" name="btnAdd" id="btnAdd" value="Add Property" class="btn btn-primary"></td>
                        </tr>
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
<script src="../js/wxhBox.js"></script>
<script src="../js/thickboxa.js"></script>
<link rel="stylesheet" type="text/css" href="../css/thickbox.css"/>
<script src="../js/showPage.js"></script>
<script>
function ask(){
    if(confirm('Do you want to add this property?'))
        return true;
    else
        return false; 
}
$(document).ready(function(){
    var res = false;

    $('#txOutMon').hide();
    $('#txOutDay').hide();
    $('#txOutYear').hide();

    $('#txStatus').change(function(){
        if( $('#txStatus').val()=="Unserviceable" || $('#txStatus').val()=="Sold" ){
            $('#txOutMon').show();
            $('#txOutDay').show();
            $('#txOutYear').show();
        }
        else{
            $('#txOutMon').hide();
            $('#txOutDay').hide();
            $('#txOutYear').hide();
        }
    });
    $('#btnAdd').click(function(){
        $('#MsgSelClass').html("");
        $('#MsgSelType').html("");
        $('#MsgtxDesc').html("");
        $('#MsgStatus').html("");
          

        if( $('#selClass').val()=="" ){
            $('#MsgSelClass').html("Classification Required!");
            res=false;
        }
        else if( $('#selType').val()=="" ){
            $('#MsgSelType').html("Type Required!");
            res=false;
        }
        else if( $('#txDesc').val()=="" ){
            $('#MsgtxDesc').html("Description Required!");
            res=false;
        }
        else if( $('#txStatus').val()=="" ){
            $('#MsgStatus').html("Status Required!");
            res=false;
        }
        else{
            if(ask())
              res=true;
        }
        return res;
    });
});
</script>
<!-- end: JavaScript-->
</body>
</html>