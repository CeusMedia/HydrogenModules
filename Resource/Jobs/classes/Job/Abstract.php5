<?php
class Job_Abstract{

	/**	@var	CMF_Hydrogen_Environment				$env		Environment object */
	protected $env;
	protected $logFile;
	protected $jobClass;
	protected $jobMethod;

	protected $commands			= array();
	protected $dryMode			= FALSE;
	protected $parameters;

	/**	@var		Jobber								$manager		Job manager instance */
	protected $manager;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment			$env		Environment instance
	 *	@param		Jobber								$manager	Job manage instance
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment $env, $manager, $jobClassName = NULL ){
		$this->env			= $env;
		$this->manager		= $manager;
		$this->logFile		= $env->getConfig()->get( 'path.logs' ).'jobs.log';
		$this->jobClass		= $jobClassName === NULL ? get_class( $this ) : $jobClassName;
		$this->parameters	= new ADT_List_Dictionary();
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

	public function noteArguments( $commands = array(), $parameters = array() ){
		$this->commands		= $commands;
		$this->parameters	= new ADT_List_Dictionary( $parameters );
		$this->dryMode		= in_array( 'dry', (array) $commands );
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

	public function out( $message = NULL ){
		print( $message."\n" );
	}

	protected function showProgress( $count, $total, $sign = '.', $length = 60 ){
		echo $sign;
		if( $count % $length === 0 )
			echo str_pad( $count.'/'.$total, 18, " ", STR_PAD_LEFT ).PHP_EOL;
	}

	protected function showErrors( $taskName, $errors ){
		if( !$errors )
			return;
		$this->out( 'Errors:' );
		foreach( $errors as $mailId => $message )
			$this->out( '- '.$mailId.': '.$message );
	}


}
?>
