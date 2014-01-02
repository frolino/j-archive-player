<html>
	<head>
		<title>Jeopardy Game Generator</title>

		<style type="text/css">
			#mainWrapper
			{
			width:600px;
			}

			#mainHeader
			{
			text-align:center;
			}

			#headerTitle
			{
			font-weight:bold;
			font-size:20px;
			}

			#mainFrame
			{
			color:white;
			}

			#mainBoard
			{
			//float:left;
			//width:30%;
			//margin-left:50px;
			position:relative;
			border-style:solid;
			border-color:black;
			overflow-x:auto;
			}

			#board
			{
			text-align:center;
			}

			#board td
			{
			text-align:center;
			vertical-align:middle;
			background-color:blue;
			color:white;
			font-weight:bold;
			//width:100px;
			width:16%;
			height:75px;
			}

			.clue
			{
			cursor:pointer;
			}

			#clueBoard
			{
			background-color:blue;
			position:absolute;
			left:0px;
			top:0px;
			height:100%;
			width:100%;
			z-index:1;
			}

			#clueBoardTextContainer
			{
			height:50%;
			padding-top:100px;
			padding-left:10px;
			padding-right:10px;
			}

			#clueBoardText
			{
			text-align:center;
			vertical-align:middle;
			}

			#clueBoardText a:link, #clueBoardText a:visited, #clueBoardText a:active
			{
			color:white;
			}

			#clueBoardText a:hover
			{
			color:black;
			}

			#answerControls
			{
			text-align:center;
			padding-top:50px;
			display:none;
			}

			#nextControl
			{
			text-align:center;
			padding-top:50px;
			display:none;
			}
			
			#adjustUpButton, #adjustDownButton
			{
			display:none;
			}
			
			#adjustUpButton:disabled, #adjustDownButton:disabled
			{
			opacity:0.0;
			}

			#controls
			{
			//float:left;
			//padding-left:100px;
			//padding-right:100px;
			padding-bottom:20px;
			text-align:center;
			color:black;
			}

			#scoreContainer
			{
			background-color:blue;
			color:white;
			font-weight:bold;
			}
		</style>

		<?
			//TODO: Include a statistics panel on the side
			
			include('simplehtmldom_1_5/simple_html_dom.php');
			include('j-utils/clueRetrieval.php');
			
			$NUM_CATEGORIES = 6;
			$NUM_CLUES = 5;

			$defaultGameID = "3752"; //Default game ID
			$defaultRound = "J"; //Jeopardy round

			//Get the game ID if specified
			$gameID = $defaultGameID;
			if (!(!isset($_GET["id"]) || $_GET["id"] === "" || $_GET["id"] === false || $_GET["id"] === null || empty($_GET["id"]))) { //note the short circuit
				$gameID = htmlspecialchars($_GET["id"]);
			}
			
			//Get the round if specified (either "J" for Jeopardy or "DJ" for Double Jeopardy)
			$round = $defaultRound;
			if (!(!isset($_GET["round"]) || $_GET["round"] === "" || $_GET["round"] === false || $_GET["round"] === null || empty($_GET["round"]))) { //note the short circuit
				$round = strtoupper(htmlspecialchars($_GET["round"]));
			}

			$html = file_get_html('http://www.j-archive.com/showgame.php?game_id='.$gameID);

			//Determine if this is the error page (i.e., not in J!Archive database)
			//If so, use default game
			if (isErrorPage($html) || gameUnavailable($html, $round)) {
				$round = $defaultRound;
				$html = file_get_html('http://www.j-archive.com/showgame.php?game_id='.$defaultGameID);
			}

			//Get the answers (i.e., "questions" in Jeopardy terms)
			$answersStr = getAnswersAsHtmlString($html, $round);
			$answers = array();

			//Get the questions (i.e., "answers" in Jeopardy terms) and categories
			$questions = getQuestions($html, $round);
			$categories = getCategories($html, $round);

			$answersHtml = str_get_html($answersStr);

			$indexSoFar = 0;
			foreach($answersHtml->find('em') as $element) {
				//Make sure the questions and answers are aligned
				//Blank question should correspond to blank answer
				while ($indexSoFar < $NUM_CLUES*$NUM_CATEGORIES && $questions[$indexSoFar] == "") {
					array_push($answers, "");
					$indexSoFar++;
				}
				
				//Strip any extraneous tags in the EM's inner text
				$newText = $text = preg_replace('#</?[a-b]*[^>]*>#is', '', $element->innertext);
				//echo stripslashes($newText) . '<br>';

				//TODO: Design smarter way to accept answers
				//Note: A conservative way to accept answers is to simply ensure the typed answer is a substring of the correct answer. You can try to look for other heuristics.
				
				//Strip leading and trailing whitespace and double quote characters
				$newText = trim($newText, " '\"");
				$newText = addcslashes($newText, '"');

				//Store all answers in an array
				//Assumption: Questions and answers listed in same order in HTML code of J-Archive
				array_push($answers, $newText);
				$indexSoFar++;
			}
		?>
		
		<script type="text/javascript" src="js/answerChecker.js"></script>
		<script type="text/javascript" src="js/game.js"></script>

		<script type="text/javascript">
			var clues = new Array();
			var NUM_CATEGORIES = <? echo $NUM_CATEGORIES ?>;
			var NUM_CLUES = <? echo $NUM_CLUES ?>;
			var ROUND = <? echo "'{$round}'" ?>;

			var gameInProgress = false;
			var valueChosen = 0;
			var expectedAnswer = "";
			var score = 0;
			var numCluesRevealed = 0;
			var actualNumberOfClues = 0;

			function setUpClues() {
				for (var i = 0; i < NUM_CLUES; i++) {
					clues[i] = new Array();

					<?
						$qCounter = 0;
						for ($i = 0; $i < $NUM_CLUES; $i++) {
							if ($i != 0) {
								echo "\t\t\t\t\t";
							}
							echo "if (i == ".$i.") {\n";
							for ($j = 0; $j < $NUM_CATEGORIES; $j++) {
								echo "\t\t\t\t\t\tclues[i][$j] = [\"".addcslashes(strtoupper($questions[$qCounter]), '"')."\", \"What is ".$answers[$qCounter]."\"];\n";
								$qCounter++;
							}
							echo "\t\t\t\t\t}\n";
						}
					?>
				}
			}

			function centerPage() {
				var pageWidth = window.innerWidth;
				var mainWrapperWidth = document.getElementById("mainWrapper").offsetWidth;

				if (pageWidth > mainWrapperWidth) {
					var marginWidth = (pageWidth - mainWrapperWidth) / 2;
					document.getElementById("mainWrapper").style.marginLeft = marginWidth + "px";
					document.getElementById("mainWrapper").style.marginRight = marginWidth + "px";
				}
			}
			
			function adjustClueBoardSize() {
				var clueBoard = document.getElementById("clueBoard");
				var widthOfClueBoxes = document.getElementById("board").offsetWidth;
				
				clueBoard.style.width = widthOfClueBoxes + "px";
			}
		</script>
	</head>

	<body onload="centerPage();setUpClues();adjustClueBoardSize();">
		<div id="mainWrapper">
			<div id="controls">
				<button onclick="beginGame();" id="startButton">START</button>
				<h3>SCORE:</h3>
				<div id="scoreContainer">$<span id="score">0</span></div>
			</div>

			<div id="mainFrame">
				<div id="mainBoard">
					<table id="board">
						<tr class="categories">
							<?
								for ($i = 0; $i < $NUM_CATEGORIES; $i++) {
									echo "<td class=\"category\">";
									echo $categories[$i];
									echo "</td>";
								}
							?>
						</tr>

						<tr class="clue1">
							<td class="clue clue1_cat1" onclick="showClue(1,1);">
								$200
							</td>
							<td class="clue clue1_cat2" onclick="showClue(1,2);">
								$200
							</td>
							<td class="clue clue1_cat3" onclick="showClue(1,3);">
								$200
							</td>
							<td class="clue clue1_cat4" onclick="showClue(1,4);">
								$200
							</td>
							<td class="clue clue1_cat5" onclick="showClue(1,5);">
								$200
							</td>
							<td class="clue clue1_cat6" onclick="showClue(1,6);">
								$200
							</td>
						</tr>

						<tr class="clue2">
							<td class="clue clue2_cat1" onclick="showClue(2,1);">
								$400
							</td>
							<td class="clue clue2_cat2" onclick="showClue(2,2);">
								$400
							</td>
							<td class="clue clue2_cat3" onclick="showClue(2,3);">
								$400
							</td>
							<td class="clue clue2_cat4" onclick="showClue(2,4);">
								$400
							</td>
							<td class="clue clue2_cat5" onclick="showClue(2,5);">
								$400
							</td>
							<td class="clue clue2_cat6" onclick="showClue(2,6);">
								$400
							</td>
						</tr>

						<tr class="clue3">
							<td class="clue clue3_cat1" onclick="showClue(3,1);">
								$600
							</td>
							<td class="clue clue3_cat2" onclick="showClue(3,2);">
								$600
							</td>
							<td class="clue clue3_cat3" onclick="showClue(3,3);">
								$600
							</td>
							<td class="clue clue3_cat4" onclick="showClue(3,4);">
								$600
							</td>
							<td class="clue clue3_cat5" onclick="showClue(3,5);">
								$600
							</td>
							<td class="clue clue3_cat6" onclick="showClue(3,6);">
								$600
							</td>
						</tr>

						<tr class="clue4">
							<td class="clue clue4_cat1" onclick="showClue(4,1);">
								$800
							</td>
							<td class="clue clue4_cat2" onclick="showClue(4,2);">
								$800
							</td>
							<td class="clue clue4_cat3" onclick="showClue(4,3);">
								$800
							</td>
							<td class="clue clue4_cat4" onclick="showClue(4,4);">
								$800
							</td>
							<td class="clue clue4_cat5" onclick="showClue(4,5);">
								$800
							</td>
							<td class="clue clue4_cat6" onclick="showClue(4,6);">
								$800
							</td>
						</tr>

						<tr class="clue5">
							<td class="clue clue5_cat1" onclick="showClue(5,1);">
								$1000
							</td>
							<td class="clue clue5_cat2" onclick="showClue(5,2);">
								$1000
							</td>
							<td class="clue clue5_cat3" onclick="showClue(5,3);">
								$1000
							</td>
							<td class="clue clue5_cat4" onclick="showClue(5,4);">
								$1000
							</td>
							<td class="clue clue5_cat5" onclick="showClue(5,5);">
								$1000
							</td>
							<td class="clue clue5_cat6" onclick="showClue(5,6);">
								$1000
							</td>
						</tr>
					</table>

					<div id="clueBoard">
						<div id="clueBoardTextContainer">
							<div id="clueBoardText">
								How to Win:
								<br />
								Get as many points as you can!
								<br /><br />
								Press START to begin...
							</div>
						</div>
				
						<div id="answerControls">
							<span id="questionPhrase">What is</span>
							<input id="response" type="text" />
							<span id="questionMark">?</span>
							<button onclick="checkAnswer();" id="submitAnswerButton">Submit</button>
							<button onclick="pass();" id="passButton">Pass</button>
						</div>

						<div id="nextControl">
							<button onclick="next();" id="nextButton">Next</button><br /><br />
							<button onclick="adjustUp();" id="adjustUpButton">Judge My Answer as Correct</button>
							<button onclick="adjustDown();" id="adjustDownButton">Judge My Answer as Incorrect</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>