<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Constant as ObjectConstant;
use CeusMedia\Common\CLI\Question;
use CeusMedia\Common\FS\File\JSON\Writer as JsonFileWriter;
use CeusMedia\Common\FS\File\RegexFilter as RegexFileFilter;

class Job_Job extends Job_Abstract
{
	protected string $pathJobs		= 'config/jobs/';
	protected Logic_Job $logic;
	protected Dictionary $options;

	/**
	 *	@deprecated		Conversion from XML file to JSON file not needed anymore, since new solution is migration to module XML file + database import by jobs module
	 *	@todo			use parts of this code to implement automatic migration to module XML file
	 */
	public function convertJobConfigToJson()
	{
		$model		= new Model_Job( $this->env );
		$modes		= [];																		//  no specific modes
		$index		= new RegexFileFilter( $this->pathJobs, '/\.xml$/i' );
		foreach( $index as $file ){
			$this->out( 'Reading job XML: '.$file->getFilename() );
			$moduleJobs		= (object) [
				'moduleId'	=> NULL,
				'version'	=> NULL,
				'jobs'		=> [],
			];
			$fileJobs	= $model->readJobsFromXmlFile( $file->getPathname(), $modes );
			foreach( $fileJobs as $jobId => $jobSource ){
				$jobTarget	= (object) [
					'class'		=> $jobSource->class,
					'method'	=> $jobSource->method,
					'mode'		=> $jobSource->mode,
				];
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
			$moduleId	= Question::askStatic( 'Module ID', 'string', $default, NULL, FALSE );
			$moduleJobs->moduleId	= $moduleId;
			$targetFile	= $this->pathJobs.$moduleId.'.json';
			$this->out( 'Writing job JSON: '.$targetFile );
			JsonFileWriter::save( $targetFile, $moduleJobs, TRUE );
		}
	}

	/**
	 *	Display list of available job identifiers.
	 *	Stores list of available job definitions as result.
	 *	@access		public
	 *	@param		array		$mode		List of environment modes to filter jobs by (currently not implemented)
	 *	@todo		add old mode (= environment type [dev,test,live])
	 */
	public function index( array $mode = [] )
	{
		$conditions	= [];
//		if( $mode )
//			$conditions['mode']	= $mode;
//		$this->out( 'List of available jobs:' );
		$availableJobs	= $this->logic->getDefinitions( $conditions, ['identifier' => 'ASC'] );
		foreach( $availableJobs as $availableJob )
			$this->results[$availableJob->jobDefinitionId]	= $availableJob->identifier;
		$this->out( join( PHP_EOL, $this->results ) );
		return 1;
	}

	/**
	 *	Display information about one or more jobs.
	 *	@access		public
	 *	@param		array		$jobIdentifiers		List of job identifiers
	 *	@todo		add old mode (= environment type [dev,test,live])
	 */
	public function info( array $jobIdentifiers = [] )
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
			$constants	= new ObjectConstant( 'Model_Job_Definition' );
			$data	= [
				'Method'		=> 'Job_'.$job->className.' >> '.$job->methodName,
				'Mode'			=> strtolower( $constants->getKeyByValue( (int) $job->mode, 'MODE_' ) ),
				'Status'		=> strtolower( $constants->getKeyByValue( (int) $job->status, 'STATUS_' ) ),
			];
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
	 *	@todo		implement
	 */
	public function updateStats()
	{
		$modelStats	= new Model_Job_Statistic( $this->env );
		$modelRun	= new Model_Job_Run( $this->env );

		$now	= new DateTime();


		$lastUpdate	= $modelStats->getByIndices( [
			'span'
		] );
		if( $lastUpdate ){

		}
		else{
			$firstRun	= $modelRun->getAll(
				[],
				['jobRunId' => 'ASC'],
				[0, 1],
				['createdAt'],
			);
			if( $firstRun ){
				$nextDate	= new DateTimeImmutable();
				$nextDate->setTimestamp( $firstRun[0] );
			}
		}
	}

	protected function __onInit(): void
	{
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_cache.', TRUE );
		$this->logic	= $this->env->getLogic()->get( 'Job' );
	}
}
