<?php

	class CrsLogParser extends NbaLogParser
	{
		protected $identifier = 'crs';
		protected $category;
		protected $categories = [
			'specimens',
			'multimedia',
		];
		protected $warnings = [
			'Missing element: <ncrsDetermination>',
			'Invalid or insufficient specimen identification information',
			'Invalid latitude',
			'Invalid longitude',
			'Invalid date in element',
			'Invalid image URL'
		];		
		
		protected $breakCounter = 0;
		protected $infoBlockNrLines = 22;
		protected $infoLineCounter = 0;
		
		// Same as Brahms
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
				$tmp = array_map('trim', explode(':', $column[2]));
				// Error
				if ($column[1] == 'ERROR') {
					$this->logFileData[$this->inputFile]['error'][] = [
						'type' => $column[4],
						'unitId' => $tmp[1]
					];
				// Warning
				} else if ($column[1] == 'WARN') {
					$this->logFileData[$this->inputFile]['warning'][] = [
						'type' => $column[4],
						'unitId' => $tmp[1]
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
			$tmp = explode(' ', $s);
			return array_pop($tmp);			
		}
	}
	