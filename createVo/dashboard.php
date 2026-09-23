<?php require_once('templ_up.php');?>
            <!-- body content: start here-->
            <div class="row-fluid">
                    <a class="quick-button metro purple span3" href="project_list.php">
                        <i class="icon-tasks"></i>
                        <p><strong>Active Projects</strong></p>
                        <span style="font-size: 20px;"><?php echo $db->getValue('project','count(proj_id)',array());?></span>
                    </a>
                    <a class="quick-button metro green span3" href="voucher_list.php">
                        <i class="icon-edit"></i>
                        <p><strong>Voucher</strong></p>
                        <span style="font-size: 20px;"><?php echo $db->getValue('voucher','count(*)',array());?></span>
                    </a>
                    <a id="gradSlot" class="quick-button metro green span3" href="">
                        <i class="icon-shopping-cart"></i>
                        <p><strong>Purchase Order</strong></p>
                        <span style="font-size: 20px;"><?php echo $db->getValue('po','count(*)',array());?></span>
                    </a>
                    <a id="translot" class="quick-button metro yellow span3" href="supplier.php">
                        <i class="icon-truck"></i>
                        <p><strong>Supplier</strong></p>
                        <span style="font-size: 20px;"><?php echo $db->getValue('supplier','count(*)',array());?></span>
                    </a>                          
            </div><!--/row-fluid-->
            <!-- body content: end here-->
<?php require_once('templ_down.php');?>