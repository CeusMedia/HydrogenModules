<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Work_Mission_Kanban extends View
{
	protected ?Logic_Work_Mission $logic	= NULL;
	protected ?DateTime $today				= NULL;
	protected ?array $words					= NULL;
	protected array $projects				= [];

	public function ajaxRenderIndex(): void
	{
		extract( $this->getData() );
		$this->logic	= Logic_Work_Mission::getInstance( $this->env );
		$this->today	= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
		$this->words	= $this->env->getLanguage()->load( 'work/mission' );

		$script	= '<script>
$(document).ready(function(){
	WorkMissionsKanban.userId = '.(int) $this->env->getSession()->get( 'auth_user_id' ).';
/*	if(typeof cmContextMenu !== "undefined"){
		WorkMissionsKanban.initContextMenu();
	};*/
});
	WorkMissionsKanban.initBlockMovability();
</script>';
//		$this->env->getPage()->addHead( $script );

		$data			= [
			'total'		=> 1,
/*			'buttons'	=> array(
				'large'	=> $this->renderControls( $year, $month ),
				'small'	=> $this->renderControls( $year, $month ),
			),*/
			'lists' => [
				'large'	=> $this->renderLarge( $userId ).$script,
				'small'	=> $this->renderSmall( $userId ),
			],
			'filters'	=> $this->env->getSession()->getAll( 'filter.work.mission.kanban.' ),
		];
		print( json_encode( $data ) );
		exit;
	}

	public function index(): void
	{
		$config		= $this->env->getConfig();
		$page		= $this->env->getPage();
		$words		= $this->env->getLanguage()->load( 'work/mission' );

		$page->js->addUrl( $config->get( 'path.scripts' ).'WorkMissions.js' );
		$page->js->addUrl( $config->get( 'path.scripts' ).'WorkMissionsFilter.js' );
		$page->js->addUrl( $config->get( 'path.scripts' ).'WorkMissionsList.js' );
//		$page->js->addUrl( $config->get( 'path.scripts' ).'WorkMissionsCalendar.js' );
		$page->js->addUrl( $config->get( 'path.scripts' ).'WorkMissionsKanban.js' );

		$script		= '
var monthNames = '.json_encode( $words['months'] ).';
var monthNamesShort = '.json_encode( $words['months-short'] ).';
WorkMissions.init("kanban");
if(typeof cmContextMenu !== "undefined"){
	cmContextMenu.labels.priorities = '.json_encode( $words['priorities'] ).';
	cmContextMenu.labels.states = '.json_encode( $words['states'] ).';
};
WorkMissionsKanban.loadCurrentList();



/*!
 * jQuery UI Touch Punch 0.2.3
 *
 * Copyright 2011–2014, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Depends:
 *  jquery.ui.widget.js
 *  jquery.ui.mouse.js
 */
!function(a){function f(a,b){if(!(a.originalEvent.touches.length>1)){a.preventDefault();var c=a.originalEvent.changedTouches[0],d=document.createEvent("MouseEvents");d.initMouseEvent(b,!0,!0,window,1,c.screenX,c.screenY,c.clientX,c.clientY,!1,!1,!1,!1,0,null),a.target.dispatchEvent(d)}}if(a.support.touch="ontouchend"in document,a.support.touch){var e,b=a.ui.mouse.prototype,c=b._mouseInit,d=b._mouseDestroy;b._touchStart=function(a){var b=this;!e&&b._mouseCapture(a.originalEvent.changedTouches[0])&&(e=!0,b._touchMoved=!1,f(a,"mouseover"),f(a,"mousemove"),f(a,"mousedown"))},b._touchMove=function(a){e&&(this._touchMoved=!0,f(a,"mousemove"))},b._touchEnd=function(a){e&&(f(a,"mouseup"),f(a,"mouseout"),this._touchMoved||f(a,"click"),e=!1)},b._mouseInit=function(){var b=this;b.element.bind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),c.call(b)},b._mouseDestroy=function(){var b=this;b.element.unbind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),d.call(b)}}}(jQuery);

	';
		$page->js->addScript( $script );
//		$page->js->addScriptOnReady( 'setInterval(WorkMissionsCalendar.checkForUpdate, 10000)', 'ready' );

		$this->addData( 'filter', $this->loadTemplateFile( 'work/mission/index.filter.php' ) );
	}

	public function renderLarge( $userId ): string
	{
		$statuses	= [
			'0'		=> 'Neu',
			'1'		=> 'Angenommen',
			'2'		=> 'In Arbeit',
			'3'		=> 'Abnahmebereit',
		];
 		$this->projects	= $this->logic->getUserProjects( $userId );
		$filters		= $this->env->getSession()->getAll( 'filter.work.mission.kanban.', TRUE );
		if( $filters->has( 'projects' ) )
			foreach( $this->projects as $projectId => $project )
				if( !in_array( $projectId, $filters->get( 'projects' ) ) )
					unset( $this->projects[$projectId] );

		$lanes		= [];
		foreach( $this->projects as $projectId => $project ){
			$missionCount	= 0;
			$columns		= [];
			foreach( $statuses as $status => $statusLabel ){
				$conditions	= $this->logic->getFilterConditions( 'filter.work.mission.kanban.' );
				$conditions['status']		= $status;
				$conditions['projectId']	= $projectId;

				$missions	= $this->logic->getUserMissions( $userId, $conditions, ['priority' => 'ASC'] );
				$rows	= [];
				foreach( $missions as $mission ){
					$missionCount++;
					$buttonView	= HtmlTag::create( 'a', '<i class="fa fa-eye"></i>&nbsp;<span class="hidden-tablet">anzeigen</span>', [
						'href'	=> './work/mission/view/'.$mission->missionId,
						'class'	=> 'btn btn-small',
						'alt'	=> 'anzeigen',
						'title'	=> 'anzeigen',
					] );
					$buttonEdit	= HtmlTag::create( 'a', '<i class="fa fa-pencil"></i>&nbsp;<span class="hidden-tablet">bearbeiten</span>', [
						'href'	=> './work/mission/edit/'.$mission->missionId,
						'class'	=> 'btn btn-small',
						'alt'	=> 'bearbeiten',
						'title'	=> 'bearbeiten',
					] );
					$cells	= [];
					$cells[]	= HtmlTag::create( 'div', $mission->title, ['class' => 'mission-title'] );
					$userMap	= $this->getData( 'users' );
					if( $mission->workerId ){
						$worker	= '<del class="muted">entfernt</del>';
						if( isset( $userMap[$mission->workerId] ) )
							$worker	= $userMap[$mission->workerId]->username;
						$label		= HtmlTag::create( 'small', 'Bearbeiter: '.$worker );
						$cells[]	= HtmlTag::create( 'div', $label );
					}
					if( $mission->projectId ){
						$projectLabel	= '<del class="muted">entfernt</del>';
						if( isset( $this->projects[$mission->projectId] ) )
							$projectLabel	= $this->projects[$mission->projectId]->title;
						$label		= HtmlTag::create( 'small', 'Project: '.$projectLabel );
						$cells[]	= HtmlTag::create( 'div', $label );
					}
					$cells[]	= HtmlTag::create( 'div', $buttonView.$buttonEdit, ['class' => 'btn-group'] );
					$rows[]	= HtmlTag::create( 'li', $cells, ['class' => 'mission-block priority-'.$mission->priority, 'data-id' => $mission->missionId] );
				}
				$columns[]	= HtmlTag::create( 'div', [
					HtmlTag::create( 'h4', $statusLabel, ['class' => ''] ),
					HtmlTag::create( 'ul', $rows, ['class' => 'sortable unstyled equalize-auto', 'id' => 'sortable-status-'.$status] ),
				], ['class' => 'span3'] );
			}
			if( !$missionCount )
				continue;

			$laneLabel	= HtmlTag::create( 'h3', '<span class="muted">Projekt:</span>&nbsp;'.$project->title );
			$columns	= HtmlTag::create( 'div', $columns, ['class' => 'row-fluid'] );
			$lanes[]	= HtmlTag::create( 'div', [$laneLabel, $columns], [
				'class'		=> 'row-fluid work-mission-kanban-lane-item',
				'id'		=> 'kanban-lane-'.$projectId.'-'.$status,
			] );
		}

		return HtmlTag::create( 'div', $lanes, ['class' => 'work-mission-kanban-lane-list'] );
	}

	protected function renderSmall( $userId ): string
	{
		return 'kanban';
	}

	protected function renderControls( $year, $month ): string
	{
		$btnExport		= HtmlTag::create( 'a', '<i class="icon-calendar icon-white"></i> iCal-Export', [
			'href'		=> './work/mission/export/ical',
			'target'	=> '_blank',
			'class'		=> 'btn not-btn-small btn-warning',
			'style'		=> 'font-weight: normal',
		] );
		return '
	<div id="mission-calendar-control" class="row-fluid">
		<div class="span4" style="text-align: right">
			'.$btnExport.'
<!--			<a href="./work/mission/export/ical" target="_blank" class="btn not-btn-small" style="font-weight: normal"><i class="icon-calendar"></i> iCal-Export</a>-->
		</div>
	</div>';
	}

	protected function renderDay( $userId, DateTime $date, $orders, $cellClass = NULL ): string
	{
		$diff		= $this->today->diff( $date );
		$isPast		= $diff->invert;
		$isToday	= $diff->days == 0;
		$conditions	= $this->logic->getFilterConditions( 'filter.work.mission.calendar.' );
		$conditions['dayStart']	= $date->format( "Y-m-d" );
		$missions	= $this->logic->getUserMissions( $userId, $conditions, $orders );
		$list		= [];
		foreach( $missions as $mission ){
		//	$title		= TextTrimmer::trim( $mission->title, 20 );
			$title		= htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' );
			$title		= preg_replace( "/^--(.+)--$/", "<del>\\1</del>", $title );
			$url		= './work/mission/view/'.$mission->missionId;
			$class		= 'mission-icon-label mission-type-'.$mission->type;
			$title		= '<a class="'.$class.'" href="'.$url.'">'.$title.'</a>';
			$overdue	= '';
			if( $isPast )
				$overdue	= $this->renderOverdue( $mission );
			$list[]	= HtmlTag::create( 'li', $overdue.$title, [
				"class"			=> 'priority-'.$mission->priority,
				"data-id"		=> $mission->missionId,
				"data-type"		=> $mission->type,
				"data-priority"	=> $mission->priority,
				"data-status"	=> $mission->status,
				"data-title"	=> htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' ),
				"data-date"		=> date( "j.n. Y", strtotime( $mission->dayStart ) ),
				"data-time"		=> $mission->type ? $mission->timeStart.' - '.$mission->timeEnd : null,
				"data-project"	=> $mission->projectId ? $this->projects[$mission->projectId]->title : $mission->projectId,
			] );
		}
		$class	= $isToday ? 'active today' : ( $isPast ? 'past' : 'active future' );
		$class	= $cellClass ? $cellClass.' '.$class : $class;
		$list	= '<ul>'.join( $list ).'</ul>';
		$label	= '<div class="date-label '.$class.'">'.$date->format( "j.n." ).'</div>';
		return HtmlTag::create( 'td', $label.$list, [
			"oncontextmenu"	=> "return false",
			"class"			=> $class,
			"data-day"		=> $date->format( "j" ),
			"data-month"	=> $date->format( "n" ),
			"data-year"		=> $date->format( "Y" ),
			"data-date"		=> $date->format( "Y-m-d" )
		] );
	}

	protected function renderLabel( $year, $month ): string
	{
		$month	= (int) $month;
		if( $month < 1 || $month > 12 )
			throw new InvalidArgumentException( 'Invalid month' );
		return '<span id="mission-calendar-control-label">
	<span class="month-label">'.$this->words['months'][(int) $month].'</span>
	<span class="year-label">'.$year.'</span>
</span>';
	}

	/**
	 *	Render overdue container.
	 *	@access		protected
	 *	@param		object		$mission		Mission data object
	 *	@return		string		DIV container with number of overdue days or empty string
	 */
	protected function renderOverdue( object $mission ): string
	{
		$end	= max( $mission->dayStart, $mission->dayEnd );										//  use maximum of start and end as due date
		$diff	= $this->today->diff( new DateTime( $end ) );										//  calculate date difference
		if( $diff->days > 0 && $diff->invert )														//  date is overdue and in past
			return HtmlTag::create( 'div', $diff->days, ['class' => "overdue"] );		//  render overdue container
		return '';
	}
}
