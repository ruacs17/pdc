<?php 
require_once('../class/database.php');
require_once('../class/functions.php');

function upt($im_id,$colm,$empid){
	global $db;
	$qp=$db->select('equip_user','*',array('eu_id'=>$empid));
	#echo $db->last_query.'<br>';
	$rp = $db->fetch_array($qp);
	$prep_fname = $rp['fname'];
	$prep_lname = $rp['lname'];
	if($db->num_rows($qp)){
		$q1 = $db->query('
			SELECT * FROM employee WHERE 
			( lower(lname) LIKE "%'.strtolower($prep_lname).'%" OR concat(lower(lname),", ",lower(extname)) LIKE "%'.strtolower($prep_lname).'%" OR concat(lower(lname),", ",lower(extname),".") LIKE "%'.strtolower($prep_lname).'%" ) 
			AND 
			( lower(fname) LIKE "%'.strtolower($prep_fname).'%" OR concat(lower(fname),", ",lower(extname)) LIKE "%'.strtolower($prep_fname).'%" OR concat(lower(fname),", ",lower(extname),".") LIKE "%'.strtolower($prep_fname).'%" )');
		#echo $db->last_query.'<br>';
		if( $db->num_rows($q1) ){
			while($r1 = $db->fetch_array($q1)):
				if($r1['emp_id']){
					$upt = 'UPDATE inhouse_material SET '.$colm.'="'.$r1['emp_id'].'" WHERE im_id="'.$im_id.'";';
					$db->query($upt);
					echo $upt.'<br>';
					echo $r1['lname'].', '.$r1['fname'].' = '.$prep_lname.', '.$prep_fname.'<br>';
				}
					
			endwhile;
		}
		else{
			echo '<br><br>'.$prep_lname.','.$prep_fname.' = not found<br><br>';
		}		
	}

}

$db = new Database();

?>
<table border="1" width="100%" style="font-size:12px;">
	<tr>
		<td>prepared_by</td>
		<td>approved_by</td>
		<td>check_by</td>
		<td>deliver_by</td>
		<td>receive_by</td>
	</tr>
	<tr>
		<td valign="top">
			<?php
			$q = $db->query('SELECT * FROM inhouse_material');
			while($r = $db->fetch_array($q)):
				if( empty($r['prepared_id']) )
					upt($r['im_id'],'prepared_id',$r['prepared_by']);
			endwhile;
			?>
		</td>
		<td valign="top">
			<?php
			$q = $db->query('SELECT * FROM inhouse_material');
			while($r = $db->fetch_array($q)):
				if( empty($r['approved_id']) )
					upt($r['im_id'],'approved_id',$r['approved_by']);
			endwhile;
			?>
		</td>
		<td valign="top">
			<?php
			$q = $db->query('SELECT * FROM inhouse_material');
			while($r = $db->fetch_array($q)):
				if( empty($r['checked_id']) )
					upt($r['im_id'],'checked_id',$r['check_by']);
			endwhile;
			?>
		</td>
		<td valign="top">
			<?php
			$q = $db->query('SELECT * FROM inhouse_material');
			while($r = $db->fetch_array($q)):
				if( empty($r['delivered_id']) )
					upt($r['im_id'],'delivered_id',$r['deliver_by']);
			endwhile;
			?>
		</td>
		<td valign="top">
			<?php
			$q = $db->query('SELECT * FROM inhouse_material');
			while($r = $db->fetch_array($q)):
				if( empty($r['received_id']) )
					upt($r['im_id'],'received_id',$r['receive_by']);
			endwhile;
			?>
		</td>
	</tr>
</table>
<?php


/*
$q = $db->query('SELECT * FROM inhouse_material');
while($r = $db->fetch_array($q)):
	$colid='received_id';
	$prepared_by = $r['prepared_by'];
	$approved_by = $r['approved_by'];
	$check_by = $r['check_by'];
	$deliver_by = $r['deliver_by'];
	$receive_by = $r['receive_by'];
	if( empty($r[$colid]) )
		upt($r['im_id'],$colid,$receive_by);

	#$qp=$db->select('equip_user','*',array('eu_id'=>$prepared_by));
	$qp=$db->select('equip_user','*',array('eu_id'=>$approved_by));
	#$qp=$db->select('equip_user','*',array('eu_id'=>$check_by));
	#$qp=$db->select('equip_user','*',array('eu_id'=>$deliver_by));
	#$qp=$db->select('equip_user','*',array('eu_id'=>$receive_by));
	$rp = $db->fetch_array($qp);
	$prep_fname = $rp['fname'];
	$prep_lname = $rp['lname'];

	$q1 = $db->query('
		SELECT * FROM employee WHERE 
		( lower(lname) LIKE "%'.strtolower($prep_lname).'%" OR concat(lower(lname),", ",lower(extname)) LIKE "%'.strtolower($prep_lname).'%" OR concat(lower(lname),", ",lower(extname),".") LIKE "%'.strtolower($prep_lname).'%" ) 
		AND 
		( lower(fname) LIKE "%'.strtolower($prep_fname).'%" OR concat(lower(fname),", ",lower(extname)) LIKE "%'.strtolower($prep_fname).'%" OR concat(lower(fname),", ",lower(extname),".") LIKE "%'.strtolower($prep_fname).'%" ) LIMIT 2');
	echo $db->last_query.'<br>';
	if( $db->num_rows($q1) ){
		while($r1 = $db->fetch_array($q1)):
			if($r1['emp_id'])
				echo $r1['lname'].', '.$r1['fname'].' = '.$prep_lname.', '.$prep_fname.'<br>';
		endwhile;
	}
	else{
		echo '<br><br>'.$prep_lname.','.$prep_fname.' = not found<br><br>';
	}

endwhile;*/
/*


ALTER TABLE inhouse_material ADD COLUMN prepared_id INT AFTER im_date, ADD CONSTRAINT FOREIGN KEY (prepared_id) REFERENCES employee(emp_id) ON UPDATE CASCADE;

ALTER TABLE inhouse_material ADD COLUMN approved_id INT AFTER prepared_id, ADD CONSTRAINT FOREIGN KEY (approved_id) REFERENCES employee(emp_id) ON UPDATE CASCADE;
ALTER TABLE inhouse_material ADD COLUMN checked_id INT AFTER approved_id, ADD CONSTRAINT FOREIGN KEY (checked_id) REFERENCES employee(emp_id) ON UPDATE CASCADE;
ALTER TABLE inhouse_material ADD COLUMN delivered_id INT AFTER checked_id, ADD CONSTRAINT FOREIGN KEY (delivered_id) REFERENCES employee(emp_id) ON UPDATE CASCADE;
ALTER TABLE inhouse_material ADD COLUMN received_id INT AFTER delivered_id, ADD CONSTRAINT FOREIGN KEY (received_id) REFERENCES employee(emp_id) ON UPDATE CASCADE;
*/
// $q = $db->query("SELECT * FROM `account_statement` WHERE `transaction` LIKE '%FUND TRANSFER%'");
// while($r = $db->fetch_array($q)):
// 	$tag_id = $r['tag_id'];
// 	$as_id = $r['as_id'];
// 	$description = $r['description'];
// 	$vp_id = $db->getValue('voucher_detail','vp_id',array('vd_id'=>$tag_id));
// 	$voucher_id = $db->getValue('voucher_particular','voucher_id',array('vp_id'=>$vp_id));
// 	$voucher_no = $db->getValue('voucher','voucher_no',array('voucher_id'=>$voucher_id));
// 	$new_description = 'voucher: '.$voucher_no;
// 	if($description != $new_description && $tag_id){
// 		#echo $r['description'].'<br>';
// 		// echo $tag_id.'<br>';
// 		#echo 'old Desc: '.$description.' - new: '.$new_description;
// 		#echo '<br>';
// 		echo $db->updatePrint('account_statement',array('description'=>$new_description),array('as_id'=>$as_id));
// 		echo '<br>';		
// 	}

// endwhile;
?>