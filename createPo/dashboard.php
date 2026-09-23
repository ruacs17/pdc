<?php require_once('templ_up.php');?>

            <!-- body content: start here-->
            <div class="row-fluid">
                     
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
                    <a id="priceMonitor" class="quick-button metro blue span3" href="admin-material-reference.php">
                        <i class="icon-money"></i>
                        <p><strong>Price Monitor</strong></p>
                        <span style="font-size: 20px;">&nbsp;</span>
                    </a>
                    <a id="inhouse" class="quick-button metro blue span3 thickbox" onclick="showThis(this.id,'dash-inhouse-warehouse.php?','In-house Report','1')">
                        <i class="icon-book"></i>
                        <p><strong>In-house Report</strong></p>
                        <span style="font-size: 20px;">&nbsp;</span>
                    </a>
            </div><br>
            <!--/row-fluid-->
            <!-- body content: end here-->
<?php require_once('templ_down.php');?>
