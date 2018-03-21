<?php

	class BrahmsLogParser extends NbaLogParser
	{
		private $logDir;
		private $logFiles;
		private $outputDir;
		private $logFileData;
		private $output;
		
		public function __construct ()
		{
            $this->initialiseParsers();
		}
	
        public function __destruct ()
        {
           
        }
        
	    public function setLogDir ($dir) 
        {
        	$this->logDir = $dir;
        	return $this;
        }
        
        public function setOutputDir ($dir) 
        {
        	$this->outputDir = $dir;
        	return $this;
        }
        
		public function run ()
		{
            $this->bootstrap();
            foreach ($this->getLogFiles() as $file) {
            	$this->parseFile($file);
            	print_r($this->logFileData);
            }
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
		
		private function bootstrap ()
		{
			// Does log dir exist?
			if (empty($this->logDir) || !is_dir($this->logDir)) {
				throw new Exception('Log directory is not set or not readable!');
			}
			// Does output dir exist?
			if (empty($this->outputDir) || !is_writable($this->logDir)) {
				throw new Exception('Output directory is not set or not writable!');
			}
			$this->getLogFiles();
			// No files present
			if (count($this->logFiles) == 0) {
				throw new Exception('No log files present!');
			}
		}		
		
		private function getLogFiles () 
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
				$this->getLogData(gzgets($handle, 4096));
			}
			gzclose($handle);
		}
			
		protected function getLogData ($line) 
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
		}
		
		protected function resetLogFileData ()
		{
			$this->logFileData = [
				'errors' => [],
				'warnings' => [],
			];
		
		}
	}
	