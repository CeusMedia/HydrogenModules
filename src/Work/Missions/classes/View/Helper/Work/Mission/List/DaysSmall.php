<?php

use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Work_Mission_List_DaysSmall extends View_Helper_Work_Mission_List_Days
{
	public function renderDayList( $tense, $day, bool $showStatus = FALSE, bool $showPriority = FALSE, bool $showDate = FALSE, bool $showActions = FALSE ): string
	{
		$this->missions	= $this->list[$day];
		$list0			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 0 );
		$list1			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 1 );

		if( !strlen( $list0.$list1 ) )
			return "";
		if( $list0 ){
			$tableBody		= HtmlTag::create( 'tbody', $list0 );
			$list0			= HtmlTag::create( 'table', $tableBody, ['class' => 'table table-striped work-mission-list'] );
		}
		if( $list1 ){
			$tableBody		= HtmlTag::create( 'tbody', $list1 );
			$list1			= HtmlTag::create( 'table', $tableBody, ['class' => 'table table-striped work-mission-list'] );
		}
		return HtmlTag::create( 'div', $list1.$list0, ['class' => "table-day-small", 'id' => 'table-small-'.$day] );
	}

	/**
	 *	@param		Entity_Mission	$mission
	 *	@param		$days
	 *	@return		string
	 */
	public function renderRowButtons( Entity_Mission $mission, $days ): string
	{
		$iconView	= HtmlTag::create( 'i', '', ['class' => 'icon-eye-open'] );
		$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] );
		$iconLeft	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
		$iconRight	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-right'] );

		$dropdownItems	= [];
		if( $this->isViewer ){
			$linkView	= HtmlTag::create( 'a', $iconView.' anzeigen', [
				'href'	=> './work/mission/view/'.$mission->missionId
			] );
			$dropdownItems[]	= HtmlTag::create( 'li', $linkView );
		}
		if( $this->isEditor ){
			$linkEdit	= HtmlTag::create( 'a', $iconEdit.' bearbeiten', [
				'href'	=> './work/mission/edit/'.$mission->missionId
			] );
			$dropdownItems[]	= HtmlTag::create( 'li', $linkEdit );
		}
		if( $days ){
			$linkLeft	= HtmlTag::create( 'a', $iconLeft.' '.$this->words['list-actions']['moveLeft'], [
				'href'		=> '#',
				'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'-1'); return false;",
			] );
			$dropdownItems[]	= HtmlTag::create( 'li', $linkLeft );
		}
		$linkRight	= HtmlTag::create( 'a', $iconRight.' '.$this->words['list-actions']['moveRight'], [
			'href'		=> '#',
			'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'+1'); return false;",
		] );
		$dropdownItems[]	= HtmlTag::create( 'li', $linkRight );

		$dropdownMenu		= HtmlTag::create( 'ul', $dropdownItems, ['class' => 'dropdown-menu pull-right'] );

		$caret				= HtmlTag::create( 'span', '', ['class' => 'caret'] );
		$dropdownToggle		= HtmlTag::create( 'button', $caret, [
			'class'			=> 'btn btn-large dropdown-toggle',
			'data-toggle'	=> 'dropdown'
		] );

		return HtmlTag::create( 'div', $dropdownToggle.$dropdownMenu, ['class' => 'btn-group'] );
	}

	/**
	 *	@param		Entity_Mission	$mission
	 *	@param		bool			$edit
	 *	@param		bool			$showIcon
	 *	@return		string
	 */
	public function renderRowLabel( Entity_Mission $mission, bool $edit = TRUE, bool $showIcon = TRUE ): string
	{
		$label		= TextTrimmer::trimCentric( $mission->title, $this->titleLength );
		$label		= htmlentities( $label, ENT_QUOTES, 'UTF-8' );
		$label		= preg_replace( "/^--(.+)--$/", "<del>\\1</del>", $label );
		$url		= $this->baseUrl.'work/mission/view/'.$mission->missionId;
//		if( $this->isEditor && $edit )
//			$url	= $this->baseUrl.'work/mission/edit/'.$mission->missionId;
//		$class		= 'mission-icon-label mission-type-'.$mission->type;
		$class		= "";
		$icon		= '<i class="icon-large icon-'.( $mission->type ? 'time' : 'wrench' ).'"></i>';
		if( $showIcon )
			$label		= $icon."&nbsp;".$label;
		return HtmlTag::create( 'a', $label, ['href' => $url, 'class' => $class, 'style' => 'font-size: 1.25em'] );
	}

	public function renderRowOfEvent( object $event, $days, bool $showStatus, bool $showPriority, bool $showDate, bool $showActions ): string
	{
		$link		= $this->renderRowLabel( $event, TRUE, FALSE );
		$badgeO		= $this->renderBadgeDaysOverdue( $event );
		$badgeS		= $this->renderBadgeDaysStill( $event );
		$badgeU		= $this->renderBadgeDaysUntil( $event );
		$badge		= $badgeO.$badgeS.$badgeU;
//		$graph		= $this->indicator->build( $event->status, 4, 50 );
//		$graph		= HtmlTag::create( 'div', $graph, ['class' => 'cell-graph'] );
//		$worker		= $this->renderUserWithAvatar( $event->workerId, 90 );
		$project	= $event->projectId ? $this->projects[$event->projectId]->title : '-';
		$timeStart	= $this->renderTime( strtotime( $event->timeStart ) );
		$timeEnd	= $this->renderTime( strtotime( $event->timeEnd ) );
		$times		= $timeStart.' - '.$timeEnd;
//		$times		= HtmlTag::create( 'div', $times, ['class' => 'cell-time'] );

		$modelUser	= new Model_User( $this->env );
		$username	= $event->workerId && $modelUser->has( $event->workerId ) ? $modelUser->get( $event->workerId )->username : "UNKNOWN";
		$worker		= '<i class="icon-user"></i> <span>'.$username.'</span>';
		$project	= '<i class="icon-folder-close"></i> <span>'.$project.'</span>';
		$times		= '<i class="icon-time"></i> <span>'.$times.'</span>';

//		if( $showStatus )
//			$cells[]	= HtmlTag::create( 'td', $times, ['class' => 'cell-time'] );
//		if( $showDate ){
//			$date		= date( "d.m", strtotime( $event->dayStart ) );
//			$year		= HtmlTag::create( 'small', date( ".Y", strtotime( $event->dayStart ) ), ['class' => 'muted'] );
//			$cells[]	= HtmlTag::create( 'div', $date.$year, ['class' => 'cell-date'] );
//		}
//		if( $showPriority ){
//			$priority	= $this->words['priorities'][$event->priority];
//			$cells[]	= HtmlTag::create( 'div', $priority, ['class' => 'cell-priority'] );
//		}
//		if( $showActions ){
			$buttons	= $this->renderRowButtons( $event, $days );
//			$cells[]	= HtmlTag::create( 'div', $buttons, ['class' => 'cell-actions'] );
//		}
		$attributes	= [
			'class'		=> 'mission-row-small row-priority priority-'.$event->priority,
			'style'		=> 'width: 100%; border-top: 1px solid rgba(0, 0, 0, 0.25)'
		];
		$colgroup		= HtmlElements::ColumnGroup( "", "53px" );
		$tbody			= HtmlTag::create( 'tbody', [
			HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $link, ['class' => 'not-cell-title autocut'] ),
				HtmlTag::create( 'td', $badge, ['class' => 'cell-badge', 'style' => 'text-align: center'] ),
			], ['class' => 'cell-priority'] ),
			HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $times, ['class' => 'cell-time'] ),
				HtmlTag::create( 'td', $buttons, ['class' => 'not-cell-actions', 'style' => 'width: 45px', 'rowspan' => '3'] ),
			], ['class' => 'cell-priority'] ),
			HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $project, ['class' => 'cell-project'] ),
			], ['class' => 'cell-priority'] ),
			HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $worker, ['class' => 'cell-workerId'] ),
			], ['class' => 'cell-priority'] )
		] );
		return HtmlTag::create( 'table', $colgroup.$tbody, $attributes );
/*
		$modelUser	= new Model_User( $this->env );
		$link		= $this->renderRowLabel( $event );
		$badgeO		= $this->renderBadgeDaysOverdue( $event );
		$badgeS		= $this->renderBadgeDaysStill( $event );
		$badgeU		= $this->renderBadgeDaysUntil( $event );
		$date		= date( 'j.n.y', strtotime( $event->timeStart ) );
		if( $event->timeEnd && $date != date( 'j.n.y', strtotime( $event->timeEnd ) ) )
			$date	.= " - ".date( 'j.n.y', strtotime( $event->timeEnd ) );
		$timeStart	= $this->renderTime( strtotime( $event->timeStart ) );
		$timeEnd	= $this->renderTime( strtotime( $event->timeEnd ) );
		$times		= $timeStart.' - '.$timeEnds;
		$times		= HtmlTag::create( 'div', $times.$badgeO.$badgeS.$badgeU, ['class' => 'cell-time'] );
		$worker		= $this->renderUserWithAvatar( $event->workerId );
		$project	= $event->projectId ? $this->projects[$event->projectId]->title : '-';

		$cells		= [];
		if( $showStatus )
			$cells[]	= HtmlTag::create( 'td', $times, ['class' => 'cell-time'] );
		if( $showDate ){
			$date		= date( "d.m", strtotime( $event->dayStart ) );
			$year		= HtmlTag::create( 'small', date( ".Y", strtotime( $event->dayStart ) ), ['class' => 'muted'] );
			$cells[]	= HtmlTag::create( 'td', $date.$year, ['class' => 'cell-date'] );
		}
		$cells[]	= HtmlTag::create( 'td', $link, ['class' => 'cell-title'] );
		$cells[]	= HtmlTag::create( 'td', $worker, ['class' => 'cell-workerId'] );
		$cells[]	= HtmlTag::create( 'td', $project, ['class' => 'cell-project'] );
		if( $showPriority ){
			$priority	= $this->words['priorities'][$event->priority];
			$cells[]	= HtmlTag::create( 'td', $priority, ['class' => 'cell-priority'] );
		}
		if( $showActions ){
			$buttons	= $this->renderRowButtons( $event, $days );
			$cells[]	= HtmlTag::create( 'td', $buttons, ['class' => 'cell-actions'] );
		}
		$attributes	= ['class' => 'mission-row row-priority priority-'.$event->priority];
		return HtmlTag::create( 'tr', join( $cells ), $attributes );*/
	}

	public function renderRowOfTask( $task, $days, $showStatus, $showPriority, $showDate, $showActions ): string
	{
		$link		= $this->renderRowLabel( $task, TRUE, FALSE );
		$badgeO		= $this->renderBadgeDaysOverdue( $task );
		$badgeS		= $this->renderBadgeDaysStill( $task );
		$badgeU		= $this->renderBadgeDaysUntil( $task );
		$badge		= $badgeO.$badgeS.$badgeU;
//		$graph		= $this->indicator->build( $task->status, 4, 50 );
//		$graph		= HtmlTag::create( 'div', $graph, ['class' => 'cell-graph'] );
//		$worker		= $this->renderUserWithAvatar( $task->workerId, 90 );
		$project	= $task->projectId ? $this->projects[$task->projectId]->title : '-';

		$modelUser	= new Model_User( $this->env );
		$username	= $modelUser->has( $task->workerId ) ? $modelUser->get( $task->workerId )->username : "UNKNOWN";
		$worker		= '<i class="icon-user"></i> <span>'.$username.'</span>';
		$project	= '<i class="icon-folder-close"></i> <span>'.$project.'</span>';

//		$cells		= [];
//		if( $showStatus )
//			$cells[]	= HtmlTag::create( 'td', $graph, ['class' => 'cell-graph'] );
//		if( $showDate ){
//			$date		= date( "d.m", strtotime( $task->dayStart ) );
//			$year		= HtmlTag::create( 'small', date( ".Y", strtotime( $task->dayStart ) ), ['class' => 'muted'] );
//			$cells[]	= HtmlTag::create( 'div', $date.$year, ['class' => 'cell-date'] );
//		}
//		$cells[]	= HtmlTag::create( 'div', $link, ['class' => 'cell-title'] );
//		$cells[]	= HtmlTag::create( 'div', $worker, ['class' => 'cell-workerId'] );
//		$cells[]	= HtmlTag::create( 'div', $project, ['class' => 'cell-project'] );
//		if( $showPriority ){
//			$priority	= $this->words['priorities'][$task->priority];
//			$cells[]	= HtmlTag::create( 'div', $priority, ['class' => 'cell-priority'] );
//		}
		$buttons	= '';
		if( $showActions ){
			$buttons	= $this->renderRowButtons( $task, $days );
//			$cells[]	= HtmlTag::create( 'div', $buttons, ['class' => 'cell-actions'] );
		}
		$attributes	= [
			'class'		=> 'mission-row-small row-priority priority-'.$task->priority,
			'style'		=> 'width: 100%; border-top: 1px solid rgba(0, 0, 0, 0.25)'
		];
		$colgroup		= HtmlElements::ColumnGroup( "", "53px" );
		$tbody			= HtmlTag::create( 'tbody', [
			HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $link, ['class' => 'not-cell-title autocut'] ),
				HtmlTag::create( 'td', $badge, ['class' => 'cell-project', 'style' => 'text-align: center'] ),
			], ['class' => 'cell-priority'] ),
			HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $project, ['class' => 'cell-project'] ),
				HtmlTag::create( 'td', $buttons, ['class' => 'not-cell-actions', 'rowspan' => 3] ),
			], ['class' => 'cell-priority'] ),
			HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $worker, ['class' => 'cell-workerId'] ),
			], ['class' => 'cell-priority'] )
		] );
		return HtmlTag::create( 'table', $colgroup.$tbody, $attributes );
	}

	public function renderRows( $day, bool $showStatus = FALSE, bool $showPriority = FALSE, bool $showDate = FALSE, bool $showActions = FALSE, $typeOnly = NULL ): string
	{
		if( !count( $this->missions ) )
			return '';
		$list	= [];
		foreach( $this->missions as $mission ){
			if( ( is_null( $typeOnly ) || $typeOnly == $mission->type ) && $mission->type == 0 )
				$list[]	= HtmlTag::create( 'tr',
					HtmlTag::create( 'td', $this->renderRowOfTask( $mission, $day, $showPriority, $showStatus, $showDate, $showActions ), [
						'style' => 'padding: 0; margin: 0'
					] )
				);
			else if( ( is_null( $typeOnly ) || $typeOnly == $mission->type ) && $mission->type == 1 )
				$list[]	= HtmlTag::create( 'tr',
					HtmlTag::create( 'td', $this->renderRowOfEvent( $mission, $day, $showPriority, $showStatus, $showDate, $showActions ), [
						'style' => 'padding: 0; margin: 0'
					] )
				);
		}
		return HtmlTag::create( 'table', $list, ['class' => 'not-table not-table-striped'] );
	}
}
