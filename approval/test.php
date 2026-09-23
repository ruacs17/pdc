<?php 
// require_once('../class/database.php');
// require_once('../class/functions.php');
// $db = new Database();
// $q = $db->query("SELECT * FROM `account_statement` WHERE `transaction` LIKE '%FUND TRANSFER%'");
// while($r = $db->fetch_array($q)):
//   $tag_id = $r['tag_id'];
//   $as_id = $r['as_id'];
//   $description = $r['description'];
//   $vp_id = $db->getValue('voucher_detail','vp_id',array('vd_id'=>$tag_id));
//   $voucher_id = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$vp_id));
//   $voucher_no = $db->getValue('voucher','voucher_no',array('voucher_id'=>$voucher_id));
//   $new_description = 'voucher: '.$voucher_no;
//   if($description != $new_description && $tag_id){
//     #echo $r['description'].'<br>';
//     // echo $tag_id.'<br>';
//     #echo 'old Desc: '.$description.' - new: '.$new_description;
//     #echo '<br>';
//     echo $db->updatePrint('account_statement',array('description'=>$new_description),array('as_id'=>$as_id));
//     echo '<br>';    
//   }

// endwhile;
?>