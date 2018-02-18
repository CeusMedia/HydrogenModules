<?php
/**
 *	Chat maintainer.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Maintainer.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
/**
 *	Chat maintainer.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@extends		CMF_Hydrogen_Application_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Maintainer.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
class Jobber extends \CMF_Hydrogen_Application_Console {

	protected $lock;
	protected $jobs	= array();

	public function __construct( \CMF_Hydrogen_Environment_Abstract $env = NULL ){
		parent::__construct( $env, TRUE );															//  construct parent and call __onInit
		$config				= $this->env->getConfig();
		$this->pathLogs		= $config->get( 'path.logs' );
		$this->pathJobs		= 'config/jobs/';
		$this->modelJob		= new \Model_Job( $this->env );
		$this->modelLock	= new \Model_Joblock( $this->env );
	}

	public function loadJobs( $modes, $strict = TRUE ){
		$this->modelJob->load( $modes, $strict );
	}

	protected function getJobIdFromRequest(){
		if( $this->env->getRequest()->get( 0 ) )
			return $this->env->getRequest()->get( 0 );
		$commands	= $this->env->getRequest()->get( 'commands' );
		if( $commands )
			return array_shift( $commands );
		return FALSE;
	}

	public function getJobs(){
		return $this->modelJob->getAll();
	}

	public function log( $message ){
		error_log( date( "Y-m-d H:i:s" ).": Jobber: ".$message."\n", 3, $this->pathLogs."jobs.log" );
	}

	public function logError( $message ){
		error_log( date( "Y-m-d H:i:s" ).": Jobber: ".$message.PHP_EOL, 3, $this->pathLogs."jobs.error.log" );
		$this->out( "Exception: ".$message."\n" );
	}

	public function logException( $exception ){
		$message	= $exception->getMessage().'@'.$exception->getFile().':'.$exception->getLine();
		$this->logError( /*$this->getLogPrefix().*/$message );
	}

	protected function out( $message = '' ){
		print( $message.PHP_EOL );
	}

	/**
	 *	Executes possible job call.
	 *	@return		integer
	 */
	public function run() {
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
		$this->runJob( $jobId );
	}

	protected function runJob( $jobId ){
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
			$jobObject->noteJob( $job->class, $job->method );										//  ... inform job class instance about method to be called
			$result		= \Alg_Object_MethodFactory::call( $jobObject, $job->method, $arguments );	//  ... call job method of job class instance
			$this->modelLock->unlock( $job->class, $job->method );									//  remove job lock
			if( is_integer( $result ) ){
				return $result;
			if( strlen( trim( $result ) ) )															//  handle old return strings @deprecated
				foreach( explode( "\n", trim( $result ) ) as $line )								//  handle each result line
					$this->log( $line );															//  by logging
			}
			return 1;																				//  quit with positive status
		}
		catch( \Exception $e ){																		//  on exception
			$this->modelLock->unlock( $job->class, $job->method );									//  remove job lock
			$this->logError( $e->getMessage()."@".$e->getFile().":".$e->getLine() );				//  log exception
			return -1;																				//  quit with negative status
		}
	}
}
?>
