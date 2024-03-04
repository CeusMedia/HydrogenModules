<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

class Logic_Work_Timer
{
	protected Environment $env;
	protected Dictionary $session;
	protected static ?Logic_Work_Timer $instance	= NULL;
	protected Model_Work_Timer $modelTimer;
	protected int|string|NULL $userId;

	/**
	 *	@param		int|string		$timerId
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( int|string $timerId ): ?object
	{
		return $this->checkTimerId( $timerId );
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array
	 */
	public function index( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelTimer->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@param		Environment		$env
	 *	@return		static
	 */
	public static function getInstance( Environment $env ): self
	{
		if( !self::$instance )
			self::$instance		= new Logic_Work_Timer( $env );
		return self::$instance;
	}

	/**
	 *	@param		int|string		$timerId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function pause( int|string $timerId ): void
	{
		$timer	= $this->checkTimerId( $timerId );
		if( $timer->status != 2 ){
			$this->modelTimer->edit( $timerId, [
				'status'		=> 2,
				'secondsNeeded'	=> $timer->secondsNeeded + ( time() - $timer->modifiedAt ),
				'modifiedAt'	=> time(),
			] );
			$payload	= ['timer' => $this->checkTimerId( $timerId )];
			$this->env->getCaptain()->callHook( 'Work_Timer', 'onPauseTimer', $this, $payload );
		}
	}

	/**
	 *	@param		int|string		$timerId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function start( int|string $timerId ): void
	{
		$timer		= $this->checkTimerId( $timerId );
		if( $timer->status != 1 ){
			$active 	= $this->modelTimer->getByIndices( [
				'workerId'	=> $this->userId,
				'status'	=> 1
			] );
			if( $active )
				$this->pause( $active->workTimerId );
			$this->modelTimer->edit( $timerId, array( 'status' => 1, 'modifiedAt' => time() ) );
			$payload	= ['timer' => $this->checkTimerId( $timerId )];
			$this->env->getCaptain()->callHook( 'Work_Timer', 'onStartTimer', $this, $payload );
		}
	}

	/**
	 *	@param		int|string		$timerId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function stop( int|string $timerId ): void
	{
		$timer	= $this->checkTimerId( $timerId );
		if( $timer->status == 1 )
			$this->pause( $timerId );
		$this->modelTimer->edit( $timerId, [
			'status'		=> 3,
			'modifiedAt'	=> time(),
		] );
		$payload	= ['timer' => $this->checkTimerId( $timerId )];
		$this->env->getCaptain()->callHook( 'Work_Timer', 'onStopTimer', $this, $payload );
	}

	/**
	 *	@param		string		$moduleKey
	 *	@param		string		$moduleId
	 *	@param		array		$statuses
	 *	@return		int
	 */
	public function sumTimersOfModuleId( string $moduleKey, string $moduleId, array $statuses = [2, 3] ): int
	{
		$indices	= ['module' => $moduleKey, 'moduleId' => $moduleId, 'status' => $statuses];
		$timers		= $this->modelTimer->getAllByIndices( $indices );
		$seconds	= 0;
		foreach( $timers as $timer )
			$seconds	+= $timer->secondsNeeded;
		return $seconds;
	}

	/**
	 *	@param		array		$conditions
	 *	@return		int
	 */
	public function countTimers( array $conditions ): int
	{
		return $this->modelTimer->count( $conditions );
	}

	//  --  PROTECTED  --  //

	protected function __clone(){}

	/**
	 *	@param		Environment		$env
	 */
	protected function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->session		= $this->env->getSession();
		$this->userId		= $this->session->get( 'auth_user_id' );
		$this->modelTimer	= new Model_Work_Timer( $this->env );
	}

	/**
	 *	@param		int|string		$timerId
	 *	@param		bool		$strict
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkTimerId( int|string $timerId, bool $strict = TRUE ): ?object
	{
		$timer	= $this->modelTimer->get( $timerId );
		if( $timer )
			return $timer;
		if( $strict )
			throw new InvalidArgumentException( 'Timer with ID '.$timerId.' is not existing' );
		return NULL;
	}
}
