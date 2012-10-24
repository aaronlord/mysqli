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
		$this->num_rows = 0;
		$this->affected_rows = -1;

		if(is_object($this->connection)){
			$stmt = $this->connection->query($sql);
			# Affected rows has to go here for query :o
			$this->affected_rows = $this->connection->affected_rows;
			$this->stmt = $stmt;
			return $this;
		}
		else {
			throw new Exception;
		}
	}

	public function prepare($sql){
		$this->num_rows = 0;
		$this->affected_rows = -1;

		if(is_object($this->connection)){
			# Ready the stmt
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
					array($this->stmt, 'bind_param'),
					$this->_pass_by_reference($params)
				);
			}

			if($this->stmt->execute()){
				# Affected rows to be run after execute for prepares
				$this->affected_rows = $this->stmt->affected_rows;
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

	public function results($method = 'assoc', $close_stmt = false){
		if(is_object($this->stmt)){
			$stmt_type = get_class($this->stmt);

			# Grab the result prepare() & query()
			switch($stmt_type){
				case 'mysqli_stmt':
					$result = $this->stmt->get_result();
					$close_result = 'close';
					break;

				case 'mysqli_result':
					$result = $this->stmt;
					$close_result = 'free';
					break;

				default:
					throw new Exception;
			}

			$this->num_rows = $result->num_rows;
			
			# Set the results type
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

			$result->$close_result();
			return $results;
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

	public function commit(){
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

	public function rollback(){
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
		return $this->affected_rows;
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