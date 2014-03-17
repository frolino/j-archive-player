<html>
	<head>
		<title>Jeopardy Game Generator - Installation</title>
	</head>
	<body>
<?php
	/**
	 * TODO: Change these configuration parameters to match your database info
	 */
	$db_name = "jarp_db"; //The name of the database
	$db_username = "root"; //The username
	$db_password = "root"; //The password
	$db_hostname = "localhost"; //The hostname
	
	/**
	 * Connect to the database and create the tables
	 */
	 
	$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
	
	if (mysqli_connect_errno()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br />";
	}
	
	$users_table = "CREATE TABLE IF NOT EXISTS `users` (
	`UserID` INT(25) NOT NULL AUTO_INCREMENT PRIMARY KEY ,  
	`Username` VARCHAR(65) NOT NULL ,  
	`Password` VARCHAR(32) NOT NULL ,  
	`EmailAddress` VARCHAR(255) NOT NULL 
	)";
	
	$clues_table = "CREATE TABLE IF NOT EXISTS `clues` (
	`ClueID` INT(25) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`GameID` INT(25) NOT NULL ,
	`Round` VARCHAR(2) NOT NULL ,
	`CategoryNumber` INT(25) NOT NULL ,
	`ClueNumber` INT(25) NOT NULL
	)";
	
	$clue_scores_table = "CREATE TABLE IF NOT EXISTS `clue_scores` (
	`ClueScoresID` INT(25) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`ClueID` INT(25) NOT NULL ,
	`UserID` INT(25) NOT NULL ,
	`NumCorrect` INT(25) NOT NULL ,
	`NumWrong` INT(25) NOT NULL ,
	`NumPassed` INT(25) NOT NULL ,
	`TopicID` INT(25)
	)";
	
	$games_scores_table = "CREATE TABLE IF NOT EXISTS `games_scores` (
	`GamesScoresID` INT(25) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`UserID` INT(25) NOT NULL ,
	`GameID` INT(25) NOT NULL ,
	`Round` VARCHAR(2) NOT NULL ,
	`Score` INT(25) NOT NULL
	)";
	
	$topics_table = "CREATE TABLE IF NOT EXISTS `topics` (
	`TopicID` INT(25) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`Topic` VARCHAR(255) NOT NULL ,
	`UserID` INT(25) NOT NULL
	)";
	
	if (mysqli_query($con, $users_table)) {
		echo "Table 'users' created successfully!<br />";
	}
	else {
		echo "Error creating table 'users': " . mysqli_error($con) . "<br />";
	}
	
	if (mysqli_query($con, $clues_table)) {
		echo "Table 'clues' created successfully!<br />";
	}
	else {
		echo "Error creating table 'clues': " . mysqli_error($con) . "<br />";
	}
	
	if (mysqli_query($con, $clue_scores_table)) {
		echo "Table 'clue_scores' created successfully!<br />";
	}
	else {
		echo "Error creating table 'clue_scores': " . mysqli_error($con) . "<br />";
	}
	
	if (mysqli_query($con, $games_scores_table)) {
		echo "Table 'games_scores' created successfully!<br />";
	}
	else {
		echo "Error creating table 'games_scores': " . mysqli_error($con) . "<br />";
	}
	
	if (mysqli_query($con, $topics_table)) {
		echo "Table 'topics' created successfully!<br />";
	}
	else {
		echo "Error creating table 'topics': " . mysqli_error($con) . "<br />";
	}
	
	mysqli_close($con);
?>
	</body>
</html>