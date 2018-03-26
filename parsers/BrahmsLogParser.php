<?php

	require_once 'NbaLogParserInterface.php';

	class BrahmsLogParser extends NbaLogParser implements NbaLogParserInterface
	{
		protected $identifier = 'brahms';
		//protected $categories = ['specimens', 'multimedia'];
		protected $categories = [
			'specimens' => 'BrahmsSpecimenImporter',
			//'multimedia' => 'BrahmsMultiMediaImporter',
		];
		protected $warnings = [
			'Unable to construct date',
			'Invalid multimedia URL',
			'Invalid longitude',
		];
		protected $logFileData;
		protected $inputFile;
		
		protected $breakCounter = 0;
		protected $infoBlockNrLines = 22;
		protected $infoLineCounter = 0;
		
		
		public function __construct ($main)
		{
			$this->logDir = $main->getLogDir();
			$this->outputDir = $main->getOutputDir();
		}
	
        public function parseLogs ()
        {
			foreach ($this->categories as $category => $importer) {
				$this->resetFileData();
		       	foreach ($this->selectLogFiles($this->identifier, $category) as $file) {
		         	$this->resetLineData();
		         	$this->parseFile($file);
		         	$this->summarise();
		         	
		         	//print_r($this->logFileData[$this->inputFile]['info']);
		        }
		        // Merge data from reports into a single overview
		        $this->mergeSummaries();
			}
			
			
        }
        
		protected function parseLine ($line) 
		{			
			$column =  array_map('trim', explode('|', $line));
			
			// Input file info; assumes start of interesting data
			if (strpos($column[2], 'Processing file') !== false) {
				$this->inputFile = substr($column[2], strpos($column[2], '/'));
			}
			
			// Stats start at the break counter; simply add lines to output
			// All parsing should quit once $breakCounter hits 3, otherwise stats will be included twice
			if (strpos($column[2], '===============================') !== false) {
				$this->breakCounter++;
			}
			if ($this->breakCounter >= 3) {
				return false;
			}
			// Hard-coded shit! info block contains 21 lines
			if ($this->breakCounter >= 1 && $this->breakCounter <= 2 && 
				$this->infoLineCounter < $this->infoBlockNrLines) {
				$this->logFileData[$this->inputFile]['info']['logLines'][] = $column[2];
				$this->infoLineCounter++;
			}
			
			// Errors and warnings
			if (strpos($column[2], 'Transformer') !== false) {
				// Error
				if ($column[1] == 'ERROR') {
					$this->logFileData[$this->inputFile]['errors'][] = [
						'type' => $column[4],
						'unitId' => $column[3]
					];
				// Warning
				} else if ($column[1] == 'WARN') {
					$this->logFileData[$this->inputFile]['warnings'][] = [
						'type' => $column[4],
						'unitId' => $column[3]
					];
				
				}
			}
		}
		
		private function mergeSummaries ()
		{
			$data = [];
			foreach ($this->logFileData as $source => $file) {
				foreach ($file['info']['logLines'] as $line) {
					$column =  array_map('trim', explode(':', $line));
					// Lines with data contains two colons
					if (count($column) == 3) {
						list($blah, $label, $count) = $column;
						if (!isset($data[$label])) {
							$data[$label] = $count;
						} else {
							$data[$label] += $count;
						}
					}
				}
			}
			$this->logFileData['summary'] = $data;
			return $this->logFileData;
		}
		
		private function resetLineData ()
        {
		    $this->inputFile = false;
        	$this->breakCounter = 0;
        	$this->infoLineCounter = 0;
        }
        
        
        

	}
	