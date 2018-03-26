<?php

	class NbaLogParser
	{
		protected $logDefaultDir = 'log';
		protected $outputDefaultDir = 'output';
		
		protected $logDir;
		protected $outputDir;
		
		// Set parsers to initialize at startup
		private $parsers = [
			'BrahmsLogParser',
		];
		// Parsers that have succesfully been loaded
		protected $loadedParsers;
		
		public function __construct ()
		{
            $this->setLogDir();
            $this->setOutputDir();
            $this->getLogFiles();
            $this->initialiseParsers();
		}
	
        public function __destruct ()
        {
           
        }
        
	    public function setLogDir ($dir = false) 
        {
        	$this->logDir = dirname(dirname(__FILE__)) . '/' . $this->logDefaultDir;
        	if ($dir) {
        		$this->logDir = $dir;
        	}
	    	// Does log dir exist?
			if (empty($this->logDir) || !is_dir($this->logDir)) {
				throw new Exception('Log directory ' . $this->logDir . 
					' is not set or not readable!');
			}
        	return $this;
        }
        
        public function getLogDir ()
        {
        	return $this->logDir;
        }
        
        public function setOutputDir ($dir = false) 
        {
            $this->outputDir = dirname(dirname(__FILE__)) . '/' . $this->outputDefaultDir;
        	if ($dir) {
        		$this->outputDir = $dir;
        	}
        	if (empty($this->outputDir) || !is_writable($this->outputDir)) {
				throw new Exception('Output directory ' . $this->outputDir . 
					' is not set or not writable!');
			}
        	return $this;
        }
        
	    public function getOutputDir ()
        {
        	return $this->outputDir;
        }
        
        public function deleteExistingOutput ($option)
		{
			if ($option) {
				$files = scandir($this->outputDir);
				foreach ($files as $file) {
					if ($file[0] !== '.') {
						unlink($this->outputDir . '/' . $file);
					}
				}
			}
			return $this;
		}
		
		public function run ()
		{
			foreach ($this->loadedParsers as $parser) {
				$parser->parseLogs();
			}
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
			
		protected function resetFileData ()
		{
			$this->logFileData = [];
		}
	
		protected function summarise () 
		{
			// Count warning and errors
			foreach ($this->logFileData as $file => $data) {
				foreach (['warnings', 'errors'] as $type) {
					if (!empty($data[$type])) {
						foreach ($data[$type] as $k => $message) {
							// Test if message is in fixed list
							$text = $this->setWarningMessage($message['type']);
							if (!isset($output[$type][$text])) {
								$output[$type][$text] = 0;
							}
							$output[$type][$text]++;
						}
					}
				}
			}
			$this->logFileData[$this->inputFile]['info']['summary'] = $output;
		}
	
		private function setWarningMessage ($message) {
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
	