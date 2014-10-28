<?php
class View_Helper_Work_Mission_List_DaysSmall extends View_Helper_Work_Mission_List_Days{
/*
	public function renderDayList( $tense, $day, $showStatus = FALSE, $showPriority = FALSE, $showDate = FALSE, $showActions = FALSE ){
		$this->missions	= $this->list[$day];

		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', $link, array( 'class' => 'cell-title' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'span', $worker, array( 'class' => 'cell-workerId' ) ),
				UI_HTML_Tag::create( 'span', $worker, array( 'class' => 'cell-project' ) ),
				UI_HTML_Tag::create( 'span', $worker, array( 'class' => 'cell-priority' ) ),
				UI_HTML_Tag::create( 'span', $worker, array( 'class' => 'cell-actions' ) ),
			) )
		) );

		$colgroup		= array();
		$tableHeads		= array();

		$colgroup		= UI_HTML_Elements::ColumnGroup( $colgroup );
		$tableHeads		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( $tableHeads ) );
		$list0			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 0 );
		$list1			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 1 );
		
		$tableBody		= UI_HTML_Tag::create( 'tbody', $list1.$list0 );
		$table			= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.$tableBody, array( 'class' => 'table table-striped work-mission-list' ) );
		return UI_HTML_Tag::create( 'div', $table, array( 'class' => "table-day", 'id' => 'table-'.$day ) );
	}
*/
	
/*	
	protected $baseUrl;
	protected $indicator;
	protected $logic;
	protected $pathIcons	= 'http://img.int1a.net/famfamfam/silk/';
	protected $projects		= array();
	protected $titleLength	= 80;
	protected $today;
	protected $words		= array();
	protected $isEditor;
	protected $isViewer;

	public function __construct( $env ){
		parent::__construct( $env );
		$this->baseUrl		= $env->getConfig()->get( 'app.base.url' );
		$this->indicator	= new UI_HTML_Indicator();
		$this->logic		= new Logic_Mission( $env );
		$this->today		= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
		$this->projects		= array();
		$modelProject		= new Model_Project( $this->env );
		foreach( $modelProject->getAll() as $project )
			$this->projects[$project->projectId] = $project;
		$this->isEditor	= $this->env->getAcl()->has( 'work/mission', 'edit' );
		$this->isViewer	= $this->env->getAcl()->has( 'work/mission', 'view' );
	}

	protected function renderBadgeDays( $days, $class ){
		$label	= UI_HTML_Tag::create( 'small', $this->formatDays( $days ) );
		return UI_HTML_Tag::create( 'span', $label, array( 'class' => 'badge badge-'.$class ) );
	}

	public function renderBadgeDaysOverdue( $mission ){
		$end	= max( $mission->dayStart, $mission->dayEnd );										//  use maximum of start and end as due date
		$diff	= $this->today->diff( new DateTime( $end ) );										//  calculate date difference
		if( $diff->days > 0 && $diff->invert )														//  date is overdue and in past
			return $this->renderBadgeDays( $diff->days, "important" );
	}
*/
	/**
	 *	Render overdue container.
	 *	@access		public
	 *	@param		object		$mission		Mission data object
	 *	@return		string		DIV container with number of overdue days or empty string
	 */
/*	public function renderBadgeDaysStill( $mission ){
		if( !$mission->dayEnd || $mission->dayEnd == $mission->dayStart )						//  mission has no duration
			return "";																			//  return without content
		$start	= new DateTime( $mission->dayStart );
		$end	= new DateTime( $mission->dayEnd );
		if( $this->today < $start || $end <= $this->today )										//  starts in future or has already ended
			return "";																			//  return without content
		return $this->renderBadgeDays( $this->today->diff( $end )->days, "warning" );
	}

	public function renderBadgeDaysUntil( $mission ){
		$start	= new DateTime( $mission->dayStart );
		if( $start <= $this->today )																//  mission has started in past
			return "";																			//  return without content
		return $this->renderBadgeDays( $this->today->diff( $start)->days, "success" );
	}
*/
	public function renderDayList( $tense, $day, $showStatus = FALSE, $showPriority = FALSE, $showDate = FALSE, $showActions = FALSE ){
		$this->missions	= $this->list[$day];
		$list0			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 0 );
		$list1			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 1 );

		if( !strlen( $list0.$list1 ) )
			return "";
		if( $list0 ){
			$tableBody		= UI_HTML_Tag::create( 'tbody', $list0 );
			$list0			= UI_HTML_Tag::create( 'table', $tableBody, array( 'class' => 'table table-striped work-mission-list' ) );
		}
		if( $list1 ){
			$tableBody		= UI_HTML_Tag::create( 'tbody', $list1 );
			$list1			= UI_HTML_Tag::create( 'table', $tableBody, array( 'class' => 'table table-striped work-mission-list' ) );
		}
		return UI_HTML_Tag::create( 'div', $list1.$list0, array( 'class' => "table-day-small", 'id' => 'table-small-'.$day ) );
	}

	public function renderRowButtons( $mission, $days ){
		$buttons	= array();
		$baseUrl	= './work/mission/changeDay/'.$mission->missionId;

		$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-open' ) );
		$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
		$iconLeft	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
		$iconRight	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right' ) );

		$list		= array();
		if( $this->isViewer ){
			$linkView	= UI_HTML_Tag::create( 'a', $iconView.' anzeigen', array(
				'href'	=> './work/mission/view/'.$mission->missionId
			) );
			$list[]		= UI_HTML_Tag::create( 'li', $linkView );
		}
		if( $this->isEditor ){
			$linkEdit	= UI_HTML_Tag::create( 'a', $iconEdit.' bearbeiten', array(
				'href'	=> './work/mission/edit/'.$mission->missionId
			) );
			$list[]		= UI_HTML_Tag::create( 'li', $linkEdit );
		}
		if( $days ){
			$linkLeft	= UI_HTML_Tag::create( 'a', $iconLeft.' '.$this->words['list-actions']['moveLeft'], array(
				'href'		=> '#',
				'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'-1'); return false;",
			) );
			$list[]		= UI_HTML_Tag::create( 'li', $linkLeft );
		}
		$linkRight	= UI_HTML_Tag::create( 'a', $iconRight.' '.$this->words['list-actions']['moveRight'], array(
			'href'		=> '#',
			'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'+1'); return false;",
		) );
		$list[]		= UI_HTML_Tag::create( 'li', $linkRight );
		$list		= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu pull-right' ) );
		$caret		= UI_HTML_Tag::create( 'span', '', array( 'class' => 'caret' ) );
		$button		= UI_HTML_Tag::create( 'button', $caret, array( 'class' => 'btn btn-large dropdown-toggle', 'data-toggle' => 'dropdown' ) );
		$buttons	= UI_HTML_Tag::create( 'div', $button.$list, array( 'class' => 'btn-group' ) );
		return $buttons;

		if( $days ){
			$attributes	= array(
				'type'		=> 'button',
				'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'-1')",
				'class'		=> 'btn btn-large',
				'title'		=> $this->words['list-actions']['moveLeft'],
			);
			$buttons[]  = UI_HTML_Tag::create( 'button', $this->icons['left'], $attributes );
		}
		$attributes	= array(
			'type'		=> 'button',
			'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'+1')",
			'class'		=> 'btn btn-large',
			'title'		=> $this->words['list-actions']['moveRight'],
		);
		$buttons[]  = UI_HTML_Tag::create( 'button', $this->icons['right'], $attributes );
		return '<div class="btn-group">'.join( '', $buttons ).'</div>';
	}

	public function renderRowLabel( $mission, $edit = TRUE, $showIcon = TRUE ){
		$label		= Alg_Text_Trimmer::trimCentric( $mission->title, $this->titleLength, '...' );
		$label		= htmlentities( $label, ENT_QUOTES, 'UTF-8' );
		$label		= preg_replace( "/^--(.+)--$/", "<strike>\\1</strike>", $label );
		$url		= $this->baseUrl.'work/mission/view/'.$mission->missionId;
		if( $this->isEditor && $edit )
			$url	= $this->baseUrl.'work/mission/edit/'.$mission->missionId;
		$class		= 'mission-icon-label mission-type-'.$mission->type;
		$class		= "";
		$icon		= '<i class="icon-large icon-'.( $mission->type ? 'time' : 'wrench' ).'"></i>';
		if( $showIcon )
			$label		= $icon."&nbsp;".$label;
		return UI_HTML_Tag::create( 'a', $label, array( 'href' => $url, 'class' => $class, 'style' => 'font-size: 1.25em' ) );
	}

	public function renderRowOfEvent( $event, $days, $showStatus, $showPriority, $showDate, $showActions ){
		$link		= $this->renderRowLabel( $event, TRUE, FALSE );
		$badgeO		= $this->renderBadgeDaysOverdue( $event );
		$badgeS		= $this->renderBadgeDaysStill( $event );
		$badgeU		= $this->renderBadgeDaysUntil( $event );
		$badge		= $badgeO.$badgeS.$badgeU;
		$graph		= $this->indicator->build( $event->status, 4, 50 );
		$graph		= UI_HTML_Tag::create( 'div', $graph, array( 'class' => 'cell-graph' ) );
		$worker		= $this->renderUserWithAvatar( $event->workerId, 90 );
		$project	= $event->projectId ? $this->projects[$event->projectId]->title : '-';
		$timeStart	= $this->renderTime( strtotime( $event->timeStart ) );
		$timeEnd	= $this->renderTime( strtotime( $event->timeEnd ) );
		$times		= $timeStart.' - '.$timeEnd;
//		$times		= UI_HTML_Tag::create( 'div', $times, array( 'class' => 'cell-time' ) );

		$modelUser	= new Model_User( $this->env );
		$worker		= '<i class="icon-user"></i> <span>'.$modelUser->get( $event->workerId )->username.'</span>';
		$project	= '<i class="icon-folder-close"></i> <span>'.$project.'</span>';
		$times		= '<i class="icon-time"></i> <span>'.$times.'</span>';

//		if( $showStatus )
//			$cells[]	= UI_HTML_Tag::create( 'td', $times, array( 'class' => 'cell-time' ) );
//		if( $showDate ){
//			$date		= date( "d.m", strtotime( $event->dayStart ) );
//			$year		= UI_HTML_Tag::create( 'small', date( ".Y", strtotime( $event->dayStart ) ), array( 'class' => 'muted' ) );
//			$cells[]	= UI_HTML_Tag::create( 'div', $date.$year, array( 'class' => 'cell-date' ) );
//		}
//		if( $showPriority ){
//			$priority	= $this->words['priorities'][$event->priority];
//			$cells[]	= UI_HTML_Tag::create( 'div', $priority, array( 'class' => 'cell-priority' ) );
//		}
//		if( $showActions ){
			$buttons	= $this->renderRowButtons( $event, $days );
//			$cells[]	= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'cell-actions' ) );
//		}
		$attributes	= array(
			'class'		=> 'mission-row-small row-priority priority-'.$event->priority,
			'style'		=> 'width: 100%; border-top: 1px solid rgba(0, 0, 0, 0.25)'
		);
		$colgroup		= UI_HTML_Elements::ColumnGroup( "", "53px" );
		$tbody			= UI_HTML_Tag::create( 'tbody', array(
			UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $link, array( 'class' => 'not-cell-title autocut' ) ),
				UI_HTML_Tag::create( 'td', $badge, array( 'class' => 'cell-badge', 'style' => 'text-align: center' ) ),
			), array( 'class' => 'cell-priority' ) ),
			UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $times, array( 'class' => 'cell-time' ) ),
				UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'not-cell-actions', 'style' => 'width: 45px', 'rowspan' => '3' ) ),
			), array( 'class' => 'cell-priority' ) ),
			UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $project, array( 'class' => 'cell-project' ) ),
			), array( 'class' => 'cell-priority' ) ),
			UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $worker, array( 'class' => 'cell-workerId' ) ),
			), array( 'class' => 'cell-priority' ) )
		) );
		return UI_HTML_Tag::create( 'table', $colgroup.$tbody, $attributes );
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
		$times		= UI_HTML_Tag::create( 'div', $times.$badgeO.$badgeS.$badgeU, array( 'class' => 'cell-time' ) );
		$worker		= $this->renderUserWithAvatar( $event->workerId );
		$project	= $event->projectId ? $this->projects[$event->projectId]->title : '-';

		$cells		= array();
		if( $showStatus )
			$cells[]	= UI_HTML_Tag::create( 'td', $times, array( 'class' => 'cell-time' ) );
		if( $showDate ){
			$date		= date( "d.m", strtotime( $event->dayStart ) );
			$year		= UI_HTML_Tag::create( 'small', date( ".Y", strtotime( $event->dayStart ) ), array( 'class' => 'muted' ) );
			$cells[]	= UI_HTML_Tag::create( 'td', $date.$year, array( 'class' => 'cell-date' ) );
		}
		$cells[]	= UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-title' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $worker, array( 'class' => 'cell-workerId' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $project, array( 'class' => 'cell-project' ) );
		if( $showPriority ){
			$priority	= $this->words['priorities'][$event->priority];
			$cells[]	= UI_HTML_Tag::create( 'td', $priority, array( 'class' => 'cell-priority' ) );
		}
		if( $showActions ){
			$buttons	= $this->renderRowButtons( $event, $days );
			$cells[]	= UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'cell-actions' ) );
		}
		$attributes	= array( 'class' => 'mission-row row-priority priority-'.$event->priority );
		return UI_HTML_Tag::create( 'tr', join( $cells ), $attributes );*/
	}

	public function renderRowOfTask( $task, $days, $showStatus, $showPriority, $showDate, $showActions ){
		$link		= $this->renderRowLabel( $task, TRUE, FALSE );
		$badgeO		= $this->renderBadgeDaysOverdue( $task );
		$badgeS		= $this->renderBadgeDaysStill( $task );
		$badgeU		= $this->renderBadgeDaysUntil( $task );
		$badge		= $badgeO.$badgeS.$badgeU;
		$graph		= $this->indicator->build( $task->status, 4, 50 );
		$graph		= UI_HTML_Tag::create( 'div', $graph, array( 'class' => 'cell-graph' ) );
		$worker		= $this->renderUserWithAvatar( $task->workerId, 90 );
		$project	= $task->projectId ? $this->projects[$task->projectId]->title : '-';

		$modelUser	= new Model_User( $this->env );
		$worker		= '<i class="icon-user"></i> <span>'.$modelUser->get( $task->workerId )->username.'</span>';
		$project	= '<i class="icon-folder-close"></i> <span>'.$project.'</span>';

//		$cells		= array();
//		if( $showStatus )
//			$cells[]	= UI_HTML_Tag::create( 'td', $graph, array( 'class' => 'cell-graph' ) );
//		if( $showDate ){
//			$date		= date( "d.m", strtotime( $task->dayStart ) );
//			$year		= UI_HTML_Tag::create( 'small', date( ".Y", strtotime( $task->dayStart ) ), array( 'class' => 'muted' ) );
//			$cells[]	= UI_HTML_Tag::create( 'div', $date.$year, array( 'class' => 'cell-date' ) );
//		}
//		$cells[]	= UI_HTML_Tag::create( 'div', $link, array( 'class' => 'cell-title' ) );
//		$cells[]	= UI_HTML_Tag::create( 'div', $worker, array( 'class' => 'cell-workerId' ) );
//		$cells[]	= UI_HTML_Tag::create( 'div', $project, array( 'class' => 'cell-project' ) );
//		if( $showPriority ){
//			$priority	= $this->words['priorities'][$task->priority];
//			$cells[]	= UI_HTML_Tag::create( 'div', $priority, array( 'class' => 'cell-priority' ) );
//		}
		if( $showActions ){
			$buttons	= $this->renderRowButtons( $task, $days );
//			$cells[]	= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'cell-actions' ) );
		}
		$attributes	= array(
			'class'		=> 'mission-row-small row-priority priority-'.$task->priority,
			'style'		=> 'width: 100%; border-top: 1px solid rgba(0, 0, 0, 0.25)'
		);
		$colgroup		= UI_HTML_Elements::ColumnGroup( "", "53px" );
		$tbody			= UI_HTML_Tag::create( 'tbody', array(
			UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $link, array( 'class' => 'not-cell-title autocut' ) ),
				UI_HTML_Tag::create( 'td', $badge, array( 'class' => 'cell-project', 'style' => 'text-align: center' ) ),
			), array( 'class' => 'cell-priority' ) ),
			UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $project, array( 'class' => 'cell-project' ) ),
				UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'not-cell-actions', 'rowspan' => 3 ) ),
			), array( 'class' => 'cell-priority' ) ),
			UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $worker, array( 'class' => 'cell-workerId' ) ),
			), array( 'class' => 'cell-priority' ) )
		) );
		return UI_HTML_Tag::create( 'table', $colgroup.$tbody, $attributes );
	}

	public function renderRows( $day, $showStatus, $showPriority, $showDate, $showActions, $typeOnly = NULL ){
		if( !count( $this->missions ) )
			return "";
		$list	= array();
		foreach( $this->missions as $mission ){
			if( ( is_null( $typeOnly ) || $typeOnly == $mission->type ) && $mission->type == 0 )
				$list[]	= UI_HTML_Tag::create( 'tr',
					UI_HTML_Tag::create( 'td', $this->renderRowOfTask( $mission, $day, $showPriority, $showStatus, $showDate, $showActions ), array(
						'style' => 'padding: 0; margin: 0'
					) )
				);
			else if( ( is_null( $typeOnly ) || $typeOnly == $mission->type ) && $mission->type == 1 )
				$list[]	= UI_HTML_Tag::create( 'tr',
					UI_HTML_Tag::create( 'td', $this->renderRowOfEvent( $mission, $day, $showPriority, $showStatus, $showDate, $showActions ), array(
						'style' => 'padding: 0; margin: 0'
					) )
				);
		}
		return UI_HTML_Tag::create( 'table', $list, array( 'class' => 'not-table not-table-striped' ) );
	}
}
?>
