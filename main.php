<?

//include("openings.php");
ini_set('memory_limit','300M');
include("functions.php");

$openings = json_decode(file_get_contents('gmopeningfens.json'), true);

if (isset($argv[2])) {
  $min = intval($argv[1]);
  $max = min(intval($argv[2]), count($openings) - 1);
} else if (isset($argv[1])) {
  $min = $argv[1];
  $max = count($openings) - 1;
} else {
  $min = 0;
  $max = count($openings) - 1;
}

for ($x = $min; $x <= $max; ++$x) {
  if (!file_exists("output/$x.json")) {
    $output = array();
    $output['fen'] = $openings[$x]['fen'];
    $output['moves'] = getMoves($openings[$x]['fen']);
    if ($output['moves'] === false) {
      continue;
    }
    file_put_contents("output/$x.json", json_encode($output, JSON_PRETTY_PRINT));
  }
}