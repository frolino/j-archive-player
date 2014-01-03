J! Archive Player
==================
				
Overview
---------
J! Archive Player is a PHP-based program that turns the J! Archive repository into playable Jeopardy! rounds. This has been developed primarily as a Jeopardy! training tool. Future enhancements will focus on tracking statistical information that aspiring Jeopardy! players may find pertinent.

Documentation
---------------
The documentation can be found in GitHub

Installation
-------------
No installation is required. However, for the PHP code to run, you will need to install a web server that includes MySQL and PHP (Apache would work fine). 

You also need to create a database (we recommed the name "jarp_db", but you can choose any name you want). Once you do so, modify the contents of base.php to include the name of your database, as well as the login credentials for the database. Finally, create a table called 'users' in your database by running the following SQL query:

<pre>
CREATE TABLE `users` (  
`UserID` INT(25) NOT NULL AUTO_INCREMENT PRIMARY KEY ,  
`Username` VARCHAR(65) NOT NULL ,  
`Password` VARCHAR(32) NOT NULL ,  
`EmailAddress` VARCHAR(255) NOT NULL  
);
</pre>

After following the steps above, create an account by registering via register.php, and then log in by going to index.php.

Running
--------
To run the program, you will first need to specify the game ID number (assigned by J! Archive) and the round (either "J" for "Jeopardy" or "DJ" for "Double Jeopardy"). You can specify these using the form on the left statistics panel. You can also specify the same information in the URL, as follows:

http://&lt;path_to_index>/index.php?id=&lt;game_id>&round=&lt;round_code>

where &lt;path_to_index> refers to the path leading to (and including) the folder containing the index.php file; &lt;game_id> refers to the game id; and &lt;round_code> refers to the round (either "J" or "DJ").

The game begins once you press the "Start" button. Note that all dollar values are "post-doubling" (i.e., $200-$1000 in J! and $400-$2000 in DJ!). This design choice was made for simplicity, but if you know you know how to divide by 2, it shouldn't be too difficult to convert to pre-doubling dollar values ;)

Due to potential inaccuracies in comparing the user's answer with the "correct" answer (as posted in J! Archive), you are allowed to force the program to either accept or reject your answer, effectively overriding an earlier decision.

TODO
-----

- Allow user to input game number (from J! Archive)
- Smarter "question form" detector (i.e., What is, What are, etc.)