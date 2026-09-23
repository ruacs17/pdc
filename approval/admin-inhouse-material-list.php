<?php require_once('authorize.php');
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');

$db = new Database();
$count=0;
$qSL='';
$srch = (isset($_REQUEST['srch']) && !empty($_REQUEST['srch']) ) ? $db->clean(trim($_REQUEST['srch'])) : 0;
$srchLoc = (isset($_REQUEST['loc']) && !empty($_REQUEST['loc']) ) ? $db->clean(trim(functions::decode($_REQUEST['loc']))) : '';
if($srchLoc)
    $qSL = 'location="'.$srchLoc.'" AND ';
$im_id = (isset($_REQUEST['im_id']) && !empty($_REQUEST['im_id']) ) ? $_REQUEST['im_id'] : 0;
$poidEdt = (isset($_REQUEST['poidEdt']) && !empty($_REQUEST['poidEdt']) ) ? $_REQUEST['poidEdt'] : 0;

if($srch){ ?>
<table class="table table-hover" border="0">
    <tr>
        <td width="40%"><strong>Item</strong></td>
        <td width="10%"><strong>Unit</strong></td>
        <td width="15%"><strong>Brand</strong></td>
        <td width="10%"><div align="center"><strong>Availabe</strong></div></td>
        <td width="15%"><strong>Location</strong></td>
        <td width="10%">&nbsp;</td>
    </tr>
    <?php
    $q = $db->query('SELECT item,unit,brand,location,sum(quantity) as qty FROM `inhouse_material_storage` WHERE '.$qSL.' (item LIKE "%'.$srch.'%" OR unit LIKE "%'.$srch.'%" OR brand LIKE "%'.$srch.'%") GROUP BY item,unit,brand,location ORDER BY item, unit, brand LIMIT 40');
    while($r = $db->fetch_array($q)):
        $count++;
        $consumed = $db->getValue('inhouse_material_item','sum(quantity)',array('item'=>$r['item'],'unit'=>$r['unit'],'brand'=>$r['brand'],'location'=>$r['location']));
        $available = $r['qty'] - $consumed;
        $lnk = '?im_id='.$im_id.'&poidEdt='.$poidEdt.'&srcItm='.functions::encode($r['item']).'&srcBrnd='.functions::encode($r['brand']).'&srcUnit='.functions::encode($r['unit']).'&srcItmQtyAvlbl='.functions::encode($available).'&srcLoc='.functions::encode($r['location']);
        if($available){
    ?>
    <tr onclick="javascript:location.href='<?php echo $lnk;?>'">
<?php }else{?>
    <tr>
    <?php }?>
        <td><?php echo $r['item']?></td>
        <td><?php echo $r['unit']?></td>
        <td><?php echo $r['brand']?></td>
        <td><div align="center"><?php echo $available?></div></td>
        <td><?php echo $r['location']?></td>
        <td><?php if($available){?><div align="center"><a href="<?php echo $lnk;?>" class="btn btn-mini btn-success">select</a></div><?php }?></td>
    </tr>
<?php endwhile;?>
<?php if($count==0){?>
    <tr>
        <td colspan="6"><div align="center"><strong>----- No Result -----</strong></div></td>
    </tr>
<?php }?>
    <tr>
        <td colspan="6"></td>
    </tr>
</table>
<br><br><br>
<?php }?>