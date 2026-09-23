<?php require_once('authorize.php');
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');

$db = new Database();
$count=0;
$qSL='';
$srch = (isset($_REQUEST['srch']) && !empty($_REQUEST['srch']) ) ? $db->clean(trim($_REQUEST['srch'])) : 0;
$mrq_id = (isset($_REQUEST['mrq_id']) && !empty($_REQUEST['mrq_id']) ) ? $_REQUEST['mrq_id'] : 0;
$poidEdt = (isset($_REQUEST['poidEdt']) && !empty($_REQUEST['poidEdt']) ) ? $_REQUEST['poidEdt'] : 0;
$proj_id = (isset($_REQUEST['proj']) && !empty($_REQUEST['proj']) ) ? functions::decode($_REQUEST['proj']) : 0;
if($srch){ ?>
<table class="table table-hover" border="0">
    <tr>
        <td width="40%"><strong>Item</strong></td>
        <td width="10%"><strong>Unit</strong></td>
        <td width="15%"><strong>Brand</strong></td>
        <td width="10%"><div align="center"><strong>Availabe</strong></div></td>
        <td width="10%">&nbsp;</td>
    </tr>
    <?php
    $q = $db->query('SELECT item,unit,brand,sum(qty) as qty FROM medicine_distribution md, medicine_distribution_detail mdd WHERE md.md_id=mdd.md_id AND md.proj_id="'.$db->clean($proj_id).'" AND (item LIKE "%'.$srch.'%" OR unit LIKE "%'.$srch.'%" OR brand LIKE "%'.$srch.'%") GROUP BY item,unit,brand ORDER BY item, unit, brand LIMIT 40');
    #echo $db->last_query;
    while($r = $db->fetch_array($q)):
        $count++;
        $stored = (is_numeric($r['qty'])) ? $r['qty'] : 0;
        $cons = $db->getValue('medicine_request mrq, medicine_request_detail mrd','sum(qty)',array('item'=>$r['item'],'unit'=>$r['unit'],'brand'=>$r['brand'],'proj_id'=>$proj_id),'AND mrq.mrq_id=mrd.mrq_id');
        $consumed = (is_numeric($cons)) ? $cons : 0;
        $available = $stored - $consumed;
        $lnk = '?mrq_id='.$mrq_id.'&poidEdt='.$poidEdt.'&srcItm='.functions::encode($r['item']).'&srcBrnd='.functions::encode($r['brand']).'&srcUnit='.functions::encode($r['unit']).'&srcItmQtyAvlbl='.functions::encode($available);
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