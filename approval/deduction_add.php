<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname," ",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');

$did = (isset($_REQUEST['did']) && !empty($_REQUEST['did']) ) ? functions::decode($_REQUEST['did']) : 0;

$deduct_name='';$deduct_desc='';$numbering='';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title>Charge Add</title>
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
<?php
if( isset($_POST['btnSave']) ){
    $arrUpdate=array();
    $inchargeID="";
    $txDeduct_name = ( isset($_POST['txDeduct_name']) && !empty($_POST['txDeduct_name']) ) ? $_POST['txDeduct_name'] : '';
    $txDeduct_desc = ( isset($_POST['txDeduct_desc']) && !empty($_POST['txDeduct_desc']) ) ? $_POST['txDeduct_desc'] : '';
    $expenseType = ( isset($_POST['expenseType']) && !empty($_POST['expenseType']) ) ? $_POST['expenseType'] : '';
    $numbering = ( isset($_POST['txNumber']) && !empty($_POST['txNumber']) ) ? trim($_POST['txNumber']) : '';
    $costType = ( isset($_POST['costType']) && !empty($_POST['costType']) ) ? $_POST['costType'] : '';
    $selAccountType = ( isset($_POST['selAccountType']) && !empty($_POST['selAccountType']) ) ? $_POST['selAccountType'] : '';
    $xp = explode("|", $selAccountType);
    $account_type = isset($xp[0]) ? $xp[0] : '';
    $account_type_group = isset($xp[1]) ? $xp[1] : '';
    if( $txDeduct_name && $txDeduct_desc && $expenseType && $costType && $account_type && $account_type_group ){
        if( $db->getValue('item_deduction','count(name)',array('name'=>$txDeduct_name))==0 ){ #if there's changes on project name, check if it's unique
            $insertID = $db->insert('item_deduction',array('numbering'=>$numbering,'name'=>$txDeduct_name,'description'=>$txDeduct_desc,'expense_type'=>$expenseType,'cost_type'=>$costType,'account_type'=>$account_type,'account_type_group'=>$account_type_group));
            functions::say('Category name successfully added!');
            functions::sendTo('deduction_add.php');   
        }
        else{
            functions::say('Category name already existed!');
        }
    }
    else
         functions::say('Please fill up the form properly!');
}
?>
    <!-- end: Favicon -->
</head>
<body>
<!-- body content: start here-->
<div class="row-fluid">
    <div class="box span12">
        <div class="box-header" data-original-title>
            <h2><i class="halflings-icon white edit"></i><span class="break"></span>Charge Category Add Form</h2>
        </div>
        <div class="box-content">
            <form class="form-horizontal" method="post">
                <table width="80%" align="center" border="0" class="table table-bordered" style="background-color:#E4E1E1">
                    <tr>
                      	<td width="17%" height="30">Category Name</td>
                        <td width="43%"><input type="text" name="txDeduct_name" id="txDeduct_name" class="span6" value="<?php echo $deduct_name;?>"></td>
                    </tr>
                    <tr>
                      	<td height="30">Description</td>
                        <td><input type="text" name="txDeduct_desc" id="txDeduct_desc" class="span6" value="<?php echo $deduct_desc?>" /></td>
                    </tr>
                    <tr>
                        <td height="30">Project Expense Type</td>
                        <td>
                            <select name="expenseType" id="expenseType">
                                <option value="overhead">Overhead (default)</option>
                				<option value="materials">Materials</option>
                				<option value="labor">Labor</option>
                				<option value="equipment">Equipment</option>
                                <option value="subcon">Subcon</option>
              				</select>
                        </td>
                    </tr>
                    <tr>
                        <td height="30">Direct Cost Type</td>
                        <td>
                            <select name="costType" id="costType">
                              <option value="operating expenses">Operating Expenses (default)</option>
                              <option value="commitment">Commitment</option>
                              <option value="finders fee">Finder's Fee</option>
                              <option value="consultancy">Consultancy</option>
                              <option value="technical fee">Techincal Fee</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td height="30">Account Type</td>
                        <td>
                            <select data-placeholder="Account Type" name="selAccountType" id="selAccountType" data-rel="chosen" style="width:460px;">
                                <option value=""></option>
                                <optgroup label="ASSETS">
                                    <option value="assets|current asset">CURRENT ASSETS</option>
                                    <option value="assets|non-current asset">NON-CURRENT ASSETS</option>
                                </optgroup>
                                <optgroup label="LIABILITIES">
                                    <option value="liabilities|current liabilities">CURRENT LIABILITIES</option>
                                    <option value="liabilities|non-current liabilities">NON-CURRENT LIABILITIES</option>
                                </optgroup>
                                <optgroup label="EQUITY">
                                    <option value="equity|equity">EQUITY</option>
                                </optgroup>
                                <optgroup label="INCOME">
                                    <option value="income|income">INCOME</option>
                                </optgroup>
                                <optgroup label="EXPENSE">
                                    <option value="expense|direct labor">DIRECT LABOR</option>
                                    <option value="expense|direct material">DIRECT MATERIALS</option>
                                    <option value="expense|sub contractor">SUB CONTRACTOR</option>
                                    <option value="expense|indirect cost">INDIRECT COST</option>
                                    <option value="expense|general, administrative and selling expense">GENERAL, ADMINISTRATIVE AND SELLING EXPENSE</option>
                                </optgroup>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td height="30">Numbering</td>
                        <td><input type="text" name="txNumber" id="txNumber" class="span6" style="width:100px" value="<?php echo $numbering?>" onkeypress="return checkinput(this, event);" /></td>
                    </tr>
                </table>
                <div align="center">
                    <input type="submit" name="btnSave" id="btnSave" value="Save" class="btn btn-primary">
                    <input type="submit" name="btnCancel" id="btnCancel" value="Cancel" class="btn">
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
<!-- end: JavaScript-->
</body>
</html>