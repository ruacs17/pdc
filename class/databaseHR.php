<?php
require_once("config.php");

require_once("database.php");

class DatabaseHR extends Database{

	function __construct($db_host=DB_HOST_HR,$db_user=DB_USER_HR,$db_password=DB_PASSWORD_HR,$db_name=DB_NAME_HR){
		parent::__construct($db_host,$db_user,$db_password,$db_name);
	
	}
}

?>