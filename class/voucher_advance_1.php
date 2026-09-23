<?php
require_once("functions.php");
class voucherAdvance{
	var $arrVoucher=array();
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
		#require_once("../class/database.php");
		require_once("database.php");
		$this->db =  new Database();
		$this->current_voucher_id=$voucher_id;
		$this->source_voucher = $this->getFirstVoucher($voucher_id);
		$this->arrayVoucher = functions::insert_array($this->voucherHier($voucher_id),$voucher_id);
		$this->previousVoucherDetail();
		#print_r($this->arrVoucher);
		$index = count($this->arrVoucher) - 1;
		$vDetail = $this->arrVoucher[$index];
		$this->current_voucher_amount = $vDetail['vamount'];
		$this->taxable_amount = $vDetail['total_taxable_amount'];
		$this->current_voucher_withholding_tax = $vDetail['wtax'];
		$this->current_voucher_withholding_tax_amount = $vDetail['wtax_amount'];
		$this->overall_payment = $vDetail['overall_payment'];
		$this->current_voucher_payable_amount = $vDetail['payable_amount'];
		$this->overall_withholding_tax_amount = $vDetail['overall_wtax_amount'];
		$this->total_advance_voucher_amount = $vDetail['total_advance_payment'];
		$this->voucher_amount_without_withholding_tax = $vDetail['advance_payment_taxable'];
		$this->total_advance_withholding_tax_amount = $vDetail['wtax_amount_advance'];
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

	function previousVoucherDetail(){
		$amount=0;$voucher_type='';$count=0;$vamount=0;
		foreach($this->arrayVoucher as $vchr_ID):
			$voucher_type = $this->db->getValue('voucher_particular','vtype',array('voucher_id'=>$vchr_ID));
			$amount_issue=0;
			$vamount=0;
			$qParticulars = $this->db->select('voucher_particular','*',array('voucher_id'=>$vchr_ID));
			while($rPar = $this->db->fetch_array($qParticulars)):
				$po_id = $this->db->getValue('po','po_id',array('vp_id'=>$rPar['vp_id']));
				if($po_id)
					$amount = $this->db->getValue('po_item','sum( (qty_delivered * cost) - ( (qty_delivered * cost) * (discount/100) ) )',array('po_id'=>$po_id));
				else
					$amount = $this->db->getValue('voucher_detail','sum(amount_issue)',array('vp_id'=>$rPar['vp_id']));
				$amount_issue += $amount;
				$vamount += $amount;
			endwhile;
			$wtax_amount=0;
			$advance_payment_taxable=0;
			$total_taxable_amount_real = $vamount;
			$total_taxable_amount = $vamount;
			$wtax_amount_advance=0;
			$total_advance_payment=0;
			$overall_payment = $vamount;
			$overall_wtax_amount=0;
			$payable_amount=0;
			$vchr_wt = $this->db->getValue('voucher','witholding_tax',array('voucher_id'=>$vchr_ID));

			if( isset($this->arrVoucher[$count-1]) ){
				$advance_payment_taxable=$this->arrVoucher[$count-1]['total_taxable_amount_real'];
				$total_taxable_amount += $this->arrVoucher[$count-1]['total_taxable_amount_real'];
				$total_taxable_amount_real += $this->arrVoucher[$count-1]['total_taxable_amount_real'];
				$wtax_amount_advance = $this->arrVoucher[$count-1]['wtax_amount'];
				$total_advance_payment = $this->arrVoucher[$count-1]['total_advance_payment'] + $this->arrVoucher[$count-1]['vamount'];
				$overall_payment += $this->arrVoucher[$count-1]['overall_payment'];
				$overall_wtax_amount += $this->arrVoucher[$count-1]['overall_wtax_amount'];

				if( $this->arrVoucher[$count-1]['vtype'] != $voucher_type){
					$total_taxable_amount -= $total_advance_payment;
					$total_taxable_amount_real -= $total_advance_payment;
				}
			}

			if($vchr_wt){
				$wtax_amount = ($total_taxable_amount_real / 1.12) * ($vchr_wt / 100);
				$overall_wtax_amount += $wtax_amount;
				$total_taxable_amount_real=0;
			}

			$payable_amount = $vamount - $wtax_amount;

			if( isset($this->arrVoucher[$count-1]) ){
				if( $this->arrVoucher[$count-1]['vtype'] != $voucher_type){
					#$payable_amount = $vamount - $wtax_amount - $total_advance_payment - $wtax_amount_advance;
					$payable_amount = $vamount - $wtax_amount - $total_advance_payment;
					$overall_payment -= $total_advance_payment;
				}
			}


			$this->arrVoucher[] = array(
				'vtype'=>$voucher_type,
				'vamount'=>$vamount,
				'wtax'=>$vchr_wt,
				'advance_payment_taxable'=>$advance_payment_taxable,
				'total_taxable_amount'=>$total_taxable_amount,
				'wtax_amount'=>$wtax_amount,
				'total_taxable_amount_real'=>$total_taxable_amount_real,
				'wtax_amount_advance'=>$wtax_amount_advance,
				'total_advance_payment'=>$total_advance_payment,
				'overall_payment'=>$overall_payment,				
				'overall_wtax_amount'=>$overall_wtax_amount,
				'payable_amount'=>$payable_amount);
			$count++;
		endforeach;
	}
}
?>