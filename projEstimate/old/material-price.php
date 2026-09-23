<?php require_once('templ_up.php');?>
<?php
  $item=(isset($_POST['sel_item']) && !empty($_POST['sel_item']) ) ? functions::decode($_POST['sel_item']) : '';
  $itemDate=(isset($_REQUEST['dte']) && !empty($_REQUEST['dte']) ) ? functions::decode($_REQUEST['dte']) : '';
?>
      <!-- body content: start here-->
            <div class="row-fluid">
            <form method="post">
                <div class="box span12">
                    <div class="box-header" data-original-title>
                        <h2><i class="halflings-icon white edit"></i><span class="break"></span>Price History</h2>
                    </div>
                    <div class="box-content">
                      <br><br>
                    <table width="98%" border="0">
                      <tr>
                        <td width="13%" align="right">Item / Product: &nbsp;</td>
                        <td width="32%" align="left">
                            <select name="sel_item" id="sel_item" data-rel="chosen" style="width:700px;">
                            <option value="">-- Search Item --</option>
                            <?php
                                $qItem = $db->query('SELECT DISTINCT item FROM po_item ORDER BY item');
                                while($rItem = $db->fetch_array($qItem)):
                            ?>
                             <option value="<?php echo functions::encode($rItem['item'])?>" <?php if($item==$rItem['item'])echo 'selected="selected"';?>><?php echo $rItem['item']?></option>
                             <?php endwhile;?>
                             </select>
                        </td>
                        <td width="10%" valign="middle"><input type="submit" name="btnSearch" id="btnSearch" value="Search" class="btn btn-primary"></td>
                      </tr>
                    </table><br><br><br>
                    <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="table table-bordered table-hover" style="font-size:12px;">
                      <tr>
                        <th width="8%" scope="col"><div align="left">Date</div></th>
                        <th width="8%" scope="col"><div align="left">Unit</div></th>
                        <th width="8%" scope="col"><div align="left">Price</div></th>
                      </tr>
                      <?php
                      $qPrice = $db->query('SELECT po_date,unit,cost FROM `po_item` pi, po p WHERE p.po_id=pi.po_id and `item` = "'.$db->clean($item).'" group by po_date,cost ORDER BY `p`.`po_date` DESC');
                      while($rPrice = $db->fetch_array($qPrice)):
                          $bgColor='';
                        if($itemDate==$rPrice['po_date']){
                           $bgColor = 'bgcolor="#f5ae00"';
                        }
                      ?>
                      <tr <?php echo $bgColor;?>>
                        <td height="25"><?php echo functions::datearr($rPrice['po_date'])?></td>
                        <td><?php echo $rPrice['unit'];?></td>
                        <td><?php echo functions::formatMoney($rPrice['cost'])?></td>
                      </tr>
                    <?php endwhile;?>
                    </table>
                    <div style="font-size:12px;">
                    <?php
                      $high=0;
                      $low=0;
                      echo ( $high = $db->getValue('po_item','cost',array('item'=>$item),'ORDER BY cost DESC') ) ? '<br><br><strong>Highest Price: '. functions::formatMoney($high) .'</strong>': '';
                      echo ( $low = $db->getValue('po_item','cost',array('item'=>$item),'ORDER BY cost ASC') ) ? '<br><br><strong>Lowest Price: '. functions::formatMoney($low) .'</strong>': '';
                    ?>
                  </div>
                    </div>
                </div><!--/span-->
            </form>
            </div><!--/row-->
            <!-- body content: end here-->
<script>
document.getElementById('txSearch').focus();
function askDel(){
  if(confirm('Do you want to delete this equipment?'))
    return true;
  else
    return false; 
}
</script>
<?php require_once('templ_down.php');?>