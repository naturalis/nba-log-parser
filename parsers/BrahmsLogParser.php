<?php

	require_once 'NbaLogParserInterface.php';

	class BrahmsLogParser extends NbaLogParser implements NbaLogParserInterface
	{
		private $identifier = 'brahms';
		private $selectedFiles;
		
		public function __construct ($main)
		{
			$this->logDir = $main->getLogDir();
			$this->outputDir = $main->getOutputDir();
			$this->selectLogFiles();
		}
	
        public function __destruct ()
        {
           
        }
        
        public function selectLogFiles ()
        {
        	foreach ($this->getLogFiles() as $file) {
        		if (strpos($file, $this->identifier) !== false) {
        			$this->selectedFiles[] = $file;
        		}
        	}
        	
 print_r($this->selectedFiles);       	
        	return $this->selectedFiles;
        }
        
        public function parseLog ()
        {
        	
        }

	}
	