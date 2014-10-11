<?php
class View_Helper_Work_Mission_List extends View_Helper_Work_Mission_Abstract{

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
	protected $icons;

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
		$this->icons	= array(
			'left'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) ),
			'right'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right' ) ),
			'edit'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) ),
			'view'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-open' ) ),
		);
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
		return $this->renderBadgeDays( $this->today->diff( $end )->days, "warning" );
	}

	public function renderBadgeDaysUntil( $mission ){
		$start	= new DateTime( $mission->dayStart );
		if( $start <= $this->today )																//  mission has started in past
			return "";																			//  return without content
		return $this->renderBadgeDays( $this->today->diff( $start)->days, "success" );
	}

	public function renderDayList( $tense, $day, $showStatus = FALSE, $showPriority = FALSE, $showDate = FALSE, $showActions = FALSE ){
		$colgroup		= array( "20px");
		$tableHeads		= array( "" );

		$colgroup[]		= "120px";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Zustand', array( 'class' => 'sortable', 'data-column' => 'status' ) );

		if( $showDate ){
			$colgroup[]		= "80px";
			$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Datum', array( 'class' => 'sortable', 'data-column' => 'dayStart' ) );
		}

		$colgroup[]		= "";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Titel', array( 'class' => 'sortable', 'data-column' => 'title' ) );

		$colgroup[]		= "120px";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Bearbeiter', array( 'class' => 'not-sortable', 'data-column' => 'workerId' ) );

		$colgroup[]		= "160px";
		$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Projekt', array( 'class' => 'not-sortable', 'data-column' => 'projectId' ) );

		if( $showPriority ){
			$colgroup[]		= "90px";
			$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Priorität', array( 'class' => 'sortable', 'data-column' => 'priority' ) );
		}
		if( $showActions && $tense ){
			$colgroup[]		= "80px";
			$tableHeads[]	= UI_HTML_Tag::create( 'div', 'Aktion', array( 'class' => 'sortable right', 'data-column' => NULL ) );
		}
		$colgroup		= UI_HTML_Elements::ColumnGroup( $colgroup );
		$tableHeads		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( $tableHeads ) );
		$list0			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 0 );
		$list1			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 1 );

		if( !strlen( $list0.$list1 ) )
			return "";
		$tableBody		= UI_HTML_Tag::create( 'tbody', $list1.$list0 );
		$table			= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.$tableBody, array( 'class' => 'table table-striped work-mission-list' ) );
		return UI_HTML_Tag::create( 'div', $table, array( 'class' => "table-day", 'id' => 'table-'.$day ) );
	}

	public function renderRowButtons( $mission, $days ){
		$buttons	= array();
		$baseUrl	= './work/mission/changeDay/'.$mission->missionId;
		if( $days ){
			$attributes	= array(
				'type'		=> 'button',
				'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'-1')",
				'class'		=> 'btn btn-mini',
				'title'		=> $this->words['list-actions']['moveLeft'],
			);
			$buttons[]  = UI_HTML_Tag::create( 'button', $this->icons['left'], $attributes );
		}
		$attributes	= array(
			'type'		=> 'button',
			'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'+1')",
			'class'		=> 'btn btn-mini',
			'title'		=> $this->words['list-actions']['moveRight'],
		);
		$buttons[]  = UI_HTML_Tag::create( 'button', $this->icons['right'], $attributes );
		return '<div class="btn-group">'.join( '', $buttons ).'</div>';
	}

	public function renderRowLabel( $mission, $edit = TRUE ){
		$label		= Alg_Text_Trimmer::trimCentric( $mission->title, $this->titleLength, '...' );
		$label		= htmlentities( $label, ENT_QUOTES, 'UTF-8' );
		$label		= preg_replace( "/^--(.+)--$/", "<strike>\\1</strike>", $label );
		$url		= $this->baseUrl.'work/mission/view/'.$mission->missionId;
		if( $this->isEditor && $edit )
			$url	= $this->baseUrl.'work/mission/edit/'.$mission->missionId;
		$class		= 'mission-icon-label mission-type-'.$mission->type;
		$class		= "";
		$icon		= '<i class="icon-'.( $mission->type ? 'time' : 'wrench' ).'"></i>';
		$label		= $icon."&nbsp;".$label;
		return UI_HTML_Tag::create( 'a', $label, array( 'href' => $url, 'class' => $class ) );
	}

	public function renderRowOfEvent( $event, $days, $showStatus, $showPriority, $showDate, $showActions ){
		$modelUser	= new Model_User( $this->env );
		$link		= $this->renderRowLabel( $event, FALSE );
		$badgeO		= $this->renderBadgeDaysOverdue( $event );
		$badgeS		= $this->renderBadgeDaysStill( $event );
		$badgeU		= $this->renderBadgeDaysUntil( $event );
		$date		= date( 'j.n.y', strtotime( $event->timeStart ) );
		if( $event->timeEnd && $date != date( 'j.n.y', strtotime( $event->timeEnd ) ) )
			$date	.= " - ".date( 'j.n.y', strtotime( $event->timeEnd ) );
		$timeStart	= $this->renderTime( strtotime( $event->timeStart ) );
		$timeEnd	= $this->renderTime( strtotime( $event->timeEnd ) );
		$times		= $timeStart.' - '.$timeEnd/*.' '.$this->words['index']['suffixTime']*/;
		$times		= UI_HTML_Tag::create( 'div', /*$date."<br/>".*/$times.$badgeO.$badgeS.$badgeU, array( 'class' => 'cell-time' ) );
		$worker		= $this->renderUserWithAvatar( $event->workerId );
		$project	= $event->projectId ? $this->projects[$event->projectId]->title : '-';
		$buttonEdit	= $this->renderRowButtonEdit( $event );

		$checkbox	= UI_HTML_Tag::create( 'input', '', array(
			'type'	=> 'checkbox',
			'name'	=> 'missionIds[]',
			'value'	=> $event->missionId,
		) );
		$cells		= array( UI_HTML_Tag::create( 'td', $checkbox ) );
		if( $showStatus )
			$cells[]	= UI_HTML_Tag::create( 'td', $times, array( 'class' => 'cell-time' ) );
		if( $showDate ){
			$date		= date( "d.m", strtotime( $event->dayStart ) );
			$year		= UI_HTML_Tag::create( 'small', date( ".Y", strtotime( $event->dayStart ) ), array( 'class' => 'muted' ) );
			$cells[]	= UI_HTML_Tag::create( 'td', $date.$year, array( 'class' => 'cell-date' ) );
		}
		$cells[]	= UI_HTML_Tag::create( 'td', $link.'&nbsp;'.$buttonEdit, array( 'class' => 'cell-title' ) );
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
		return UI_HTML_Tag::create( 'tr', join( $cells ), $attributes );
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

	public function renderRowOfTask( $task, $days, $showStatus, $showPriority, $showDate, $showActions ){
		$link		= $this->renderRowLabel( $task, FALSE );
		$badgeO		= $this->renderBadgeDaysOverdue( $task );
		$badgeS		= $this->renderBadgeDaysStill( $task );
		$badgeU		= $this->renderBadgeDaysUntil( $task );
		$graph		= $this->indicator->build( $task->status, 4, 60 );
		$graph		= UI_HTML_Tag::create( 'div', $graph.$badgeO.$badgeS.$badgeU, array( 'class' => 'cell-graph' ) );
		$worker		= $this->renderUserWithAvatar( $task->workerId );
		$project	= $task->projectId ? $this->projects[$task->projectId]->title : '-';
		$buttonEdit  = $this->renderRowButtonEdit( $task );

		$checkbox	= UI_HTML_Tag::create( 'input', '', array(
			'type'	=> 'checkbox',
			'name'	=> 'missionIds[]',
			'value'	=> $task->missionId,
		) );
		$cells		= array( UI_HTML_Tag::create( 'td', $checkbox ) );
		if( $showStatus )
			$cells[]	= UI_HTML_Tag::create( 'td', $graph, array( 'class' => 'cell-graph' ) );
		if( $showDate ){
			$date		= date( "d.m", strtotime( $task->dayStart ) );
			$year		= UI_HTML_Tag::create( 'small', date( ".Y", strtotime( $task->dayStart ) ), array( 'class' => 'muted' ) );
			$cells[]	= UI_HTML_Tag::create( 'td', $date.$year, array( 'class' => 'cell-date' ) );
		}
		$cells[]	= UI_HTML_Tag::create( 'td', $link.' '.$buttonEdit, array( 'class' => 'cell-title' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $worker, array( 'class' => 'cell-workerId' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $project, array( 'class' => 'cell-project' ) );
		if( $showPriority ){
			$priority	= $this->words['priorities'][$task->priority];
			$cells[]	= UI_HTML_Tag::create( 'td', $priority, array( 'class' => 'cell-priority' ) );
		}
		if( $showActions ){
			$buttons	= $this->renderRowButtons( $task, $days );
			$cells[]	= UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'cell-actions' ) );
		}
		$attributes	= array( 'class' => 'mission-row row-priority priority-'.$task->priority );
		return UI_HTML_Tag::create( 'tr', join( $cells ), $attributes );
	}

	public function renderRows( $day, $showStatus, $showPriority, $showDate, $showActions, $typeOnly = NULL ){
		$list	= array();
		foreach( $this->missions as $mission ){
			if( ( is_null( $typeOnly ) || $typeOnly == $mission->type ) && $mission->type == 0 )
				$list[]	= $this->renderRowOfTask( $mission, $day, $showPriority, $showStatus, $showDate, $showActions );
			else if( ( is_null( $typeOnly ) || $typeOnly == $mission->type ) && $mission->type == 1 )
				$list[]	= $this->renderRowOfEvent( $mission, $day, $showPriority, $showStatus, $showDate, $showActions );
		}
		return join( $list );
	}

	public function setMissions( $missions ){
		$this->missions		= $missions;
	}

	public function setWords( $words ){
		$this->words	= $words;
	}
}
?>
