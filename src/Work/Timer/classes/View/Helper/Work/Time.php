<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

abstract class View_Helper_Work_Time extends Abstraction
{
	public string $from;

	protected Dictionary $session;
	protected Logic_Work_Timer $logicTimer;
	protected Logic_Project $logicProject;
	protected Model_Mission $modelMission;
	protected Model_Project $modelProject;
	protected Model_Work_Timer $modelTimer;
	protected ?string $userId			= NULL;

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
		$this->session			= $this->env->getSession();
		$this->userId			= $this->session->get( 'auth_user_id' );
		$this->logicTimer		= Logic_Work_Timer::getInstance( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicProject		= Logic_Project::getInstance( $this->env );
		$this->modelMission		= new Model_Mission( $this->env );
		$this->modelProject		= new Model_Project( $this->env );
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->from				= $this->env->getRequest()->get( '__path', '' );
		if( $this->env->getRequest()->has( 'from' ) )
			$this->from				= $this->env->getRequest()->get( 'from' );
	}

	/**
	 * @param		int|string	$duration
	 * @param		string		$space
	 * @param		bool		$shorten
	 * @return		string
	 */
	public static function formatSeconds( $duration, string $space = ' ', bool $shorten = FALSE ): string
	{
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

	static public function parseTime( $time ): int
	{
		$regexWeeks	= '@([0-9]+)w\s*@';
		$regexDays	= '@([0-9]+)d\s*@';
		$regexHours	= '@([0-9]+)h\s*@';
		$regexMins	= '@([0-9]+)m\s*@';
		$regexSecs	= '@([0-9]+)s\s*@';
		$seconds	= 0;
		$matches	= [];
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

	abstract public function render();

	public function setFrom( string $from ): self
	{
		$this->from	= $from;
		return $this;
	}

	/**
	 * @param		Environment		$env
	 * @param		string			$moduleKey
	 * @param		string			$moduleId
	 * @param		array			$statuses
	 * @param		bool			$formatAsTime
	 * @return		int|string
	 */
	public static function sumTimersOfModuleId( Environment $env, string $moduleKey, string $moduleId, array $statuses = [3], bool $formatAsTime = FALSE ): int|string
	{
		$logic		= Logic_Work_Timer::getInstance( $env );
		$seconds	= $logic->sumTimersOfModuleId( $moduleKey, $moduleId, $statuses );
		if( $formatAsTime )
			return self::formatSeconds( $seconds );
		return $seconds;
	}
}
