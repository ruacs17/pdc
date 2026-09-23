<?php session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="10" ){
  header("Location: ../");
  die();
}
  require_once('../class/database.php');
  #require_once('../class/logs.php');
  require_once('../class/functions.php');
$db = new Database();
$count=0;
$srch = (isset($_REQUEST['srch']) && !empty($_REQUEST['srch']) ) ? $db->clean(trim($_REQUEST['srch'])) : 0;
?>
<table class="table table-hover" border="0">
  <tr>
    <td width="15%"><strong>Code</strong></td>
    <td width="45%"><strong>Item</strong></td>
    <td><strong>Unit</strong></td>
    <td><strong>Brand</strong></td>
    <td width="10%">&nbsp;</td>
  </tr>
<?php
if($srch){
$q = $db->query('SELECT * FROM material_reference WHERE item LIKE "%'.$srch.'%" OR m_code LIKE "%'.$srch.'%" OR unit LIKE "%'.$srch.'%" OR brand LIKE "%'.$srch.'%" ORDER BY item, unit, brand LIMIT 40');
while($r = $db->fetch_array($q)):
  $count++;
?>
<tr onclick="javascript:location.href='?srcItm=<?php echo functions::encode($r['mf_id'])?>'">
  <td><?php echo $r['m_code']?></td>
  <td><?php echo $r['item']?></td>
  <td><?php echo $r['unit']?></td>
  <td><?php echo $r['brand']?></td>
  <td><div align="center"><a href="?srcItm=<?php echo functions::encode($r['mf_id'])?>" class="btn btn-mini btn-success">select</a></div></td>
</tr>
<?php endwhile;?>
<?php if($count==0){?>
<tr>
  <td colspan="5"><div align="center"><strong>----- No Result -----</strong></div></td>
</tr>
<?php }?>
<tr>
  <td colspan="4"></td>
</tr>
</table>
<br><br><br>
<?php }?>