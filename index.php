<?php
	require_once 'parsers/NbaLogParser.php';
	require_once 'config.php'; // set log and output dirs here!

	$parser = new NbaLogParser;
	$parser
		->setLogDir($logDir)
		->setOutputDir($outputDir)
		->run();
	
	echo 'Ready';
	