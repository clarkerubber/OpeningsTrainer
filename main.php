<?php

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

    $json = json_encode($output);

    file_get_contents('http://en.l.org/api/opening?token=foo',null,stream_context_create(array(
          'http' => array(
              'protocol_version' => 1.1,
              'user_agent'       => 'PHPExample',
              'method'           => 'POST',
              'header'           => "Content-type: application/json\r\n".
                                    "Connection: close\r\n" .
                                    "Content-length: " . strlen($json) . "\r\n",
              'content'          => $json
          ),
      )));
  }
}
