# Mysqli Database Class
---
This mysqli class allows you to use the **_mysqli_** extension without wanting to rip your eyes out and break down into tears every thirty seconds. Enjoy.

## SQL

### Prepare
	$Mysqli_Database->prepare(string $query);
Prepare an SQL statement for execution.

_Parameters:_
> `$query` : An SQL query

		
### Query
	$Mysqli_Database->query(string $query);
Performs a query on the database.

_Parameters:_
> `$query` : An SQL query




## Execution
	$Mysql_Database->execute(mixed $var [,mixed $..]);
Escapes the arguments passed in and executes a prepared Query.

_Parameters:_
> `$var` : The value to be bound to the first SQL ?

> `..` : Each subsequent value to be bound



### Start a new transaction
	$Mysqli_Database->start_transaction();
Turns off auto-committing database modifications, starting a new transaction.

### Commit
	$Mysqli_Database->commit();
Commits the current transaction and turns auto-committing database modifications on, ending transactions.

### Rollback
	 $Mysqli_Database->rollback();
Rolls back current transaction and turns auto-committing database modifications on,  ending transactions.



## Results

### Results
	$Mysqli_Database->results(string $method = 'assoc', bool $close_stmt = false);
Fetch all results as an array, the type of array depend on the `$method` passed through.

_Parameters:_
> `$method` : Optional perameter to indicate what type of array to return. 'assoc' is the default and returns an accociative array, 'row' returns a numeric array and 'array' returns an array of both.

> `$close_stmt` : Optional perameter to indicate if the statement should be destroyed after execution.

### Affected Rows
	$Mysqli_Database->affected_rows();
Gets the number of affected rows in a previous MySQL operation.

### Num Rows
	$Mysqli_Database->num_rows();
Return the number of rows in statements result set.
	
### Insert ID
	$Mysqli_Database->insert_id();
Returns the auto generated id used in the last query.
 	
 
 	
## Chaining & Examples
It is possible, and **recommended**, that you chain the methods together.

### Query
Delete all records from the table `foo`, and store how many records were deleted in `$num_deleted`.

_**Chained** (recommnded)_

	$num_deleted = $Mysqli_Database
			->query("DELETE FROM `foo`;")
			->affected_rows();
			
_**Unchained**_

	$Mysqli_Database->query("DELETE FROM `foo`;");
	$num_deleted = $Mysqli_Database->->affected_rows();
			
_**Executes**_

	mysql > DELETE FROM `foo`;
	
### Prepare Query
Retrieve records from the table `foo` and store them as an array in `$results`.

_**Chained** (recommnded)_

	$results = $Mysqli_Database
			->prepare("SELECT `foo` FROM `bar` WHERE `foo` = ? OR `foo` = ?;")
			->execute("Timmy O'Toole", 2)
			->results('array');	
			
_**Unchained**_
	
	$Mysqli_Database->prepare("SELECT `foo` FROM `bar` WHERE `foo` = ? OR `foo` = ?;");
	$Mysqli_Database->execute("Timmy O'Toole", 2);
	$results = $Mysqli_Database->results('array');

_**Executes**_

	mysql > SELECT `foo` FROM `bar` WHERE `foo` = 'Timmy O\'Toole' OR `foo` = 2;