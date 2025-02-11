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

	public function reflect(): void
	{
		$this->reflectCommands();
		$this->reflectParameters();
	}

	public function reflectCommands(): void
	{
//		$this->out( json_encode( $this->commands ) );
		$this->out( 'Commands: '.join( ', ', $this->commands ) );
	}

	/**
	 *	Prints given parameters.
	 *	@access		public
	 *	@return		void
	 */
	public function reflectParameters(): void
	{
//		$this->out( json_encode( $this->parameters->getAll() ) );
		$this->out( 'Parameters: ' );
		foreach( $this->parameters as $key => $value )
			$this->out( '  '.$key.' => '.$value );
	}

	/**
	 *	Prints given parameters.
	 *	@access		public
	 *	@return		void
	 */
	public function throwException(): void
	{
		throw new RuntimeException( 'Test Exception' );
	}

	/**
	 *	Waits for n second.
	 *	Takes first argument as number of seconds, defaults to 1.
	 *	@access		public
	 *	@return		void
	 */
	public function wait(): void
	{
//		throw new Exception( 'Test Exception' );
		$seconds	= 1;
		if( [] !== $this->commands )
			$seconds	= max( 1, (int) current( $this->commands ) );
		$this->out( 'Waiting for '.$seconds.' seconds ...' );
		sleep( $seconds );
	}

	//  --  PROTECTED  --  //

	protected function __onInit(): void
	{
//		$this->logic	= $this->env->getLogic()->get( 'Job' );
	}
}
