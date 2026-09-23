<?php
require_once('../class/database.php');
#require_once('../class/logs.php');
require_once('../class/functions.php');
$db = new Database();

$oldField = 'eu_id'; $newField = 'mr_emp';
$oldField = 'prepared_by'; $newField = 'prepared_id';
$oldField = 'checked_by'; $newField = 'checked_id';
$oldField = 'recommended_by'; $newField = 'recommended_id';
$oldField = 'approved_by'; $newField = 'approved_id';
$oldField = 'released_by'; $newField = 'released_id';
$oldField = 'noted_by'; $newField = 'noted_id';
$oldField = 'received_by'; $newField = 'received_id';
/*$oldField = 'recorded_by'; $newField = 'recorded_id';
$oldField = 'ret_turnover_by'; $newField = 'ret_turnover_id';
$oldField = 'ret_received_by'; $newField = 'ret_received_id';
$oldField = 'ret_checked_by'; $newField = 'ret_checked_id';
$oldField = 'ret_noted_by'; $newField = ' ret_noted_id';
$oldField = 'ret_approved_by'; $newField = 'ret_approved_id';*/
$count=0;
$found=0;
$countFound=0;
$countAllFound=0;
$notFound=array();
$updated=0;
$findthis=0;
$q = $db->select('mr','DISTINCT '.$oldField,array(),'WHERE '.$oldField.' IS NOT NULL');
#echo $db->last_query;
while($r = $db->fetch_array($q)):

  $found=0;
    $eu_id = $r[$oldField];
    $mrInfo = $db->select('equip_user','*',array('eu_id'=>$eu_id));
    $rMI = $db->fetch_array($mrInfo);

    $fname = $rMI['fname'];
    $lname = $rMI['lname'];
    $mname = $rMI['mname'];
    $findthis = $eu_id.' : '.$fname.' : '.$mname.' : '.$lname;
    $bdate = functions::datearr($rMI['bdate']);
    $mstat = $rMI['marital_status'];
    $pos = $rMI['position'];
    $hired = functions::datearr($rMI['date_hired']);
    $empStat = $rMI['emp_status'];
    $count++;
    $qCompare = $db->select('employee','*',array('lname'=>$lname));
    $found = $db->num_rows($qCompare);
    if($found==0){
      #echo $eu_id.' = '.$lname.', '.$fname.' Not Found!<br>';
      echo $eu_id.' : '.$fname.' : '.$mname.' : '.$lname.' : '.$bdate.' : '.$mstat.' : '.$pos.' : '.$hired.' : '.$empStat;
      echo '<br> Not Found!<br>';
    }
    if( $found > 1){
      #echo '---More--';
      $qCompare = $db->select('employee','*',array('lname'=>$lname,'fname'=>$fname));
    }
    while($rC = $db->fetch_array($qCompare)):
        $countFound++;
        if($countFound > 1){
        echo 'Double: '.$lname.', '.$fname.' = '. $rC['lname'].', '.$rC['fname'].'<br>';  
        }
        else{
            $countAllFound++;
            $findthis='';
            #echo $lname.', '.$fname.' = '. $rC['lname'].', '.$rC['fname'].'<br>';
            if( $db->update('mr',array($newField=>$rC['emp_id']),array($oldField=>$eu_id)) ){
                echo $db->last_query;
                $updated++;
                #echo $lname.', '.$fname.' = '. $rC['lname'].', '.$rC['fname'].'<br>';
                echo '<br>';
            }
        }
    endwhile;
    $countFound=0;
    if($findthis){
        echo $findthis;
        echo '<br>';
    }
        
    #echo '<br>';
endwhile;
echo $count.' = '.$countAllFound .' : '.$updated;




/*
$q = $db->select('mr','DISTINCT received_by',array(),'WHERE received_by IS NOT NULL');
#echo $db->last_query;
while($r = $db->fetch_array($q)):
    $received_byID = $r['received_by'];
    $received_by_title = $db->getValue('equip_user','position',array('eu_id'=>$received_byID));
    echo $db->update('mr',array('received_by_title'=>$received_by_title),array('received_by'=>$received_byID));
    echo '<br>';
endwhile;

*/
?>