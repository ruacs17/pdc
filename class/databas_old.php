<?php
require_once("config.php");

class Database {
	private $host='';
	private $user='';
	private $password='';
	private $dbname='';	
	private $con='';
	private $stmnt='';
	var $last_query='';
	var $error='';
	
	function __construct($db_host=DB_HOST,$db_user=DB_USER,$db_password=DB_PASSWORD,$db_name=DB_NAME){
		try{
			$this->con = new PDO("mysql:host=".$db_host.";dbname=".$db_name, $db_user, $db_password);
			$this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->host=$db_host;
			$this->user=$db_user;
			$this->password=$db_password;
			$this->dbname=$db_name;			
		}
		catch(Exception $e){
			$this->error=$e->getMessage();	
			die('no database connection');
		}
	}
	
	public function info(){
			echo "Host: [" . $this->host. "] User: [" . $this->user. "] Password: [". $this->password. "] Database: [" . $this->dbname."]";
	}
	
	public function utf8(){
		$this->query("SET CHARACTER SET 'utf8'");
	}	
	
	public function close(){
		if(isset($this->con))
			unset($this->con);
	}
	
	public function close_connection(){
		if(isset($this->con))
			unset($this->con);
	}	
	
	public function status(){
		return ($this->con) ? true : false;
	}
	
	public function clean($data){
		return addslashes($data);
	}
	
  	public function insert_id() {
    // get the last id inserted over the current db connection
		return $this->con->lastInsertId();
  	}
	
  	public function fetch_array($result_set="") {
		if($this->status())
			return ($result_set) ? $result_set->fetch() : $this->stmnt->fetch();			
		else
			return array();
  	}
	
  	public function num_rows($result_set="") {
		if($this->status())
			return ($result_set) ? $result_set->rowCount() : $this->stmnt->rowCount();			
		return 0;	
  	}
	
  	public function num_fields($result_set="") {
		if($this->status())
			return ($result_set) ? $result_set->columnCount() : '';
		else
			return 0;
  	}
	
  	public function fetch_field($result_set='') {
			die('fetch_field not found!......');
   	#return mysql_fetch_field($result_set);
  	}

  	public function affected_rows($result_set="") {
		if($this->status())
			return ($result_set) ? $result_set->rowCount() : $this->stmnt->rowCount();
		else
			return 0;
  	}
	
	public function result($result_set="",$row=""){
		if($this->status())
			return ($result_set) ? $result_set->fetchColumn() : $this->stmnt->fetchColumn();
		else
			return 0;
	}
	
	public function free_result($result_set=""){
		if($this->status())
			return ($result_set) ? $result_set->closeCursor() : $this->stmnt->closeCursor();
		else
			return 0;
	}		
	
	public function columnType($table,$columnName){
		$dataType='';
		if($this->status()){
			try{
				$sql = 'SELECT DISTINCT data_type FROM information_schema.columns WHERE table_name="'.$this->clean($table).'" AND column_name="'.$this->clean($columnName).'" AND table_schema="'.$this->clean($this->dbname).'"';
				foreach($this->con->query($sql) as $row):
					$dataType = $row['data_type'];
				endforeach;
			}
			catch(Exception $e){
				$this->error=$e->getMessage();	
			}
		}
		return $dataType;
	}

	private function append($table,$columnName,$value){
		$column='';
		$dataType = $this->columnType($table,$columnName);
			if( $dataType=="tinyint" || $dataType=="smallint" || $dataType=="mediumint" || $dataType=="int" || $dataType=="bigint" )
					$column = (is_numeric($value)) ? $value : $this->con->quote($value);
			else if( $dataType=="decimal" || $dataType=="float" || $dataType=="double" || $dataType=="real" )
					$column = (is_numeric($value)) ? $this->clean($value) : $this->con->quote($value);
			else if( $dataType=="bit" || $dataType=="boolean" || $dataType=="serial")
					$column = (is_numeric($value)) ? $this->clean($value) : $this->con->quote($value);	
			else if($value=='NULL')
					$column = 'NULL';	
			else	
					$column = $this->con->quote($value);
		return $column;	
	}
	
	public function query($query){
			$this->last_query=$query;
			if($this->status()){
				try{
					$this->stmnt = $this->con->query($query);
					return $this->stmnt;	
				}
				catch(Exception $e){
					$this->error=$e->getMessage();
					$output = $this->error;
					$output .= "<br>Last Query: ".$this->last_query;
					die($output);
				}
			}
			else
				return 0;	
	}			
	
	public function getValue($table,$getColumn,$reference=array(),$others=""){
		$result='';
		$table = $this->clean($table);
		$allReference = count($reference);
		$ref="";
		$count=1;
		$where = ($allReference >= 1) ? "WHERE" : "";
		if( is_array($reference) ){
		foreach($reference as $refIndex => $refValue):
			$ref .= $refIndex;
			$ref .= "=";
			$ref .= $this->append($table,$refIndex,$refValue);
			$ref .= ($count < $allReference) ? " AND " : "";
			$count++;	
		endforeach;	
		}
		
		try{
			$statement = "SELECT {$getColumn} FROM {$table} {$where} {$ref} {$others}";	
			$q = $this->query($statement);
			$q->execute();
			$result = $q->fetchColumn();
			$this->free_result($q);
		}
		catch(Exception $e){
			$this->error=$e->getMessage();	
		}
		$this->last_query = $this->getValuePrint($table,$getColumn,$reference,$others);
		return $result;			
	}
	
	public function getValuePrint($table,$getColumn,$reference=array(),$others=""){
		$result='0';
		$table = $this->clean($table);
		$allReference = count($reference);
		$ref="";
		$count=1;
		$where = ($allReference >= 1) ? "WHERE" : "";
		if( is_array($reference) ){
		foreach($reference as $refIndex => $refValue):
			$ref .= $refIndex;
			$ref .= "=";
			$ref .= $this->append($table,$refIndex,$refValue);
			$ref .= ($count < $allReference) ? " AND " : "";
			$count++;	
		endforeach;	
		}
			return $statement = "SELECT {$getColumn} FROM {$table} {$where} {$ref} {$others}";	
	}
	
public function select($table,$getColumn,$reference=array(),$others=""){
		$table = $this->clean($table);
		$allReference = count($reference);
		$ref="";
		$count=1;
		$where = ($allReference >= 1) ? "WHERE" : "";
		$allRefValue=array();
		$query='';
		if( is_array($reference) ){
		foreach($reference as $refName => $refValue):
			$ref .= $refName;
			$ref .= "=";
			$ref .= "?";
			$ref .= ($count < $allReference) ? " AND " : "";
			$allRefValue[]=$refValue;
			$count++;	
		endforeach;	
		}
		$columnGet = ($getColumn) ? $this->clean($getColumn) : "*";
		$statement = "SELECT {$columnGet} FROM {$table} {$where} {$ref} {$others}";	
		$this->last_query=$this->selectPrint($table,$getColumn,$reference,$others);
		try{
			$query = $this->con->prepare($statement);
			$query->execute($allRefValue);
		}
		catch(Exception $e){
			$this->error=$e->getMessage();	
		}	
		return $query;	
	}	
	
	public function selectPrint($table,$getColumn,$reference=array(),$others=""){
		$table = $this->clean($table);
		$allReference = count($reference);
		$ref="";
		$count=1;
		$where = ($allReference >= 1) ? "WHERE" : "";
		if( is_array($reference) ){
		foreach($reference as $refName => $refValue):
			$ref .= $refName;
			$ref .= "=";
			$ref .= $this->append($table,$refName,$refValue);
			$ref .= ($count < $allReference) ? " AND " : "";
			$count++;	
		endforeach;	
		}
		$columnGet = ($getColumn) ? $this->clean($getColumn) : "*";
		return "SELECT {$columnGet} FROM {$table} {$where} {$ref} {$others}";	
	}			

	public function insert($table,$column=array()){
		$result='';
		$table = $this->clean($table);
		$allColumn = count($column);
		$col='';
		$count=1;
		$colName='';
		$disable_column = false;
		$allRefValue=array();
		if( $allColumn ){
			foreach($column as $columnName => $columnValue):
				if(is_numeric($columnName))
					$disable_column=true;			
				$colName.=$columnName;
				$colName .= ($count < $allColumn) ? ", " : "";
				$col .= "?";
				$col .= ($count < $allColumn) ? ", " : "";
				$allRefValue[]=$columnValue;
				$count++;	
			endforeach;
		}
		$columnNameDeclare=($disable_column==false) ? '('.$colName.')' : '';
		$statement = "INSERT INTO {$table} $columnNameDeclare VALUES ({$col}) ";
		if($this->status()){
			try{
				$q = $this->con->prepare($statement);
				$q->execute($allRefValue);
				#$result=$q;
				$result=1;
			}
			catch(Exception $e){
				$this->error=$e->getMessage();	
			}
		}
		$this->last_query = $this->insertPrint($table,$column);
		return $result;
	}
	
	public function insertPrint($table,$column=array()){
		$table = $this->clean($table);
		$allColumn = count($column);
		$col='';
		$count=1;
		$colName='';
		$disable_column = false;
		
		if( $allColumn ){
			foreach($column as $columnName => $columnValue):
				if(is_numeric($columnName))
					$disable_column=true;
				$colName.=$columnName;
				$colName .= ($count < $allColumn) ? ", " : "";
				$col .= $this->append($table,$columnName,$columnValue);
				$col .= ($count < $allColumn) ? ", " : "";
				$count++;	
			endforeach;
		}
		$columnNameDeclare=($disable_column==false) ? '('.$colName.')' : '';
		return "INSERT INTO {$table} $columnNameDeclare VALUES ({$col}) ";
	}		
	
	public function update($table,$column=array(),$reference=array(),$others=""){
		$result='';
		$table = $this->clean($table);
		$allColumn = count($column);
		$count=1;
		$col='';
		$ref='';
		$allReference = count($reference);
		$allRefValue=array();
		if( is_array($column) ){
			foreach($column as $columnName => $columnValue):
				$col .= $columnName;
				$col .= "=";
				$col .= "?";
				$allRefValue[]=$columnValue;
				if($count < $allColumn)
					$col.=",";
				$count++;	
			endforeach;
		}
		$count=1;
		$where = ($allReference >= 1) ? "WHERE" : "";
		
		if( is_array($reference) ){
			foreach($reference as $refName => $refValue):
				$ref .= $refName;
				$ref .= "=";
				$ref .= "?";
				$allRefValue[]=$refValue;
				if($count < $allReference)
					$ref.=" AND ";
				$count++;	
			endforeach;
		}

		$statement = "UPDATE {$table} SET {$col} {$where} {$ref} {$others}";
		if($this->status()){
			try{
				$q = $this->con->prepare($statement);
				$q->execute($allRefValue);
				$result=$q;
			}
			catch(Exception $e){
				$this->error=$e->getMessage();	
			}
		}
		$this->last_query = $this->updatePrint($table,$column,$reference,$others);
		return $q;
	}
	
	public function updatePrint($table,$column=array(),$reference=array(),$others=""){
		$table = $this->clean($table);
		$allColumn = count($column);
		$count=1;
		$col='';
		$ref='';
		$allReference = count($reference);
		if( is_array($column) ){
			foreach($column as $columnName => $columnValue):
				$col .= $columnName;
				$col .= "=";
				$col .= $this->append($table,$columnName,$columnValue);
				if($count < $allColumn)
					$col.=",";
				$count++;	
			endforeach;
		}
		
		$count=1;
		$where = ($allReference >= 1) ? "WHERE" : "";
		
		if( is_array($reference) ){
			foreach($reference as $refName => $refValue):
				$ref .= $refName;
				$ref .= "=";
				$ref .= $this->append($table,$refName,$refValue);
				if($count < $allReference)
					$ref.=" AND ";
				$count++;	
			endforeach;
		}
		
		return "UPDATE {$table} SET {$col} {$where} {$ref} {$others};";
	}	

	public function delete($table,$reference=array(),$others=""){
		$result='';
		$table = $this->clean($table);
		$allReference = count($reference);
		$count=1;
		$ref='';
		$allRefValue=array();
		if( is_array($reference) ){
			foreach($reference as $refName => $refValue):
				$ref .= $refName;
				$ref .= "=";
				$ref .= "?";
				$allRefValue[]=$refValue;
				if($count < $allReference)
					$ref.=" AND ";
				$count++;	
			endforeach;
		}
		
		$where = ($allReference >= 1) ? "WHERE" : "";
		$statement = "DELETE FROM {$table} {$where} {$ref} {$others}";
		if($this->status()){
			try{
				$q = $this->con->prepare($statement);
				$q->execute($allRefValue);
				$result=$q;
			}
			catch(Exception $e){
				$this->error=$e->getMessage();	
			}
		}
		$this->last_query=$this->deletePrint($table,$reference,$others);
		return $result;
	}
	
	public function deletePrint($table,$reference=array(),$others=""){
		$table = $this->clean($table);
		$allReference = count($reference);
		$count=1;
		$ref='';
		if( is_array($reference) ){
			foreach($reference as $refName => $refValue):
				$ref .= $refName;
				$ref .= "=";
				$ref .= $this->append($table,$refName,$refValue);
				if($count < $allReference)
					$ref.=" AND ";
				$count++;	
			endforeach;
		}
		
		$where = ($allReference >= 1) ? "WHERE" : "";
		return "DELETE FROM {$table} {$where} {$ref} {$others} ;";
	}				
}
?>