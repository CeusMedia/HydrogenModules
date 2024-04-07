<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\CLI\Output\Progress as ProgressOutput;
use CeusMedia\HydrogenFramework\Environment;

class Job_Abstract
{
	/**	@var	Environment				$env			Environment object */
	protected Environment $env;

	protected string $logFile;

	/**	@var	?string					$jobClass		Class name of inheriting job */
	protected ?string $jobClass			= NULL;

	/**	@var	?string					$jobMethod		Method name of job task */
	protected ?string $jobMethod		= NULL;

	/**	@var	?string					$jobModuleId	Module ID of inheriting job */
	protected ?string $jobModuleId		= NULL;

	protected array $commands			= [];
	protected bool $dryMode			= FALSE;
	protected bool $verbose			= FALSE;
	protected Dictionary $parameters;

	protected ?string $versionModule	= NULL;
	protected ?ProgressOutput $progress	= NULL;

	protected $results;

	/**	@var		Jobber				$manager		Job manager instance */
	protected $manager;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment			$env		Environment instance
	 *	@param		Jobber				$manager	Job manage instance
	 *	@return		void
	 */
	public function __construct( Environment $env, $manager, ?string $jobClassName = NULL, ?string $jobModuleId = NULL )
	{
		$this->env			= $env;
		$this->manager		= $manager;
		$this->logFile		= $env->getConfig()->get( 'path.logs' ).'jobs.log';
		$this->parameters	= new Dictionary();
		if( $jobClassName )
			$this->setJobClassName( $jobClassName );
		if( $jobModuleId )
			$this->setJobModuleId( $jobModuleId );
		$this->__onInit();
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		???
	 */
	public function getResults()
	{
		return $this->results;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		array		$commands		...
	 *	@param		array		$parameters		...
	 *	@return		self
	 */
	public function noteArguments( array $commands = [], array $parameters = [] ): self
	{
		$this->commands		= array_diff( $commands, ['dry', 'verbose'] );
		$this->parameters	= new Dictionary( $parameters );
		$this->dryMode		= in_array( 'dry', $commands );
		$this->verbose		= in_array( 'verbose', $commands );
		return $this;
	}

	/**
	 *	Set information about inheriting job for output or logging.
	 *	@access		public
	 *	@param		string		$className		Class name of inheriting job
	 *	@param		string		$jobName		Method name of job task
	 *	@param		?string		$moduleId		Module ID of inheriting job
	 *	@return		self
	 */
	public function noteJob( string $className, string $jobName, string $moduleId = NULL ): self
	{
		$this->setJobClassName( $className );
		$this->jobMethod	= $jobName;
		$this->setJobModuleId( $moduleId );
		return $this;
	}

	/**
	 *	@access		public
	 *	@param		?string		$message		Message to be displayed
	 *	@return		self
	 *	@todo		make protected
	 */
	public function out( ?string $message = NULL ): self
	{
		print( $message."\n" );
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	Initialization, called at the end of construction.
	 *	@access		protected
	 */
	protected function __onInit(): void
	{
	}

	/**
	 *	Returns prefix for log lines depending on set job class and method.
	 *	@access		protected
	 *	@return		string
	 */
	protected function getLogPrefix(): string
	{
		$label		= $this->jobClass;
		if( $this->jobMethod )
			$label	.= '.'.$this->jobMethod;
		return $label.': ';
	}

	/**
	 *	Write message to log.
	 *	@access		protected
	 *	@param		string		$message		Message to log
	 *	@return		self
	 */
	protected function log( string $message ): self
	{
//		$this->manager->log( $this->getLogPrefix().$message );
		return $this;
	}

	/**
	 *	Write error message to log.
	 *	@access		protected
	 *	@param		string		$message		Error message to log
	 *	@return		self
	 */
	protected function logError( string $message ): self
	{
		$this->manager->logError( $this->getLogPrefix().$message );
		return $this;
	}

	/**
	 *	Log caught exception.
	 *	@access		protected
	 *	@param		Throwable	$exception		Exception to be logged
	 *	@return		self
	 */
	protected function logException( Throwable $exception ): self
	{
		$this->manager->logException( $exception );
		return $this;
	}

	/**
	 *	Set class name of inheriting job for information output or logging.
	 *	@access		protected
	 *	@param		string		$jobClassName	Class name of inheriting job
	 *	@return		self
	 */
	protected function setJobClassName( string $jobClassName ): self
	{
		$this->jobClass		= strlen( trim( $jobClassName ) ) ? $jobClassName : get_class( $this );
		return $this;
	}

	/**
	*	Set module of inheriting job for information output or logging.
	 *	@access		protected
	 *	@param		?string		$jobModuleId	Module ID of inheriting job
	 *	@return		self
	 */
	protected function setJobModuleId( ?string $jobModuleId ): self
	{
		$this->jobModuleId		= strlen( trim( $jobModuleId ?? '' ) ) ? $jobModuleId : NULL;
		$this->versionModule	= NULL;
		if( $this->jobModuleId && $this->env->getModules()->has( $this->jobModuleId ) ){
			$module	= $this->env->getModules()->get( $this->jobModuleId );
			$this->versionModule	= $module->version->installed;
		}
		return $this;
	}

	/**
	 *	Show or update progress bar.
	 *	@access		protected
	 *	@param		integer		$count			Currently reached step of all steps
	 *	@param		integer		$total			Number of all steps of progress bar
	 *	@param		string		$sign			Character to display progress within bar
	 *	@param		integer		$length			Length of progress bar
	 *	@return		self
	 */
	protected function showProgress( int $count, int $total, string $sign = '.', int $length = 60 ): self
	{
		if( $count === 0 ){
			$this->progress	= new ProgressOutput();
			$this->progress->setTotal( $total )->start();
		}
		else if( $count === $total ){
			if( $this->progress ){
				$this->progress->update( $count );
				$this->progress->finish();
			}
		}
		else{
			if( !$this->progress ){
				$this->progress	= new ProgressOutput();
				$this->progress->setTotal( $total );
				$this->progress->start();
			}
			$this->progress->update( $count );
		}
		return $this;
	}

	/**
	 *	Display caught error messages.
	 *	@access		protected
	 *	@param		string		$taskName		Name of task producing errors
	 *	@param		array		$errors			List of error messages to show
	 *	@return		self
	 */
	protected function showErrors( string $taskName, array $errors ): self
	{
		if( 0 !== count( $errors ) ){
			$this->out( 'Errors on '.$taskName.':' );
			foreach( $errors as $mailId => $message )
				$this->out( '- '.$mailId.': '.$message );
		}
		return $this;
	}
}
