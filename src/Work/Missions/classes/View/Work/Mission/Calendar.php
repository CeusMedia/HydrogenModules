<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Work_Mission_Calendar extends View
{
	protected Logic_Work_Mission $logic;
	protected array $words;
	protected array $projects				= [];

	public function index(): void
	{
		$page		= $this->env->getPage();
		$words		= $this->env->getLanguage()->load( 'work/mission' );

		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissions.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsFilter.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsList.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsCalendar.js' );

		$script		= '
WorkMissionsCalendar.monthNames = '.json_encode( $words['months'] ).';
WorkMissionsCalendar.monthNamesShort = '.json_encode( $words['months-short'] ).';
WorkMissions.init("calendar");
WorkMissionsCalendar.userId = '.(int) $this->env->getSession()->get( 'auth_user_id' ).';
WorkMissionsCalendar.monthCurrent	= '.date( "n" ).';
WorkMissionsCalendar.month			= '.(int) $this->getData( 'month' ).';
WorkMissionsCalendar.year			= '.(int) $this->getData( 'year' ).';
if(typeof cmContextMenu !== "undefined"){
	cmContextMenu.labels.priorities = '.json_encode( $words['priorities'] ).';
	cmContextMenu.labels.states = '.json_encode( $words['states'] ).';
};
WorkMissionsList.loadCurrentListAndDayControls();
';
		$page->js->addScriptOnReady( $script );
		$page->js->addScriptOnReady( 'setInterval(WorkMissionsCalendar.checkForUpdate, 10000)' );

		$this->addData( 'filter', $this->loadTemplateFile( 'work/mission/index.filter.php' ) );
	}

	protected function __onInit(): void
	{
		$this->logic	= Logic_Work_Mission::getInstance( $this->env );
		$this->words	= $this->env->getLanguage()->load( 'work/mission' );
	}
}
