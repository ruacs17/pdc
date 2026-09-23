<?php require_once('templ_up.php');?>
<?php


if( isset($_REQUEST['DswrW']) ){

    $selMonth = ( isset($_REQUEST['bdMon']) && !empty($_REQUEST['bdMon']) ) ? functions::decode($_REQUEST['bdMon']) : '';
    $account_type = ( isset($_REQUEST['txVoType']) && !empty($_REQUEST['txVoType']) ) ? functions::decode($_REQUEST['txVoType']) : '';
    $date_explode = explode("-",$selMonth);
    if( count($date_explode)==3 ){
        $year=$date_explode[0];
        $month = $date_explode[1];
    }
    #echo $account_type.'<br>'.$month.'-'.$year;

    $_SESSION['as_acType']=$account_type;
    $_SESSION['as_selMonth']=$month;
    $_SESSION['as_selYr']=$year;
    functions::sendTo("account_statement.php");
}




$balance_date = $db->getValue('account_balance','DISTINCT bal_date',array(),'LIMIT 1');
$balance_time = $db->getValue('account_balance','DISTINCT bal_time',array(),'LIMIT 1');

if($balance_date && $balance_time){
    $balanceDate_explode = explode("-",$balance_date);
    $balanceTime_explode = explode(":",$balance_date);
    $bal_year=date('Y');
    $bal_month = date('m');
    $bal_day = date('d');
    $bal_hr = date('h');
    $bal_min = date('i');
    $bal_ss = '00';
    if( count($balanceDate_explode)==3 ){
        $bal_year=$balanceDate_explode[0];
        $bal_month = $balanceDate_explode[1];
        $bal_day = $balanceDate_explode[2];
    }
    if( count($balanceTime_explode)==3 ){
        $bal_hr=$balanceTime_explode[0];
        $bal_min = $balanceTime_explode[1];
        $bal_ss = $balanceTime_explode[2];
    }
    $bal_mktime = mktime($bal_hr,$bal_min,$bal_ss,$bal_month,$bal_day,$bal_year);
}
if($balance_date != date('Y-m-d')){
    $date=date('Y-m-d');
    $time=date('H:i:00');
    $qAccount = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
    while($rAccount = $db->fetch_array($qAccount)):
        $bal = getBalance($rAccount['vt_id'],$date);
        if( $db->getValue('account_balance','count(*)',array('vt_id'=>$rAccount['vt_id'])) )
            $db->update('account_balance',array('bal'=>$bal,'bal_date'=>$date,'bal_time'=>$time),array('vt_id'=>$rAccount['vt_id']));
        else
            $db->insert('account_balance',array('vt_id'=>$rAccount['vt_id'],'bal'=>$bal,'bal_date'=>$date,'bal_time'=>$time));
    endwhile;
    functions::sendTo($_SERVER['PHP_SELF']);  
}

if( isset($_POST['btnUpdate']) ){
    $date=date('Y-m-d');
    $time=date('H:i:00');
    $qAccount = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
    while($rAccount = $db->fetch_array($qAccount)):
        $bal = getBalance($rAccount['vt_id'],$date);
        if( $db->getValue('account_balance','count(*)',array('vt_id'=>$rAccount['vt_id'])) )
            $db->update('account_balance',array('bal'=>$bal,'bal_date'=>$date,'bal_time'=>$time),array('vt_id'=>$rAccount['vt_id']));
        else
            $db->insert('account_balance',array('vt_id'=>$rAccount['vt_id'],'bal'=>$bal,'bal_date'=>$date,'bal_time'=>$time));
    endwhile;
    functions::sendTo($_SERVER['PHP_SELF']);
}

$bidUpdt = ( isset($_REQUEST['bidUpdt']) && !empty($_REQUEST['bidUpdt']) ) ? functions::decode($_REQUEST['bidUpdt']) : '';
if($bidUpdt){
    if( $db->getValue('account_balance','included',array('vt_id'=>$bidUpdt))==1 )
        $db->update('account_balance',array('included'=>0),array('vt_id'=>$bidUpdt));
    else
        $db->update('account_balance',array('included'=>1),array('vt_id'=>$bidUpdt));
    functions::sendTo($_SERVER['PHP_SELF']);
}

function monthDays($month=0,$year=0,$day=31){
    $list = array();
    $month = ($month) ? $month : date('m');
    $year = ($year) ? $year : date('Y');
    for($d=1; $d<=$day; $d++):
        $time = mktime(12,0,0,$month,$d,$year);
        if( date('m',$time)==$month )
            $list[]=date('Y-m-d',$time);
    endfor;
    return $list;
}


function getPayablePO($year=0){
    global $db;
        $totalCost=0;
        $monthlyCost=0;
        $cost=0;
        if($year==0)
            $qPODate = $db->select('po','DISTINCT LEFT(po_date,7) as dte',array(),'WHERE vp_id IS NULL ORDER BY po_date');
        else
            $qPODate = $db->select('po','DISTINCT LEFT(po_date,7) as dte',array('LEFT(po_date,4)'=>$year),'AND vp_id IS NULL ORDER BY po_date');

            #$qPODate = $db->query('SELECT ');

        while($rPODate = $db->fetch_array($qPODate)):

            $qPO = $db->select('po','DISTINCT supplierID',array('LEFT(po_date,7)'=>$rPODate['dte']),'AND (vp_id IS NULL OR vp_id IN (SELECT vp_id FROM voucher v, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND v.claimed=2))');
            while($rPO = $db->fetch_array($qPO)):
                    $unclaimedPO = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) as res FROM voucher v, voucher_particular vp, po, po_item poi WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=po.vp_id AND po.po_id=poi.po_id AND v.claimed=2 AND po.supplierID="'.$db->clean($rPO['supplierID']).'" AND vp.vtype="po" AND LEFT(po.po_date,7)="'.$db->clean($rPODate['dte']).'"');
                    $qq=$db->last_query;
                    $unclaimedPOCost = $db->result($unclaimedPO,0);
                    $qPoCost = $db->query('SELECT round( sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) ),2) as res FROM `po_item` poi, po p where p.po_id=poi.po_id and p.supplierID="'.$db->clean($rPO['supplierID']).'" AND LEFT(p.po_date,7)="'.$db->clean($rPODate['dte']).'" AND p.vp_id IS NULL');
                    $costPO = $db->result($qPoCost,0);
                    $totalCost += $cost = $costPO + $unclaimedPOCost;
                    $monthlyCost += $cost;
            endwhile;#endwhile $rPO
        endwhile;#endwhile PODate
        return $totalCost;
}

function getBalance($account_type,$dateNow){
    global $db;
    $balance=0;
    $totalDebit=0;
    $totalCredit=0;
    $monthlyDebit=0;
    $monthlyCredit=0;
    $break=0;
    $year=date('Y');
        for($m = 1; $m <= 12; $m++):
            $monthlyCredit=0;
            $monthlyDebit=0;
            $time = mktime(0,0,0,$m,1,$year);
            $monthName = date('F',$time);
            $days = monthDays($m,$year);

            foreach($days as $day):

                if($day <= $dateNow){

                    $qCredit = $db->select('account_statement','*',array('as_date'=>$day,'as_type'=>'credit','account_type'=>$account_type));
                    while($rCredit = $db->fetch_array($qCredit)):
                            $credID = $rCredit['as_id'];
                            if($rCredit['confirmned']==2){
                                $balance += $rCredit['as_amount'];
                                $balance = ($balance) ? round($balance,2) : $balance;
                                $totalCredit += $rCredit['as_amount'];
                                $monthlyCredit += $rCredit['as_amount'];
                            }
                        $balanceDisplay =($balance <= -.01) ? $balance : intval($balance);
                    endwhile; #while Credit


                    #voucher or supplier for credit
                    $qVoucher = $db->select('voucher','*',array('cheque_date'=>$day,'dest_accnt'=>$account_type));
                    while($rVoucher = $db->fetch_array($qVoucher)):
                            $vid = $rVoucher['voucher_id'];
                            $claimed = $rVoucher['claimed'];
                            $amountCredit=0;
                            $amount_q = $db->query('SELECT SUM(vd.amount_issue) FROM voucher v, voucher_detail vd, voucher_particular vp WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=vd.vp_id AND v.voucher_id="'.$db->clean($rVoucher['voucher_id']).'"');
                            $non_po = $db->result($amount_q);

                            $amount_non_po_q = $db->query('SELECT round( sum( (pi.qty_delivered * pi.cost) - ( (pi.qty_delivered * pi.cost) * (pi.discount/100) ) ),2) AS total FROM voucher v,voucher_particular vp,po p,po_item pi WHERE v.voucher_id=vp.voucher_id AND vp.vp_id=p.vp_id AND p.po_id=pi.po_id AND v.voucher_id="'.$db->clean($rVoucher['voucher_id']).'"');
                            $po = $db->result($amount_non_po_q);
                            $amountCredit = $non_po + $po;
                            if($claimed==1){
                                $totalCredit += $amountCredit;
                                $balance += $amountCredit;
                                $balance = ($balance) ? round($balance,2) : $balance;
                                $monthlyCredit += $amountCredit;
                            }
                        $balanceDisplay = $balance;
                    endwhile;

                    $qDebit = $db->select('account_statement','*',array('as_date'=>$day,'as_type'=>'debit','account_type'=>$account_type));
                    while($rDebit = $db->fetch_array($qDebit)):
                            $debID = $rDebit['as_id'];
                            if($rDebit['confirmned']==2){
                                $totalDebit += $rDebit['as_amount'];
                                $balance -= $rDebit['as_amount'];
                                $balance = ($balance) ? round($balance,2) : $balance;
                                $monthlyDebit += $rDebit['as_amount'];
                            }
                        $balanceDisplay =($balance <= -.01) ? $balance : intval($balance);
                    endwhile; #end while Debit


                    #voucher or supplier for debit
                    $qVoucher = $db->select('voucher','*',array('cheque_date'=>$day,'vt_id'=>$account_type),'AND dest_account IS NULL');
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
                            $balanceDisplay = $balance;
                    endwhile;#end while Voucher or supplier for debit

                    }#end if date is greater than required
                    else{
                        $break=1;
                        break;
                    }   
            endforeach; #end for each day
            if($break)
                break;
        endfor; #end for each month
    $balanceDisplay = $balance;
    return $balance;
}
?>
            <!-- body content: start here-->
            <form method="post">
            <table width="100%" cellspacing="4" cellpadding="6" border='0' align="left">
                <tr>
                    <td width="3%" style="visibility:hidden"><div style="background-color:#f5ae00; width:20px;">&nbsp;</div></td>
                    <td width="47%" style="visibility:hidden"> Inactive</td>
                    <td width="50%"><div align="right"><input type="submit" id="btnUpdate" name="btnUpdate" value="Get Updated Balance" class="btn btn-info"></div></td>
                </tr>
            </table><br><br>
                    <div class="row-fluid">
                        <div class="box span12">
                            <div class="box-header" data-original-title>
                                <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Account Balance</h2>
                            </div>
                            <div align="center"><br>&nbsp;&nbsp;Balance as of <?php echo date('l F d, Y',$bal_mktime);?> - <strong><?php echo functions::MilToTwelve($balance_time);?></strong><br><br></div>
            
                            <div class="box-content">
                                <form>
                                <table class="table table-bordered table-hover" style="font-size:14px;">
                                    <thead>
                                        <tr>
                                            <th width="45%">ACCOUNT</th>
                                            <th width="45%">BALANCE</th>
                                            <th width="5%">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $qAccount = $db->select('account_balance','*',array());
                                        while($rAccount = $db->fetch_array($qAccount)):
                                    ?>      
                                        <tr>
                                            <td><?php echo $db->getValue('voucher_type','vt_name',array('vt_id'=>$rAccount['vt_id']));?></td>
                                            <td><a href="<?php echo $_SERVER['PHP_SELF']?>?DswrW=Gsetg&txVoType=<?php echo functions::encode($rAccount['vt_id'])?>&bdMon=<?php echo functions::encode($balance_date)?>"><?php echo functions::formatMoney($rAccount['bal']);?></a></td>
                                            <td><input type="checkbox" name="acnt<?php echo $rAccount['vt_id']?>" id="acnt<?php echo $rAccount['vt_id']?>" value="<?php echo functions::encode($rAccount['vt_id']);?>" <?php echo ($rAccount['included']) ? 'checked="checked"' : '';?> onClick="updtChk(this.value)" /></td>
                                        </tr>
                                    <?php endwhile;
                                        $totalBalance = $db->getValue('account_balance','sum(bal)',array('included'=>1));
                                        $payable = getPayablePO(date('Y',$bal_mktime));
                                    ?>
                                        <tr>
                                            <td colspan="3"><hr width="100%" style="color:#FF0000"></td>
                                        </tr>
                                        <tr>
                                            <td><div align="right"><strong>Total Balance</strong></div></td>
                                            <td><div id="sumBal"><strong><?php echo functions::formatMoney($totalBalance);?></strong></div></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td><div align="right"><strong>Payable P.O.</strong></div></td>
                                            <td><div id="sumBal"><strong><?php echo functions::formatMoney($payable);?></strong></div></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td><div align="right"><strong>Remaining Balance</strong></div></td>
                                            <td><div id="sumBal"><strong><?php echo functions::formatMoney($totalBalance - $payable);?></strong></div></td>
                                            <td></td>
                                        </tr>

                                    </tbody>
                                </table>
                                <input type="hidden" name="txSum" id="txSum" value="">
                                </form>
                            </div>

                        </div><!--/span-->

                    </div><!--/row-->
             </form>
            <!-- body content: end here-->
<script>function updtChk(PiEwgD){window.location="<?php echo $_SERVER['PHP_SELF']?>?bidUpdt="+PiEwgD}</script>
<?php require_once('templ_down.php');?>