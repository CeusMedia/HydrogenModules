<?php
/**
 *	View.
 *	@version		$Id$
 */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

/**
 *	View.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class View_Work_Mission extends View
{
	public function help(): string
	{
		$topic	= $this->getData( 'topic' );
		if( $topic == "sync" ){
			return $this->loadContentFile( 'html/work/mission/export.html' );
		}
		return "HELP";
	}

	public function ajaxRenderDashboardPanel(): string
	{
		try{
			switch( $this->getData( 'panelId' ) ){
				case 'work-mission-my-tasks':
					$helper		= new View_Helper_Work_Mission_Dashboard_MyTasks( $this->env );
					$helper->setTasks( $this->getData( 'tasks' ) );
					break;
				case 'work-mission-my-today':
				default:
					$helper		= new View_Helper_Work_Mission_Dashboard_MyEvents( $this->env );
					$helper->setEvents( $this->getData( 'events' ) );
					break;
			}
			$helper->setProjects( $this->getData( 'projects' ) );
			return $helper->render();
		}
		catch( Exception $e ){
			return $e->getMessage();
		}
	}

	public static function formatSeconds( $duration, string $space = ' ' ): string
	{
		$seconds 	= $duration % 60;
		$duration	= ( $duration - $seconds ) / 60;
		$minutes	= $duration % 60;
		$duration	= ( $duration - $minutes ) / 60;
		$hours		= $duration % 8;
		$days		= ( $duration - $hours ) / 8;
		$duration	= ( $seconds ? $space.str_pad( $seconds, 2, 0, STR_PAD_LEFT ).'s' : '' );
		$duration	= ( $minutes ? $space.( $hours ? str_pad( $minutes, 2, 0, STR_PAD_LEFT ).'m' : $minutes.'m' ) : '' ).$duration;
		$duration	= ( $hours ? $space.( $days ? str_pad( $hours, 2, 0, STR_PAD_LEFT ).'h' : $hours.'h' ) : '' ).$duration;
		$duration	= ( $days ? $space.$days.'d' : '' ).$duration;
		return ltrim( $duration, $space );
	}

	public static function parseTime( $time )
	{
		$regexDays	= '@([0-9]+)d\s*@';
		$regexHours	= '@([0-9]+)h\s*@';
		$regexMins	= '@([0-9]+)m\s*@';
		$regexSecs	= '@([0-9]+)s\s*@';
		$seconds	= 0;
		$matches	= [];
		if( preg_match( $regexDays, $time, $matches ) ){
			$time		= preg_replace( $regexDays, '', $time );
			$seconds	+= (int) $matches[1] * 8 * 60 * 60;
		}
		if( preg_match( $regexHours, $time, $matches ) ){
			$time		= preg_replace( $regexHours, '', $time );
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

	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
		$page		= $this->env->getPage();
//		$page->js->addScriptOnReady( 'WorkMissions.init("now");' );			//  @deprecated use Page::runScript instead
		$page->runScript( 'WorkMissions.init("now");', 9 );
	}

	public function remove(): void
	{
	}

	public function view(): void
	{
		$page			= $this->env->getPage();
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsViewer.js' );
	}

	protected function __onInit(): void
	{
		$page			= $this->env->getPage();
		$config			= $this->env->getConfig();
		$monthsLong		= array_values( (array) $this->getWords( 'months' ) );
		$monthsShort	= array_values( (array) $this->getWords( 'months-short' ) );

		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );

		$page->js->addUrl( $config->get( 'path.scripts' ).'WorkMissionsCalendar.js' );
		$page->js->addUrl( $config->get( 'path.scripts' ).'WorkMissionsEditor.js' );
		$page->js->addUrl( $config->get( 'path.scripts' ).'WorkMissionsFilter.js' );
		$page->js->addUrl( $config->get( 'path.scripts' ).'WorkMissionsList.js' );
		$page->js->addUrl( $config->get( 'path.scripts' ).'WorkMissions.js' );

		/*		$this->config		= $this->env->getConfig();
				$this->session		= $this->env->getSession();
				$this->request		= $this->env->getRequest();
				$this->messenger	= $this->env->getMessenger();
		*/
	}

	protected function renderNiceTime( $time ): string
	{
		if( !strlen( $time ) )
			return '-';
		[$hours, $minutes] = explode( ':', $time );
		return HtmlTag::create( 'span', [
			HtmlTag::create( 'big', str_pad( $hours, 2, 0, STR_PAD_LEFT ) ),
			HtmlTag::create( 'sup', str_pad( $minutes, 2, 0, STR_PAD_LEFT ) ),
		], ['class' => 'time-nice'] );
	}
}
