<?php
try {
	include 'database/Mysqli_Database.php';

	# New instance
	$database = new Mysqli_Database;

	# Selects
	$page_content = $database
						->query("SELECT Body FROM Content WHERE ContentID = ? OR ContentID = ?;")
						->execute(1, 2)
						->results();
	# Num Rows
	if($database->num_rows()){
		echo 'DB Results: <pre>'.print_r($page_content, 1).'</pre>';
	}

	# Inserts
	$rows_in = $database
					->query("INSERT INTO Content(PageID, StyleID, Body) VALUES (?, ?, ?), (?, ?, ?);")
					->execute(2, 1, 'Whaddup', 2, 2, 'Ribs')
					->affected_rows();
	# Insert ID
	if($rows_in > 0){
		echo 'Inserted: '.$rows_in.' row'.(count($rows_in) > 0 ? 's' : '').'<br/>';
		echo 'Insert ID: '.$database->insert_id().'<br/>';
	}

	# Transactions
	
	# Multiquery
}
catch(Exception $e){
	echo $e->getMessage();
}