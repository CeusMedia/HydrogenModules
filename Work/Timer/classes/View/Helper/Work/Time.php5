<?php
abstract class View_Helper_Work_Time extends CMF_Hydrogen_View_Helper_Abstract{

	protected $session;
	protected $userId;
	protected $modelProject;
	protected $modelTimer;
	public $from;

	public function __construct( $env ){
		$this->setEnv( $env );
		$this->session			= $this->env->getSession();
		$this->userId			= $this->session->get( 'userId' );
		$this->logicTimer		= Logic_Work_Timer::getInstance( $this->env );
		$this->logicProject		= Logic_Project::getInstance( $this->env );
		$this->modelMission		= new Model_Mission( $this->env );
		$this->modelProject		= new Model_Project( $this->env );
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->from				= $this->env->getRequest()->get( '__path' );
		if( $this->env->getRequest()->has( 'from' ) )
			$this->from				= $this->env->getRequest()->get( 'from' );
	}

	static public function formatSeconds( $duration, $space = ' ', $shorten = FALSE ){
		$seconds 	= $duration % 60;
		$duration	= ( $duration - $seconds ) / 60;
		$minutes	= $duration % 60;
		$duration	= ( $duration - $minutes ) / 60;
		$hours		= $duration % 24;
		$duration	= ( $duration - $hours ) / 24;
		$days		= $duration % 7;
		$weeks		= ( $duration - $days ) / 7;

		if( $shorten && $weeks )
			$days = $minutes = $seconds = 0;
		else if( $shorten && $days )
			$hours = $minutes = $seconds = 0;
		else if( $shorten && $hours )
			$minutes = $seconds = 0;
		else if( $shorten && $minutes )
			$seconds = 0;

		$duration	= ( $seconds ? $space.str_pad( $seconds, 2, 0, STR_PAD_LEFT ).'s' : '' );
		$duration	= ( $minutes ? $space.( $hours ? str_pad( $minutes, 2, 0, STR_PAD_LEFT ).'m' : $minutes.'m' ) : '' ).$duration;
		$duration	= ( $hours ? $space.( $days ? str_pad( $hours, 2, 0, STR_PAD_LEFT ).'h' : $hours.'h' ) : '' ).$duration;
		$duration	= ( $days ? $space.$days.'d' : '' ).$duration;
		$duration	= ( $weeks ? $space.$weeks.'w' : '' ).$duration;
		return ltrim( $duration, $space );
	}

	static public function parseTime( $time ){
		$regexWeeks	= '@([0-9]+)w\s*@';
		$regexDays	= '@([0-9]+)d\s*@';
		$regexHours	= '@([0-9]+)h\s*@';
		$regexMins	= '@([0-9]+)m\s*@';
		$regexSecs	= '@([0-9]+)s\s*@';
		$seconds	= 0;
		$matches	= array();
		if( preg_match( $regexWeeks, $time, $matches ) ){
			$time		= preg_replace( $regexWeeks, '', $time );
			$seconds	+= (int) $matches[1] * 7 * 24 * 60 * 60;
		}
		if( preg_match( $regexDays, $time, $matches ) ){
			$time		= preg_replace( $regexDays, '', $time );
			$seconds	+= (int) $matches[1] * 24 * 60 * 60;
		}
		if( preg_match( $regexHours, $time, $matches ) ){
			$time	= preg_replace( $regexHours, '', $time );
			$seconds	+= (int) $matches[1] * 60 * 60;
		}
		if( preg_match( $regexMins, $time, $matches ) ){
			$time		= preg_replace( $regexMins, '', $time );
			$seconds	+= (int) $matches[1] * 60;
		}
		if( preg_match( $regexSecs, $time, $matches ) ){
			$time	= preg_replace( $regexSecs, '', $time );
			$seconds	+= (int) $matches[1];
		}
		return $seconds;
	}

	public function setFrom( $from ){
		$this->from	= $from;
	}

	static public function sumTimersOfModuleId( CMF_Hydrogen_Environment $env, $moduleKey, $moduleId, $statuses = array( 3 ), $formatAsTime = FALSE ){
		$logic		= Logic_Work_Timer::getInstance( $env );
		$seconds	= $logic->sumTimersOfModuleId( $moduleKey, $moduleId, $statuses );
		if( $formatAsTime )
			return self::formatSeconds( $seconds );
		return $seconds;
	}

	abstract public function render();
}
?>