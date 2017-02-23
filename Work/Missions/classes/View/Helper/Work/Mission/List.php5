<?php
class View_Helper_Work_Mission_List extends View_Helper_Work_Mission_Abstract{

	protected $baseUrl;
	protected $indicator;
	protected $logic;
	protected $projects			= array();
	protected $titleLength		= 80;
	protected $today;
	protected $words			= array();
	protected $isEditor;
	protected $isViewer;
	protected $icons;
	protected $badgesShowPast	= TRUE;
	protected $badgesShowFuture	= TRUE;
	protected $badgesColored	= TRUE;

	public function __construct( $env ){
		parent::__construct( $env );
		$this->baseUrl		= $env->url;
		$this->indicator	= new UI_HTML_Indicator();
		$this->logic		= Logic_Work_Mission::getInstance( $env );
		$this->today		= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
		$this->projects		= array();
		$modelProject		= new Model_Project( $this->env );
		foreach( $modelProject->getAll() as $project )
			$this->projects[$project->projectId] = $project;
		$this->isEditor	= $this->env->getAcl()->has( 'work/mission', 'edit' );
		$this->isViewer	= $this->env->getAcl()->has( 'work/mission', 'view' );
		$this->icons	= array(
			'left'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) ),
			'right'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right' ) ),
			'edit'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) ),
			'view'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-open' ) ),
		);
	}

	protected function renderBadgeDays( $days, $class = NULL ){
		$label	= UI_HTML_Tag::create( 'small', $this->formatDays( $days ) );
		$class	= 'badge'.( $class ? ' badge-'.$class : '' );
		return UI_HTML_Tag::create( 'span', $label, array( 'class' => $class ) );
	}

	public function renderBadgeDaysOverdue( $mission ){
		$end	= max( $mission->dayStart, $mission->dayEnd );										//  use maximum of start and end as due date
		$diff	= $this->today->diff( new DateTime( $end ) );										//  calculate date difference
		$class	= $this->badgesColored ? "important" : NULL;
		if( $diff->days > 0 && $diff->invert )														//  date is overdue and in past
			return $this->renderBadgeDays( $diff->days, $class );
	}

	/**
	 *	Render overdue container.
	 *	@access		public
	 *	@param		object		$mission		Mission data object
	 *	@return		string		DIV container with number of overdue days or empty string
	 */
	public function renderBadgeDaysStill( $mission ){
		if( !$mission->dayEnd || $mission->dayEnd == $mission->dayStart )						//  mission has no duration
			return "";																			//  return without content
		$start	= new DateTime( $mission->dayStart );
		$end	= new DateTime( $mission->dayEnd );
		if( $this->today < $start || $end <= $this->today )										//  starts in future or has already ended
			return "";																			//  return without content
		$class	= $this->badgesColored ? "warning" : NULL;
		return $this->renderBadgeDays( $this->today->diff( $end )->days, $class );
	}

	public function renderBadgeDaysUntil( $mission ){
		$start	= new DateTime( $mission->dayStart );
		if( $start <= $this->today )																//  mission has started in past
			return "";																			//  return without content
		$class	= $this->badgesColored ? "success" : NULL;
		return $this->renderBadgeDays( $this->today->diff( $start)->days, $class );
	}

	public function renderDayListOfEvents( $tense, $day, $showStatus = FALSE, $showPriority = FALSE, $showDate = FALSE, $showActions = FALSE ){
		$list			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 1 );
		if( !strlen( $list ) )
			return '';
		$colgroup		= array();
		$tableHeads		= array();

		if( 0 && $showCheckbox ){
			$colgroup[]		= "20px";
			$tableHeads[]	= "";
		}

		if( $showPriority ){
			$colgroup[]		= "30px";
			$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Prio'/*'Priorität'*/, array( 'data-column' => 'priority' ) );
		}
		$colgroup[]		= "";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Titel', array( 'data-column' => 'title' ) );
		$colgroup[]		= "160px";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Bearbeiter', array( 'data-column' => 'workerId' ) );
		$colgroup[]		= "160px";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Projekt', array( 'data-column' => 'projectId' ) );
		if( $showDate ){
			$colgroup[]		= "80px";
			$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Datum', array( 'data-column' => 'dayStart' ) );
		}
		$colgroup[]		= "120px";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Zeit', array( 'data-column' => 'time' ) );
		if( $showActions && $tense ){
			$colgroup[]		= "65px";
			$tableHeads[]	= UI_HTML_Tag::create( 'div', ''/*'Aktion'*/, array( 'class' => 'right', 'data-column' => NULL ) );
		}
		$colgroup		= UI_HTML_Elements::ColumnGroup( $colgroup );
		$tableHeads		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( $tableHeads ) );
		$tableBody	= UI_HTML_Tag::create( 'tbody', $list );
		$list		= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.$tableBody, array( 'class' => 'table table-striped work-mission-list table-fixed' ) );
		$list		= UI_HTML_Tag::create( 'h4', 'Termine' ).$list;
		return $list;
	}

	public function renderDayListOfTasks( $tense, $day, $showStatus = FALSE, $showPriority = FALSE, $showDate = FALSE, $showActions = FALSE ){
		$list			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 0 );
		if( !strlen( $list ) )
			return '';
		$colgroup		= array();
		$tableHeads		= array();

		if( 0 && $showCheckbox ){
			$colgroup[]		= "20px";
			$tableHeads[]	= "";
		}

		if( $showPriority ){
			$colgroup[]		= "30px";
			$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Prio'/*'Priorität'*/, array( 'class' => 'sortable', 'data-column' => 'priority' ) );
		}
		$colgroup[]		= "";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Titel', array( 'class' => 'sortable', 'data-column' => 'title' ) );
		$colgroup[]		= "160px";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Bearbeiter', array( 'class' => 'sortable', 'data-column' => 'workerId' ) );
		$colgroup[]		= "160px";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Projekt', array( 'class' => 'sortable', 'data-column' => 'projectId' ) );
		if( $showDate ){
			$colgroup[]		= "80px";
			$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Datum', array( 'class' => 'sortable', 'data-column' => 'dayStart' ) );
		}
		$colgroup[]		= "120px";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Zustand', array( 'class' => 'sortable', 'data-column' => 'status' ) );
		if( $showActions && $tense ){
			$colgroup[]		= "65px";
			$tableHeads[]	= UI_HTML_Tag::create( 'div', ''/*'Aktion'*/, array( 'class' => 'not-sortable right', 'data-column' => NULL ) );
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( $colgroup );
		$tableHeads	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( $tableHeads ) );
		$tableBody	= UI_HTML_Tag::create( 'tbody', $list );
		$list		= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.$tableBody, array( 'class' => 'table table-striped work-mission-list table-fixed' ) );
		$list		= UI_HTML_Tag::create( 'h4', 'Aufgaben' ).$list;
		return $list;
	}

	public function renderDayList( $tense, $day, $showStatus = FALSE, $showPriority = FALSE, $showDate = FALSE, $showActions = FALSE ){
		$list0		= $this->renderDayListOfTasks( $tense, $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 0 );
		$list1		= $this->renderDayListOfEvents( $tense, $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 1 );
		if( !strlen( $list0.$list1 ) )
			return '';
		return UI_HTML_Tag::create( 'div', $list1.$list0, array( 'class' => "table-day", 'id' => 'table-'.$day ) );
	}

	public function renderRowButtonEdit( $mission ){
		if( !$this->isEditor )
			return '';
		$attributes = array(
			'href'		=> "./work/mission/edit/".$mission->missionId,
			'class'		=> 'btn btn-mini work-mission-list-row-button work-mission-list-row-button-edit',
			'title'		=> $this->words['list-actions']['edit'],
		);
		return UI_HTML_Tag::create( 'a', $this->icons['edit'], $attributes );
	}

	public function renderRowButtons( $mission, $days ){
		$buttonToggle	= UI_HTML_Tag::create( 'button', UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-caret-down' ) ), array(
			'type'				=> 'button',
			'class'				=> 'btn btn-small dropdown-toggle',
			'data-toggle'		=> 'dropdown',
			'data-mission-id'	=> $mission->missionId,
		) );

		$link	= UI_HTML_Tag::create( 'a', $this->icons['right'].'&nbsp;'.$this->words['list-actions']['moveRight'], array(
			'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'+1'); return false;",
			'href'		=> '#',
		) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );

		if( $days ){
			$link	= UI_HTML_Tag::create( 'a', $this->icons['left'].'&nbsp;'.$this->words['list-actions']['moveLeft'], array(
				'href'		=> '#',
				'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'-1'); return false;",
				'title'		=> $this->words['list-actions']['moveLeft'],
			) );
			$list[]	= UI_HTML_Tag::create( 'li', $link );
		}
		$dropdown		= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) );
		$buttonGroup	= UI_HTML_Tag::create( 'div', array( $buttonToggle.$dropdown ), array( 'class' => 'btn-group pull-right' ) );
		return $buttonGroup;
	}

	public function renderRowLabel( $mission, $edit = TRUE ){
//		$label		= Alg_Text_Trimmer::trimCentric( $mission->title, $this->titleLength, '...' );
		$label		= htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' );
		$label		= preg_replace( "/^--(.+)--$/", "<strike>\\1</strike>", $label );
		$url		= $this->baseUrl.'work/mission/view/'.$mission->missionId;
		if( $this->isEditor && $edit )
			$url	= $this->baseUrl.'work/mission/edit/'.$mission->missionId;
		$class		= 'mission-icon-label mission-type-'.$mission->type;
		$class		= "";
		$icon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-'.( $mission->type ? 'time' : 'wrench' ) ) );
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$icon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-'.( $mission->type ? 'clock-o' : 'thumb-tack' ) ) );
		$label		= $icon."&nbsp;".$label;
		return UI_HTML_Tag::create( 'a', $label, array( 'href' => $url, 'class' => $class ) );
	}

	public function renderRowOfEvent( $event, $days, $showStatus, $showPriority, $showDate, $showActions ){
		$modelUser	= new Model_User( $this->env );
		$link		= $this->renderRowLabel( $event, FALSE );
		$badgeO		= $this->badgesShowPast ? $this->renderBadgeDaysOverdue( $event ) : '';
		$badgeS		= $this->badgesShowFuture ? $this->renderBadgeDaysStill( $event ) : '';
		$badgeU		= $this->badgesShowFuture ? $this->renderBadgeDaysUntil( $event ) : '';
		$date		= date( 'j.n.y', strtotime( $event->timeStart ) );
		if( $event->timeEnd && $date != date( 'j.n.y', strtotime( $event->timeEnd ) ) )
			$date	.= " - ".date( 'j.n.y', strtotime( $event->timeEnd ) );
		$timeStart	= $this->renderTime( strtotime( $event->timeStart ) );
		$timeEnd	= $this->renderTime( strtotime( $event->timeEnd ) );
		$times		= $timeStart.' - '.$timeEnd/*.' '.$this->words['index']['suffixTime']*/;
		$times		= UI_HTML_Tag::create( 'div', $times.$badgeO.$badgeS.$badgeU, array( 'class' => 'cell-time' ) );
		$worker		= $this->renderUserWithAvatar( $event->workerId, 120 );
		$project	= $event->projectId ? $this->projects[$event->projectId]->title : '-';
		$buttonEdit	= $showActions ? $this->renderRowButtonEdit( $event ) : '';
		$cells		= array();

/*		$checkbox	= UI_HTML_Tag::create( 'input', '', array(
			'type'	=> 'checkbox',
			'name'	=> 'missionIds[]',
			'value'	=> $event->missionId,
		) );
		$cells[]	= UI_HTML_Tag::create( 'td', $checkbox );*/
		if( $showPriority ){
			$priority	= $this->words['priorities'][$event->priority];
			$cells[]	= UI_HTML_Tag::create( 'td', $event->priority, array( 'class' => 'cell-priority', 'title' => $priority ) );
		}
		$cells[]	= UI_HTML_Tag::create( 'td', $link.'&nbsp;'.$buttonEdit, array( 'class' => 'cell-title autocut' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $worker, array( 'class' => 'cell-workerId' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $project, array( 'class' => 'cell-project autocut', 'title' => $project ) );
		if( $showDate ){
			$date		= date( "d.m", strtotime( $event->dayStart ) );
			$year		= UI_HTML_Tag::create( 'small', date( ".Y", strtotime( $event->dayStart ) ), array( 'class' => 'muted' ) );
			$cells[]	= UI_HTML_Tag::create( 'td', $date.$year, array( 'class' => 'cell-date' ) );
		}
		if( $showStatus )
			$cells[]	= UI_HTML_Tag::create( 'td', $times, array( 'class' => 'cell-time' ) );
		if( $showActions ){
			$buttons	= $this->renderRowButtons( $event, $days );
			$cells[]	= UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'cell-actions' ) );
		}
		$attributes	= array( 'class' => 'mission-row row-priority priority-'.$event->priority );
		return UI_HTML_Tag::create( 'tr', join( $cells ), $attributes );
	}

	public function renderRowOfTask( $task, $days, $showStatus, $showPriority, $showDate, $showActions ){
		$link		= $this->renderRowLabel( $task, FALSE );
		$badgeO		= $this->renderBadgeDaysOverdue( $task );
		$badgeS		= $this->renderBadgeDaysStill( $task );
		$badgeU		= $this->renderBadgeDaysUntil( $task );
		$graph		= $this->indicator->build( $task->status, 4, 60 );
		$graph		= UI_HTML_Tag::create( 'div', $graph.$badgeO.$badgeS.$badgeU, array( 'class' => 'cell-graph' ) );
		$worker		= $this->renderUserWithAvatar( $task->workerId, 120 );
		$project	= $task->projectId ? $this->projects[$task->projectId]->title : '-';
		$buttonEdit	= $this->renderRowButtonEdit( $task );
		$cells		= array();

/*		$checkbox	= UI_HTML_Tag::create( 'input', '', array(
			'type'	=> 'checkbox',
			'name'	=> 'missionIds[]',
			'value'	=> $task->missionId,
		) );
		$cells[]	= UI_HTML_Tag::create( 'td', $checkbox );*/
		if( $showPriority ){
			$priority	= $this->words['priorities'][$task->priority];
			$cells[]	= UI_HTML_Tag::create( 'td', $task->priority/*$priority*/, array( 'class' => 'cell-priority', 'title' => $priority ) );
		}
		$cells[]	= UI_HTML_Tag::create( 'td', $link.' '.$buttonEdit, array( 'class' => 'cell-title' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $worker, array( 'class' => 'cell-workerId' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $project, array( 'class' => 'cell-project autocut', 'title' => $project ) );
		if( $showDate ){
			$date		= date( "d.m", strtotime( $task->dayStart ) );
			$year		= UI_HTML_Tag::create( 'small', date( ".Y", strtotime( $task->dayStart ) ), array( 'class' => 'muted' ) );
			$cells[]	= UI_HTML_Tag::create( 'td', $date.$year, array( 'class' => 'cell-date' ) );
		}
		if( $showStatus )
			$cells[]	= UI_HTML_Tag::create( 'td', $graph, array( 'class' => 'cell-graph' ) );
		if( $showActions ){
			$buttons	= $this->renderRowButtons( $task, $days );
			$cells[]	= UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'cell-actions' ) );
		}
		$attributes	= array( 'class' => 'mission-row row-priority priority-'.$task->priority );
		return UI_HTML_Tag::create( 'tr', join( $cells ), $attributes );
	}

	public function renderRows( $day, $showStatus, $showPriority, $showDate, $showActions, $typeOnly = NULL ){
		$list	= array();
		foreach( $this->missions as $nr => $mission ){
			$nr	= str_pad( $nr, 4, 0, STR_PAD_LEFT );
			if( ( is_null( $typeOnly ) || $typeOnly == $mission->type ) && $mission->type == 0 ){
				$key	= 'task_'.$nr;
				$list[$key]	= $this->renderRowOfTask( $mission, $day, $showPriority, $showStatus, $showDate, $showActions );
			}
			else if( ( is_null( $typeOnly ) || $typeOnly == $mission->type ) && $mission->type == 1 ){
				$key	= 'event_'.str_replace( ':', '_', $mission->timeStart ).'_'.$nr;
				$list[$key]	= $this->renderRowOfEvent( $mission, $day, $showPriority, $showStatus, $showDate, $showActions );
			}
		}
//		ksort( $list );
		return join( $list );
	}

	public function setMissions( $missions ){
		$this->missions		= $missions;
	}

	public function setWords( $words ){
		$this->words	= $words;
	}

	public function setBadges( $showPast = TRUE, $showFuture = TRUE, $colored = TRUE ){
		$this->badgesShowPast	= $showPast;
		$this->badgesShowFuture	= $showFuture;
		$this->badgesColored	= $colored;
	}
}
?>
