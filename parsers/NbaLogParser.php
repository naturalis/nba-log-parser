<?php

	class NbaLogParser
	{
		protected $logDefaultDir = 'log';
		protected $outputDefaultDir = 'output';
		
		protected $logDir;
		protected $outputDir;
		
		protected $logFiles;
		protected $logFileData;
		protected $output;
		
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
					$this->logFiles[] = $this->logDir . '/' . $file;
				}
			}
			return $this->logFiles;
		}
		
		protected function parseFile ($file)
		{
			$this->resetLogFileData();
			$handle = gzopen($file, 'r');
			while (!gzeof($handle)) {
				$this->parseLine(gzgets($handle, 4096));
			}
			gzclose($handle);
		}
			
		protected function resetLogFileData ()
		{
			$this->logFileData = [
				'errors' => [],
				'warnings' => [],
			];
		
		}
		
		private function initialiseParsers () 
		{
			foreach ($this->parsers as $file) {
				require_once $file . '.php';
				$this->loadedParsers[$file] = new $file($this);
			}
		}
	}
	