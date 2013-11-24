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
				var modifiedClueText = nextAnchor.getAttribute("href").replace(/MEDIA/, "media").replace(/\.WMV/, ".wmv").replace(/\.JPG/, ".jpg");
				nextAnchor.setAttribute("href", modifiedClueText);
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
	}
	else {
		clueBoardText.innerHTML = "Wrong! The correct answer<br \>is \"" + expectedAnswer + "?\"";
		score -= valueChosen;
		document.getElementById("score").innerHTML = score;
		document.getElementById('adjustUpButton').style.display = 'inline';
		document.getElementById('adjustUpButton').disabled = false;
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
}

function next() {
	document.getElementById('nextControl').style.display = "none";
	document.getElementById('clueBoard').style.display = "none";
	
	document.getElementById('adjustUpButton').style.display = 'none';
	document.getElementById('adjustDownButton').style.display = 'none';

	if (numCluesRevealed == NUM_CATEGORIES*NUM_CLUES) {
		endGame();
	}
}

function adjustUp() {
	document.getElementById('adjustUpButton').disabled = true;
	
	//Undo the reduction, then award the points
	score += 2*valueChosen;
	updateScoreboard();
}

function adjustDown() {
	document.getElementById('adjustDownButton').disabled = true;
	
	//Undo the increase, then reduce the points
	score -= 2*valueChosen;
	updateScoreboard();
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

	gameInProgress = false;
	document.getElementById('startButton').innerHTML = 'PLAY AGAIN';
	document.getElementById('startButton').style.display = 'inline';
}

function generateClues() {
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
			}
		}
	}
}