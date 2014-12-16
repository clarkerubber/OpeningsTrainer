<?php
ini_set('memory_limit','16000M');
include('GMOpeningFens.php');

$fenOpenings = array();
$totalFens = 0;
$fensParsed = 0;
$gamesParsed = 0;

foreach ($openingFens as $fens) {
  $opening = array_shift($fens);
  $gamesParsed++;
  foreach ($fens as $fen) {
    $fensParsed++;
    $tmp = explode(' ', $fen);
    array_pop($tmp);
    array_pop($tmp);
    $tmp = implode(' ', $tmp);
    if (isset($fenOpenings[$tmp])) {
      if (!in_array($opening, $fenOpenings[$tmp]['openings'])) {
        $fenOpenings[$tmp]['openings'][] = $opening;
      }
      $fenOpenings[$tmp]['nb']++;
    } else {
      $totalFens++;
      $fenOpenings[$tmp]['fen'] = $fen;
      $fenOpenings[$tmp]['openings'][0] = $opening;
      $fenOpenings[$tmp]['nb'] = 1;
    }
  }
}

$output = array();

foreach ($fenOpenings as $key => $value) {
  if ($value['nb'] > 14) {
    $output[] = $value;
  }
}
//echo count($openingFens)."\n";
//echo "Games Parsed: $gamesParsed\nFENs Parsed: $fensParsed\nTotal FENs: $totalFens\n";
print_r(json_encode($output, JSON_PRETTY_PRINT));
