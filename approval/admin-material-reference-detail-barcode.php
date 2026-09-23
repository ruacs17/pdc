<?php require_once('authorize.php');
$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : '';
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
  
$db = new Database();
$name = $db->getValue('users','concat(lname,", ",fname,"",mname)',array('username'=>$username));
#$logs = new Logs();
#$logs->save('visit');
$mf_id = (isset($_REQUEST['mf_id']) && !empty($_REQUEST['mf_id']) ) ? functions::decode($_REQUEST['mf_id']) : 0;
$_SESSION['notif_id_list']=$mf_id;
$editTrue=0;
$poidEdt='';
$item='';$itemDesc='';$unit='';$brand='';$class='';$type='';$min='';$cat='';$size='';$code='';

$qvedt = $db->select('material_reference','*',array('mf_id'=>$mf_id));
#$rnd = rand(1,999);
#$qvedt = $db->select('material_reference','*',array(),'LIMIT '.$rnd.',1');
$rvedt = $db->fetch_array($qvedt);
$brcode = $rvedt['mf_id'];
$item = $rvedt['item'];
$unit = $rvedt['unit'];
$brand = $rvedt['brand'];
$code = $rvedt['m_code'];
$class = $rvedt['classification'];
$type = $rvedt['mtype'];
$min = $rvedt['least_required'];
$size = $rvedt['msize'];
$itemDesc = $rvedt['mdescription'];
$cat = $rvedt['category'];
$cad = $rvedt['cad'];
$engr_standard = $rvedt['engr_standard'];
$dupa = $rvedt['dupa'];
$specification = $rvedt['specification'];
$product_brochure = $rvedt['product_brochure'];/**/


function barcds($text) { // Part 1, make list of widths
	$char128asc=' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~'; 
	$char128wid = array(
	'212222','222122','222221','121223','121322','131222','122213','122312','132212','221213', // 0-9 
	'221312','231212','112232','122132','122231','113222','123122','123221','223211','221132', // 10-19 
	'221231','213212','223112','312131','311222','321122','321221','312212','322112','322211', // 20-29 
	'212123','212321','232121','111323','131123','131321','112313','132113','132311','211313', // 30-39 
	'231113','231311','112133','112331','132131','113123','113321','133121','313121','211331', // 40-49 
	'231131','213113','213311','213131','311123','311321','331121','312113','312311','332111', // 50-59 
	'314111','221411','431111','111224','111422','121124','121421','141122','141221','112214', // 60-69 
	'112412','122114','122411','142112','142211','241211','221114','413111','241112','134111', // 70-79 
	'111242','121142','121241','114212','124112','124211','411212','421112','421211','212141', // 80-89 
	'214121','412121','111143','111341','131141','114113','114311','411113','411311','113141', // 90-99
	'114131','311141','411131','211412','211214','211232','23311120' ); // 100-106
		$txt='';$countStr=0;
		$str = str_split($text);
		$allStr = count($str);
		foreach($str as $tx):
			$countStr++;
			$txt.=$tx;
			if($allStr > $countStr)
				$txt.=' ';
		endforeach;
		$w = $char128wid[$sum = 104]; // START symbol
		$onChar=1;
		$html='<table cellpadding="0" cellspacing="0" border="0" width="0">
				<tr>'; 
		for($x=0;$x<strlen($text);$x++) // GO THRU TEXT GET LETTERS
			if (!( ($pos = strpos($char128asc,$text[$x])) === false )){ // SKIP NOT FOUND CHARS
				$w.= $char128wid[$pos];
				$sum += $onChar++ * $pos;
			} 
			$w.= $char128wid[ $sum % 103 ].$char128wid[106]; //Check Code, then END
			//Part 2, Write rows
			
			for($x=0;$x<strlen($w);$x+=2) // code 128 widths: black border, then white space
				$html.='<td><div class="b128" style="border-left: 1px black solid;height: 40px;border-left-width:'.$w[$x].';width:'.$w[$x+1].'"></div></td>';
		$html .='<tr>
					<td colspan="'.strlen($w).'" align="left">
						<div style="font-family:arial;font-size:11px;text-align:justify;">'.$txt.'<span style="width:100%;display:inline-block;"></span></div>
					</td>
				</tr>
			</table>';
		#echo $html;
		return $html;
	}
?>
<html lang="en">
<head>
	<title>Material Reference</title>
	<link rel="shortcut icon" href="../img/favicon.png">
	<!-- end: Favicon -->
	<style type="text/css">
	body{margin: 65px 0px 0px 45px;}
	.spce{padding-top:65px;}
	</style>
	<script type="text/javascript">window.print()</script>
</head>
<body>
<!-- body content: start here-->
<?php
#$brcode = rand(1000,9999);
#$brcode = 4907;
#echo $brcode;
$barcode = barcds($brcode); ?>
<?php #echo $barcode ?>
<div><?php echo $barcode ?></div>
<div class="spce"><?php echo $barcode ?></div>
<div class="spce"><?php echo $barcode ?></div>
</body>
</html>