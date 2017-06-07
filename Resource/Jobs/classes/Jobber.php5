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
		$this->lock			= new \Model_Joblock( $this->env );
	}

	protected function getJobIdFromRequest(){
		foreach( $this->env->getRequest()->getAll() as $key => $value ){							//  iterate collected request values
			if( $value || substr( $key, 0, 2 ) === '__' || substr( $key, 0, 1 ) === '-' )			//  is option or internal value
				continue;																			//  exclude
			return $key;																			//  assume first valid part as job ID
		}
		return FALSE;
	}

	public function getJobs(){
		return $this->jobs;
	}

	public function loadJobs( $modes, $strict = TRUE ){
		$this->jobs	= array();
		foreach( self::readJobXmlFile( $modes )->jobs as $jobId => $job ){
			if( $strict && array_key_exists( $jobId, $this->jobs ) )
				throw new \DomainException( 'Duplicate job ID "'.$jobId.'"' );
			$this->jobs[$jobId]	= $job;
		}
		foreach( self::readJobJsonFile( $modes )->jobs as $jobId => $job ){
			if( $strict && array_key_exists( $jobId, $this->jobs ) )
				throw new \DomainException( 'Duplicate job ID "'.$jobId.'"' );
			$this->jobs[$jobId]	= $job;
		}
		ksort( $this->jobs/*, SORT_NATURAL | SORT_FLAG_CASE*/ );
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

	/**
	 *	@deprecated		use logError instead and return status in called job method
	 */
	public function logErrorAndExit( $message ){
		$this->logError( $message );
		exit;
	}

	protected function out( $message = '' ){
		print( $message.PHP_EOL );
	}

	/**
	 *	@todo  			convert module job files from XML to JSON
	 */
	protected function readJobJsonFile( $modes = array() ){
		$map			= new \stdClass();
		$map->jobs		= array();
		$map->intervals	= array();
		$index			= new \FS_File_RegexFilter( 'config/jobs/', '/\.json$/i' );
		foreach( $index as $file ){
			$json	= \FS_File_JSON_Reader::load( $file->getPathname(), FALSE );
			foreach( $json as $jobId => $job ){
				$job->id	= $jobId;
				$job->mode = explode( ",", $job->mode );
				if( $modes && !array_intersect( $job->mode, $modes ) )
					continue;
				$map->jobs[$jobId]	= $job;
			}
		}
		return $map;
	}

	public static function readJobXmlFile( $modes = array() ){
		$map			= new \stdClass();
		$map->jobs		= array();
		$map->intervals	= array();
		$index			= new \FS_File_RegexFilter( 'config/jobs/', '/\.xml$/i' );
		foreach( $index as $file ){
			$xml	= \XML_ElementReader::readFile( $file->getPathname() );
			foreach( $xml->job as $job ){
				$jobObj			= new \stdClass();
				$jobObj->mode	= array( 'dev' );
				$jobObj->id			= $job->getAttribute( 'id' );
				foreach( $job->children() as $nodeName => $node ){
					$value	= (string) $node;
					if( $nodeName == 'mode' )
						$value	= explode( ',' , $value );
					$jobObj->$nodeName	= $value;
				}
				if( $modes && !array_intersect( $jobObj->mode, $modes ) )
					continue;
				if( array_key_exists( $jobObj->id, $map->jobs ) )
					throw new \DomainException( 'Duplicate job ID "'.$jobObj->id.'"' );
				$map->jobs[$jobObj->id] = $jobObj;
			}
		}
		return $map;
	}


	/**
	 *	Executes possible job call.
	 *	@return		integer
	 */
	public function run() {
		$jobId	= $this->getJobIdFromRequest();
		if( !$jobId ){
			$this->logError( 'Job ID needed.' );													//  log error
			return -1;																				//  quit with negative status
		}

		$arguments	= array();																		//  prepare empty arguments list
		foreach( $this->env->getRequest()->getAll() as $key => $value )								//  iterate all parsed console request parts
			if( $key !== $jobId && substr( $key, 0, 2 ) !== '__' )									//  request part is neither jib ID nor of internal scope
				$arguments[$key]	= $value;														//  carry remaining request part as argument
		$this->runJob( $jobId, $arguments );
	}

	public function runJob( $jobId, $parameters = array() ){
		if( !array_key_exists( $jobId, $this->jobs ) ){												//  job ID is not in list of registered jobs
			$this->logError( 'Job with ID "'.$jobId.'" is not existing.' );							//  log error
			return -1;																				//  quit with negative status
		}
		$job		= $this->jobs[$jobId];															//  get job data from job list by job ID
		$classArgs	= array( $this->env, $this );													//  prepare job class instance arguments
//		$arguments	= array_keys( $parameters );													//
		$arguments	= array( $parameters );															//
		$className	= 'Job_'.$job->class;															//  build job class name
		if( !class_exists( '\\'.$className ) ){															//  job class is not existing
			$this->logError( 'Job class "'.$className.'" is not existing.' );						//  log error
			return -1;																				//  quit with negative status
		}
		try{																						//  try to ...
			if( $this->lock->isLocked( $job->class, $job->method ) )								//  job is locked (=still running)
				return 0;																			//  quit with neutral status
			$this->lock->lock( $job->class, $job->method );											//  set lock on job
			$jobObject	= \Alg_Object_Factory::createObject( '\\'.$className, $classArgs );			//  ... create job class instance with arguments
			$jobObject->noteJob( $job->class, $job->method );										//  ... inform job class instance about method to be called
			$result		= \Alg_Object_MethodFactory::call( $jobObject, $job->method, $arguments );	//  ... call job method of job class instance
			$this->lock->unlock( $job->class, $job->method );										//  remove job lock
			if( is_integer( $result ) ){
				return $result;
			if( strlen( trim( $result ) ) )															//  handle old return strings @deprecated
				foreach( explode( "\n", trim( $result ) ) as $line )								//  handle each result line
					$this->log( $line );															//  by logging
			}
			return 1;																				//  quit with positive status
		}
		catch( \Exception $e ){																		//  on exception
			$this->lock->unlock( $job->class, $job->method );										//  remove job lock
			$this->logError( $e->getMessage()."@".$e->getFile().":".$e->getLine() );				//  log exception
			return -1;																				//  quit with negative status
		}
	}
}
?>
