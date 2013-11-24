<?php
	/**
	 * Determine if the the HTML page retrieved is an error page
	 * @param html The HTML document
	 * @return true if html is an error page; false otherwise
	 */
	function isErrorPage($html) {
		foreach($html->find('p') as $element) {
			if ($element->class == "error") {
				return true;
			}
		}
		return false;
	}
	
	function gameUnavailable($html, $round) {
		foreach($html->find('div') as $element) {
			//We're checking $round != "DJ" here so that the first Jeopardy round remains the default
			if (($element->id == "jeopardy_round" && $round != "DJ") || ($element->id == "double_jeopardy_round" && $round == "DJ")) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Get the questions (i.e., "answers") from the HTML page
	 * @param html The HTML document
	 * @param round The Jeopardy round
	 * @param num_clues (optional) The total number of clues
	 * @param num_categories (optional) The total number of categories
	 * @return the array of questions
	 */
	function getQuestions($html, $round, $num_clues=5, $num_categories=6) {
		$questions = array();
		for ($i = 1; $i <= $num_clues; $i++) {
			for ($j = 1; $j <= $num_categories; $j++) {
				$element = $html->getElementById("clue_{$round}_{$j}_{$i}");
				if ($element == NULL) {
					array_push($questions, "");
				}
				else {
					array_push($questions, $element->innertext);
				}
			}
		}
		return $questions;
	}
	
	/**
	 * Get the answers (i.e., "questions") as an HTML string, coming from the
	 * onmouseover handler in the original HTML page
	 * @param html The original HTML page
	 * @param round The Jeopardy round
	 * @return An HTML string containing the answers within EM elements
	 */
	function getAnswersAsHtmlString($html, $round) {
		$x = '';
		foreach($html->find('div') as $element) {
			$nextStr = htmlspecialchars_decode($element->onmouseover);
			if (strpos($nextStr, 'clue_'.$round) !== false) {
				$x .= $nextStr . '<br>';
			}
		}
		return $x;
	}
	
	/**
	 * Get the categories from the HTML page
	 * @param html The HTML document
	 * @param round The Jeopardy round
	 * @param num_categories (optional) The total number of categories
	 * @return the array of categories
	 */
	function getCategories($html, $round, $num_categories=6) {
		$categories = array();
		$numSkipped = 0;
		foreach($html->find('td') as $element) {
			if ($element->class == "category_name" && count($categories) < $num_categories) {
				if ($round == "DJ" && $numSkipped < $num_categories) {
					//Need to skip some elements if round is "DJ"
					$numSkipped++;
					continue;
				}
				array_push($categories, $element->innertext);
			}
		}
		return $categories;
	}
?>