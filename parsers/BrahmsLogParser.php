<?php

	class BrahmsLogParser extends NbaLogParser
	{
		protected $identifier = 'brahms';
		protected $category;
		protected $categories = [
			'specimens',
			'multimedia',
		];
		protected $warnings = [
			'Unable to construct date',
			'Invalid multimedia URL',
			'Invalid image URL',
			'Invalid longitude',
		];
		protected $logFileData;
		protected $inputFile;
		
		protected $breakCounter = 0;
		protected $infoBlockNrLines = 22;
		protected $infoLineCounter = 0;
		
		// Directories have been set in main class
		public function __construct ($main)
		{
			$this->logDir = $main->getLogDir();
			$this->outputDir = $main->getOutputDir();
		}
		
		protected function parseLine ($line) 
		{			
			$column =  array_map('trim', explode('|', $line));			
			// Input file info; assumes start of interesting data
			if (strpos($column[2], 'Processing file') !== false) {
				$this->inputFile = $this->getInputFileName($column[2]);
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
				$this->logFileData[$this->inputFile]['logLines'][] = $column[2];
				$this->infoLineCounter++;
			}			
			// Errors and warnings
			if (strpos($column[2], 'Transformer') !== false) {
				// Error
				if ($column[1] == 'ERROR') {
					$this->logFileData[$this->inputFile]['error'][] = [
						'type' => $column[4],
						'unitId' => $column[3]
					];
				// Warning
				} else if ($column[1] == 'WARN') {
					$this->logFileData[$this->inputFile]['warning'][] = [
						'type' => $column[4],
						'unitId' => $column[3]
					];
				}
			}			
			// Theme
			if (strpos($column[2], 'ThemeCache') !== false && 
				strpos($column[2], 'theme') !== false) {
				$this->logFileData[$this->inputFile]['themeLines'][] = $column[2];
			}
			// Normalizeation info
			if (strpos($column[2], 'normalize') !== false && 
				strpos($column[2], 'Invalid') !== false) {
				$tmp = array_map('trim', explode(':', $column[2]));
				$this->logFileData[$this->inputFile]['normalizeLines'][] = $tmp[1];
			}
		}

		protected function getInputFileName ($s)
		{
			return substr($s, strpos($s, '/'));	
		} 
	}
	