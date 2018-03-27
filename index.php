<?php
	require_once 'parsers/NbaLogParser.php';

	$parser = new NbaLogParser;
	$parser
		->setLogDir('/Users/ruud/ETI/Zend workbenches/Current/nba-log-parser/2018-03-14')
		->setOutputDir('/Users/ruud/ETI/Zend workbenches/Current/nba-log-parser/output')
		->run();
	
	echo 'Ready';
	