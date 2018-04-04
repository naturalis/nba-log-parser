<?php

	class CoLLogParser extends NbaLogParser
	{
		protected $identifier = 'col';
		protected $category;
		protected $categories = [
			'taxa',
		];
		protected $warnings = [
			'Duplicate synonym',
			'Orphan',
			'Duplicate vernacular name',
			'Duplicate reference',
			'Invalid date'
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
			// Hard-coded shit! info block contains 21 lines
			if ($this->breakCounter >= 1 && $this->breakCounter <= 2 && 
				$this->infoLineCounter < $this->infoBlockNrLines) {
				$this->logFileData[$this->inputFile]['logLines'][] = $column[2];
				$this->infoLineCounter++;
			}			
			// Errors and warnings
			if (strpos($column[2], 'Transformer') !== false) {
				$tmp = array_map('trim', explode(':', $column[2]));
				// Error
				if ($column[1] == 'ERROR') {
					$this->logFileData[$this->inputFile]['error'][] = [
						'type' => $column[3],
						'unitId' => $tmp[1]
					];
				// Warning
				} else if ($column[1] == 'WARN') {
					$this->logFileData[$this->inputFile]['warning'][] = [
						'type' => $tmp[1] . ': '. $tmp[2],
						'unitId' => 'n/a'
					];
				}
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
	
		protected function writeFile ($fileName, $title, $section) 
		{
			$fp = fopen($this->setCsvBasePath() . $fileName, 'w');
			fputcsv($fp, [$title, 'Id', 'File']);
			foreach ($this->logFileData as $file => $data) {
				if (isset($data[$section])) {
					foreach ($data[$section] as $message) {
						fputcsv($fp, [$message['type'], $message['unitId'], $file]);
					}
				}
			}
			fclose($fp);
		}
		
		
	}
	