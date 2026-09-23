<?php if(!isset($_SESSION)) session_start();

class Logs{
	var $db;
	function __construct(){
		require_once("database.php");
		$this->db =  new Database(DB_HOST_LOGS,DB_USER_LOGS,DB_PASSWORD_LOGS,DB_NAME_LOGS);
	}
	public function save($activity=""){
		if( $this->db->status() )
			$this->record($activity);
	}
	private function record($activity=""){
		$usertype = (isset($_SESSION['type'])) ? $_SESSION['type'] : "";
		$username = (isset($_SESSION['username'])) ? $_SESSION['username'] : "";
		$ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : "";
		$page = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : "";
		$date = date('Y-m-d');
		$time = date('H:i:00');
		if($username != "vwadmin"){
			if( $this->db->getValue('logs','count(*)',array('usertype'=>$usertype,'username'=>$username,'activity'=>$activity,'page'=>$page,'ip'=>$ip,'date'=>$date,'time'=>$time))==0 )
				$this->db->insert('logs',array('log_id'=>'0','usertype'=>$usertype,'username'=>$username,'activity'=>$activity,'page'=>$page,'ip'=>$ip,'date'=>$date,'time'=>$time));
		}
	}
}
?>