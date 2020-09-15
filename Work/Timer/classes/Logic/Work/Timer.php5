<?php
class Logic_Work_Timer{

	static protected $instance;

	protected function __construct( $env ){
		$this->env				= $env;
		$this->session			= $this->env->getSession();
		$this->userId			= $this->session->get( 'userId' );
		$this->modelTimer		= new Model_Work_Timer( $this->env );
	}

	protected function __clone(){}

	public function get( $timerId ){
		return $this->checkTimerId( $timerId );
	}

	public function index( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelTimer->getAll( $conditions, $orders, $limits );
	}

	protected function checkTimerId( $timerId, $strict = TRUE ){
		$timer	= $this->modelTimer->get( $timerId );
		if( $timer )
			return $timer;
		if( $strict )
			throw new InvalidArgumentException( 'Timer with ID '.$timerId.' is not existing' );
		return NULL;
	}

	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance = new Logic_Work_Timer( $env );
		return self::$instance;
	}

	public function pause( $timerId ){
		$timer	= $this->checkTimerId( $timerId );
		if( $timer->status != 2 ){
			$this->modelTimer->edit( $timerId, array(
				'status'		=> 2,
				'secondsNeeded'	=> $timer->secondsNeeded + ( time() - $timer->modifiedAt ),
				'modifiedAt'	=> time(),
			) );
			$this->env->getCaptain()->callHook( 'Work_Timer', 'onPauseTimer', $this, array(
				'timer'	=> $this->checkTimerId( $timerId )
			) );
		}
	}

	public function start( $timerId ){
		$timer		= $this->checkTimerId( $timerId );
		if( $timer->status != 1 ){
			$active 	= $this->modelTimer->getByIndices( array(
				'workerId'	=> $this->userId,
				'status'	=> 1
			) );
			if( $active )
				$this->pause( $active->workTimerId );
			$this->modelTimer->edit( $timerId, array( 'status' => 1, 'modifiedAt' => time() ) );
			$this->env->getCaptain()->callHook( 'Work_Timer', 'onStartTimer', $this, array(
				'timer'	=> $this->checkTimerId( $timerId )
			) );
		}
	}

	public function stop( $timerId ){
		$timer	= $this->checkTimerId( $timerId );
		if( $timer->status == 1 )
			$this->pause( $timerId );
		$this->modelTimer->edit( $timerId, array(
			'status'		=> 3,
			'modifiedAt'	=> time(),
		) );
		$this->env->getCaptain()->callHook( 'Work_Timer', 'onStopTimer', $this, array(
			'timer'	=> $this->checkTimerId( $timerId )
		) );
	}

	public function sumTimersOfModuleId( $moduleKey, $moduleId, $statuses = array( 2, 3 ) ){
		$modelTimer	= new Model_Work_Timer( $this->env );
		$indices	= array( 'module' => $moduleKey, 'moduleId' => $moduleId, 'status' => $statuses );
		$timers		= $this->modelTimer->getAllByIndices( $indices );
		$seconds	= 0;
		foreach( $timers as $timer )
			$seconds	+= $timer->secondsNeeded;
		return $seconds;
	}

	public function countTimers( $conditions ){
		return $this->modelTimer->count( $conditions );
	}
}
?>
