<?php require_once('templ_up.php');?>
<!-- body content: start here-->
<form method="post">
    <div class="row-fluid">
        <div class="box span12">
            <div class="box-header" data-original-title>
                <h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Equipments Inventory Report</h2>
            </div><br>
        <div>
        <div>
            <table class="table">
                <tr>
                    <th>Year</th>
                    <th>Cost</th>
                </tr>
                <?php
                $allCost=0;
                $qYear = $db->query('SELECT sum(price * quantity) as cost, substring(date_acquired,1,4) as yr FROM `equipment` group by yr having yr > 1 order by yr,cost desc');
                while($rRes = $db->fetch_array($qYear)):
                    $allCost+=$rRes['cost'];
                ?>
                <tr><td colspan="2"><?php echo $rRes['yr']?></td></tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><?php echo functions::formatMoney($rRes['cost'])?></td>
                </tr>
                <tr>
                    <td>Ending</td>
                    <td><strong><?php echo functions::formatMoney($allCost)?></strong></td>
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>
                <?php endwhile;?>
            </table>
        </div>
    </div>
</form>
<!-- body content: end here-->
<?php require_once('templ_down.php');?>