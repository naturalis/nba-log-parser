<?php

	// Make sure we have some memory to work with...
	ini_set('memory_limit', '2048M');
	// ... and some time
	set_time_limit(300);

	class NbaLogParser
	{
		protected $logDir;
		protected $outputDir;
		protected $loadedParsers;
		
		// Set parsers to initialize at startup
		private $parsers = [
			'BrahmsLogParser',
			'CrsLogParser',
			'CoLLogParser',
		];
		// These lines appear twice in the summaries!
		private $half = [
			'Records accepted', 
			'Specimens accepted', 
			'Multimedia accepted', 
			'Objects accepted',
		];
		
 	    public function setLogDir ($dir = false) 
        {
        	$this->logDir = $dir;
 			if (empty($this->logDir) || !is_dir($this->logDir)) {
				throw new Exception('Log directory ' . $this->logDir . 
					' is not set or not readable!');
			}
        	return $this;
        }
        
        public function getLogDir ($stripPath = false)
        {
       		$dir = substr($this->logDir, -1) == '/' ? substr($this->logDir, 0, -1) :
        		$this->logDir;
       		return $stripPath ? basename($dir) : $dir;
        }
        
        public function setOutputDir ($dir = false) 
        {
        	$this->outputDir = $dir;
        	if (empty($this->outputDir) || !is_writable($this->outputDir)) {
				throw new Exception('Output directory ' . $this->outputDir . 
					' is not set or not writable!');
			}
        	return $this;
        }
        
	    public function getOutputDir ($stripPath = false)
        {
        	$dir = substr($this->outputDir, -1) == '/' ? substr($this->outputDir, 0, -1) :
        		$this->outputDir;
      		return $stripPath ? basename($dir) : $dir;
         }
        
		public function run ()
		{
			 if (empty($this->getLogDir())) {
			 	throw new Exception('Log directory not set!');
			 }
			 if (empty($this->getOutputDir())) {
			 	throw new Exception('Output directory not set!');
			 }
             $this->initialiseParsers();
			 //$this->getLogFiles();
			 foreach ($this->loadedParsers as $parser) {
				$parser->parseLogs();
			}
		}
		    
        protected function parseLogs ()
        {
			foreach ($this->categories as $this->category) {
				$this->resetFileData();
		       	foreach ($this->selectLogFiles($this->identifier, $this->category) as $file) {
		         	$this->resetLineData();
		         	$this->parseFile($file);
		        }
	         	$this->summariseWarningsAndErrors();
		        $this->summarizeStatInfo();
		        $this->summarizeThemeInfo();
		        $this->writeData();
			}
        }
        
        protected function writeData ()
		{
			$this->writeSummary();
			$this->writeWarnings();
			$this->writeErrors();
			$this->writeNormalizationInfo();
		}
		
	    protected function summarizeStatInfo ()
		{
			$this->getSummary('logLines', 'summary');
			return $this->logFileData;
		}
		
		protected function summarizeThemeInfo ()
		{
			$this->getSummary('themeLines', 'themes');
			return $this->logFileData;
		}
		
		protected function getSummary ($input, $output) 
		{
			$data = [];
			foreach ($this->logFileData as $source => $file) {
				if (isset($file[$input])) {			
					foreach ($file[$input] as $line) {
						$column =  array_map('trim', explode(':', $line));
						if (count($column) == 3) {
							list($blah, $label, $count) = $column;
							$count = $this->checkCountSummary($label, $count, 
								$this->category == 'multimedia');
							if (!isset($data[$label])) {
								$data[$label] = $count;
							} else {
								$data[$label] += $count;
							}
						}
					}
				}
			}
			$this->logFileData[$output] = $data;
			return $this->logFileData;
		}
		
		// Some lines appear twice in info block...
		protected function checkCountSummary ($label, $count, $overrule = false)
		{
			if (!$overrule && in_array($label, $this->half)) {
				return $count/2;
			}
			return $count;
		}
		
		protected function writeSummary () 
		{
			$print = [
				['Source', $this->identifier],
				['Type', $this->category],
				['Date', $this->getLogDir(true)],
				['Files', implode("\n", $this->selectLogFiles($this->identifier, $this->category))]
			];
			// Compile warning and error stats
			foreach (array_merge($this->logFileData['summary'], 
				$this->logFileData['themes']) as $label => $value) {
				$print[] = [$label, $value];
			}
			// Errors and warnings
			foreach (['warning', 'error'] as $type) {
				if (!empty($this->logFileData[$type])) {
					foreach ($this->logFileData[$type] as $message => $count) {
						$print[] = [ucfirst($type) . ': ' . $message, $count];
					}
				}
			}
			// Create parent directory 
			if (!file_exists($this->setCsvBasePath())) {
			    mkdir($this->setCsvBasePath(), 0777, true);
			}
			$fp = fopen($this->setCsvBasePath() . 'summary.csv', 'w');
			foreach ($print as $row) {
				fputcsv($fp, $row);
			}
			fclose($fp);
		}
		
		protected function writeWarnings ()
		{
			$this->writeFile('warnings.csv', 'Warning', 'warning');
		}
		
		protected function writeErrors ()
		{
			$this->writeFile('errors.csv', 'Error', 'error');
		}
		
		protected function writeNormalizationInfo () 
		{
			$fp = fopen($this->setCsvBasePath() . 'normalization.csv', 'w');
			fputcsv($fp, ['Info', 'File']);
			foreach ($this->logFileData as $file => $data) {
				if (isset($data['normalizeLines'])) {
					foreach ($data['normalizeLines'] as $message) {
						fputcsv($fp, [$message, $file]);
					}
				}
			}
			fclose($fp);
		}
		
		protected function setCsvBasePath () 
		{
			return $this->getOutputDir() . '/' . $this->identifier . '_' . 
				$this->category . '_' . $this->getLogDir(true) . '/';
		}
		
       protected function getLogFiles () 
		{
			$files = scandir($this->logDir);
			foreach ($files as $file) {
				$info = pathinfo($file);
				if (isset($info['extension']) && $info['extension'] == 'gz') {
					$logFiles[] = $this->logDir . '/' . $file;
				}
			}
			return $logFiles;
		}
		
	    protected function selectLogFiles ($identifier, $category = false)
        {
         	foreach ($this->getLogFiles() as $file) {	        	
	        	if (strpos($file, $identifier) !== false) {
	        		if (!$category || $category && strpos($file, $category) !== false) {
	        			$selectedFiles[] = $file;
	        		}
	        	}
	        }
         	return $selectedFiles;
        }
		
		protected function parseFile ($file)
		{
			$handle = gzopen($file, 'r');
			while (!gzeof($handle)) {
				$this->parseLine(gzgets($handle, 4096));
			}
			gzclose($handle);
		}
			
		protected function writeFile ($fileName, $title, $section) 
		{
			$fp = fopen($this->setCsvBasePath() . $fileName, 'w');
			fputcsv($fp, [$title, 'Unit id', 'File']);
			foreach ($this->logFileData as $file => $data) {
				if (isset($data[$section])) {
					foreach ($data[$section] as $message) {
						fputcsv($fp, [$message['type'], $message['unitId'], $file]);
					}
				}
			}
			fclose($fp);
		}
		
		protected function resetFileData ()
		{
			$this->logFileData = false;
		}
	
		protected function resetLineData ()
        {
		    $this->inputFile = false;
        	$this->breakCounter = 0;
        	$this->infoLineCounter = 0;
        }
        
 		protected function summariseWarningsAndErrors () 
		{
			// Count warning and errors
			foreach ($this->logFileData as $file => $data) {
				foreach (['warning', 'error'] as $type) {
					if (!empty($data[$type])) {
						foreach ($data[$type] as $k => $message) {
							// Test if message is in fixed list
							$text = $this->setWarningMessage($message['type']);
							if (!isset($this->logFileData[$type][$text])) {
								$this->logFileData[$type][$text] = 0;
							}
							$this->logFileData[$type][$text]++;
						}
					}
				}
			}
			return $this->logFileData;
		}
	
		protected function setWarningMessage ($message) {
			foreach ($this->warnings as $warning) {
				if (strpos($message, $warning) !== false) {
					return $warning;
				}
			}
			return $message;
		}
		
		private function initialiseParsers () 
		{
			foreach ($this->parsers as $file) {
				require_once $file . '.php';
				$this->loadedParsers[$file] = new $file($this);
			}
		}
		
	}
	
