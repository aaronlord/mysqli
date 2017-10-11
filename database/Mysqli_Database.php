<?php
class Mysqli_Database {
	/**
	 * Set up the database connection
	 */
	public function __construct($host, $user, $pass, $db, $persistant = true){
		$this->connection = $this->connect($host, $user, $pass, $db, $persistant);
	}

	/**
	 * Connect to the database, with or without a persistant connection
	 * @param  String  $host       Mysql server hostname
	 * @param  String  $user       Mysql username
	 * @param  String  $pass       Mysql password
	 * @param  String  $db         Database to use
	 * @param  boolean $persistant Create a persistant connection
	 * @return Object              Mysqli
	 */
	private function connect($host, $user, $pass, $db, $persistant){
		$host = $persistant === true ? 'p:'.$host : $host;

		$mysqli = new mysqli($host, $user, $pass, $db);

		if($mysqli->connect_error)
			throw new Exception('Connection Error: '.$mysqli->connect_error);

		return $mysqli;
	}

	/**
	 * Execute an SQL statement for execution.
	 * @param  String $sql An SQL query
	 * @return Object      $this
	 */
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

	/**
	 * Prepare an SQL statement
	 * @param  String $sql An SQL query
	 * @return Object      $this
	 */
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

	/**
	 * Escapes the arguments passed in and executes a prepared Query.
	 * @param Mixed $var   The value to be bound to the first SQL ?
	 * @param Mixed $...   Each subsequent value to be bound to ?
	 * @return Object      $this
	 */
	public function execute(){
		if(is_object($this->connection) && is_object($this->stmt)){
			# Ready the params
			if(count($args = func_get_args()) > 0){
				if (isset($types)) {unset($types);}
				if (isset($params)) {unset($params);}
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

	/**
	 * Fetch all results as an array, the type of array depend on the $method passed through.
	 * @param  string  $method     Optional perameter to indicate what type of array to return.'assoc' is the default and returns an accociative array, 'row' returns a numeric array and 'array' returns an array of both.
	 * @param  boolean $close_stmt Optional perameter to indicate if the statement should be destroyed after execution.
	 * @return Array              Array of database results
	 */
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
					return 'fetch_row';
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

	/**
	 * Turns off auto-committing database modifications, starting a new transaction.
	 * @return bool Dependant on the how successful the autocommit() call was
	 */
	public function start_transaction(){
		if(is_object($this->connection)){
			return $this->connection->autocommit(false);
		}
	}

	/**
	 * Commits the current transaction and turns auto-committing database modifications on, ending transactions.
	 * @return bool Dependant on the how successful the autocommit() call was
	 */
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

	/**
	 * Rolls back current transaction and turns auto-committing database modifications on, ending transactions.
	 * @return bool Dependant on the how successful the autocommit() call was
	 */
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

	/**
	 * Return the number of rows in statements result set.
	 * @return integer The number of rows
	 */
	public function num_rows(){
		return $this->num_rows;
	}

	/**
	 * Gets the number of affected rows in a previous MySQL operation.
	 * @return integer The affected rows
	 */
	public function affected_rows(){
		return $this->affected_rows;
	}

	/**
	 * Returns the auto generated id used in the last query.
	 * @return integer The last auto generated id
	 */
	public function insert_id(){
		if(is_object($this->connection)){
			return $this->connection->insert_id;
		}
	}

	/**
	 * Fixes the call_user_func_array & bind_param pass by reference crap.
	 * @param  array $arr The array to be referenced
	 * @return array      A referenced array
	 */
	private function _pass_by_reference(&$arr){
		$refs = array();
		foreach($arr as $key => $value){
			$refs[$key] = &$arr[$key];
		}
		return $refs;
	}
}
