<?php

//-----thresholds
$BALANCED = 150; // +-centipawns for a position to be considered even

//-----engine settings
$STOCKFISH_PATH = "/Users/clarkey/Documents/Development/lichess/stockfish-5-mac/Mac/stockfish-5-64"; // location of stockfish engine
$FIRST_PASS_TIME = 10000; // milliseconds to gather candidate moves
$SECOND_PASS_TIME = 10000; // milliseconds to consider each candidate move

$MULTIPV = 15;