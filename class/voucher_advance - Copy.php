<?php
require_once("functions.php");
class voucherAdvance{
	var $db;
	var $source_voucher;
	var $arrayVoucher;
	var $current_voucher_amount;
	var $current_voucher_id;
	var $total_advance_voucher_amount;
	var $total_advance_withholding_tax_amount=0;
	var $voucher_amount_without_withholding_tax;
	var $taxable_amount;
	var $current_voucher_withholding_tax_amount; //withholding tax of current voucher
	var $current_voucher_withholding_tax;
	var $wtax = 1.12;
	var $current_voucher_payable_amount; //Payable Amount
	var $overall_payment;
	var $overall_withholding_tax_amount;
	var $is_deduct;

	function __construct($voucher_id){
		require_once("database.php");
		$this->db =  new Database();
		$this->current_voucher_id=$voucher_id;
		$this->source_voucher = $this->getFirstVoucher($voucher_id);
		$this->arrayVoucher = $this->voucherHier($voucher_id);
		$this->isDeduct();
		$this->previousVoucher();
		

		$this->current_voucher_amount = $this->currentVoucherAmount($voucher_id);
		
		$this->taxable_amount = $this->current_voucher_amount + $this->voucher_amount_without_withholding_tax;
		$this->current_voucher_withholding_tax = $this->db->getValue('voucher','witholding_tax',array('voucher_id'=>$voucher_id));
		$this->current_voucher_withholding_tax_amount = ($this->taxable_amount && $this->current_voucher_withholding_tax) ? ($this->taxable_amount / 1.12) * ($this->current_voucher_withholding_tax / 100) : 0;
		$this->overall_payment = $this->current_voucher_amount + $this->total_advance_voucher_amount;
		$this->current_voucher_payable_amount = $this->current_voucher_amount - $this->current_voucher_withholding_tax_amount;

		$this->overall_withholding_tax_amount = $this->current_voucher_withholding_tax_amount + $this->total_advance_withholding_tax_amount;
		if($this->is_deduct){
			$this->taxable_amount = $this->overall_payment;
			$this->current_voucher_payable_amount = $this->overall_payment - $this->overall_withholding_tax_amount;
			$this->overall_withholding_tax_amount = $this->current_voucher_withholding_tax_amount;
			#$this->current_voucher_payable_amount = $this->overall_payment;
			#echo $this->current_voucher_withholding_tax_amount = $this->current_voucher_payable_amount;
			$this->total_advance_withholding_tax_amount = $this->total_advance_withholding_tax_amount * -1; // changing the negative to positive
			$this->total_advance_voucher_amount = $this->total_advance_voucher_amount * -1;
			$this->current_voucher_withholding_tax_amount = ($this->current_voucher_payable_amount && $this->current_voucher_withholding_tax) ? ($this->current_voucher_payable_amount / 1.12) * ($this->current_voucher_withholding_tax / 100) : 0;
			$this->overall_payment = $this->taxable_amount + $this->total_advance_voucher_amount;
		}		

	}


	function getFirstVoucher($vid){
	  $source=0;
	  $parent = $this->db->getValue('voucher_advance_payment','voucher_advances',array('voucher_id_owner'=>$vid));
	  if($parent){
		    $grand = $this->db->getValue('voucher_advance_payment','voucher_advances',array('voucher_id_owner'=>$parent));
		    if($grand)
		      	$source=$this->getFirstVoucher($parent);
		    else
		      	$source = $parent;
	  }
	  return $source;
	}

	function voucherHier($vid){
	  global $db;
	  $source=0;
	  $arrVID_list=array();
	  $parent = $this->db->getValue('voucher_advance_payment','voucher_advances',array('voucher_id_owner'=>$vid));
	  if($parent){
		    $grand = $this->db->getValue('voucher_advance_payment','voucher_advances',array('voucher_id_owner'=>$parent));
		    if($grand)
		       	$arrVID_list = functions::insert_array($this->voucherHier($parent),$parent);
		    else
		      	$arrVID_list = functions::insert_array($arrVID_list,$parent);
	  }
	  return $arrVID_list;
	}

	function previousVoucher(){
		$amount_issue=0;$witholding_tax_amount_issue=0;
		foreach($this->arrayVoucher as $vchr_ID):
			$voucher_type = $this->db->getValue('voucher_particular','vtype',array('voucher_id'=>$vchr_ID));
			
            $qParticulars = $this->db->select('voucher_particular','*',array('voucher_id'=>$vchr_ID));
            while($rPar = $this->db->fetch_array($qParticulars)):
                $po_id = $this->db->getValue('po','po_id',array('vp_id'=>$rPar['vp_id']));
                if($po_id)
                    $amount = $this->db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$po_id));
                else
                    $amount = $this->db->getValue('voucher_detail','sum(amount_issue)',array('vp_id'=>$rPar['vp_id']));
                $amount_issue += $amount;
                $witholding_tax_amount_issue +=$amount;
            endwhile;

		  	$vchr_wt = $this->db->getValue('voucher','witholding_tax',array('voucher_id'=>$vchr_ID));
		  	if( $this->is_deduct ){
		  		if($voucher_type=='cash'){
		  			$amount_issue = $amount_issue * -1;
		  		}
		  	}

		  	$this->total_advance_voucher_amount = $amount_issue;
		  	$this->voucher_amount_without_withholding_tax = $amount_issue;
		  	if( $vchr_wt ){
		  		#$this->total_advance_withholding_tax_amount = ($witholding_tax_amount_issue && $vchr_wt) ? ($witholding_tax_amount_issue / 1.12) * ($vchr_wt / 100) : 0;
		  		$this->total_advance_withholding_tax_amount = ($amount_issue && $vchr_wt) ? ($amount_issue / 1.12) * ($vchr_wt / 100) : 0;
		    	$this->voucher_amount_without_withholding_tax = 0;
		  	}
		endforeach;
	}

	function isDeduct(){
		$po=0; $cash=0;
		if( $this->db->getValue('voucher_particular','vtype',array('voucher_id'=>$this->source_voucher)) == "cash")
			$cash++;
		else
			$po++;
		if( $this->db->getValue('voucher_particular','vtype',array('voucher_id'=>$this->current_voucher_id)) == 'cash')
			$cash++;
		else
			$po++;

		foreach($this->arrayVoucher as $vchr_ID):
			if( $this->db->getValue('voucher_particular','vtype',array('voucher_id'=>$vchr_ID)) == 'po' )
				$po++;
			else
				$cash++;
		endforeach;
		if($po && $cash){
			$this->is_deduct=1;return 1;
		}else{
			$this->is_deduct=0;return 0;
		}
		#echo $this->is_deduct;
	}

	function currentVoucherAmount($vid){
		$total_amount=0;
        $qvp = $this->db->select('voucher_particular','*',array('voucher_id'=>$vid));
        while($rvp = $this->db->fetch_array($qvp)):
            $vp_id=$rvp['vp_id'];
            $voucher_type = $rvp['vtype'];
            if($rvp['vtype'] == "po"){
                $po_id = $this->db->getValue('voucher_po_payment','po_id',array('vp_id'=>$rvp['vp_id']));
                $amount = $this->db->getValue('voucher_po_payment','amount',array('vp_id'=>$rvp['vp_id']));
            }
            else
                $amount = $this->db->getValue('voucher_detail','SUM(amount_issue)',array('vp_id'=>$rvp['vp_id']));

        $total_amount += $amount;
        endwhile;
        return $total_amount;
	}
}
?>