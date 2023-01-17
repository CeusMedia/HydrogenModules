<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

class Logic_Work_Timer
{
	protected Environment $env;
	protected Dictionary $session;
	protected static Logic_Work_Timer $instance;
	protected Model_Work_Timer $modelTimer;
	protected ?string $userId;

	public function get( $timerId )
	{
		return $this->checkTimerId( $timerId );
	}

	public function index( $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelTimer->getAll( $conditions, $orders, $limits );
	}

	static public function getInstance( $env ): self
	{
		if( !self::$instance )
			self::$instance = new Logic_Work_Timer( $env );
		return self::$instance;
	}

	public function pause( $timerId )
	{
		$timer	= $this->checkTimerId( $timerId );
		if( $timer->status != 2 ){
			$this->modelTimer->edit( $timerId, array(
				'status'		=> 2,
				'secondsNeeded'	=> $timer->secondsNeeded + ( time() - $timer->modifiedAt ),
				'modifiedAt'	=> time(),
			) );
			$payload	= ['timer' => $this->checkTimerId( $timerId )];
			$this->env->getCaptain()->callHook( 'Work_Timer', 'onPauseTimer', $this, $payload );
		}
	}

	public function start( $timerId )
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

	public function stop( $timerId )
	{
		$timer	= $this->checkTimerId( $timerId );
		if( $timer->status == 1 )
			$this->pause( $timerId );
		$this->modelTimer->edit( $timerId, array(
			'status'		=> 3,
			'modifiedAt'	=> time(),
		) );
		$payload	= ['timer' => $this->checkTimerId( $timerId )];
		$this->env->getCaptain()->callHook( 'Work_Timer', 'onStopTimer', $this, $payload );
	}

	public function sumTimersOfModuleId( string $moduleKey, $moduleId, array $statuses = [2, 3] ): int
	{
		$modelTimer	= new Model_Work_Timer( $this->env );
		$indices	= ['module' => $moduleKey, 'moduleId' => $moduleId, 'status' => $statuses];
		$timers		= $this->modelTimer->getAllByIndices( $indices );
		$seconds	= 0;
		foreach( $timers as $timer )
			$seconds	+= $timer->secondsNeeded;
		return $seconds;
	}

	public function countTimers( array $conditions ): int
	{
		return $this->modelTimer->count( $conditions );
	}

	//  --  PROTECTED  --  //

	protected function __clone(){}

	protected function __construct( Environment $env )
	{
		$this->env				= $env;
		$this->session			= $this->env->getSession();
		$this->userId			= $this->session->get( 'auth_user_id' );
		$this->modelTimer		= new Model_Work_Timer( $this->env );
	}

	protected function checkTimerId( $timerId, bool $strict = TRUE )
	{
		$timer	= $this->modelTimer->get( $timerId );
		if( $timer )
			return $timer;
		if( $strict )
			throw new InvalidArgumentException( 'Timer with ID '.$timerId.' is not existing' );
		return NULL;
	}
}
