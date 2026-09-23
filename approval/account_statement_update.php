#!/usr/local/bin/php -q
<?php
require_once('/home/del/web/class/database.php');
require_once('/home/del/web/class/functions.php');
$db = new Database();
require_once('/home/del/web/class/voucher_advance_cron.php');
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
echo 'Started: '.date('H:i:s').' -- ';
$qVtype = $db->select('voucher_type','*',array(),'ORDER BY vt_name');
while($rVtype = $db->fetch_array($qVtype)):
    $account_type = $rVtype['vt_id'];
    $balance=0;$totalDebit=0;$totalCredit=0;$monthlyDebit=0;$monthlyCredit=0;
    for($year=2017; $year<=(date('Y')+7); $year++):
        for($m = 1; $m <= 12; $m++):
            $monthlyCredit=0;$monthlyDebit=0;
            $time = mktime(0,0,0,$m,1,$year);
            $monthName = date('F',$time);
            $days = monthDays($m,$year);

            $qHasAdvance = $db->query('SELECT DISTINCT voucher_id_owner FROM voucher_advance_payment vap, voucher v WHERE vap.voucher_id_owner=v.voucher_id AND v.vt_id="'.$db->clean($account_type).'" AND LEFT(cheque_date,4)="'.$db->clean($year).'"');
            $arrVoucherAdvanceID=array();
            while($rHasAdvance = $db->fetch_array($qHasAdvance)):
                $arrVoucherAdvanceID[$rHasAdvance['voucher_id_owner']]=$rHasAdvance['voucher_id_owner'];
            endwhile;
            foreach($days as $day):
                $qCredit = $db->select('account_statement','*',array('as_date'=>$day,'as_type'=>'credit','account_type'=>$account_type));
                while($rCredit = $db->fetch_array($qCredit)):
                    $credID = $rCredit['as_id'];
                    if($rCredit['confirmned']==2){
                        $balance += $rCredit['as_amount'];
                        $balance = ($balance) ? round($balance,2) : $balance;
                        $totalCredit += $rCredit['as_amount'];
                        $monthlyCredit += $rCredit['as_amount'];
                    }
                endwhile; #while Credit

                $qDebit = $db->select('account_statement','*',array('as_date'=>$day,'as_type'=>'debit','account_type'=>$account_type));
                while($rDebit = $db->fetch_array($qDebit)):
                    $debID = $rDebit['as_id'];
                    if($rDebit['confirmned']==2){
                        $totalDebit += $rDebit['as_amount'];
                        $balance -= $rDebit['as_amount'];
                        $balance = ($balance) ? round($balance,2) : $balance;
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

                    $amount_non_po_q = $db->query('SELECT sum(amount) FROM voucher_particular vp, voucher_po_payment vpp WHERE vpp.vp_id=vp.vp_id AND vp.voucher_id="'.$db->clean($rVoucher['voucher_id']).'"');
                    $po = $db->result($amount_non_po_q);
                    $voucher_amount = $non_po + $po;

                    $wtax = ($rVoucher['witholding_tax'] > 0) ? ($rVoucher['witholding_tax'] / 100) : 0;

                    $withholding_amount = $wtax * (($voucher_amount) / 1.12);
                    $amountDebit = ($wtax) ? $voucher_amount - $withholding_amount : $voucher_amount;
                                            
                    $withAdvance=0;
                    if( isset($arrVoucherAdvanceID[$rVoucher['voucher_id']]) ){
                        $withAdvance=1;
                        $va = $a = new voucherAdvance($rVoucher['voucher_id']);
                        $amountDebit = $va->current_voucher_payable_amount;
                        $withholding_amount = $va->current_voucher_withholding_tax_amount;
                    }
                    if($claimed==1){
                        $totalDebit += $amountDebit;
                        $balance -= $amountDebit;
                        $balance = ($balance) ? round($balance,2) : $balance;
                        $monthlyDebit += $amountDebit;
                    }
                endwhile;#end while Voucher or supplier for debit

            endforeach;//foreach($days as $day):

        endfor;//for($m = 1; $m <= 12; $m++):

        #this is to carry over the ending balance the selected year to the next year of it.
        if($year > 2016 && $account_type){
            $remainingBalance = $balance;
            $transaction = 'REMAINING BALANCE of '. $year;
            $nextYear = ($year + 1) .'-01-01';
            if( $db->getValue('account_statement','count(*)',array('transaction'=>$transaction,'as_type'=>'credit','as_date'=>$nextYear,'account_type'=>$account_type,'confirmned'=>2))==0 ){
                $db->insert('account_statement',array('as_type'=>'credit','transaction'=>$transaction,'description'=>'','as_date'=>$nextYear,'as_amount'=>$remainingBalance,'account_type'=>$account_type,'confirmned'=>2,'transaction_type'=>'balance'));
                #echo $db->last_query.' || ';
            }
            else{
                $nextRemBal = $db->getValue('account_statement','as_amount',array('transaction'=>$transaction,'as_date'=>$nextYear,'account_type'=>$account_type,'confirmned'=>2));
                if($remainingBalance != $nextRemBal){
                    $db->update('account_statement',array('as_amount'=>$remainingBalance),array('transaction'=>$transaction,'as_type'=>'credit','as_date'=>$nextYear,'account_type'=>$account_type,'confirmned'=>2,'transaction_type'=>'balance'));
                    #echo $db->last_query.' || ';
                }
            }
        }
    endfor;//for($syr=2017; $syr<=(date('Y')-1); $syr++):
endwhile;
echo ' -- End: '.date('H:i:s');
?>

