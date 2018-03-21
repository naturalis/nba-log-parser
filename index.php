<?php
	require_once 'parsers/NbaLogParser';

	$parser = new NbaLogParser;
	$parser
		->setLogDir('/Users/ruud/Documents/MAMP/htdocs/nba-log/logs')
		->setOutputDir('/Users/ruud/Documents/MAMP/htdocs/nba-log/output')
		->deleteExistingOutput(true)
		->run();
	

	