<?php

	require_once 'BrahmsLogParser.php';
	
	class CrsLogParser extends BrahmsLogParser
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
		
		// Same as Brahms
		public function __construct ($main)
		{
			$this->logDir = $main->getLogDir();
			$this->outputDir = $main->getOutputDir();
		}
		
	}
	