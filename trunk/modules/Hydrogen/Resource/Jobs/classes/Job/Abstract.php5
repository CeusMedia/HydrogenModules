<?php
class Job_Abstract{
	
	/**	@var	CMF_Hydrogen_Environment_Abstract	$env		Environment object */
	protected $env;
	protected $logFile;
	protected $jobClass;
	protected $jobMethod;

	/**	@var		Jobber								$manager		Job manager instance */
	protected $manager;
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment instance
	 *	@param		Jobber								$manager	Job manage instance
	 *	@return		void
	 */
	public function __construct( $env, $manager, $jobClassName = NULL ){
		$this->env			= $env;
		$this->manager		= $manager;
		$this->logFile		= $env->getConfig()->get( 'path.logs' ).'jobs.log';
		$this->jobClass		= $jobClassName === NULL ? get_class( $this ) : $jobClassName;
		$this->__onInit();
	}
	
	protected function __onInit(){
	}

	protected function getLogPrefix(){
		$label		= $this->jobClass;
		if( $this->jobMethod )
			$label	.= '.'.$this->jobMethod;
		return $label.': ';
	}
	public function noteJob( $className, $jobName ){
		$this->jobClass		= $className;
		$this->jobMethod	= $jobName;
	}
	
	protected function log( $message ){
		$this->manager->log( $this->getLogPrefix().$message );
	}

	protected function logError( $message ){
		$this->manager->logError( $this->getLogPrefix().$message );
	}

	protected function logException( $exception ){
		$this->manager->logException( $exception );
	}
	
	public function out( $message ){
		print( $message."\n" );
	}
}
?>
