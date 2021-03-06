function beginGame() {
	if (!gameInProgress) {
		generateClues();
		document.getElementById('clueBoard').style.display = 'none';
		document.getElementById('board').style.display = 'block';
		document.getElementById("score").innerHTML = '0';
		document.getElementById("scoreContainer").style.backgroundColor = "blue";
		score = 0;
		numCluesRevealed = 0;
		gameInProgress = true;
		document.getElementById('startButton').style.display = 'none';
		document.getElementById('response').onkeypress = enterPressed;
	}
}

function showClue(clue, cat) {
	//Set dollar value
	var multiplier = ((ROUND == "DJ") ? 400:200);
	valueChosen = multiplier*clue;

	//Retrieve the clue
	var clueText = clues[clue-1][cat-1][0];

	//Retrieve expected answer
	expectedAnswer = clues[clue-1][cat-1][1];
	var qPhrase = "What is";
	//if (expectedAnswer.startsWith("Who is")) {
	if (expectedAnswer.indexOf("Who is") == 0) {
		qPhrase = "Who is";
	}
	//else if (expectedAnswer.startsWith("Who are")) {
	else if (expectedAnswer.indexOf("Who are") == 0) {
		qPhrase = "Who are";
	}
	//else if (expectedAnswer.startsWith("What are")) {
	else if (expectedAnswer.indexOf("What are") == 0) {
		qPhrase = "What are";
	}
	document.getElementById('questionPhrase').innerHTML = qPhrase;

	//Show the clue box
	var clueBoardText = document.getElementById('clueBoardText');
	clueBoardText.innerHTML = clueText;
	clueBoardText.style.display = 'block';
	document.getElementById('clueBoard').style.display = 'block';
	document.getElementById('answerControls').style.display = 'block';
	document.getElementById('response').value = "";
	
	//Check if there are any anchor tags in the clue text
	var anchorTags = clueBoardText.getElementsByTagName("a");
	if (anchorTags.length > 0) {
		for (var i = 0; i < anchorTags.length; i++) {
			var nextAnchor = anchorTags[i];
			if (nextAnchor.hasAttribute("href")) {
				var modifiedClueText = nextAnchor.getAttribute("href").replace(/MEDIA/, "media").replace(/\.WMV/, ".wmv").replace(/\.JPG/, ".jpg").replace(/\.MP3/, ".mp3");
				modifiedClueText = modifiedClueText.replace(/A\./, "a.");
				modifiedClueText = modifiedClueText.replace(/HTTP/, "http");
				modifiedClueText = modifiedClueText.replace(/J-ARCHIVE/, "j-archive");
				nextAnchor.setAttribute("href", modifiedClueText);
				nextAnchor.setAttribute("target", "_blank");
			}
		}
	}

	//Gray out the clue
	var box = document.getElementsByClassName('clue' + clue + "_cat" + cat);
	if (box.length > 0) {
		box[0].style.backgroundColor = "gray";
		box[0].setAttribute("onclick", "");
	}

	numCluesRevealed++;
	
	document.getElementById('response').focus();
	
	currentClue = clue;
	currentCat = cat;
}

function setDollarValues() {
	var clue_values = ["$200", "$400", "$600", "$800", "$1000"];
	if (ROUND == "DJ") {
		clue_values = ["$400", "$800", "$1200", "$1600", "$2000"];
	}

	for (var i = 1; i <= NUM_CLUES; i++) {
		var clues = document.querySelectorAll(".clue" + i + " .clue")
		for (var j = 0; j < clues.length; j++) {
			clues[j].innerHTML = clue_values[i-1];
		}
	}
}

function checkAnswer() {
	//TODO: Implement a smarter way to check the answers
	document.getElementById('answerControls').style.display = 'none';
	document.getElementById('nextControl').style.display = 'block';

	//Retrieve user's answer and compare with expectedAnswer
	var qPhrase = document.getElementById('questionPhrase').innerHTML;
	var usersAnswer = qPhrase + " " + document.getElementById('response').value.trim();
	var usersAnswerNoQ = document.getElementById('response').value.trim();
	var clueBoardText = document.getElementById('clueBoardText');
	var expectedAnswerNoQ = expectedAnswer.substr(qPhrase.length+1);

	if (answerIsCorrect(usersAnswerNoQ, expectedAnswerNoQ)) {
		clueBoardText.innerHTML = "\"" + usersAnswer+"?\"<br \>is correct!" + "<br \><br \>(Answer posted in J! Archive: " + expectedAnswerNoQ + ")";
		score += valueChosen;
		document.getElementById("score").innerHTML = score;
		document.getElementById('adjustDownButton').style.display = 'inline';
		document.getElementById('adjustDownButton').disabled = false;
		
		clues[currentClue-1][currentCat-1][2] = "right"; //Answer for this clue is correct, so indicate this in the array so that it can be recorded after the game
	}
	else {
		clueBoardText.innerHTML = "Wrong! The correct answer<br \>is \"" + expectedAnswer + "?\"";
		score -= valueChosen;
		document.getElementById("score").innerHTML = score;
		document.getElementById('adjustUpButton').style.display = 'inline';
		document.getElementById('adjustUpButton').disabled = false;
		
		clues[currentClue-1][currentCat-1][2] = "wrong"; //Answer for this clue is incorrect, so indicate this in the array so that it can be recorded after the game
	}

	updateScoreboard();
}

function enterPressed(evt) {
    if (!evt) {
    	evt = window.event;
    }
    var keyCode = evt.keyCode || evt.which;
    if (keyCode == '13'){
    	// Enter pressed
    	checkAnswer();
    }	
}

function pass() {
	document.getElementById('answerControls').style.display = 'none';
	document.getElementById('nextControl').style.display = 'block';
	
	clueBoardText.innerHTML = "The correct answer<br \>is \"" + expectedAnswer + "?\"";
	
	clues[currentClue-1][currentCat-1][2] = "passed"
}

function next() {
	document.getElementById('nextControl').style.display = "none";
	document.getElementById('clueBoard').style.display = "none";
	
	document.getElementById('adjustUpButton').style.display = 'none';
	document.getElementById('adjustDownButton').style.display = 'none';

	if (numCluesRevealed == actualNumberOfClues) {
		endGame();
	}
}

function adjustUp() {
	document.getElementById('adjustUpButton').disabled = true;
	
	//Undo the reduction, then award the points
	score += 2*valueChosen;
	updateScoreboard();
	
	clues[currentClue-1][currentCat-1][2] = "right";
}

function adjustDown() {
	document.getElementById('adjustDownButton').disabled = true;
	
	//Undo the increase, then reduce the points
	score -= 2*valueChosen;
	updateScoreboard();
	
	clues[currentClue-1][currentCat-1][2] = "wrong";
}

function updateScoreboard() {
	document.getElementById("score").innerHTML = score;
	if (score < 0) {
		document.getElementById("scoreContainer").style.backgroundColor = "red";
	}
	else {
		document.getElementById("scoreContainer").style.backgroundColor = "blue";
	}
}

function endGame() {
	//Show the results
	document.getElementById("clueBoard").style.display = "block";
	var boardText = document.getElementById("clueBoardText");
	if (score >= 0) {
		boardText.innerHTML = "Congratulations! You finished the game with $" + score + ".";
	}
	else {
		boardText.innerHTML = "You finished the game with $" + score + ". Try harder next time.";
	}
	
	//Send the game data to the server to update the user's progress
	updateProgress();

	gameInProgress = false;
	document.getElementById('startButton').innerHTML = 'PLAY AGAIN';
	document.getElementById('startButton').style.display = 'inline';
}

function generateClues() {
	actualNumberOfClues = 0;
	setDollarValues();
	
	var i = 0;
	//Change color to blue
	var clueBoxes = document.getElementsByClassName("clue");
	for (i = 0; i < clueBoxes.length; i++) {
		clueBoxes[i].style.backgroundColor = "blue";
	}

	//Change onclick
	for (i = 0; i < NUM_CLUES; i++) {
		for (var j = 0; j < NUM_CATEGORIES; j++) {
			clueNum = i + 1;
			catNum = j + 1;
			if (clues[i][j][0] == "") {
				document.getElementsByClassName("clue" + clueNum + "_cat" + catNum)[0].setAttribute("onclick", "");
				document.getElementsByClassName("clue" + clueNum + "_cat" + catNum)[0].style.backgroundColor = "gray";
			}
			else {
				document.getElementsByClassName("clue" + clueNum + "_cat" + catNum)[0].setAttribute("onclick", "showClue(" + clueNum + "," + catNum + ");");
				actualNumberOfClues++;
			}
		}
	}
}

/**
 * Send the game data to the server
 * 
 * Source: http://stackoverflow.com/questions/9713058/sending-post-data-with-a-xmlhttprequest
 */
function updateProgress() {
	var http = new XMLHttpRequest();
	var url = "updateprogress.php";
	var params = "score=" + score + "&gameid=" + document.getElementById("game-id").innerHTML;
	if (document.getElementById("game-round").innerHTML == "Double Jeopardy") {
		params += "&round=DJ";
	}
	else {
		params += "&round=J";
	}
	for (var i = 1; i <= NUM_CLUES; i++) {
		for (var j = 1; j <= NUM_CATEGORIES; j++) {
			if (clues[i-1][j-1][0] != "") {
				params += "&clue" + i + "_" + j + "=" + clues[i-1][j-1][2];
			}
			else {
				params += "&clue" + i + "_" + j + "=null";
			}
		}
	}
	http.open("POST", url, true);
	
	//Send the proper header information along with the request
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", params.length);
	http.setRequestHeader("Connection", "close");
	
	http.onreadystatechange = function() {//Call a function when the state changes
		//TODO: Alert user when game progress has been updated/recorded
	}
	
	http.send(params);
}
