<?

include('openingFens.php');

$fenOpenings = array();
$total = 0;

foreach ($openingFens as $opening => $fens) {
  foreach ($fens as $fen) {
    $tmp = explode(' ', $fen);
    array_pop($tmp);
    array_pop($tmp);
    $tmp = implode(' ', $tmp);
    if (isset($fenOpenings[$tmp])) {
      $fenOpenings[$tmp]['openings'][] = $opening;
    } else {
      $total++;
      $fenOpenings[$tmp]['fen'] = $fen;
      $fenOpenings[$tmp]['openings'][0] = $opening;
    }
  }
}

$output = array();

foreach ($fenOpenings as $key => $value) {
  $output[] = $value;
}

print_r(json_encode($output, JSON_PRETTY_PRINT));