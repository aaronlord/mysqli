<?php
class Mysqli_Database {
	# Connect to database
	public function __construct(){
		$this->connection = $this->pconnect('localhost', 'root', 'root', 'test');
	}

	private function pconnect($host, $user, $pass, $db){
		$mysqli = new mysqli('p:'.$host, $user, $pass, $db);

		if($mysqli->connect_error)
			throw new Exception('Connection Error: '.$mysqli->connect_error);

		return $mysqli;
	}

	public function query($sql){
		if(is_object($this->connection)){
			# Ready the statement
			$this->stmt = $this->connection->prepare($sql);
			return $this;
		}
		else {
			throw new Exception;
		}
	}

	public function multi_query(){ }

	public function execute(){
		if(is_object($this->connection) && is_object($this->stmt)){
			# Ready the params
			$args = func_get_args();
			$types = array();
			$params = array();

			foreach($args as $arg){
				$types[] = is_int($arg) ? 'i' : (is_float($arg) ? 'd' : 's');
				$params[] = $this->connection->real_escape_string($arg);
			}

			# Stick the types at the start of the params
			array_unshift($params, implode($types));
			
			# Call bind_param (avoiding the pass_by_reference crap)
			call_user_func_array(
				array($this->stmt, 'bind_param'),
				$this->_pass_by_reference($params)
			);

			echo '<pre>'.print_r($this->stmt, 1).'</pre>';
		}
		else {
			throw new Exception;
		}
	}

	public function results(){ }

	public function num_rows(){ }

	public function affected_rows(){ }

	public function last_id(){ }

	/**
	 * Fix call_user_func_array & bind_param pass by reference crap.
	 */
	private function _pass_by_reference(&$arr){ 
		$refs = array(); 
		foreach($arr as $key => $value){
			$refs[$key] = &$arr[$key]; 
		}
		return $refs; 
	}
}


try {
	$database = new Mysqli_Database;
	$database->query("INSERT INTO test_table(name) VALUES (?), (?), (?);")->execute(3, 'Aaron', 5.5);
}
catch(Exception $e){
	echo $e->getMessage();
}