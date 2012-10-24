<?php
class Mysqli_Database {
	# Connect to database
	public function __construct(){
		$this->connection = $this->connect('localhost', 'root', '', 'polly', true);
	}

	private function connect($host, $user, $pass, $db, $persistant = true){
		$host = $persistant === true ? 'p:'.$host : $host;

		$mysqli = new mysqli($host, $user, $pass, $db);

		if($mysqli->connect_error)
			throw new Exception('Connection Error: '.$mysqli->connect_error);

		return $mysqli;
	}

	public function query($sql){
		if(is_object($this->connection)){
			# Ready the statement
			$this->statement = $this->connection->prepare($sql);
			return $this;
		}
		else {
			throw new Exception;
		}
	}

	public function multi_query(){ }

	public function execute(){
		if(is_object($this->connection) && is_object($this->statement)){
			# Ready the params
			if(count($args = func_get_args()) > 0){
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
					array($this->statement, 'bind_param'),
					$this->_pass_by_reference($params)
				);
			}

			if($this->statement->execute()){
				return $this;
			}
			else {
				throw new Exception;
			}
		}
		else {
			throw new Exception;
		}
	}

	public function results($method = 'assoc', $close_statement = true){
		if(is_object($this->statement)){
			if($result = $this->statement->get_result()){

				$this->num_rows = $result->num_rows;
				
				if($close_statement === true){
					$this->statement->close();
				}

				switch($method) {
					case 'assoc':
						$method = 'fetch_assoc';
						break;

					case 'row':
						return $result->fetch_row();
						break;

					default:
						$method = 'fetch_array';
						break;
				}

				$results = array();
				while($row = $result->$method()){
					$results[] = $row;
				}

				return $results;
			}
			else {
				throw new Exception;
			}
		}
		else {
			throw new Exception;
		}
	}

	public function start_transaction(){
		if(is_object($this->connection)){
			return $this->connection->autocommit(false);
		}
	}

	public function commit_transaction(){
		if(is_object($this->connection)){
			# Commit!
			if($this->connection->commit()){
				return $this->connection->autocommit(true);
			}
			else {
				$this->connection->autocommit(true);
				throw new Exception;
			}
		}
	}

	public function rollback_transaction(){
		if(is_object($this->connection)){
			# Commit!
			if($this->connection->rollback()){
				return $this->connection->autocommit(true);
			}
			else {
				$this->connection->autocommit(true);
				throw new Exception;
			}
		}
	}

	public function num_rows(){
		return $this->num_rows;
	}

	public function affected_rows(){
		if(is_object($this->connection)){
			return $this->connection->affected_rows;
		}
	}

	public function insert_id(){
		if(is_object($this->connection)){
			return $this->connection->insert_id;
		}
	}

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