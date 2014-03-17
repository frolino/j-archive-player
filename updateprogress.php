<?php session_start(); ?>
<html>
	<head>
<?php
	/**
	 * TODO: Change these configuration parameters to match your database info
	 */
	$db_name = "jarp_db"; //The name of the database
	$db_username = "root"; //The username
	$db_password = "root"; //The password
	$db_hostname = "localhost"; //The hostname
	
	/**
	 * Connect to the database
	 */
	$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
	
	//Determine the user ID
	$userId = -1;
	$foundUserId = false;
	if (!mysqli_connect_errno() && !empty($_SESSION['Username'])) {
		$user = $_SESSION['Username'];
		$check_user_id = "SELECT * FROM users WHERE Username='".$user."'";
		
		$result = mysqli_query($con, $check_user_id);
		
		if ($row = mysqli_fetch_array($result)) {
			$foundUserId = true;
			$userId = $row['UserID'];
		}
	}
	
	/**
	 * Grab the data and upload to database
	 */
	$NUM_CLUES = 5;
	$NUM_CATEGORIES = 6;
	
	$url = "index.php";
	$allDataAvailable = isset($_POST["score"]) && isset($_POST["gameid"]) && isset($_POST["round"]);
	for ($i = 1; $i <= $NUM_CLUES; $i++) {
		for ($j = 1; $j <= $NUM_CATEGORIES; $j++) {
			$allDataAvailable = ($allDataAvailable && isset($_POST["clue".$i."_".$j]));
		}
	}
	if ($allDataAvailable && $foundUserId) {
		$score = htmlspecialchars($_POST["score"]);
		$gameid = htmlspecialchars($_POST["gameid"]);
		$round = htmlspecialchars($_POST["round"]);
		$clues = array();
		for ($i = 1; $i <= $NUM_CLUES; $i++) {
			array_push($clues, array());
			for ($j = 1; $j <= $NUM_CATEGORIES; $j++) {
				array_push($clues[$i-1], htmlspecialchars($_POST["clue".$i."_".$j]));
			}
		}
		
		//Upload the game stats to the database. No need to check if game exists since user might have played the game
		//multiple times
		$score_upload = "INSERT INTO games_scores (UserID, GameID, Round, Score) VALUES (".$userId.", ".$gameid.", '".$round."', ".$score.")";
		if(!mysqli_query($con, $score_upload)) {
			//TODO: Do something if score upload is not successful (e.g., send data back to index.php to notify)
		}
		
		//Update the clues table (if necessary)
		for ($i = 1; $i <= $NUM_CLUES; $i++) {
			for ($j = 1; $j <= $NUM_CATEGORIES; $j++) {
				$check_clue = "SELECT * FROM clues WHERE GameID=".$gameid." AND Round='".$round."' AND CategoryNumber=".$j." AND ClueNumber=".$i;
				$result = mysqli_query($con, $check_clue);
				if (!($row = mysqli_fetch_array($result))) {
					$insert_clue = "INSERT INTO clues (GameID, Round, CategoryNumber, ClueNumber) VALUES (".$gameid.", '".$round."', ".$j.", ".$i.")";
					if (!mysqli_query($con, $insert_clue)) {
						//TODO: Do something if clue upload is not successful
					}
				}
			}
		}
		
		//Update the clue stats
		for ($i = 1; $i <= $NUM_CLUES; $i++) {
			for ($j = 1; $j <= $NUM_CATEGORIES; $j++) {
				//Get the clue ID
				$get_clue_id = "SELECT * FROM clues WHERE GameID=".$gameid." AND Round='".$round."' AND CategoryNumber=".$j." AND ClueNumber=".$i;
				$result = mysqli_query($con, $get_clue_id);
				if ($row = mysqli_fetch_array($result)) {
					$clueId = $row['ClueID'];
					
					//Determine if the clue ID and user combo already exists in the clue stats table
					$find_clue_stats = "SELECT * FROM clue_scores WHERE ClueID=".$clueId." AND UserID=".$userId;
					$new_result = mysqli_query($con, $find_clue_stats);
					if ($new_row = mysqli_fetch_array($new_result)) {
						//Already exists, so just update the score
						$clue_stat_id = $new_row['ClueScoresID'];
						$update_clue_stat = "";
						if ($clues[$i-1][$j-1] == "right") {
							$update_clue_stat = "UPDATE clue_scores SET NumCorrect=NumCorrect+1 WHERE ClueScoresID=".$clue_stat_id;
						}
						else if ($clues[$i-1][$j-1] == "wrong") {
							$update_clue_stat = "UPDATE clue_scores SET NumWrong=NumWrong+1 WHERE ClueScoresID=".$clue_stat_id;
						}
						else {
							$update_clue_stat = "UPDATE clue_scores SET NumPassed=NumPassed+1 WHERE ClueScoresID=".$clue_stat_id;
						}
						if (!mysqli_query($con, $update_clue_stat)) {
							//TODO: Do something if clue stat update is not successful
						}
					}
					else {
						//Does not exist yet, so insert new row
						//TODO: Insert topic ID if the user decides to assign a topic to a clue during a game
						$insert_new_clue_stat = "";
						if ($clues[$i-1][$j-1] == "right") {
							$insert_new_clue_stat = "INSERT INTO clue_scores (ClueID, UserID, NumCorrect, NumWrong, NumPassed) VALUES (".$clueId.", ".$userId.", 1, 0, 0)";
						}
						else if ($clues[$i-1][$j-1] == "wrong") {
							$insert_new_clue_stat = "INSERT INTO clue_scores (ClueID, UserID, NumCorrect, NumWrong, NumPassed) VALUES (".$clueId.", ".$userId.", 0, 1, 0)";
						}
						else {
							$insert_new_clue_stat = "INSERT INTO clue_scores (ClueID, UserID, NumCorrect, NumWrong, NumPassed) VALUES (".$clueId.", ".$userId.", 0, 0, 1)";
						}
						if (!mysqli_query($con, $insert_new_clue_stat)) {
							//TODO: Do something if clue stat insertion is not successful
						}
					}
				}
			}
		}
		
		mysqli_close($con);
	}
	else {
		echo "<meta http-equiv='refresh' content='2;index.php' />";
		ob_start();
		while (ob_get_status()) {
			ob_end_clean();
		}
		//header("Location: $url");
		die(); //Just in case...
	}
	
?>
	</head>
	<body></body>
</html>