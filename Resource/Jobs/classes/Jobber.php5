<?php
/**
 *	Chat maintainer.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media (https://ceusmedia.de/)
 */
/**
 *	Chat maintainer.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@extends		CMF_Hydrogen_Application_Console
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media (https://ceusmedia.de/)
 */
class Jobber extends \CMF_Hydrogen_Application_Console
{
	protected $jobs	= array();
	protected $lock;
	protected $modelJob;
	protected $modelLock;
	protected $pathLogs;
	protected $pathJobs;

	public function __construct( \CMF_Hydrogen_Environment $env = NULL )
	{
		parent::__construct( $env, TRUE );															//  construct parent and call __onInit
		$config				= $this->env->getConfig();
		$this->pathLogs		= $config->get( 'path.logs' );
		$this->pathJobs		= 'config/jobs/';
		$this->modelJob		= new \Model_Job( $this->env );
		$this->modelJob->setFormat( Model_Job::FORMAT_XML );
		$this->modelLock	= new \Model_Joblock( $this->env );
	}

	public function loadJobs( array $modes, bool $strict = TRUE ): self
	{
		$this->modelJob->load( $modes, $strict );
		return $this;
	}

	public function getJobs(): array
	{
		return $this->modelJob->getAll();
	}

	public function log( string $message ): self
	{
		$line	= sprintf( '%s: Jobber: %s', date( "Y-m-d H:i:s" ), $message );
		error_log( $line.PHP_EOL, 3, $this->pathLogs.'jobs.log' );
		return $this;
	}

	public function logError( string $message ): self
	{
		$line	= sprintf( '%s: Jobber: %s', date( "Y-m-d H:i:s" ), $message );
		error_log( $line.PHP_EOL, 3, $this->pathLogs."jobs.error.log" );
		$this->out( "Exception: ".$message.PHP_EOL );
		return $this;
	}

	public function logException( Exception $exception ): self
	{
		$message	= $exception->getMessage().'@'.$exception->getFile().':'.$exception->getLine();
		$this->logError( /*$this->getLogPrefix().*/$message );
		return $this;
	}

	/**
	 *	Executes possible job call.
	 *	@return		integer
	 */
	public function run(): int
	{
		$jobId	= $this->getJobIdFromRequest();
		if( !strlen( trim( $jobId ) ) ){
			$this->out( '' );
			$this->out( 'Usage: ./job.php [job]' );
			$this->out( '' );
			$this->out( 'List of available jobs:' );
			$jobId	= 'Job.list';
		}
		else{
			$commands	= $this->env->getRequest()->get( 'commands' );
			$commands	= array_slice( $commands, 1 );
			$this->env->getRequest()->set( 'commands', $commands );
		}
		return $this->runJob( $jobId );
	}

	/*  --  PROTECTED  --  */
	protected function getJobIdFromRequest()
	{
		if( $this->env->getRequest()->get( 0 ) )
			return $this->env->getRequest()->get( 0 );
		$commands	= $this->env->getRequest()->get( 'commands' );
		if( $commands )
			return array_shift( $commands );
		return FALSE;
	}

	protected function out( string $message = '' )
	{
		print( $message.PHP_EOL );
	}

	protected function runJob( string $jobId ): int
	{
		$commands	= $this->env->getRequest()->get( 'commands' );
		$parameters	= $this->env->getRequest()->get( 'parameters' );
		if( !$this->modelJob->has( $jobId ) ){														//  job ID is not in list of registered jobs
			$this->logError( 'Job with ID "'.$jobId.'" is not existing.' );							//  log error
			return -1;																				//  quit with negative status
		}
		$job		= $this->modelJob->get( $jobId );												//  get job data from job list by job ID
		$classArgs	= array( $this->env, $this );													//  prepare job class instance arguments
		$arguments	= array( $commands, $parameters );															//
		$className	= 'Job_'.$job->class;															//  build job class name
		if( !class_exists( '\\'.$className ) ){														//  job class is not existing
			$this->logError( 'Job class "'.$className.'" is not existing.' );						//  log error
			return -1;																				//  quit with negative status
		}
		try{																						//  try to ...
			if( !$job->multiple && $this->modelLock->isLocked( $job->class, $job->method ) )		//  job is locked (=still running)
				return 0;																			//  quit with neutral status
			$this->modelLock->lock( $job->class, $job->method );									//  set lock on job
			$jobObject	= \Alg_Object_Factory::createObject( '\\'.$className, $classArgs );			//  ... create job class instance with arguments
			$jobObject->noteJob( $job->class, $job->method );										//  ... inform job instance about method to be called
			$jobObject->noteArguments( $commands, $parameters );									//  ... inform job instance about request arguments
			$result		= \Alg_Object_MethodFactory::call( $jobObject, $job->method, $arguments );	//  ... call job method of job instance
			$this->modelLock->unlock( $job->class, $job->method );									//  remove job lock
			if( is_integer( $result ) ){
				return $result;
			if( strlen( trim( $result ) ) )															//  handle old return strings @deprecated
				foreach( explode( "\n", trim( $result ) ) as $line )								//  handle each result line
					$this->log( $line );															//  by logging
			}
			return 1;																				//  quit with positive status
		}
		catch( Throwable $t ){																		//  on throwable error or exception
			$this->modelLock->unlock( $job->class, $job->method );									//  remove job lock
			$this->logError( $t->getMessage()."@".$t->getFile().":".$t->getLine() );				//  log throwable error or exception
			return -1;																				//  quit with negative status
		}
	}
}
