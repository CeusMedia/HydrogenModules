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
	protected $indicator;
	protected $titleLength	= 80;
	protected $baseUrl;
	protected $today;
	protected $timeOffset	= 14400;

	public function __construct( $env, $missions, $words ){
		$this->env		= $env;
		$this->words	= $words;
		
		$this->today			= strtotime( date( 'Y-m-d', time() - $this->timeOffset ) );
		foreach( $missions as $mission ){
			$days	= ( strtotime( $mission->dayStart ) - $this->today ) / ( 24 * 60 * 60 );
			$days	= max( min( $days , 6 ), 0 );
			$this->list[$days][]	= $mission;
		}

		$pathIcons		= 'http://img.int1a.net/famfamfam/silk/';
		$this->icons	= array(
			'up'		=> UI_HTML_Elements::Image( $pathIcons.'arrow_up.png', $words['filter-directions']['ASC'] ),
			'down'		=> UI_HTML_Elements::Image( $pathIcons.'arrow_down.png', $words['filter-directions']['DESC'] ),
			'right'		=> UI_HTML_Elements::Image( $pathIcons.'arrow_right.png', $words['list-actions']['moveRight'] ),
			'left'		=> UI_HTML_Elements::Image( $pathIcons.'arrow_left.png', $words['list-actions']['moveLeft'] ),
			'edit'		=> UI_HTML_Elements::Image( $pathIcons.'pencil.png', $words['list-actions']['edit'] ),
			'remove'	=> UI_HTML_Elements::Image( $pathIcons.'bin_closed.png', $words['list-actions']['remove'] ),
		);
		$this->indicator		= new UI_HTML_Indicator();
#		$this->titleLength		= $config->get( 'module.work_mission.mail.title.length' );
		$this->baseUrl			= $this->env->getConfig()->get( 'app.base.url' );
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
		$then	= time() - $this->timeOffset + ( $daysInFuture * 24 * 60 * 60 );
		$day	= isset( $this->words['days'] ) ? $this->words['days'][date( "w", $then )] : '';
		$date	= date( "j.n.", $then );
		return sprintf( $template, $day, $date );
	}

	public function renderDayButton( $day, $label ){
		$count		= isset(  $this->list[$day] ) ? count( $this->list[$day] ) : 0;
		$classes	= array( 'day' );
		if( $day < 3 )
			$classes[]	= 'important';
		if( !$count )
			$classes[]	= 'empty';
		$attributes	= array(
			'class'		=> join( ' ', $classes ),
			'disabled'	=> $count ? NULL : 'disabled',
			'type'		=> 'button',
			'onclick'	=> 'showDayTable('.$day.',true);',
		);
		return UI_HTML_Tag::create( 'button', $label, $attributes );
	}

	public function renderNumber( $days ){
		$count	= $this->countMissions( $days );
		if( $count )
	#		return ' <small>('.$count.')</small>';
			return ' <div class="mission-number">'.$count.'</div>';
	}

	public function renderOverdue( $mission ){
		$end		= strtotime( max( $mission->dayStart, $mission->dayEnd ) );
		$days		= ( $end - $this->today ) / ( 24 * 60 * 60);
		if( $days < 0 )
			return UI_HTML_Tag::create( 'div', abs( $days ), array( 'class' => "overdue" ) );
	}

	public function renderRowButtons( $mission, $days ){
		$buttons	= array();
		$baseUrl	= './work/mission/changeDay/'.$mission->missionId;
		if( $days ){
			$url		= $baseUrl.'?date='.urlencode( '-1' );
			$buttons[]	= UI_HTML_Elements::LinkButton( $url, $this->icons['left'], 'tiny' );
		}
		$url		= $baseUrl.'?date='.urlencode( '+1' );
		$buttons[]	= UI_HTML_Elements::LinkButton( $url, $this->icons['right'], 'tiny' );
		return join( '', $buttons );
	}

	public function renderRowLabel( $mission ){
		$title		= Alg_Text_Trimmer::trimCentric( $mission->content, $this->titleLength, '...' );
		$title		= htmlentities( $title, ENT_QUOTES, 'UTF-8' );
		$url		= $this->baseUrl.'work/mission/edit/'.$mission->missionId;
		$class		= 'icon-label mission-type-'.$mission->type;
		return UI_HTML_Tag::create( 'a', $title, array( 'href' => $url, 'class' => $class ) );
	}

	public function renderRowOfEvent( $event, $days, $showPriority = FALSE, $showActions = FALSE ){
		$link		= $this->renderRowLabel( $event );
		$overdue	= $this->renderOverdue( $event );
		$timeStart	= date( 'H:i', strtotime( $event->timeStart ) );
		$timeEnd	= date( 'H:i', strtotime( $event->timeEnd ) );
		$times		= $timeStart.' - '.$timeEnd.' '.$this->words['index']['suffixTime'];
		
		$cells		= array();
		$cells[]	= UI_HTML_Tag::create( 'td', $times, array( 'class' => 'cell-time' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $overdue.$link, array( 'class' => 'cell-title' ) );
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
	
	public function renderRowOfTask( $task, $days, $showPriority = FALSE, $showActions = FALSE ){
		$link		= $this->renderRowLabel( $task );
		$overdue	= $this->renderOverdue( $task );
		$graph		= $this->indicator->build( $task->status, 4 );

		$cells		= array();
		$cells[]	= UI_HTML_Tag::create( 'td', $graph, array( 'class' => 'cell-graph' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $overdue.$link, array( 'class' => 'cell-title' ) );
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
}
?>
