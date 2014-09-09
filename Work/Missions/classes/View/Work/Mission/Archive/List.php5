<?php
class View_Work_Mission_Archive_List extends View_Work_Mission_Archive{

	protected function __onInit(){
		$page			= $this->env->getPage();
		$session		= $this->env->getSession();
		$monthsLong		= $this->env->getLanguage()->getWords( 'work/mission', 'months' );
		$monthsShort	= $this->env->getLanguage()->getWords( 'work/mission', 'months-short' );

//		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
//		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );

		$tense	= (int) $session->get( 'filter.work.mission.tense' );
		$page->js->addScript( '$(document).ready(function(){WorkMissions.init('.$tense.');});' );

		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissions.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsFilter.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsList.js' );
//		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsCalendar.js' );
	}

	public function ajaxRenderContent(){
		$words		= $this->env->getLanguage()->getWords( 'work/mission' );
		$helperList	= new View_Helper_Work_Mission_List( $this->env );
		$helperList->setMissions( $this->getData( 'missions' ) );
		$helperList->setWords( $words );
		$list		= $helperList->renderDayList( 2, 0, TRUE, TRUE, TRUE, FALSE );
		return '
<div class="content-panel content-panel-list">
	<h3><span class="muted">Aufgaben: </span>Archiv</h3>
	<div class="content-panel-inner">
		<div id="day-lists">
			'.$list.'
		</div>
	</div>
</div>';
//		return 'Controller_Work_Mission_Archive_List::ajaxRenderContent';
	}
}
?>
