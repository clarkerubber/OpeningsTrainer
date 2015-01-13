<?php

ini_set('memory_limit','300M');

$positions = json_decode(file_get_contents('gmopeningfens.json'), true);
$output = array();

function filterNames($openings) {
	$output = array();
	foreach ($openings as $key => $opening) {
		preg_match("/.+?,/", $opening, $matches);
		if (!in_array(rtrim($matches[0], ','), $output) && !in_array($opening, $output)) {
			$output[] = ($matches[0]) ? rtrim($matches[0], ',') : $opening;
		}	
	}
	return $output;
}

foreach($positions as $key => $position) {
	$output[$position['fen']] = (count($position['openings']) > 5) ? filterNames($position['openings']) : $position['openings'];
}

print_r(json_encode($output, JSON_PRETTY_PRINT));