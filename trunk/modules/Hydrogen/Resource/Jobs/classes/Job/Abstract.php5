<?php
class Job_Abstract{
	
	/**	@var	CMF_Hydrogen_Environment_Abstract	$env		Environment object */
	protected $env;
	protected $lockExt		= ".lock";
	protected $lockPath		= "config/jobs/";
	protected $logFile;
	protected $jobClass;
	protected $jobMethod;
	

	public function __construct( $env ){
		$this->env		= $env;
		$this->logFile	= $env->getConfig()->get( 'path.logs' ).'jobs.log';
		$this->__onInit();
	}
	
	protected function __onInit(){
	}

	public function noteJob( $className, $jobName ){
		$this->jobClass		= $className;
		$this->jobMethod	= $jobName;
	}
	
	protected function isLocked( $name, $maxTime = 0 ){
		$pathLock	= $this->lockPath.$this->jobClass.'.'.$this->jobMethod.$this->lockExt;
		if( $maxTime && ( time() - filemtime( $pathLock ) ) > $maxTime )
			$this->unlock( $pathLock );
		return file_exists( $this->lockPath.$name.$this->lockExt );
	}

	protected function lock( $name ){
		touch( $this->lockPath.$this->jobClass.'.'.$this->jobMethod.$this->lockExt );
	}

	protected function log( $message, $status = 0 ){
		$job		= $this->jobClass ? $this->jobClass.'.'.$this->jobMethod.': ' : '';
		$prefix		= time().': '.$job;
		$message	= str_replace( array( "\n", "\t" ), " ", $message );
		error_log( $prefix.$message."\n", 3, $this->logFile );
	}

	public function out( $message ){
		print( $message."\n" );
	}

	protected function unlock( $name ){
		unlink( $this->lockPath.$this->jobClass.'.'.$this->jobMethod.$this->lockExt );
	}
}
?>
