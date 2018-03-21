<?php
	require_once 'parsers/NbaLogParser.php';

	$parser = new NbaLogParser;
	$parser->deleteExistingOutput(true);
	$parser->run();

	