<?php
class View_Helper_MissionList{

	protected $env;
	protected $list			= array(
		0 => array(),
		1 => array(),
		2 => array(),
		3 => array(),
		4 => array(),
		5 => array(),
		6 => array(),
	);
	protected $words		= array();
	protected $icons		= array();
	protected $projects		= array();
	protected $indicator;
	protected $titleLength	= 80;
	protected $baseUrl;
	protected $today;
	protected $pathIcons	= 'http://img.int1a.net/famfamfam/silk/';
	protected $useGravatar	= FALSE;

	public function __construct( $env, $missions, $words ){
		$this->env		= $env;
		$this->words	= $words;
		$this->logic	= new Logic_Mission( $env );
		$this->today	= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
		foreach( $missions as $mission ){															//  iterate missions
			$diff	= $this->today->diff( new DateTime( $mission->dayStart ) );						//  get difference to today
			$days	= $diff->invert ? -1 * $diff->days : $diff->days;								//  calculate days left
			$days	= max( min( $days , 6 ), 0 );													//  restrict to be within 0 and 6
			$this->list[$days][]	= $mission;														//  assign mission to day list
		}
		$this->projects	= array();
		$modelProject	= new Model_Project( $this->env );
		foreach( $modelProject->getAll() as $project )
			$this->projects[$project->projectId] = $project;
		$this->icons	= array(
			'up'		=> UI_HTML_Elements::Image( $this->pathIcons.'arrow_up.png', $words['filter-directions']['ASC'] ),
			'down'		=> UI_HTML_Elements::Image( $this->pathIcons.'arrow_down.png', $words['filter-directions']['DESC'] ),
			'right'		=> UI_HTML_Elements::Image( $this->pathIcons.'arrow_right.png', $words['list-actions']['moveRight'] ),
			'left'		=> UI_HTML_Elements::Image( $this->pathIcons.'arrow_left.png', $words['list-actions']['moveLeft'] ),
			'edit'		=> UI_HTML_Elements::Image( $this->pathIcons.'pencil.png', $words['list-actions']['edit'] ),
			'remove'	=> UI_HTML_Elements::Image( $this->pathIcons.'bin_closed.png', $words['list-actions']['remove'] ),
		);
		$this->indicator		= new UI_HTML_Indicator();
#		$this->titleLength		= $config->get( 'module.work_mission.mail.title.length' );
		$this->baseUrl			= $this->env->getConfig()->get( 'app.base.url' );
		$this->useGravatar		= $this->env->getModules()->has( 'UI_Helper_Gravatar' );
	}

	public function countMissions( $day = NULL ){
		if( $day !== NULL ){
			if( !is_int( $day ) )
				throw new InvalidArgumentException( 'Day must be of integer' );
			if( $day < 0 || $day > 6 )
				throw new OutOfRangeException( 'Day must be atleast 0 and atmost 6' );
			return count( $this->list[$day] );
		}
		$sum	= 0;
		for( $i=0; $i<7; $i++ )
			$sum	+= count( $this->list[$i] );
		return $sum;
	}

	public function getNearestFallbackDay( $day ){
		$left	= $right	= (int) $day;
		while( $left >= 0 || $right <= 6 ){
			if( --$left >= 0 && count( $this->list[$left] ) )
				return $left;
			if( ++$right < 7 && count( $this->list[$right] ) )
				return $right;
		}
		return -1;
	}

	public function renderDate( $daysInFuture = 0, $template = '%1$s, %2$s' ){
		$then	= time() - $this->logic->timeOffset + ( $daysInFuture * 24 * 60 * 60 );
		$day	= isset( $this->words['days'] ) ? $this->words['days'][date( "w", $then )] : '';
		$date	= date( "j.n.", $then );
		return sprintf( $template, $day, $date );
	}

	protected function renderDayButton( $day, $label ){
		$count		= isset(  $this->list[$day] ) ? count( $this->list[$day] ) : 0;
		$classes	= array( 'button day' );
		if( $day < 3 )
			$classes[]	= 'important';
		if( !$count )
			$classes[]	= 'empty';
		$attributes	= array(
			'class'		=> join( ' ', $classes ),
			'disabled'	=> $count ? NULL : 'disabled',
			'type'		=> 'button',
			'onclick'	=> 'WorkMissions.showDayTable('.$day.',true);',
		);
		return UI_HTML_Tag::create( 'button', $label, $attributes );
	}

	protected function renderNumber( $days ){
		$count	= $this->countMissions( $days );
		if( $count )
			return ' <div class="mission-number">'.$count.'</div>';
	}

	/**
	 *	Render overdue container.
	 *	@access		public
	 *	@param		object		$mission		Mission data object
	 *	@return		string		DIV container with number of overdue days or empty string 
	 */
	public function renderOverdue( $mission ){
		$end	= max( $mission->dayStart, $mission->dayEnd );										//  use maximum of start and end as due date
		$diff	= $this->today->diff( new DateTime( $end ) );										//  calculate date difference
		if( $diff->days > 0 && $diff->invert )														//  date is overdue and in past
			return UI_HTML_Tag::create( 'div', $diff->days, array( 'class' => "overdue" ) );		//  render overdue container
	}

	public function renderRowButtons( $mission, $days ){
		$buttons	= array();
		$baseUrl	= './work/mission/changeDay/'.$mission->missionId;
		if( $days ){
			$url		= $baseUrl.'?date='.urlencode( '-1' );
			$title		= $this->words['list-actions']['moveLeft'];
			$buttons[]	= UI_HTML_Elements::LinkButton( $url, $this->icons['left'], 'tiny', NULL, NULL, $title );
		}
		$url		= $baseUrl.'?date='.urlencode( '+1' );
		$title		= $this->words['list-actions']['moveRight'];
		$buttons[]	= UI_HTML_Elements::LinkButton( $url, $this->icons['right'], 'tiny', NULL, NULL, $title );
		return join( '', $buttons );
	}

	public function renderRowLabel( $mission ){
		$title		= Alg_Text_Trimmer::trimCentric( $mission->title, $this->titleLength, '...' );
		$title		= htmlentities( $title, ENT_QUOTES, 'UTF-8' );
		$url		= $this->baseUrl.'work/mission/edit/'.$mission->missionId;
		$class		= 'icon-label mission-type-'.$mission->type;
		return UI_HTML_Tag::create( 'a', $title, array( 'href' => $url, 'class' => $class ) );
	}

	public function renderRowOfEvent( $event, $days, $showPriority = FALSE, $showActions = FALSE ){
		$modelUser	= new Model_User( $this->env );
		$link		= $this->renderRowLabel( $event );
		$overdue	= $this->renderOverdue( $event );
		$date		= date( 'j.n.y', strtotime( $event->timeStart ) );
		if( $event->timeEnd && $date != date( 'j.n.y', strtotime( $event->timeEnd ) ) )
			$date	.= " - ".date( 'j.n.y', strtotime( $event->timeEnd ) );
		$timeStart	= $this->renderTime( strtotime( $event->timeStart ) );
		$timeEnd	= $this->renderTime( strtotime( $event->timeEnd ) );
		$times		= $timeStart.' - '.$timeEnd/*.' '.$this->words['index']['suffixTime']*/;
		$times		= UI_HTML_Tag::create( 'div', /*$date."<br/>".*/$times.$overdue, array( 'class' => 'cell-time' ) );
		$worker		= $this->renderUserWithAvatar( $event->workerId );
		$project	= $event->projectId ? $this->projects[$event->projectId]->title : '-';

		$cells		= array();
		$cells[]	= UI_HTML_Tag::create( 'td', $times, array( 'class' => 'cell-time' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $worker, array( 'class' => 'cell-workerId' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-title' ) );
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

	public function renderUserWithAvatar( $userId ){
		$modelUser	= new Model_User( $this->env );
		$worker		= $modelUser->get( $userId );
		if( !$this->useGravatar )
			return $worker->username;
		$gravatar	= new View_Helper_Gravatar( $this->env );
		$workerPic	= $gravatar->getImage( $worker->email, 20 );
		$workerPic	= UI_HTML_Tag::create( 'span', $workerPic, array( 'class' => 'user-avatar' ) );
		$workerName	= UI_HTML_Tag::create( 'span', $worker->username, array( 'class' => 'user-label' ) );
		return UI_HTML_Tag::create( 'div', $workerPic.' '.$workerName );
	}

	public function renderRowOfTask( $task, $days, $showPriority = FALSE, $showActions = FALSE ){
		$link		= $this->renderRowLabel( $task );
		$overdue	= $this->renderOverdue( $task );
		$graph		= $this->indicator->build( $task->status, 4, 60 );
		$graph		= UI_HTML_Tag::create( 'div', $graph.$overdue, array( 'class' => 'cell-graph' ) );
		$worker		= $this->renderUserWithAvatar( $task->workerId );
		$project	= $task->projectId ? $this->projects[$task->projectId]->title : '-';

		$cells		= array();
		$cells[]	= UI_HTML_Tag::create( 'td', $graph, array( 'class' => 'cell-graph' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $worker, array( 'class' => 'cell-workerId' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-title' ) );
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

	public function renderRows( $day, $showPriority = FALSE, $showActions = FALSE ){
		$list	= array();
		foreach( $this->list[$day] as $mission ){
			if( $mission->type == 0 )
				$list[]	= $this->renderRowOfTask( $mission, $day, $showPriority, $showActions );
			else if( $mission->type == 1 )
				$list[]	= $this->renderRowOfEvent( $mission, $day, $showPriority, $showActions );
		}
		return join( $list );
	}

	public function renderButtons(){
		$buttons	= array();
		$labels		= array(
#			'<b>Heute</b><br/>'.$this->renderDate( 0 ),
			'<b>'.$this->renderDate( 0, '%2$s</b><br/>%1$s' ),
#			'<b>Morgen</b><br/>'.$this->renderDate( 1 ),
			'<b>'.$this->renderDate( 1, '%2$s</b><br/>%1$s' ),
#			'<b>Übermorgen</b><br/>'.$this->renderDate( 2 ),
			'<b>'.$this->renderDate( 2, '%2$s</b><br/>%1$s' ),
			$this->renderDate( 3, '%2$s<br/>%1$s' ),
			$this->renderDate( 4, '%2$s<br/>%1$s' ),
			$this->renderDate( 5, '%2$s<br/>%1$s' ),
			'<br/><span>Zukunft</span>',
		);
		foreach( $labels as $nr => $label )
			$buttons[]	= $this->renderDayButton( $nr, $this->renderNumber( $nr ).$label );
		return join( $buttons );
	}

	public function renderLists(){
		$colgroup	= UI_HTML_Elements::ColumnGroup( "80px", "110px", "", "120px", "90px", "80px" );				//  
		$tableHeads	= UI_HTML_Elements::TableHeads( array(
			UI_HTML_Tag::create( 'div', 'Zustand', array( 'class' => 'sortable', 'data-column' => 'status' ) ),
			UI_HTML_Tag::create( 'div', 'Bearbeiter', array( 'class' => 'not-sortable', 'data-column' => 'workerId' ) ),
			UI_HTML_Tag::create( 'div', 'Titel', array( 'class' => 'sortable', 'data-column' => 'title' ) ),
			UI_HTML_Tag::create( 'div', 'Projekt', array( 'class' => 'not-sortable', 'data-column' => 'projectId' ) ),
			UI_HTML_Tag::create( 'div', 'Priorität', array( 'class' => 'sortable', 'data-column' => 'priority' ) ),
			UI_HTML_Tag::create( 'div', 'Aktion', array( 'class' => 'sortable right', 'data-column' => NULL ) )
		) );

		$list	= array();
		for( $i=0; $i<7; $i++ ){
			$rows		= UI_HTML_Tag::create( 'tbody', $this->renderRows( $i, TRUE, TRUE ) );
			$table		= UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.$rows );
			$list[]	= '<div class="table-day" id="table-'.$i.'">'.$table.'</div>';
		}
		return join( $list );
	}

	protected function renderTime( $timestamp ){
		$hours	= date( 'H', $timestamp );
		$mins	= '<sup><small>'.date( 'i', $timestamp ).'</small></sup>';
		return $hours.$mins;
	}
}
?>
