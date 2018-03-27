<?php
	require_once 'parsers/NbaLogParser.php';

	$parser = new NbaLogParser;
	// Set directory with log files that should be parsed
	$parser->setLogDir('/Users/ruud/ETI/Zend workbenches/Current/nba-log-parser/2018-03-14');
	// Directory to which output files are written
	$parser->setOutputDir('/Users/ruud/ETI/Zend workbenches/Current/nba-log-parser/output');
	// Remove older data
	$parser->run();

	