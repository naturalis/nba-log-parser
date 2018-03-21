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
		}
	
        public function __destruct ()
        {
           
        }
        
        public function selectLogFiles ()
        {
        	if (empty($this->selectedFiles)) {
        		foreach ($this->getLogFiles() as $file) {	        	
	        		if (strpos($file, $this->identifier) !== false) {
	        			$this->selectedFiles[] = $file;
	        		}
	        	}
	        }
         	return $this->selectedFiles;
        }
        
        public function parseLogs ()
        {
        	foreach ($this->selectLogFiles() as $file) {
         		$this->parseFile($file);
        	}
        }
        
		protected function parseLine ($line) 
		{
			$column =  array_map('trim', explode('|', $line));
			// We're interested only in the transformer, which is element #3
			if (strpos($column[2], 'Transformer') !== false) {
				// Error
				if ($column[1] == 'ERROR') {
					$this->logFileData['errors'][] = [
						'type' => $column[4],
						'unitId' => $column[3]
					];
				// Warning
				} else if ($column[1] == 'WARN') {
					$this->logFileData['warnings'][] = [
						'type' => $column[4],
						'unitId' => $column[3]
					];
				
				}
			}
			
			print_r($column); die();
		}
		
        
        
        
        
        
        
        

	}
	