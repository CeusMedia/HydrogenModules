<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Work_Mission_List_DaysSmall extends View_Helper_Work_Mission_List_Days
{
	protected array $missions		= [];
/*
	public function renderDayList( $tense, $day, $showStatus = FALSE, $showPriority = FALSE, $showDate = FALSE, $showActions = FALSE ){
		$this->missions	= $this->list[$day];

		return HtmlTag::create( 'div', array(
			HtmlTag::create( 'div', $link, ['class' => 'cell-title'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'span', $worker, ['class' => 'cell-workerId'] ),
				HtmlTag::create( 'span', $worker, ['class' => 'cell-project'] ),
				HtmlTag::create( 'span', $worker, ['class' => 'cell-priority'] ),
				HtmlTag::create( 'span', $worker, ['class' => 'cell-actions'] ),
			) )
		) );

		$colgroup		= [];
		$tableHeads		= [];

		$colgroup		= HtmlElements::ColumnGroup( $colgroup );
		$tableHeads		= HtmlTag::create( 'thead', HtmlElements::TableHeads( $tableHeads ) );
		$list0			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 0 );
		$list1			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 1 );

		$tableBody		= HtmlTag::create( 'tbody', $list1.$list0 );
		$table			= HtmlTag::create( 'table', $colgroup.$tableHeads.$tableBody, ['class' => 'table table-striped work-mission-list'] );
		return HtmlTag::create( 'div', $table, ['class' => "table-day", 'id' => 'table-'.$day] );
	}
*/

/*
	protected $baseUrl;
	protected $indicator;
	protected $logic;
	protected $pathIcons	= 'https://cdn.ceusmedia.de/img/famfamfam/silk/';
	protected $projects		= [];
	protected $titleLength	= 80;
	protected $today;
	protected $words		= [];
	protected $isEditor;
	protected $isViewer;

	public function __construct( $env ){
		parent::__construct( $env );
		$this->baseUrl		= $env->getConfig()->get( 'app.base.url' );
		$this->indicator	= new UI_HTML_Indicator();
		$this->logic		= Logic_Work_Mission::getInstance( $env );
		$this->today		= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
		$this->projects		= [];
		$modelProject		= new Model_Project( $this->env );
		foreach( $modelProject->getAll() as $project )
			$this->projects[$project->projectId] = $project;
		$this->isEditor	= $this->env->getAcl()->has( 'work/mission', 'edit' );
		$this->isViewer	= $this->env->getAcl()->has( 'work/mission', 'view' );
	}

	protected function renderBadgeDays( $days, $class ){
		$label	= HtmlTag::create( 'small', $this->formatDays( $days ) );
		return HtmlTag::create( 'span', $label, ['class' => 'badge badge-'.$class] );
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
	public function renderDayList( $tense, $day, $showStatus = FALSE, $showPriority = FALSE, $showDate = FALSE, $showActions = FALSE )
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

	public function renderRowButtons( $mission, $days )
	{
		$buttons	= [];
		$baseUrl	= './work/mission/changeDay/'.$mission->missionId;

		$iconView	= HtmlTag::create( 'i', '', ['class' => 'icon-eye-open'] );
		$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] );
		$iconLeft	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
		$iconRight	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-right'] );

		$list		= [];
		if( $this->isViewer ){
			$linkView	= HtmlTag::create( 'a', $iconView.' anzeigen', array(
				'href'	=> './work/mission/view/'.$mission->missionId
			) );
			$list[]		= HtmlTag::create( 'li', $linkView );
		}
		if( $this->isEditor ){
			$linkEdit	= HtmlTag::create( 'a', $iconEdit.' bearbeiten', array(
				'href'	=> './work/mission/edit/'.$mission->missionId
			) );
			$list[]		= HtmlTag::create( 'li', $linkEdit );
		}
		if( $days ){
			$linkLeft	= HtmlTag::create( 'a', $iconLeft.' '.$this->words['list-actions']['moveLeft'], array(
				'href'		=> '#',
				'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'-1'); return false;",
			) );
			$list[]		= HtmlTag::create( 'li', $linkLeft );
		}
		$linkRight	= HtmlTag::create( 'a', $iconRight.' '.$this->words['list-actions']['moveRight'], array(
			'href'		=> '#',
			'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'+1'); return false;",
		) );
		$list[]		= HtmlTag::create( 'li', $linkRight );
		$list		= HtmlTag::create( 'ul', $list, ['class' => 'dropdown-menu pull-right'] );
		$caret		= HtmlTag::create( 'span', '', ['class' => 'caret'] );
		$button		= HtmlTag::create( 'button', $caret, ['class' => 'btn btn-large dropdown-toggle', 'data-toggle' => 'dropdown'] );
		$buttons	= HtmlTag::create( 'div', $button.$list, ['class' => 'btn-group'] );
		return $buttons;

		if( $days ){
			$attributes	= array(
				'type'		=> 'button',
				'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'-1')",
				'class'		=> 'btn btn-large',
				'title'		=> $this->words['list-actions']['moveLeft'],
			);
			$buttons[]  = HtmlTag::create( 'button', $this->icons['left'], $attributes );
		}
		$attributes	= array(
			'type'		=> 'button',
			'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'+1')",
			'class'		=> 'btn btn-large',
			'title'		=> $this->words['list-actions']['moveRight'],
		);
		$buttons[]  = HtmlTag::create( 'button', $this->icons['right'], $attributes );
		return '<div class="btn-group">'.join( '', $buttons ).'</div>';
	}

	public function renderRowLabel( $mission, $edit = TRUE, $showIcon = TRUE )
	{
		$label		= Alg_Text_Trimmer::trimCentric( $mission->title, $this->titleLength, '...' );
		$label		= htmlentities( $label, ENT_QUOTES, 'UTF-8' );
		$label		= preg_replace( "/^--(.+)--$/", "<strike>\\1</strike>", $label );
		$url		= $this->baseUrl.'work/mission/view/'.$mission->missionId;
//		if( $this->isEditor && $edit )
//			$url	= $this->baseUrl.'work/mission/edit/'.$mission->missionId;
		$class		= 'mission-icon-label mission-type-'.$mission->type;
		$class		= "";
		$icon		= '<i class="icon-large icon-'.( $mission->type ? 'time' : 'wrench' ).'"></i>';
		if( $showIcon )
			$label		= $icon."&nbsp;".$label;
		return HtmlTag::create( 'a', $label, ['href' => $url, 'class' => $class, 'style' => 'font-size: 1.25em'] );
	}

	public function renderRowOfEvent( $event, $days, $showStatus, $showPriority, $showDate, $showActions )
	{
		$link		= $this->renderRowLabel( $event, TRUE, FALSE );
		$badgeO		= $this->renderBadgeDaysOverdue( $event );
		$badgeS		= $this->renderBadgeDaysStill( $event );
		$badgeU		= $this->renderBadgeDaysUntil( $event );
		$badge		= $badgeO.$badgeS.$badgeU;
		$graph		= $this->indicator->build( $event->status, 4, 50 );
		$graph		= HtmlTag::create( 'div', $graph, ['class' => 'cell-graph'] );
		$worker		= $this->renderUserWithAvatar( $event->workerId, 90 );
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
		$attributes	= array(
			'class'		=> 'mission-row-small row-priority priority-'.$event->priority,
			'style'		=> 'width: 100%; border-top: 1px solid rgba(0, 0, 0, 0.25)'
		);
		$colgroup		= HtmlElements::ColumnGroup( "", "53px" );
		$tbody			= HtmlTag::create( 'tbody', array(
			HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $link, ['class' => 'not-cell-title autocut'] ),
				HtmlTag::create( 'td', $badge, ['class' => 'cell-badge', 'style' => 'text-align: center'] ),
			), ['class' => 'cell-priority'] ),
			HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $times, ['class' => 'cell-time'] ),
				HtmlTag::create( 'td', $buttons, ['class' => 'not-cell-actions', 'style' => 'width: 45px', 'rowspan' => '3'] ),
			), ['class' => 'cell-priority'] ),
			HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $project, ['class' => 'cell-project'] ),
			), ['class' => 'cell-priority'] ),
			HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $worker, ['class' => 'cell-workerId'] ),
			), ['class' => 'cell-priority'] )
		) );
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

	public function renderRowOfTask( $task, $days, $showStatus, $showPriority, $showDate, $showActions )
	{
		$link		= $this->renderRowLabel( $task, TRUE, FALSE );
		$badgeO		= $this->renderBadgeDaysOverdue( $task );
		$badgeS		= $this->renderBadgeDaysStill( $task );
		$badgeU		= $this->renderBadgeDaysUntil( $task );
		$badge		= $badgeO.$badgeS.$badgeU;
		$graph		= $this->indicator->build( $task->status, 4, 50 );
		$graph		= HtmlTag::create( 'div', $graph, ['class' => 'cell-graph'] );
		$worker		= $this->renderUserWithAvatar( $task->workerId, 90 );
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
		$attributes	= array(
			'class'		=> 'mission-row-small row-priority priority-'.$task->priority,
			'style'		=> 'width: 100%; border-top: 1px solid rgba(0, 0, 0, 0.25)'
		);
		$colgroup		= HtmlElements::ColumnGroup( "", "53px" );
		$tbody			= HtmlTag::create( 'tbody', array(
			HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $link, ['class' => 'not-cell-title autocut'] ),
				HtmlTag::create( 'td', $badge, ['class' => 'cell-project', 'style' => 'text-align: center'] ),
			), ['class' => 'cell-priority'] ),
			HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $project, ['class' => 'cell-project'] ),
				HtmlTag::create( 'td', $buttons, ['class' => 'not-cell-actions', 'rowspan' => 3] ),
			), ['class' => 'cell-priority'] ),
			HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $worker, ['class' => 'cell-workerId'] ),
			), ['class' => 'cell-priority'] )
		) );
		return HtmlTag::create( 'table', $colgroup.$tbody, $attributes );
	}

	public function renderRows( $day, $showStatus, $showPriority, $showDate, $showActions, $typeOnly = NULL )
	{
		if( !count( $this->missions ) )
			return "";
		$list	= [];
		foreach( $this->missions as $mission ){
			if( ( is_null( $typeOnly ) || $typeOnly == $mission->type ) && $mission->type == 0 )
				$list[]	= HtmlTag::create( 'tr',
					HtmlTag::create( 'td', $this->renderRowOfTask( $mission, $day, $showPriority, $showStatus, $showDate, $showActions ), array(
						'style' => 'padding: 0; margin: 0'
					) )
				);
			else if( ( is_null( $typeOnly ) || $typeOnly == $mission->type ) && $mission->type == 1 )
				$list[]	= HtmlTag::create( 'tr',
					HtmlTag::create( 'td', $this->renderRowOfEvent( $mission, $day, $showPriority, $showStatus, $showDate, $showActions ), array(
						'style' => 'padding: 0; margin: 0'
					) )
				);
		}
		return HtmlTag::create( 'table', $list, ['class' => 'not-table not-table-striped'] );
	}
}
