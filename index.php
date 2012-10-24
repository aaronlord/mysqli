<?php
try {
	include 'database/Mysqli_Database.php';

	# New instance
	$database = new Mysqli_Database;

	###################################
	# Prepared Select & Results
	/*
	$page_content = $database
		->prepare("SELECT Body FROM Content WHERE ContentID = ? OR ContentID = ?;")
		->execute(1, 2)
		->results();

	# Num Rows
	if($database->num_rows() > 0){
		echo '<h3>$database->prepare(select_sql)->excecute(params,here)->results();<br/>'
			.'$database->num_rows();</h3>'
			.'<pre>'.print_r($page_content, 1).'</pre>'
			.'<hr>';
	}
	*/

	$database->prepare("SELECT Body FROM Content WHERE ContentID = ? OR ContentID = ?;");
	$database->execute(1, 2);

	$page_content = $database->results();

	# Num Rows
	if($database->num_rows() > 0){
		echo '<h3>$database->prepare(select_sql)->excecute(params,here)->results();<br/>'
			.'$database->num_rows();</h3>'
			.'<pre>'.print_r($page_content, 1).'</pre>'
			.'<hr>';
	}


	###################################
	# Inserts & Affected Rows
	$rows_in = $database
		->prepare("INSERT INTO Content(PageID, StyleID, Body) VALUES (?, ?, ?), (?, ?, ?);")
		->execute(2, 1, 'Whaddup', 2, 2, 'Ribs')
		->affected_rows();

	# Insert ID
	if($rows_in > 0){
		echo '<h3>$database->prepare(insert_sql)->excecute(params,here)->affected_rows();</h3>'
			.$rows_in.' row'.(count($rows_in) > 0 ? 's' : '')
			.'<hr>'

			.'<h3>$database->insert_id()</h3>'
			.$database->insert_id()
			.'<hr>';
	}

	###################################
	# Query Delete & Affected Rows & Transaction
	# ~ Side Note: This query hould really use prepare, but testing query method)
	$database->start_transaction();

	$rows_out = $database
		->query("DELETE FROM Content WHERE PageID = 2;")
		->affected_rows();
	
	if($rows_out > 0){
		echo '<h3>$database->query(delete_sql)->affected_rows();</h3>'
			.$rows_out.' row'.(count($rows_out) > 0 ? 's' : '')
			.'<hr>';
	}	

	$database->rollback();

	###################################
	# Query Select
	$all_content = $database
		->query("SELECT Body FROM Content;")
		->results();

	if($database->num_rows() > 0){
		echo '<h3>$database->query(select_sql)->results();<br/>'
			.'$database->num_rows();</h3>'
			.'<pre>'.print_r($all_content, 1).'</pre>'
			.'<hr>';
	}

	###################################
	# Transactions
	$database->start_transaction();
	
	$rows_out = $database
		->query("DELETE FROM Content WHERE PageID = 2;")
		->affected_rows();
	
	if($rows_out > 0){
		echo '<h3>$database->query(delete_sql)->affected_rows();</h3>'
			.$rows_out.' row'.(count($rows_out) > 0 ? 's' : '')
			.'<hr>';
	}	

	$database->commit();

	###################################
	# Multiquery
}
catch(Exception $e){
	echo $e->getMessage();
}