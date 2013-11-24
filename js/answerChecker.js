var Level = {
	LENIENT: 0,
	STRICT: 1,
	STRICTEST: 2
};

var gameLevel = Level.STRICT; //Default

/**
 * Check if the user's answer is correct, based on
 * the difficulty level
 * @param {String} usersAnswer
 * @param {String} expectedAnswer
 */
function answerIsCorrect(usersAnswer, expectedAnswer) {
	if (gameLevel == Level.LENIENT) {
		return lenientCheckPasses(usersAnswer, expectedAnswer);
	}
	else if (gameLevel == Level.STRICT) {
		return strictCheckPasses(usersAnswer, expectedAnswer);
	}
	else if (gameLevel == Level.STRICTEST) {
		return strictestCheckPasses(usersAnswer, expectedAnswer);
	}
	else {
		//Go with default just in case (but this shouldn't even execute)
		return strictestCheckPasses(usersAnswer, expectedAnswer);
	}
}

/**
 * Check if the user's answer is contained in the expected answer
 * string, or vice versa, disregarding case
 * @param {String} usersAnswer
 * @param {String} expectedAnswer
 */
function lenientCheckPasses(usersAnswer, expectedAnswer) {
	return ((usersAnswer.toLowerCase().trim().indexOf(expectedAnswer.toLowerCase().trim()) >= 0)
		|| (expectedAnswer.toLowerCase().trim().indexOf(usersAnswer.toLowerCase().trim()) >= 0));
}

/**
 * Check if the user's answer matches the expected answer according to
 * some regular expression
 * @param {String} usersAnswer
 * @param {String} expectedAnswer
 */
function strictCheckPasses(usersAnswer, expectedAnswer) {
	var usersAnswerNoArticles = usersAnswer;
	var expectedAnswerNoArticles = expectedAnswer;
	if (usersAnswer.toLowerCase().indexOf("the ") == 0 ||
		usersAnswer.toLowerCase().indexOf("a") == 0 ||
		usersAnswer.toLowerCase().indexOf("an") == 0) {
		usersAnswerNoArticles = usersAnswer.toLowerCase().replace(/(the|a|an) /, ""); //only first match gets changed
	}
	if (expectedAnswer.toLowerCase().indexOf("the ") == 0 ||
		expectedAnswer.toLowerCase().indexOf("a") == 0 ||
		expectedAnswer.toLowerCase().indexOf("an") == 0) {
		expectedAnswerNoArticles = expectedAnswer.toLowerCase().replace(/(the|a|an) /, ""); //only first match gets changed
	}
	
	var usersAnswerNoSpace = usersAnswerNoArticles.trim();
	var expectedAnswerNoSpace = expectedAnswerNoArticles.trim();
	
	//How many "("'s and ")"'s are there
	var numLeftParentheses = (expectedAnswerNoSpace.match(/\(/g)||[]).length;
	var numRightParentheses = (expectedAnswerNoSpace.match(/\)/g)||[]).length;
	
	//Convert the expected answer into a regex
	var noException = true;
	var isMatching = false;
	try {
		var expectedRegExStr = expectedAnswerNoSpace.toLowerCase();
		if (expectedRegExStr.indexOf("(") == 0 && expectedRegExStr.lastIndexOf(")") == expectedRegExStr.length-1) {
			expectedRegExStr = "(|" + expectedRegExStr.substring(1);
			expectedRegExStr = expectedRegExStr.replace(new RegExp("\\)\\s", "g"), "\\s+)");
			expectedRegExStr = expectedRegExStr.replace(new RegExp("\\s\\(", "g"), "(\\s+|\\s+");
			
			var lastLeftParamIdx = expectedRegExStr.lastIndexOf("(\\s+|\\s+");
			if (lastLeftParamIdx > 0 && (lastLeftParamIdx + 8) < expectedRegExStr.length) {
				//The string "(\\s+|\\s+" contains 8 characters
				expectedRegExStr = expectedRegExStr.substring(0, lastLeftParamIdx) + "(|\\s+" + expectedRegExStr.substring(lastLeftParamIdx + 8);
			}
		}
		else if (expectedRegExStr.indexOf("(") == 0) {
			expectedRegExStr = "(|" + expectedRegExStr.substring(1);
			expectedRegExStr = expectedRegExStr.replace(new RegExp("\\)\\s", "g"), "\\s+)");
			expectedRegExStr = expectedRegExStr.replace(new RegExp("\\s\\(", "g"), "(\\s+|\\s+");
		}
		else if (expectedRegExStr.lastIndexOf(")") == expectedRegExStr.length-1) {
			expectedRegExStr = expectedRegExStr.replace(new RegExp("\\)\\s", "g"), "\\s+)");
			expectedRegExStr = expectedRegExStr.replace(new RegExp("\\s\\(", "g"), "(\\s+|\\s+");
			
			var lastLeftParamIdx = expectedRegExStr.lastIndexOf("(\\s+|\\s+");
			if (lastLeftParamIdx > 0 && (lastLeftParamIdx + 8) < expectedRegExStr.length) {
				//The string "(\\s+|\\s+" contains 8 characters
				expectedRegExStr = expectedRegExStr.substring(0, lastLeftParamIdx) + "(|\\s+" + expectedRegExStr.substring(lastLeftParamIdx + 8);
			}
		}
		else {
			expectedRegExStr = expectedRegExStr.replace(new RegExp("\\)\\s", "g"), "\\s+)");
			expectedRegExStr = expectedRegExStr.replace(new RegExp("\\s\\(", "g"), "(\\s+|\\s+");
		}
		
		expectedRegExStr = "^" + expectedRegExStr + "$";
		
		expectedRegEx = new RegExp(expectedRegExStr);
		isMatching = expectedRegEx.test(usersAnswerNoSpace.toLowerCase());
	}
	catch(err) {
		noException = false;
	}
	
	if (numLeftParentheses == numRightParentheses && noException && isMatching) {
		return true;
	}
	else {
		return strictestCheckPasses(usersAnswer, expectedAnswer);
	}
}

/**
 * Check if the user's answer exactly matches the expected answer string,
 * disregarding case
 * @param {String} usersAnswer
 * @param {String} expectedAnswer
 */
function strictestCheckPasses(usersAnswer, expectedAnswer) {
	return (usersAnswer.toLowerCase().trim() == expectedAnswer.toLowerCase().trim());
}

function oldCheck(usersAnswer, expectedAnswerNoQ) {
	return (usersAnswer.toLowerCase().indexOf(expectedAnswerNoQ.toLowerCase()) >= 0);
}
