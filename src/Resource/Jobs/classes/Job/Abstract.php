<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\CLI\Output\Progress as ProgressOutput;
use CeusMedia\Common\Exception\NotSupported as NotSupportedException;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Log;

class Job_Abstract
{
	/**	@var	Environment				$env			Environment object */
	protected Environment $env;

	/**	@var	?string					$jobClass		Class name of inheriting job */
	protected ?string $jobClass			= NULL;

	/**	@var	?string					$jobMethod		Method name of job task */
	protected ?string $jobMethod		= NULL;

	/**	@var	?string					$jobModuleId	Module ID of inheriting job */
	protected ?string $jobModuleId		= NULL;

	protected array $commands			= [];
	protected bool $dryMode				= FALSE;
	protected bool $verbose				= FALSE;
	protected Dictionary $parameters;

	protected ?string $versionModule	= NULL;
	protected ?ProgressOutput $progress	= NULL;

	protected Entity_Job_Result|array $results	= [];

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment			$env		Environment instance
	 *	@return		void
	 */
	public function __construct( Environment $env, ?string $jobClassName = NULL, ?string $jobModuleId = NULL )
	{
		$this->env			= $env;
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
	 *	@return		Entity_Job_Result|array
	 *	@todo		Refactor results array to list of result entities
	 */
	public function getResults(): Entity_Job_Result|array
	{
		return $this->results;
	}

	/**
	 *	@param		int		$status
	 *	@param		int		$count
	 *	@param		object|array|NULL $data
	 *	@return		void
	 *	@throws		ReflectionException		if reflection of entity class property failed
	 *	@throws		NotSupportedException	entity property is typed as union or intersection
	 */
	protected function setResult( int $status, int $count, object|array $data = NULL ): void
	{
		$this->results	= Entity_Job_Result::fromArray( [
			'status'	=> $status,
			'count'		=> $count,
			'data'		=> $data
		] );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		array		$commands		...
	 *	@param		array		$parameters		...
	 *	@return		static
	 */
	public function noteArguments( array $commands = [], array $parameters = [] ): static
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
	 *	@return		static
	 */
	public function noteJob( string $className, string $jobName, string $moduleId = NULL ): static
	{
		$this->setJobClassName( $className );
		$this->jobMethod	= $jobName;
		$this->setJobModuleId( $moduleId );
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
	 *	@param		string		$requestParameterName
	 *	@param		string		$default
	 *	@return		DateTime
	 *	@throws		DateInvalidOperationException
	 *	@throws		DateMalformedIntervalStringException
	 */
	protected function getAgeThreshold( string $requestParameterName, string $default = '1M' ): DateTime
	{
		$value	= $this->parameters->get( $requestParameterName, $default );
		$period	= $this->getPeriod( $value ) ?? $this->getPeriod( $default );
		return date_create()->sub( new DateInterval( $period ) );
	}

	/**
	 *	Returns limits part for database listings, using Model_*::getAll.
	 *	Therefor, reads request parameters --limit and --offset.
	 *	Sets defaults if not requested: default limit is 1000 and default offset is 0.
	 *	Limit will be at least 1 and offset will be at least 0.
	 *	@return		array<int,int>
	 */
	protected function getLimitsFromRequest( int $defaultLimit = 1000, int $defaultOffset = 0 ): array
	{
		return [
			max( 0, (int) $this->getParameterFromRequest( '--offset', $defaultOffset ) ),
			max( 1, (int) $this->getParameterFromRequest( '--limit', $defaultLimit ) ),
		];
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
	 *	Returns request parameter by name or sets given default.
	 *	All space characters will be removed.
	 *	@param		string		$requestParameterName
	 *	@param		string		$default
	 *	@return		string
	 */
	protected function getParameterFromRequest( string $requestParameterName, string $default = '*' ): string
	{
		$value	= $this->parameters->get( $requestParameterName, $default );
		return preg_replace( '/\s/', '', $value );
	}

	/**
	 *	Returns date period string.
	 *	Examples: 1Y (1 year), 2M (2 months), 3D (3 days), 4h (4 hours), 5m (5 minutes), 6s (6 seconds)
	 *	Attention: Combination is NOT supported.
	 *	@param		string		$value
	 *	@return		?string
	 *	@see		https://www.php.net/manual/en/dateinterval.construct.php
	 */
	protected function getPeriod( string $value ): ?string
	{
		if( !preg_match( '/^[0-9]+[YMDhms]$/', $value ) )
			return NULL;
		$number	= preg_replace( '/[YMDhms]$/', '', $value );
		$unit	= preg_replace( '/^[0-9]+/', '', $value );
		$prefix	= in_array( $unit, ['Y', 'M', 'D'], TRUE ) ? 'P' : 'PT';
		return $prefix.$number.strtoupper( $unit );
	}

	/**
	 *	Write message to log.
	 *	@access		protected
	 *	@param		string		$message		Message type as string (debug,info,note,warn,error), @see Log::TYPE_*
	 *	@param		string		$logLevel		Message to log
	 *	@return		static
	 */
	protected function log( string $message, string $logLevel = Log::TYPE_INFO ): static
	{
		$this->env->getLog()->log( $logLevel, $this->getLogPrefix().$message );
		return $this;
	}

	/**
	 *	Write error message to log.
	 *	@access		protected
	 *	@param		string		$message		Error message to log
	 *	@return		static
	 */
	protected function logError( string $message ): static
	{
		return $this->log( $message, Log::TYPE_ERROR );
	}

	/**
	 *	Log caught exception.
	 *	@access		protected
	 *	@param		Throwable	$exception		Exception to be logged
	 *	@return		static
	 */
	protected function logException( Throwable $exception ): static
	{
		$this->env->getLog()->logException( $exception );
		return $this;
	}

	/**
	 *	@access		public
	 *	@param		?string		$message		Message to be displayed
	 *	@return		static
	 */
	protected function out( ?string $message = NULL ): static
	{
		print( $message.PHP_EOL );
		return $this;
	}

	/**
	 *	Set class name of inheriting job for information output or logging.
	 *	@access		protected
	 *	@param		string		$jobClassName	Class name of inheriting job
	 *	@return		static
	 */
	protected function setJobClassName( string $jobClassName ): static
	{
		$this->jobClass	= '' !== trim( $jobClassName ) ? $jobClassName : get_class( $this );
		return $this;
	}

	/**
	 *	Set module of inheriting job for information output or logging.
	 *	@access		protected
	 *	@param		?string		$jobModuleId	Module ID of inheriting job
	 *	@return		static
	 */
	protected function setJobModuleId( ?string $jobModuleId ): static
	{
		$this->jobModuleId		= '' !== trim( $jobModuleId ?? '' ) ? $jobModuleId : NULL;
		$this->versionModule	= NULL;
		if( $this->jobModuleId && $this->env->getModules()->has( $this->jobModuleId ) ){
			$module	= $this->env->getModules()->get( $this->jobModuleId );
			$this->versionModule	= $module->version->installed;
		}
		return $this;
	}

	/**
	 *	Display caught error messages.
	 *	@access		protected
	 *	@param		string		$taskName		Name of task producing errors
	 *	@param		array		$errors			List of error messages to show
	 *	@return		static
	 */
	protected function showErrors( string $taskName, array $errors ): static
	{
		if( [] !== $errors ){
			$this->out( 'Errors on '.$taskName.':' );
			foreach( $errors as $mailId => $message )
				$this->out( '- '.$mailId.': '.$message );
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
	 *	@return		static
	 */
	protected function showProgress( int $count, int $total, string $sign = '.', int $length = 60 ): static
	{
		if( NULL === $this->progress ){
			$this->progress	= new ProgressOutput();
			$this->progress->setTotal( $total )->start();
		}
		$this->progress->update( $count );
		if( $count === $total )
			$this->progress->finish();
		return $this;
	}
}
