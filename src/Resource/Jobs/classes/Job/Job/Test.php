<?php
/**
 *	This job exists to training working with jobs, only.
 *	Nothing productive will be done.
 *	Since jobs can be configured by call using commands and parameters,
 *	you can use these jobs to learn to use these options.
 */
class Job_Job_Test extends Job_Abstract
{
//	protected $pathLocks	= 'config/locks/';
//	protected $pathJobs		= 'config/jobs/';
//	protected $logic;

	public function reflect()
	{
		$this->reflectCommands();
		$this->reflectParameters();
	}

	public function reflectCommands()
	{
//		$this->out( json_encode( $this->commands ) );
		$this->out( 'Commands: '.join( ', ', $this->commands ) );
	}

	/**
	 *	Prints given parameters.
	 *	@access		public
	 *	@return		...
	 */
	public function reflectParameters()
	{
//		$this->out( json_encode( $this->parameters->getAll() ) );
		$this->out( 'Parameters: ' );
		foreach( $this->parameters as $key => $value )
			$this->out( '  '.$key.' => '.$value );
	}

	/**
	 *	Prints given parameters.
	 *	@access		public
	 *	@return		...
	 */
	public function throwException()
	{
		throw new RuntimeException( 'Test Exception' );
	}

	/**
	 *	Prints given parameters.
	 *	@access		public
	 *	@return		...
	 */
	public function wait()
	{
//		throw new Exception( 'Test Exception' );
		$seconds	= 1;
		if( $this->commands )
			$seconds	= (int) current( $this->commands );
		$this->out( 'Waiting for '.$seconds.' seconds ...' );
		sleep( $seconds );
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
//		$this->logic	= $this->env->getLogic()->get( 'Job' );
	}
}
