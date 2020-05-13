<?php
class Job_Job_Test extends Job_Abstract{

//	protected $pathLocks	= 'config/locks/';
//	protected $pathJobs		= 'config/jobs/';
//	protected $logic;

	public function __onInit(){
//		$this->logic	= $this->env->getLogic()->get( 'Job' );

	}

	public function reflect(){
		$this->reflectCommands();
		$this->reflectParameters();
	}

	public function reflectCommands(){
//		$this->out( json_encode( $this->commands ) );
		$this->out( 'Commands: '.join( ', ', $this->commands ) );
	}

	public function reflectParameters(){
//		$this->out( json_encode( $this->parameters->getAll() ) );
		$this->out( 'Parameters: ' );
		foreach( $this->parameters as $key => $value )
			$this->out( '  '.$key.' => '.$value );
	}
}
