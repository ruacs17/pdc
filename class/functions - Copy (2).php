<?php

class functions{

	public static function sendTo($page){
		echo '
		<script>window.location="'.$page.'";</script>
		';
	}

	public static function say($msg){
		echo '
		<script>alert("'.$msg.'");</script>
		';
	}
	
	public static function toupper($value){
		return strtoupper($value);
	}
	
	public static function statMean($stat){
		switch($stat):
			case 1: return "Permanent"; break;
			case 2: return "Probee"; break;
			case 3: return "Part Time"; break;
			default: return "Part Time";
		endswitch;
	}
	
	public static function valid_email($email){
		if(filter_var($email, FILTER_VALIDATE_EMAIL))
			return true;
		else
			return false;
	}
	
	
	#special character detector
	public static function has_special_char($character){
	$string = strtolower($character);
	
	$string_count = strlen($string);
	$accepted = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","ñ","Ñ","o","p","q","r","s","t","u","v","w","x","y","z","@",".","_","-","0","1","2","3","4","5","6","7","8","9");
	$size = sizeof($accepted);
	
	
	$accepted_count = 0;
		for($x=1;$x<=$string_count;$x++){
		
		$a = substr($string,$x-1,1);
			for($i=0; $i<$size; $i++){
				if($accepted[$i] == $a)
				$accepted_count++;
			}
		}
	
	
		if($string_count != $accepted_count)
			$true = 1;
		else
			$true = 0;
		
	return $true;
	}	
	
	public static function decrypt($data){
		return base64_decode($data);
	}

	public static function encrypt($data){
		return base64_encode($data);
	}	
	
	public static function datearr($date){
		$exp = explode("-",$date);
		$d='-- -- ----';
		if( count($exp)==3){
		$mb = $exp[1];
		$dte = $exp[2];
		$yr = $exp[0];
			if($mb==="00" || $dte=="00" || $yr==="0000"){
			}
			else{
			if($mb == "01"){$mb1 = 'Jan';}
			if($mb == "02"){$mb1 = 'Feb';}
			if($mb == "03"){$mb1 = 'Mar';}
			if($mb == "04"){$mb1 = 'Apr';}
			if($mb == "05"){$mb1 = 'May';}
			if($mb == "06"){$mb1 = 'Jun';}
			if($mb == "07"){$mb1 = 'Jul';}
			if($mb == "08"){$mb1 = 'Aug';}
			if($mb == "09"){$mb1 = 'Sep';}
			if($mb == "10"){$mb1 = 'Oct';}
			if($mb == "11"){$mb1 = 'Nov';}
			if($mb == "12"){$mb1 = 'Dec';}
				$d = $mb1." ".$dte.", ".$yr;
			}
		}
		return $d;
	}
	
	static function clean($string){
		return addslashes($string);
	}

	static function average($arrNum,$includeZero=FALSE){
		$sum=0;
		$count=0;
		$average=0;
		if($includeZero){
			$count = count($arrNum);
			$sum = array_sum($arrNum);
			$average = ($sum/$count) ? $sum/$count : 0;		

		}
		else{
		foreach($arrNum as $num):
			if($num > 0){
				$sum+=$num;
				$count++;
			}
		endforeach;
		$average = ($sum && $count) ? $sum/$count : 0;
		}

		return $average;
	}
	
	static function appropriateUser($userTypeFor,$userType){
		$con=FALSE;

		foreach($userType as $each){
			if($userTypeFor == $each){
				$con=TRUE;
				break;
			}
		}
		if($con==FALSE)
			functions::sendTo("index.php");
	}


	static function decode($data){
		return base64_decode($data);
	}
	

	static function encode($data){
		return base64_encode($data);
	}
	
	static function search_array($find,$array){
			return in_array($find,$array);
	}

	static function insert_array($arr,$value){
		$count=count($arr);
		$index=0;

		//print_r($arr);
		$found=0;
		if($count==0)
			$arr[0]=$value;
		else{
			foreach($arr as $a){
				if($a==$value)
					$found++;
			}
			if($found==0){
				foreach($arr as $b => $c){
					$index=$b;
				}
				$arr[$index + 1]=$value;
			}
		}
		return $arr;
	}

	static function isfloat($num) {
		return is_float($num) || is_numeric($num) && ((float) $num != (int) $num);
	}

	static function delete_array($arr,$value){
		$count=count($arr);
		if($count>0){
			foreach($arr as $a => $val){
				if($val==$value)
					unset($arr[$a]);
			}
		}
		return $arr;
	}

	static function sortMultiArray(&$multiArray,$oderBy_index='',$sort_AscDesc=''){
		$sortArray = array(); 
		$sort_AscDesc = ($sort_AscDesc==='asc' || $sort_AscDesc==='ASC') ? SORT_ASC : SORT_DESC;

		foreach($multiArray as $array){ 
			foreach($array as $key=>$value){ 
				if(!isset($sortArray[$key])){ 
					$sortArray[$key] = array(); 
				}
				$sortArray[$key][] = $value;
			}
		}
		array_multisort($sortArray[$oderBy_index],$sort_AscDesc,$multiArray);
	}

	static function getMacIp($what){
/*
		if (getenv('HTTP_X_FORWARDED_FOR')) 
			{
				$ip = getenv('HTTP_X_FORWARDED_FOR');
				$remoteIP = rtrim($ip);
				$ip = explode(",",$remoteIP); 
				$location = rtrim(`arp $ip[0]`);
				$macadd = explode(" ", $location);
			} 
		else if(getenv('HTTP_CLIENT_IP'))
			{
			$ip = getenv('HTTP_CLIENT_IP');
			$remoteIP = rtrim($ip);
			$ip = explode(",",$remoteIP); 
			$location = rtrim(`arp $ip[0]`);
			$macadd = explode(" ", $location);
			}
		else
			{
			//$ip = $_SERVER['REMOTE_ADDR'];
			$ip = getenv('REMOTE_ADDR');
			$remoteIP = rtrim($ip);
			$ip = explode(",",$remoteIP); 
			$location = rtrim(`arp $ip[0]`);
			$macadd = explode(" ", $location);
			}	
	
		
			if($what == 'ip')
				return $ip[0];
			if($what == 'mac')
				return $macadd[3];
	*/
			if($what == 'ip')
				return '192.168.6.10';
			if($what == 'mac')
				return 'no';
	}

	static function machine_detect($useragent){

		if(preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
		return true;
	#true if mobile
	}
	static function workingDaysInYear($year){
		$workingDays=0;
		for($month=1; $month<=12; $month++):
			#$saturdays = functions::dayNameInMonth($month,$year,$dayName='Saturday');
			#$sundays = functions::dayNameInMonth($month,$year,$dayName='Sunday');
			$monthDays = functions::daysInMonth($month,$year);
			#$workingDays += $monthDays - ($saturdays + $sundays) + ($saturdays/2)
			$workingDays += functions::daysInMonth($month,$year) - functions::dayNameInMonth($month,$year,$dayName='Sunday') - (functions::dayNameInMonth($month,$year,$dayName='Saturday')/2);
		endfor;
		return $workingDays;
	}


	static function daysInMonth($month,$year){
		#return cal_days_in_month(CAL_GREGORIAN,$month,$year);
		return date('t', mktime(0, 0, 0, $month, 1, $year)); 
	}
	static function dayNameInMonth($month,$year,$dayName=''){
		$dates = functions::daysInMonth($month,$year);
		$countExist=0;
		$date_start = $year.'-'.$month.'-01';
		for($i=0;$i<$dates;$i++):
			$succeeding_date = functions::AddDay($date_start,$i);
			$daysName = date('l', strtotime($succeeding_date));
			if($daysName==$dayName)
				$countExist++;
		endfor;
		return $countExist;
	}

	static function workingDaysCount($month,$year,$dayName){
		return functions::daysInMonth($month,$year) - functions::dayNameInMonth($month,$year,$dayName);
	}

	static function AddDay($startDate,$dayAdd){
		$d = explode("-",$startDate);
		$year = $d[0];
		$month = $d[1];
		$day = $d[2];
		return $payDay = date('Y-m-d',mktime(0,0,0,$month,$day + $dayAdd,$year));
	}

	static function dutyDays($date_start,$date_end){
		$dates = functions::date_diff($date_start,$date_end);
		$dutyDays=0;
		if($dates){
			for($i=0;$i<=$dates;$i++):
				$empRegDuty=0;
				$succeeding_date = functions::AddDay($date_start,$i);
				$daysName = date('l', strtotime($succeeding_date));
				if($daysName == 'Saturday')
					$empRegDuty = .5;
				else if($daysName=='Sunday')
					;
				else
					$empRegDuty = 1;
				$dutyDays += $empRegDuty;
			endfor;
		}
		return $dutyDays;
	}
	
	
	#compute the minute difference
	static function min_diff($timefrom,$datefrom,$timeto,$dateto){
		$hrfrom = (substr($timefrom,0,2)) ? substr($timefrom,0,2) : 0;
		$minfrom = (substr($timefrom,3,2)) ? substr($timefrom,3,2) : 0;
		$monfrom = (substr($datefrom,5,2)) ? substr($datefrom,5,2) : 0;
		$dayfrom = (substr($datefrom,8,2)) ? substr($datefrom,8,2) : 0;
		$yrfrom = (substr($datefrom,0,4)) ? substr($datefrom,0,4) : 0;

		$hrto = (substr($timeto,0,2)) ? substr($timeto,0,2) : 0;
		$minto = (substr($timeto,3,2)) ? substr($timeto,3,2) : 0;
		$monto = (substr($dateto,5,2)) ? substr($dateto,5,2) : 0;
		$dayto = (substr($dateto,8,2)) ? substr($dateto,8,2) : 0;
		$yrto = (substr($dateto,0,4)) ? substr($dateto,0,4) : 0;

		$datestart = mktime($hrfrom,$minfrom,0,$monfrom,$dayfrom,$yrfrom);
		$dateend = mktime($hrto,$minto,0,$monto,$dayto,$yrto);
		  
		$dateDiff = $dateend - $datestart;
	
		$fullDays = floor($dateDiff/(60*60*24));
	
		$fullHours = floor(($dateDiff-($fullDays*60*60*24))/(60*60));
	
		$fullMinutes = floor(($dateDiff-($fullDays*60*60*24)-($fullHours*60*60))/60);
	
	
		$result = ((($fullDays * 24) + $fullHours)* 60) + $fullMinutes;
		return $result;
	}


	static function hm_to_minute($time=''){
		$minutes=0;
		if($time){
			$time_explode = explode(":",$time);
			$hr = isset($time_explode[0]) ? (int)$time_explode[0] : 0;
			$min = isset($time_explode[1]) ? (int)$time_explode[1] : 0;
			$minutes = ($hr*60) + $min;
		}
		return $minutes;
	}
	
	#converting minutes into hh:mm:ss	
	static function min_to_hour($minutes_consumed){
		$hour = intval($minutes_consumed/60);
		$minutes = intval($minutes_consumed%60);
		$second = "00";
		if(strlen($hour)==1)
			$newhour = "0".$hour;
		else
			$newhour = $hour;
		if(strlen($minutes)==1)
			$newminutes = "0".$minutes;
		else
			$newminutes = $minutes;
			
		return $time_consumed = $newhour.":".$newminutes.":".$second;
	}
	
	#converting HH:mm:ss to Day
	static function hour_to_day($hour){
		$time='';
		$tme = explode(":",$hour);
		
		if(count($tme)==3){
			$hr = $tme[0];
			$mn = $tme[1];
			$sc = $tme[2];
	
			if($hr >= 100){
				$day = intval($hr / 24);
				$hr = $hr % 24;
				$time = ($day > 1) ? $day." days ".$hr.":".$mn.":".$sc : $day." day ".$hr.":".$mn.":".$sc;
			}
			else if($hr < 100)
				$time = $hour;
		}
		return $time;
	}	

	#converting 24 hours to 12 hours hh:mm format
	static function MilToTwelve($military_hour){	
		$mil = explode(":",$military_hour);
		if( count($mil)>= 2){
			$hr=$mil[0];
			$mn=$mil[1];
			if($hr >= 12){
				$hr = $hr - 12;
				if($hr==0)
					$hr = 12;
				else if(strlen($hr) == 1)
					$hr = '0'.$hr;

				return $hr.':'.$mn.' pm';
			}
			else{
				if($hr==0)
					return '12:'.$mn.' am';
				else		
					return $hr.':'.$mn.' am';
			}	
		}
	}

	static function moneyToDouble($money){
		$number=0;
		$decimal=0;
		$num_break = explode('.',$money);
		if(count($num_break)>1){
			$decimal = $num_break[1];
			$number = $num_break[0];
		}
		else
			$number = $money;
		$s = preg_replace('|[^0-9]|i', '', $number);
		$s = ($money < 0) ? '-'.$s : $s;
		$final = ($decimal) ? floatval($s.'.'.$decimal) : $s;
		return $final;
	}
	
	static function formatMoney($figure=0,$decimal=2){
		if($figure && is_numeric($figure))
			return number_format($figure,$decimal,'.',',');
		else
			return '0.00';
	}
	
	
	static function expiration_diff($expiration_date){
		$c=0;
		$exp = explode("-",$expiration_date);
		if(count($exp)==3){
			$expiration_year = $exp[0];
			$expiration_mon = $exp[1];
			$expiration_date = $exp[2];
		
			$curr_date = getdate(time());
			$curr = mktime(0,0,0,$curr_date['mon'],$curr_date['mday'],$curr_date['year']);
			$a = getdate($curr);
			$a[0];	
		
			$expiration = mktime(0,0,0,$expiration_mon,$expiration_date,$expiration_year);
			$b = getdate($expiration);
			$b[0];	
		
			$c = $b[0] - $a[0];
		} 
		if($c <= 0)
			return 1;
		else
			return 0;	 
	}

	static function date_diff($date_from=0,$date_to=0){
		$fromYear=0;$fromMon=0;$fromDay=0;
		$toYear=0;$toMon=0;$toDay=0;
		$diff=0;$dayDiff=0;
		$fromSet=0;$toSet=0;
		$from = explode('-',$date_from);

		if(count($from)==3){
			$fromYear = $from[0];
			$fromMon = $from[1];
			$fromDay = $from[2];
			$fromSet = mktime(0,0,0,$fromMon,$fromDay,$fromYear);
		}
		$to = explode('-',$date_to);
		if(count($to)==3){
			$toYear = $to[0];
			$toMon = $to[1];
			$toDay = $to[2];
			$toSet = mktime(0,0,0,$toMon,$toDay,$toYear);
		}

		if($toSet && $fromSet){
			$diff = $toSet - $fromSet;

			$dayDiff = round($diff/(60*60*24));
		}
		return $dayDiff; 
	}

	static function month_diff($date_from=0,$date_to=0){
	    $fromYear=0;$fromMon=0;$fromDay=0;
	    $toYear=0;$toMon=0;$toDay=0;
	    $diff=0;$dayDiff=0;$monthDiff=0;
	    $fromSet=0;$toSet=0;
	    $from = explode('-',$date_from);

	    if(count($from)==3){
			$fromYear = $from[0];
			$fromMon = $from[1];
			$fromDay = $from[2];
			$fromSet = mktime(0,0,0,$fromMon,$fromDay,$fromYear);
	    }
	    $to = explode('-',$date_to);
	    if(count($to)==3){
			$toYear = $to[0];
			$toMon = $to[1];
			$toDay = $to[2];
			$toSet = mktime(0,0,0,$toMon,$toDay,$toYear);
	    }

	    if($toSet && $fromSet){
			$diff = $toSet - $fromSet;

			$dayDiff = round($diff/(60*60*24));
			$monthDiff = ceil($dayDiff/30);
	    }
	    return $monthDiff; 
	}

	static function year_diff($date_from=0,$date_to=0){
		$dateFrom = new DateTime($date_from.' 00:00:00');
		$dateTo = $dateFrom->diff(new DateTime($date_to.' 00:00:00'));
		return $dateTo->y;
		/*$months = functions::month_diff($date_from,$date_to);
		return ($months) ? round($months / 12,2) : 0;*/
	}

	
	static function pagination($display,$page_display,$num_record,$startrow,$pagename,$search){
		$pageExtension = explode("?",$pagename);
		$mainPage = $pageExtension[0];
		$pageExtension = '&'.$pageExtension[1];
		if($num_record >= $display){
		
		$searchWhat = '';
		if(is_array($search)){
			foreach($search as $searchfor):
				$searchWhat.= '&'.$searchfor;
			endforeach;
		}
		
			$pages = ceil($num_record / $display);
			$current_page = 0;
			$page_current=0;
			//getting the current page
			for($i=0; $i<$pages; $i++){
			$from = $i * $display;
			$to = $from + $display - 1;
			$page = $i + 1;
				if($from <= $startrow && $to >= $startrow){
				$page_current = $page;
				break;
				}	
			}
			
			
			
			if(($page_current % $page_display) == 0)
				$page_start = $page_current-$page_display;
			else
				$page_start = ($page_current - ($page_current % $page_display));		
		
		
		echo '<table border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
				<tr>';
		
			//for the previous pagination
			if($page_current > ($page_display)){
				$from = ($page_start -1) * $display;
				echo '
				  <td width="50" align="center" class="pagenum" onMouseOver="'."this.style.backgroundColor='#E8D7D7'".'" onMouseOut="'."this.style.backgroundColor='#FFFFFF'".'">
				  <a href="'.$mainPage.'?startrow=0'.$searchWhat.$pageExtension.'"><div class="pagination_font">First</div></a>
				  </td>
				  <td width="2" align="center"></td>
				  <td width="55" align="left" class="pagenum" onMouseOver="'."this.style.backgroundColor='#E8D7D7'".'" onMouseOut="'."this.style.backgroundColor='#FFFFFF'".'">
				  <a href="'.$mainPage.'?startrow='.$from.$searchWhat.$pageExtension.'"><div class="pagination_font"><font size="-6">&lt;&lt;</font>Previous</div></a></td>
				  <td width="8" align="center"></td>
				';
			}
		
			$x = 0;
			$start = 0;
		
			while($x < $page_display){
			
				$display_pages = $page_start + $x + 1;
				$from = ($display_pages - 1) * $display;
				if($pages >= $display_pages){
					if($display_pages == $page_current){
						echo '
							  <td width="23" align="center" class="pagenum" bgcolor="#E8D7D7" >
							  <a href="'.$mainPage.'?startrow='.$from.$searchWhat.$pageExtension.'"><div class="pagination_font">'.$display_pages.'</div></a>
							  </td>
							  <td width="4" align="center"></td>
						';			
					}
					else{
						echo '
							  <td width="23" align="center" class="pagenum" onMouseOver="'."this.style.backgroundColor='#E8D7D7'".'" onMouseOut="'."this.style.backgroundColor='#FFFFFF'".'">
							  <a href="'.$mainPage.'?startrow='.$from.$searchWhat.$pageExtension.'"><div class="pagination_font">'.$display_pages.'</div></a>
							  </td>
							  <td width="4" align="center"></td>
						';				
					}		
		
				}	
			$x++; //as long as $x is lesser than $page_display it will continue looping 
			}
		
		
		
			//for next pagination line
			if($pages >= $display_pages + 1){
				$display_pages = $page_start + $x + 1;
				$from = ($display_pages - 1) * $display;
				$lastpage = $pages * $display;	
				
				
				echo '
				  <td width="8" align="center"></td>
				  <td width="50" align="center" class="pagenum" onMouseOver="'."this.style.backgroundColor='#E8D7D7'".'" onMouseOut="'."this.style.backgroundColor='#FFFFFF'".'">
				  <a href="'.$mainPage.'?startrow='.$from.$searchWhat.$pageExtension.'"><div class="pagination_font">Next<font size="-6">&gt;&gt;</font></div></a>
				  </td>
				  <td width="2" align="center"></td>
				';		
				
				
				if( $lastpage < $num_record ){
					echo '
					<td width="55" align="left" class="pagenum" onMouseOver="'."this.style.backgroundColor='#E8D7D7'".'" onMouseOut="'."this.style.backgroundColor='#FFFFFF'".'">
					<a href="'.$mainPage.'?startrow='.$lastpage.$searchWhat.$pageExtension.'"><div class="pagination_font">Last</div></a></td>			
					';		
				}
				else{
					$lastpage = $lastpage - $display;
					echo '
					<td width="55" align="left" class="pagenum" onMouseOver="'."this.style.backgroundColor='#E8D7D7'".'" onMouseOut="'."this.style.backgroundColor='#FFFFFF'".'">
					<a href="'.$mainPage.'?startrow='.$lastpage.$searchWhat.$pageExtension.'"><div class="pagination_font">Last</div></a></td>			
					';
				}
			}
		
			echo '
				</tr>
			</table>	
			';
		}	
	}	
	
	static function findChar($string,$find){
		$found = 0;
		for($i=1; $i<=strlen($string); $i++){
			if( $find == substr($string,$i-1,1) ){
				$found = 1;
			}
		
		} 
		return $found;
	}	


		static function number_to_words($number) {

		    $hyphen      = '-';
		    #$conjunction = ' and ';
		    $conjunction = ' ';
		    $separator   = ' ';
		    $negative    = 'negative ';
		    $decimal     = ' & ';
		    $dictionary  = array(
		        0                   => 'zero',
		        1                   => 'one',
		        2                   => 'two',
		        3                   => 'three',
		        4                   => 'four',
		        5                   => 'five',
		        6                   => 'six',
		        7                   => 'seven',
		        8                   => 'eight',
		        9                   => 'nine',
		        10                  => 'ten',
		        11                  => 'eleven',
		        12                  => 'twelve',
		        13                  => 'thirteen',
		        14                  => 'fourteen',
		        15                  => 'fifteen',
		        16                  => 'sixteen',
		        17                  => 'seventeen',
		        18                  => 'eighteen',
		        19                  => 'nineteen',
		        20                  => 'twenty',
		        30                  => 'thirty',
		        40                  => 'fourty',
		        50                  => 'fifty',
		        60                  => 'sixty',
		        70                  => 'seventy',
		        80                  => 'eighty',
		        90                  => 'ninety',
		        100                 => 'hundred',
		        1000                => 'thousand',
		        1000000             => 'million',
		        1000000000          => 'billion',
		        1000000000000       => 'trillion',
		        1000000000000000    => 'quadrillion',
		        1000000000000000000 => 'quintillion'
		    );

		    if (!is_numeric($number)) {
		        return false;
		    }

		    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
		        // overflow
		        trigger_error(
		            'number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
		            E_USER_WARNING
		        );
		        return false;
		    }

		    if ($number < 0) {
		        return $negative . self::number_to_words(abs($number));
		    }

		    $string = $fraction = null;

		    if (strpos($number, '.') !== false) {
		        #list($number, $fraction) = explode('.', $number);
		        $exp = explode('.', $number);
		        $number = $exp[0];
		        $fraction = $exp[1];
		    }

		    switch (true) {
		        case $number < 21:
		            $string = $dictionary[$number];
		            break;
		        case $number < 100:
		            $tens   = ((int) ($number / 10)) * 10;
		            $units  = $number % 10;
		            $string = $dictionary[$tens];
		            if ($units) {
		                $string .= $hyphen . $dictionary[$units];
		            }
		            break;
		        case $number < 1000:
		            $hundreds  = $number / 100;
		            $remainder = $number % 100;
		            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
		            if ($remainder) {
		                $string .= $conjunction . self::number_to_words($remainder);
		            }
		            break;
		        default:
		            $baseUnit = pow(1000, floor(log($number, 1000)));
		            $numBaseUnits = (int) ($number / $baseUnit);
		            $remainder = $number % $baseUnit;
		            $string = self::number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
		            if ($remainder) {
		                $string .= $remainder < 100 ? $conjunction : $separator;
		                $string .= self::number_to_words($remainder);
		            }
		            break;
		    }

		    if (null !== $fraction && is_numeric($fraction)) {
		        $string .= $decimal;
		        $words = array();
		        foreach (str_split((string) $fraction) as $number) {
		            $words[] = $dictionary[$number];
		        }
		        $fr = (strlen($fraction) > 2) ? substr($fraction,0,2) : $fraction;
		        $string .= $fr.'/100';
		        #$string .= $fraction.'/100';
		        #$string .= implode(' ', $words);
		    }
		   # else
		    	#$string .= '& 0/100';

		    return $string;
		}
}
?>