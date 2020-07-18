<?php
class Job_Job extends Job_Abstract
{
	protected $pathJobs		= 'config/jobs/';
	protected $logic;

	public function __onInit()
	{
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_cache.', TRUE );
		$this->logic	= $this->env->getLogic()->get( 'Job' );
	}

	/**
	 *	@todo		implement
	 */
	public function updateStats()
	{
		$modelStats	= new Model_Job_Statistic( $this->env );
		$modelRun	= new Model_Job_Run( $this->env );

		$now	= new DateTime();


		$lastUpdate	= $modelStats->getByIndices( array(
			'span'
		) );
		if( $lastUpdate ){

		}
		else{
			$firstRun	= $modelRun->getAll(
				array(),
				array( 'jobRunId' => 'ASC' ),
				array( 0, 1 ),
				array( 'createdAt' ),
			);
			if( $firstRun ){
				$nextDate	= new DateTimeImmutable();
				$nextDate->setTimestamp( $firstRun[0] );
			}
		}
	}

	/**
	 *	Display list of available job identifiers.
	 *	Stores list of available job definitions as result.
	 *	@access		public
	 *	@param		array		List of environment modes to filter jobs by (currently not implemented)
	 *	@todo		add old mode (= environment type [dev,test,live])
	 */
	public function index( $mode = NULL )
	{
		$conditions	= array();
//		if( $mode )
//			$conditions['mode']	= $mode;
//		$this->out( 'List of available jobs:' );
		$availableJobs	= $this->logic->getDefinitions( $conditions, array( 'identifier' => 'ASC' ) );
		foreach( $availableJobs as $availableJob )
			$this->results[$availableJob->jobDefinitionId]	= $availableJob->identifier;
		$this->out( join( PHP_EOL, $this->results ) );
		return 1;
	}

	/**
	 *	Display information about one or more jobs.
	 *	@access		public
	 *	@param		array		List of job identifiers
	 *	@todo		add old mode (= environment type [dev,test,live])
	 */
	public function info( $jobIdentifiers = NULL )
	{
		if( !count( $jobIdentifiers ) ){
			$this->out( 'No job identifier(s) given' );
			return;
		}
		foreach( $jobIdentifiers as $jobIdentifier ){
			$job	= $this->logic->getDefinitionByIdentifier( $jobIdentifier );
			if( !$job ){
				$this->out( 'Job "'.$jobIdentifier.'" is not existing' );
				continue;
			}
			if( count( $jobIdentifiers ) !== 1 )
				$this->out( 'Job: '.$jobIdentifier );
			$constants	= new Alg_Object_Constant( 'Model_Job_Definition' );
			$data	= array(
				'Method'		=> 'Job_'.$job->className.' >> '.$job->methodName,
				'Mode'			=> strtolower( $constants->getKeyByValue( (int) $job->mode, 'MODE_' ) ),
				'Status'		=> strtolower( $constants->getKeyByValue( (int) $job->status, 'STATUS_' ) ),
			);
			$arguments	= json_decode( $job->arguments );
			if( $arguments )
				$data['Arguments']	= print_m( $arguments, NULL, NULL, TRUE );
//			if( $job->interval )
//				$data['Interval']	= $job->interval;
			if( !empty( $job->deprecated ) )
				$data['Deprecated']	= $job->deprecated;
			foreach( $data as $label => $value )
				$this->out( str_pad( '- '.$label.':', 15, ' ', STR_PAD_RIGHT ).$value );
		}
	}

	/**
	 *	@deprecated		Conversion from XML file to JSON file not needed anymore, since new solution is migration to module XML file + database import by jobs module
	 *	@todo			use parts of this code to implement automatic migration to module XML file
	 */
	public function convertJobConfigToJson()
	{
		$model		= new Model_Job( $this->env );
		$modes		= array();																		//  no specific modes
		$index		= new \FS_File_RegexFilter( $this->pathJobs, '/\.xml$/i' );
		foreach( $index as $file ){
			$this->out( 'Reading job XML: '.$file->getFilename() );
			$moduleJobs		= (object) array(
				'moduleId'	=> NULL,
				'version'	=> NULL,
				'jobs'		=> array(),
			);
			$fileJobs	= $model->readJobsFromXmlFile( $file->getPathname(), $modes );
			foreach( $fileJobs as $jobId => $jobSource ){
				$jobTarget	= (object) array(
					'class'		=> $jobSource->class,
					'method'	=> $jobSource->method,
					'mode'		=> $jobSource->mode,
				);
				if( !empty( $jobSource->interval ) )
					$jobTarget->interval	= $jobSource->interval;
				if( $jobSource->deprecated )
					$jobTarget->deprecated	= $jobSource->deprecated;
				if( $jobSource->multiple )
					$jobTarget->multiple	= $jobSource->multiple;
				if( !empty( $jobSource->arguments ) )
					$jobTarget->arguments	= $jobSource->arguments;
				$moduleJobs->jobs[$jobId]	= $jobTarget;
			}
			$fileName	= preg_replace( '@\.[^.]+$@', '', $file->getFilename() );
			$default	= str_replace( ' ', '_', ucwords( str_replace( '.', ' ', $fileName ) ) );
			$moduleId	= CLI_Question::askStatic( 'Module ID', 'string', $default, NULL, FALSE );
			$moduleJobs->moduleId	= $moduleId;
			$targetFile	= $this->pathJobs.$moduleId.'.json';
			$this->out( 'Writing job JSON: '.$targetFile );
			FS_File_JSON_Writer::save( $targetFile, $moduleJobs, TRUE );
		}
	}
}
