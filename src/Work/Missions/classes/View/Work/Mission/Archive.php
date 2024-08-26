<?php

use CeusMedia\HydrogenFramework\View;

class View_Work_Mission_Archive extends View
{
	public function index(): string
	{
		$page			= $this->env->getPage();
//		$monthsLong		= $this->env->getLanguage()->getWords( 'work/mission', 'months' );
//		$monthsShort	= $this->env->getLanguage()->getWords( 'work/mission', 'months-short' );
//		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
//		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );

		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissions.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsFilter.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsList.js' );
//		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsCalendar.js' );

		$page->js->addScript( '$(document).ready(function(){WorkMissions.init("archive");});' );
		$filter	= $this->loadTemplateFile( 'work/mission/index.filter.php' );
		return '
<div id="work-mission-index">
	'.$filter.'
	<div id="work-mission-index-content">
		<div class="content-panel content-panel-list">
			<h3><span class="muted">Aufgaben: </span>Archiv</h3>
			<div class="content-panel-inner">
				<div id="message-loading-list" class="alert alert-info">Loading...</div>
				<div id="day-controls">
					<div id="day-controls-large"></div>
					<div id="day-controls-small"></div>
				</div>
				<div id="day-lists">
					<div id="day-list-large"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	WorkMissionsList.loadCurrentListAndDayControls();
});
</script>
';
	}
}
