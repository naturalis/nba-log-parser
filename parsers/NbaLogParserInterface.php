<?php

	interface NbaLogParserInterface
	{
	    public function selectLogFiles();
	    public function parseLog();
	}

	