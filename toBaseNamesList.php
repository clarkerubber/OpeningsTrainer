<?php

ini_set('memory_limit','300M');

$positions = json_decode(file_get_contents('gmopeningfens.json'), true);
$output = array();

function unescapeString($str) {
	return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
    	return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
	}, $str);
}

function filterNames($openings) {
	$output = array();
	foreach ($openings as $key => $opening) {
		preg_match("/.+?,/", $opening, $matches);
		if (!in_array(rtrim($matches[0], ','), $output) && !in_array($opening, $output)) {
			$output[] = unescapeString(($matches[0]) ? rtrim($matches[0], ',') : $opening);
		}	
	}
	return $output;
}

foreach($positions as $key => $position) {
	$output[unescapeString($position['fen'])] = (count($position['openings']) > 5) ? filterNames($position['openings']) : $position['openings'];
}

print_r(json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));