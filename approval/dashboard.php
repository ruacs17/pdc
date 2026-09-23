<?php require_once('templ_up.php');?>

<!-- body content: start here-->
<div class="row-fluid">
	<a class="quick-button metro purple span3" href="project_list.php">
		<i class="icon-building"></i>
		<p><strong>Projects</strong></p>
		<span style="font-size: 20px;"><?php echo $db->getValue('project','count(proj_id)',array());?></span>
	</a>
	<a id="projProf" class="quick-button metro blue span3 thickbox" onclick="showThis(this.id,'dash-proj-cost-control.php?pdt=<?php echo functions::encode(date('Y'))?>','Project Cost Control','1')">
		<i class="icon-list-alt"></i>
		<p><strong>Project Cost Control</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a id="projCIS" class="quick-button metro blue span3 thickbox" onclick="showThis(this.id,'dash-proj-income-statement.php?','Comparative Income Statement','1')">
		<i class="icon-list-alt"></i>
		<p><strong>Comparative Income Statement</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a id="projControl" class="quick-button metro blue span3 thickbox" onclick="showThis(this.id,'dash-proj-profit.php?pdt=<?php echo functions::encode(date('Y'))?>','Project Detail','1')">
		<i class="icon-money"></i>
		<p><strong>Project Report</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
</div><br>
<div class="row-fluid">
	<a id="unclaimedVo" class="quick-button metro red span3 thickbox" onclick="showThis(this.id,'dash-unclaimed-voucher.php?','Unclaimed Voucher','1')">
		<i class="icon-edit"></i>
		<p><strong>Unclaimed Voucher</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a id="payablePO" class="quick-button metro red span3 thickbox" onclick="showThis(this.id,'dash-po-payable.php?','Payable P.O.','1')">
		<i class="icon-shopping-cart"></i>
		<p><strong>Payable P.O.</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a id="expenses" class="quick-button metro blue span3 thickbox" onclick="showThis(this.id,'dash-expenses.php?','Charges','1')">
		<i class="icon-money"></i>
		<p><strong>Charges</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a id="priceMonitor" class="quick-button metro blue span3" href="admin-material-reference.php">
		<i class="icon-money"></i>
		<p><strong>Price Monitor</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a> 
</div><br>
<div class="row-fluid">
	<a class="quick-button metro green span3" href="voucher_list.php">
		<i class="icon-edit"></i>
		<p><strong>Voucher</strong></p>
		<span style="font-size: 20px;"><?php echo $db->getValue('voucher','count(*)',array());?></span>
	</a>
	<a id="gradSlot" class="quick-button metro green span3" href="po_list.php">
		<i class="icon-shopping-cart"></i>
		<p><strong>Purchase Order</strong></p>
		<span style="font-size: 20px;"><?php echo $db->getValue('po','count(*)',array());?></span>
	</a>
	<a id="translot" class="quick-button metro yellow span3" href="supplier.php">
		<i class="icon-truck"></i>
		<p><strong>Supplier / Payee</strong></p>
		<span style="font-size: 20px;"><?php echo $db->getValue('supplier','count(*)',array());?></span>
	</a>
	<a id="supPatronage" class="quick-button metro blue span3 thickbox" onclick="showThis(this.id,'dash-supplier-patronage.php?','Patronage','1')">
		<i class="icon-book"></i>
		<p><strong>Supplier / Payee Patronage</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>                
</div><br>
<div class="row-fluid">
	<a id="inhouse" class="quick-button metro red span3 thickbox" onclick="showThis(this.id,'dash-inhouse-warehouse.php?','In-house Report','1')">
		<i class="icon-book"></i>
		<p><strong>In-house Report</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a id="projBilling" class="quick-button metro blue span3 thickbox" onclick="showThis(this.id,'dash-proj-billing.php?','Project Billing Report','1')">
		<i class="icon-list-alt"></i>
		<p><strong>Project Billing Report</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a id="witholding" class="quick-button metro red span3 thickbox" onclick="showThis(this.id,'dash-witholding-tax.php?','Witholding TAX Report','1')">
		<i class="icon-legal"></i>
		<p><strong>Witholding TAX</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>
	<a id="profFee" class="quick-button metro red span3 thickbox" onclick="showThis(this.id,'dash-prof-fee.php?','Professional Fee Report','1')">
		<i class="icon-gift"></i>
		<p><strong>Professional Fee</strong></p>
		<span style="font-size: 20px;">&nbsp;</span>
	</a>   
</div><br>
<div class="row-fluid">
	<a id="projBillingLedger" class="quick-button metro blue span3 thickbox" onclick="showThis(this.id,'acctng-proj-ledger-bill.php?','Project Ledger Billing / Collection','1')">
		<i class="icon-building"></i>
		<p><strong>Ledger</strong></p>
	</a>
	<a id="journalVoucher" class="quick-button metro purple span3 thickbox" onclick="showThis(this.id,'journal_voucher_list.php?','Journal Voucher','1')">
		<i class="icon-building"></i>
		<p><strong>Journal Voucher</strong></p>
	</a>
	<a id="budgetPvA" class="quick-button metro orange span3 thickbox" onclick="showThis(this.id,'dash-budget-plan.php?','Budget: Plan vs Actual','1')">
		<i class="icon-briefcase"></i>
		<p><strong>Budget: Plan vs Actual</strong></p>
	</a>
</div><br>
<!--/row-fluid-->
<!-- body content: end here-->
<?php require_once('templ_down.php');?>