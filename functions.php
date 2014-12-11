<?

include("config.php");

function getUci ( $moveSequence, $moveTime, $multiPv = 1, $startpos = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1" ) {

  global $STOCKFISH_PATH;

  $descriptorspec = array(
    0 => array( "pipe", "r" ),  // stdin is a pipe that the child will read from
    1 => array( "pipe", "w" ),  // stdout is a pipe that the child will write to
    2 => array( "file", "tmp/error-output.txt", "a" ) // stderr is a file to write to
  );

  $cwd = '/tmp';
  $env = array( 'some_option' => 'aeiou' );

  $process = proc_open( "$STOCKFISH_PATH", $descriptorspec, $pipes, $cwd, $env );

  if (is_resource($process)) {

    fwrite( $pipes[0], "uci\n" );
    fwrite( $pipes[0], "ucinewgame\n" );
    fwrite( $pipes[0], "isready\n" );
    fwrite( $pipes[0], "setoption name MultiPV value $multiPv\n" );
    if (strlen($moveSequence) > 0) {
      fwrite( $pipes[0], "position fen $startpos moves $moveSequence\n" );
    } else {
      fwrite( $pipes[0], "position fen $startpos\n" );
    }
    fwrite( $pipes[0], "go movetime $moveTime\n" );
    usleep( 1000 * $moveTime + 100 );
    fwrite( $pipes[0], "quit\n" );
    fclose( $pipes[0] );

    $output = stream_get_contents( $pipes[1] );

    fclose( $pipes[1] );
  }

  return $output;
}

function getPositionEval ( $moveString, $moveTime, $multiPv = 1, $startpos = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1" ) {
  
  $uciOutput = getUci( $moveString, $moveTime, $multiPv, $startpos );
  $output = FALSE;

  preg_match_all( "/cp (-?[0-9]+).*?(([a-h][1-8][a-h][1-8][qrnb]? ?)+)/", $uciOutput, $matches );

  $eval = end( $matches[1] );
  $line = end( $matches[2] );

  if ( isset( $eval ) ) {
    $output = array($eval, $line);
  }

  return $output;
}

function getMoves ( $startpos ) {

  global $FIRST_PASS_TIME, $SECOND_PASS_TIME, $ALT_THRESHOLD;
  global $MULTIPV;

  echo "\nPosition: $startpos\n";
  echo "\nPotential moves: ";
  $uciOutput = getUci( '', $FIRST_PASS_TIME, $MULTIPV, $startpos );

  preg_match_all( "/info.*?cp (-?[0-9]+).*?([a-h][1-8][a-h][1-8][qrnb]?)/", $uciOutput, $matches );

  $candidateMoves = array();
  $candidateMovesEval = array();
  $candidateMovesLine = array();

  $lastMove = explode( ' ', $moveString );
  array_pop( $lastMove );
  $lastMove = array_pop( $lastMove );

  // Build list of candiate moves
  foreach ( $matches[2] as $key => $match ) {
    if ( !in_array( $match , $candidateMoves) ) {
      $candidateMoves[] = $match;
    }
  }

  print_r(implode(', ', $candidateMoves));

  // Remove moves that didn't analyse correctly
  echo "\nDeep analysis: ";
  foreach ( $candidateMoves as $key => $move ) {
    $tmp = getPositionEval( $move, $SECOND_PASS_TIME, 1, $startpos );
    if ($tmp) {
      $candidateMovesEval[] = $tmp[0];
      $candidateMovesLine[] = $tmp[1];
    } else {
      unset($candidateMoves[$key]);
    }
  }

  print_r(implode(', ', $candidateMovesEval));

  // Sort by evaluation
  echo "\nSorting moves\n";
  array_multisort( $candidateMovesEval, SORT_ASC, SORT_NUMERIC, $candidateMoves, $candidateMovesLine );


  if (isset($candidateMovesEval[0])) {
    $topEval = $candidateMovesEval[0];
    if (abs($topEval) > 110) {
      return false;
    }
  } else {
    return false;
  }
  
  // Filter moves with poor evaluation
  echo "Ranking moves\n";
  $moveArray = array();
  $no_good_move = true;

  foreach ( $candidateMoves as $key => $move ) {
    if (abs($topEval - $candidateMovesEval[$key]) < 10
      && $candidateMovesEval[$key] < 40) {
      // Excellent move
      echo "$move ".$candidateMovesEval[$key]." excellent continues: ".$candidateMovesLine[$key]."\n";
      $no_good_move = false;
      $moveArray[$move] = array('cp' => $candidateMovesEval[$key], 'result' => '1', 'line' => $candidateMovesLine[$key]);

    } else if (abs($topEval - $candidateMovesEval[$key]) < 25
      && $candidateMovesEval[$key] < 60) {
      // Good move
      echo "$move ".$candidateMovesEval[$key]." good continues: ".$candidateMovesLine[$key]."\n";
      $no_good_move = false;
      $moveArray[$move] = array('cp' => $candidateMovesEval[$key], 'result' => '2', 'line' => $candidateMovesLine[$key]);

    } else if (abs($topEval - $candidateMovesEval[$key]) < 40
      && $candidateMovesEval[$key] < 70) {
      // Dubious
      echo "$move ".$candidateMovesEval[$key]." dubious continues: ".$candidateMovesLine[$key]."\n";
      $moveArray[$move] = array('cp' => $candidateMovesEval[$key], 'result' => '3', 'line' => $candidateMovesLine[$key]);
      
    } else {
      // Bad
      echo "$move ".$candidateMovesEval[$key]." bad continues: ".$candidateMovesLine[$key]."\n";
      $moveArray[$move] = array('cp' => $candidateMovesEval[$key], 'result' => '4', 'line' => $candidateMovesLine[$key]);
    }
  }

  if ($no_good_move) {
    return false;
  }

  return $moveArray;
}