<?php
/**
 *	Chat maintainer.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\Alg\Obj\Constant as ObjectConstant;
use CeusMedia\HydrogenFramework\Application\ConsoleAbstraction as ConsoleApplication;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	Chat maintainer.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Jobber extends ConsoleApplication
{
	protected Logic_Job $logic;
	protected Model_Job $modelJob;
//	protected Model_Job_Lock $modelLock;
	protected array $jobs					= [];
	protected string $pathJobs;
	protected ?string $pathLogs;
	protected ?string $mode					= NULL;
	protected ?object $runningJob			= NULL;

	public function __construct( Environment $env = NULL )
	{
		parent::__construct( $env );															//  construct parent and call __onInit
		$config				= $this->env->getConfig();
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */

		$this->logic		= $this->env->getLogic()->get( 'Job' );
		$this->pathLogs		= $config->get( 'path.logs' );
		$this->pathJobs		= 'config/jobs/';
		$this->modelJob		= new Model_Job( $this->env );
		$this->modelJob->setFormat( Model_Job::FORMAT_XML );
//		$this->modelJob->setFormat( Model_Job::FORMAT_MODULE );
//		$this->modelLock	= new \Model_Job_Lock( $this->env );
	}

/**	not working
	public function __destruct()
	{
		if( $this->runningJob ){
			echo "Running Job: ".$this->runningJob->jobRunId.PHP_EOL;
			$this->logic->quitJobRun( $this->runningJob->jobRunId, Model_Job_Run::STATUS_TERMINATED );
		}
	}*/

	/**
	 *	@param		array		$modes
	 *	@param		bool		$strict
	 *	@return		self
	 */
	public function loadJobs( array $modes, bool $strict = TRUE ): self
	{
		$this->modelJob->load( $modes, $strict );
		return $this;
	}

	/**
	 *	@param		array		$conditions
	 *	@return		array
	 */
	public function getJobs( array $conditions = [] ): array
	{
		if( $this->mode && !isset( $conditions['mode'] ) )
			$conditions['mode']	= $this->mode;
		return $this->modelJob->getAll( $conditions );
	}

	/**
	 *	@param		string		$message
	 *	@return		self
	 */
	public function log( string $message ): self
	{
		$line	= sprintf( '%s: Jobber: %s', date( "Y-m-d H:i:s" ), $message );
		error_log( $line.PHP_EOL, 3, $this->pathLogs.'jobs.log' );
		return $this;
	}

	/**
	 *	@param		string		$message
	 *	@return		self
	 */
	public function logError( string $message ): self
	{
		$line	= sprintf( '%s: Jobber: %s', date( "Y-m-d H:i:s" ), $message );
		error_log( $line.PHP_EOL, 3, $this->pathLogs."jobs.error.log" );
		$this->out( "Exception: ".$message.PHP_EOL );
		return $this;
	}

	/**
	 *	@param		Throwable		$t
	 *	@return		self
	 */
	public function logException( Throwable $t ): self
	{
		$message	= $t->getMessage().'@'.$t->getFile().':'.$t->getLine().PHP_EOL.$t->getTraceAsString();
		$this->logError( /*$this->getLogPrefix().*/$message );
		return $this;
	}

	/**
	 *	Executes possible job call.
	 *	@return		integer
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function run(): int
	{
		$jobId	= $this->getJobIdFromRequest();

		if( strlen( trim( $jobId ) ) ){
			$job	= $this->logic->getDefinitionByIdentifier( $jobId );
			if( $job ){
				$commands	= $this->env->getRequest()->get( 'commands' );
				$commands	= array_slice( $commands, 1 );
				$this->env->getRequest()->set( 'commands', $commands );
				return $this->runJobManually( $job );
			}
		}
		$this->out();
		$this->out( 'Usage: ./job.php [job]' );
		$this->out();
		$this->out( 'List of available jobs:' );
		$availableJobs	= $this->logic->getDefinitions( [], ['identifier' => 'ASC'] );
		foreach( $availableJobs as $availableJob )
			$this->out(' - '.$availableJob->identifier );
		return 0;
	}

	/**
	 *	@param		string		$mode
	 *	@return		self
	 */
	public function setMode( string $mode ): self
	{
		$this->mode	= $mode;
		return $this;
	}

	/*  --  PROTECTED  --  */

	/**
	 *	@return		false|mixed|null
	 */
	protected function getJobIdFromRequest()
	{
		if( $this->env->getRequest()->get( 0 ) )
			return $this->env->getRequest()->get( 0 );
		$commands	= $this->env->getRequest()->get( 'commands' );
		if( $commands )
			return array_shift( $commands );
		return FALSE;
	}

	protected function out( string $message = '' ): void
	{
		print( $message.PHP_EOL );
	}

	/**
	 *	@param		object		$job
	 *	@return		int
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function runJobManually( object $job ): int
	{
		$commands			= $this->env->getRequest()->get( 'commands' );
		$parameters			= $this->env->getRequest()->get( 'parameters' );
		$jobRunConstants	= new ObjectConstant( Model_Job_Run::class );
		$reportMode			= NULL;
		$reportChannel		= Model_Job_Run::REPORT_CHANNEL_NONE;
		$reportReceivers	= '';
		if( !empty( $parameters['--report-mode'] ) ){
			$modes	= $jobRunConstants->getAll( 'REPORT_MODE_' );
			if( !array_key_exists( strtoupper( $parameters['--report-mode'] ), $modes ) )
				throw new RangeException( 'Invalid job report mode given' );
			$reportMode	= $modes[strtoupper( $parameters['--report-mode'] )];
		}
		if( !empty( $parameters['--report-receivers'] ) )
			$reportReceivers	= $parameters['--report-receivers'];
		if( !empty( $parameters['--report-channel'] ) ){
			$channels	= $jobRunConstants->getAll( 'REPORT_CHANNEL_' );
			if( !array_key_exists( strtoupper( $parameters['--report-channel'] ), $channels ) )
				throw new RangeException( 'Invalid job report channel given' );
			$reportChannel	= $channels[strtoupper( $parameters['--report-channel'] )];
		}
		if( $reportMode && $reportReceivers && !$reportChannel )
			$reportChannel	= Model_Job_Run::REPORT_CHANNEL_MAIL;

		$options	= [
			'reportMode'		=> $reportMode,
			'reportChannel'		=> $reportChannel,
			'reportReceivers'	=> $reportReceivers,
		];
		if( !empty( $parameters['--title'] ) ){
			$options['title']	= trim( $parameters['--title'] );
		}

		$preparedJobRun	= $this->logic->prepareManuallyJobRun( $job, $options );
		if( !$preparedJobRun ){
			$this->out( 'Job not runnable at the moment. Maybe already running or blocked by an exclusive job.' );
			print_m($job);
			return 0;
		}
		$className	= 'Job_'.$job->className;														//  build job class name
		if( !class_exists( '\\'.$className ) ){														//  job class is not existing
			$this->logError( 'Job class "'.$className.'" is not existing.' );						//  log error
			return -1;																				//  quit with negative status
		}
		$this->runningJob	= $preparedJobRun;
		try{
			$result		= $this->logic->startJobRun( $preparedJobRun, $commands, $parameters );
		}
		catch( Exception $e ){
//			$cwd	= __DIR__.'/';
			$cwd	= getCwd().'/';
			$p		= $e->getPrevious() ?: $e;
			print( 'Error:     '.get_class( $p ).' thrown and not caught'.PHP_EOL );
			print( 'Message:   '.$p->getMessage().PHP_EOL );
			print( 'Location:  '.str_replace( $cwd, '', $p->getFile() ).' line #'.$p->getLine().PHP_EOL );
//			print( 'File Dir:  '.$cwd.PHP_EOL );
			print( 'Trace:'.PHP_EOL );
			print( str_replace( $cwd, '', $p->getTraceAsString() ).PHP_EOL );
			$this->runningJob	= NULL;
			return 0;
		}

		$this->runningJob	= NULL;
		return $result;
	}
}
