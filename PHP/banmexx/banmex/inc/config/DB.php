<?php

require_once 'db-settings.php';

class DB {
	private static $_instance = null;
	private $_dbh, $_query, $_error = false, $_results, $_resultsArray, $_count = 0, $_lastId, $_queryCount=0;

	private function __construct(){
		try{
			$this->_dbh = new PDO($GLOBALS['dsn'], $GLOBALS['db_user'], $GLOBALS['db_pass'],
				array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")); 
		} catch(PDOException $e){
			die($e->getMessage());
		}
	}

	public static function getInstance(){
		if (!isset(self::$_instance)) {
			self::$_instance = new DB();
		}
		return self::$_instance;
	}

	public function query($sql, $params = array()){
		$this->_queryCount++;
		$this->_error = false;
		if ($this->_query = $this->_dbh->prepare($sql)) {
			$x = 1;
			if (count($params)) {
				foreach ($params as $param) {
					$this->_query->bindValue($x, $param);
					$x++;
				}
			}

			if ($this->_query->execute()) {
				$this->_results = $this->_query->fetchALL();
				$this->_resultsArray = $this->_query->fetchALL();
				//$this->_resultsArray = json_decode(json_encode($this->_results),true);
				$this->_count = $this->_query->rowCount();
				$this->_lastId = $this->_dbh->lastInsertId();
			} else{
				$this->_error = true;
			}
		}
		return $this;
	}

	public function query2($sql)
	{
		$this->_query = $this->_dbh->prepare($sql);
		return $this->_query->execute();
	}

	public function findAll($table){
		return $this->action('SELECT *',$table);
	}

	public function findById($id,$idValue,$table){
		return $this->action('SELECT *',$table,array($id,'=',$idValue));
	}

	public function action($action, $table, $where = array()){
		$sql = "{$action} FROM {$table}";
		$value = '';
		if (count($where) === 3) {
			$operators = array('=', '>', '<', '>=', '<=');

			$field = $where[0];
			$operator = $where[1];
			$value = $where[2];

			if(in_array($operator, $operators)){
				$sql .= " WHERE {$field} {$operator} ?";
			}
		}
		if (!$this->query($sql, array($value))->error()) {
			return $this;
		}
		return false;
	}

	public function get($table, $where){
		return $this->action('SELECT *', $table, $where);
	}

	public function get2($reg, $table, $where){
		return $this->action("SELECT {$reg}", $table, $where);
	}

	public function delete($table, $where){
		return $this->action('DELETE', $table, $where);
	}

	public function deleteById($id,$idValue,$table){
		return $this->action('DELETE',$table,array($id,'=',$idValue));
	}

	public function insert($table, $fields = array()){
		$keys = array_keys($fields);
		$values = null;
		$x = 1;

		foreach ($fields as $field) {
			$values .= "?";
			if ($x < count($fields)) {
				$values .= ', ';
			}
			$x++;
		}

		$sql = "INSERT INTO {$table} (". implode(',', $keys).") VALUES ({$values})";
		
		if (!$this->query($sql, $fields)->error()) {
			return true;
		}
		return false;
	}

	public function update($table, $id, $fields){
		$set = '';
		$x = 1;

		foreach ($fields as $name => $value) {
			$set .= "{$name} = ?";
			if ($x < count($fields)) {
				$set .= ', ';
			}
			$x++;
		}

		$sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";

		if (!$this->query($sql, $fields)->error()) {
			return true;
		}
		return false;
	}

	public function updateReg($table, $reg, $regValue, $id, $idValue)
	{
		return $this->query("UPDATE ? SET ? = ? WHERE ? = ?", $table, $reg, $regValue, $id, $idValue);
	}

	public function results($assoc = false){
		if($assoc) return $this->_resultsArray;
		return $this->_results;
	}

	public function first(){
		return $this->results()[0];
	}

	public function count(){
		return $this->_count;
	}

	public function error(){
		return $this->_error;
	}

	public function lastId(){
		return $this->_lastId;
	}
	
	public function getQueryCount(){
		return $this->_queryCount;
	}	
	
}
