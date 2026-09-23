<?php require_once('templ_up.php');?>
<?php
function monthDays($month=0,$year=0){
    $list = array();
    $month = ($month) ? $month : date('m');
    $year = ($year) ? $year : date('Y');
    for($d=1; $d<=31; $d++):
        $time = mktime(12,0,0,$month,$d,$year);
        if( date('m',$time)==$month )
            $list[]=date('Y-m-d',$time);
    endfor;
    return $list;
}

$yr = ( isset($_REQUEST['y']) && !empty($_REQUEST['y']) ) ? functions::decode($_REQUEST['y']) : date('Y');
$mn = ( isset($_REQUEST['m']) && !empty($_REQUEST['m']) ) ? functions::decode($_REQUEST['m']) : '';
$ac_type = ( isset($_REQUEST['t']) && !empty($_REQUEST['t']) ) ? functions::decode($_REQUEST['t']) : '';

if( isset($_POST['btnSearch']) ){
    $year = ( isset($_POST['bdYear']) && !empty($_POST['bdYear']) ) ? functions::decode($_POST['bdYear']) : date('Y');
    $selMonth = ( isset($_POST['bdMon']) && !empty($_POST['bdMon']) ) ? $_POST['bdMon'] : '';
    $account_type = ( isset($_POST['txVoType']) && !empty($_POST['txVoType']) ) ? $_POST['txVoType'] : '';
    $_SESSION['as_acType']=$account_type;
    $_SESSION['as_selMonth']=$selMonth;
    $_SESSION['as_selYr']=$year;
}

if( isset($_REQUEST['ddid']) && !empty($_REQUEST['ddid']) ){
    $as_id = ( isset($_REQUEST['ddid']) && !empty($_REQUEST['ddid']) ) ? functions::decode($_REQUEST['ddid']) : '';
    $getProjIncomeID = $db->getValue('account_statement','project_income',array('as_id'=>$as_id));
    $db->delete('project_income',array('pi_id'=>$getProjIncomeID));
    $db->delete('account_statement',array('as_id'=>$as_id));

    #$url = $_SERVER['PHP_SELF'] . '?y=' . functions::encode($year) .'&m=' . functions::encode($selMonth).'&t='.functions::encode($account_type);
    functions::sendTo($_SERVER['PHP_SELF']);
}
$account_type = ( isset($_SESSION['as_acType']) && !empty($_SESSION['as_acType']) ) ? $_SESSION['as_acType'] : $ac_type;
$selMonth = ( isset($_SESSION['as_selMonth']) && !empty($_SESSION['as_selMonth']) ) ? $_SESSION['as_selMonth'] : $mn;
$year = ( isset($_SESSION['as_selYr']) && !empty($_SESSION['as_selYr']) ) ? $_SESSION['as_selYr'] : $yr;

?>
            <!-- body content: start here-->
<table width="460" cellspacing="4" cellpadding="6" border='0' align="right">
    <tr>
        <td width="5"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
        <td width="35"> Unconfirmned Statement</td>
        <td width="5"><div style="background-color:#fa6f6f; width:20px;">&nbsp;</div></td>
        <td width="35"> Unclaimed Voucher</td>
    </tr>
</table><br><br>
            <form method="post">
            <table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
                <tr>
                    <td width="3%" style="visibility:hidden"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
                    <td width="47%" style="visibility:hidden"> Inactive</td>
                    <td width="50%"><div align="right"><a id="adc" href="#" class="btn btn-info btn-setting thickbox" onclick="showThis(this.id,'account_statement_add.php?t=<?php echo functions::encode($account_type)?>&y=<?php echo functions::encode($year)?>&m=<?php echo functions::encode($selMonth)?>','Statement Add')">Add New Statement</a></div></td>
                </tr>
            </table><br><br>
                    <div class="row-fluid">
                        <div class="box span12">
                            <div class="box-header" data-original-title>
                                <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Account Statement</h2>
                            </div>
                            <div align="center"><br>&nbsp;&nbsp;
                              <select name="bdYear" id="bdYear">
                                <option value="">--Select Year--</option>
                                <?php for($y=(date('Y')+1);$y>=2017;$y--):?>
                                <option value="<?php echo functions::encode($y);?>" <?php if($year==$y)echo 'selected="selected"';?>><?php echo $y;?></option>
                                <?php endfor;?>
                              </select>
                            <select name="bdMon" id="bdMon" style="width:95px;">
                                <option value="">All Month</option>
                                <option value="01" <?php if($selMonth=='01')echo 'selected="selected"';?>>Jan</option>
                                <option value="02" <?php if($selMonth=='02')echo 'selected="selected"';?>>Feb</option>
                                <option value="03" <?php if($selMonth=='03')echo 'selected="selected"';?>>Mar</option>
                                <option value="04" <?php if($selMonth=='04')echo 'selected="selected"';?>>Apr</option>
                                <option value="05" <?php if($selMonth=='05')echo 'selected="selected"';?>>May</option>
                                <option value="06" <?php if($selMonth=='06')echo 'selected="selected"';?>>Jun</option>
                                <option value="07" <?php if($selMonth=='07')echo 'selected="selected"';?>>Jul</option>
                                <option value="08" <?php if($selMonth=='08')echo 'selected="selected"';?>>Aug</option>
                                <option value="09" <?php if($selMonth=='09')echo 'selected="selected"';?>>Sep</option>
                                <option value="10" <?php if($selMonth=='10')echo 'selected="selected"';?>>Oct</option>
                                <option value="11" <?php if($selMonth=='11')echo 'selected="selected"';?>>Nov</option>
                                <option value="12" <?php if($selMonth=='12')echo 'selected="selected"';?>>Dec</option>
                            </select>

                              <select name="txVoType" id="txVoType">
                              <option value="">--Account Type--</option>
                              <?php 
                                $qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
                                while($rVtype = $db->fetch_array($qVtype)):
                              ?>
                              <option value="<?php echo $rVtype['vt_id']?>" <?php if($account_type==$rVtype['vt_id'])echo 'selected="selected"';?> ><?php echo $rVtype['vt_name']?></option>
                              <?php endwhile;?>
                              </select>
                            <input type="submit" name="btnSearch" id="btnSearch" value="View" class="btn btn-primary">
                            </div>
            
                            <div class="box-content">
                                    <?php
                                    $balance=0;
                                    $totalDebit=0;
                                    $totalCredit=0;
                                    $monthlyDebit=0;
                                    $monthlyCredit=0;
                                    for($m = 1; $m <= 12; $m++):
                                    $monthlyCredit=0;
                                    $monthlyDebit=0;
                                    $time = mktime(0,0,0,$m,1,$year);
                                    $monthName = date('F',$time);
                                    $days = monthDays($m,$year);


                                        foreach($days as $day):

                                        $qCredit = $db->select('account_statement','*',array('as_date'=>$day,'as_type'=>'credit','account_type'=>$account_type));
                                        while($rCredit = $db->fetch_array($qCredit)):
                                        $credID = $rCredit['as_id'];
                                        $bgColor = 'bgcolor="#f5ae00"';
                                        if($rCredit['confirmned']==2){
                                            $balance += $rCredit['as_amount'];
                                            #$balance = ($balance) ? round($balance,2) : $balance;
                                            $totalCredit += $rCredit['as_amount'];
                                            $monthlyCredit += $rCredit['as_amount'];
                                        }

                                        endwhile; #while Credit


                                        $qDebit = $db->select('account_statement','*',array('as_date'=>$day,'as_type'=>'debit','account_type'=>$account_type));
                                        while($rDebit = $db->fetch_array($qDebit)):
                                        $debID = $rDebit['as_id'];
                                        $bgColor = 'bgcolor="#f5ae00"';
                                        if($rDebit['confirmned']==2){
                                            $totalDebit += $rDebit['as_amount'];
                                            $balance -= $rDebit['as_amount'];
                                            #$balance = ($balance) ? round($balance,2) : $balance;
                                            $monthlyDebit += $rDebit['as_amount'];
                                        }
                                        endwhile; #end while Debit

                                    #voucher or supplier for debit
                                            $qVoucher = $db->select('voucher','*',array('cheque_date'=>$day,'vt_id'=>$account_type));
                                            while($rVoucher = $db->fetch_array($qVoucher)):
                                            $vid = $rVoucher['voucher_id'];
                                            $claimed = $rVoucher['claimed'];
                                            $amountDebit=0;
                                            $amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rVoucher['voucher_id']).'"');
                                            $non_po = $db->result($amount_q);

                                            $amount_non_po_q = $db->query('SELECT round( sum( (pi.qty_delivered * pi.cost) - ( (pi.qty_delivered * pi.cost) * (pi.discount/100) ) ),2) AS total FROM voucher v,voucher_particular vp,po p,po_item pi WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=p.vp_id AND p.po_id=pi.po_id AND v.voucher_id="'.$db->clean($rVoucher['voucher_id']).'"');
                                            $po = $db->result($amount_non_po_q);
                                            $amountDebit = $non_po + $po;
                                            if($claimed==1){
                                                $totalDebit += $amountDebit;
                                                $balance -= $amountDebit;
                                                $balance = ($balance) ? round($balance,2) : $balance;
                                                $monthlyDebit += $amountDebit;
                                            }
                                           endwhile;#end while Voucher or supplier for debit
                                        endforeach; #end for each day
                                        if($selMonth==$m || $selMonth == ''){
                                            $balanceDisplay = $balance;
                                        }
                                    endfor; #end for each month
                                    $balanceDisplay = $balance;
                                    ?>
                            </div>
                        </div><!--/span-->

                    </div><!--/row-->
             </form>
             <?php echo functions::formatMoney($balanceDisplay);?>
            <!-- body content: end here-->
<?php
#$remainingBalance =($balance <= -.01) ? $balance : intval($balance);
$remainingBalance = $balance;
$transaction = 'REMAINING BALANCE of '. $year;
$nextYear = ($year + 1) .'-01-01';


if( $db->getValue('account_statement','count(*)',array('transaction'=>$transaction,'as_type'=>'credit','as_date'=>$nextYear,'account_type'=>$account_type,'confirmned'=>2))==0 ){
    $db->insert('account_statement',array('as_type'=>'credit','transaction'=>$transaction,'description'=>'','as_date'=>$nextYear,'as_amount'=>$remainingBalance,'account_type'=>$account_type,'confirmned'=>2));

}
else{
    $nextRemBal = $db->getValue('account_statement','as_amount',array('transaction'=>$transaction,'as_date'=>$nextYear,'account_type'=>$account_type,'confirmned'=>2));
    if($remainingBalance != $nextRemBal)
        $db->update('account_statement',array('as_amount'=>$remainingBalance),array('transaction'=>$transaction,'as_type'=>'credit','as_date'=>$nextYear,'account_type'=>$account_type,'confirmned'=>2));
}
?>
  <script>
    function delt(){
      if(confirm('Do you want to remove this?'))
        return true;
      else
        return false; 
    }
  </script>
<?php require_once('templ_down.php');?>