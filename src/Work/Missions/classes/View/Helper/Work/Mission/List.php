<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Indicator as HtmlIndicator;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Mission_List extends View_Helper_Work_Mission_Abstract
{
	protected string $baseUrl;
	protected HtmlIndicator $indicator;
	protected Logic_Work_Mission $logic;
	protected array $projects			= [];
	protected int $titleLength			= 80;
	protected DateTime $today;
	protected array $words				= [];
	protected bool $isEditor;
	protected bool $isViewer;
	protected array $icons;
	protected bool $badgesShowPast		= TRUE;
	protected bool $badgesShowFuture	= TRUE;
	protected bool $badgesColored		= TRUE;
	/** @var array<Entity_Mission> $missions */
	protected array $missions			= [];

	/**
	 *	@param		Environment		$env
	 *	@throws		Exception
	 */
	public function __construct( Environment $env )
	{
		parent::__construct( $env );
		$this->baseUrl		= $env->url;
		$this->indicator	= new HtmlIndicator();
		$this->logic		= Logic_Work_Mission::getInstance( $env );
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->today		= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
		$this->projects		= [];
		$modelProject		= new Model_Project( $this->env );
		foreach( $modelProject->getAll() as $project )
			$this->projects[$project->projectId] = $project;
		$this->isEditor	= $this->env->getAcl()->has( 'work/mission', 'edit' );
		$this->isViewer	= $this->env->getAcl()->has( 'work/mission', 'view' );
		$this->icons	= [
			'left'		=> HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] ),
			'right'		=> HtmlTag::create( 'i', '', ['class' => 'icon-arrow-right'] ),
			'edit'		=> HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] ),
			'view'		=> HtmlTag::create( 'i', '', ['class' => 'icon-eye-open'] ),
		];
	}

	public function renderBadgeDaysOverdue( Entity_Mission $mission ): string
	{
		$end	= max( $mission->dayStart, $mission->dayEnd );										//  use maximum of start and end as due date
		/** @noinspection PhpUnhandledExceptionInspection */
		$diff	= $this->today->diff( new DateTime( $end ) );										//  calculate date difference
		$class	= $this->badgesColored ? "important" : NULL;
		if( $diff->days > 0 && $diff->invert )														//  date is overdue and in past
			return $this->renderBadgeDays( $diff->days, $class );
		return '';
	}

	/**
	 *	Render overdue container.
	 *	@access		public
	 *	@param		Entity_Mission	$mission		Mission data object
	 *	@return		string			DIV container with number of overdue days or empty string
	 *	@throws		Exception
	 */
	public function renderBadgeDaysStill( Entity_Mission $mission ): string
	{
		if( !$mission->dayEnd || $mission->dayEnd == $mission->dayStart )						//  mission has no duration
			return '';																			//  return without content
		/** @noinspection PhpUnhandledExceptionInspection */
		$start	= new DateTime( $mission->dayStart );
		/** @noinspection PhpUnhandledExceptionInspection */
		$end	= new DateTime( $mission->dayEnd );
		if( $this->today < $start || $end <= $this->today )										//  starts in future or has already ended
			return '';																			//  return without content
		$class	= $this->badgesColored ? "warning" : NULL;
		return $this->renderBadgeDays( $this->today->diff( $end )->days, $class );
	}

	/**
	 *	@param		Entity_Mission	$mission
	 *	@return		string
	 *	@throws		Exception
	 */
	public function renderBadgeDaysUntil( Entity_Mission $mission ): string
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$start	= new DateTime( $mission->dayStart );
		if( $start <= $this->today )															//  mission has started in past
			return '';																			//  return without content
		$class	= $this->badgesColored ? "success" : NULL;
		return $this->renderBadgeDays( $this->today->diff( $start)->days, $class );
	}

	/**
	 *	@param		$tense
	 *	@param		$day
	 *	@param		bool		$showStatus
	 *	@param		bool		$showPriority
	 *	@param		bool		$showDate
	 *	@param		bool		$showActions
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renderDayListOfEvents( $tense, $day, bool $showStatus = FALSE, bool $showPriority = FALSE, bool $showDate = FALSE, bool $showActions = FALSE ): string
	{
		$list			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 1 );
		if( !strlen( $list ) )
			return '';
		$colgroup		= [];
		$tableHeads		= [];

//		if( 0 && $showCheckbox ){
//			$colgroup[]		= "20px";
//			$tableHeads[]	= "";
//		}

		if( $showPriority ){
			$colgroup[]		= "30px";
			$tableHeads[]	= HtmlTag::create( 'div', 'Prio'/*'Priorität'*/, ['data-column' => 'priority'] );
		}
		$colgroup[]		= "";
		$tableHeads[]	= HtmlTag::create( 'div', 'Titel', ['data-column' => 'title'] );
		$colgroup[]		= "160px";
		$tableHeads[]	= HtmlTag::create( 'div', 'Bearbeiter', ['data-column' => 'workerId'] );
		$colgroup[]		= "160px";
		$tableHeads[]	= HtmlTag::create( 'div', 'Projekt', ['data-column' => 'projectId'] );
		if( $showDate ){
			$colgroup[]		= "80px";
			$tableHeads[]	= HtmlTag::create( 'div', 'Datum', ['data-column' => 'dayStart'] );
		}
		$colgroup[]		= "120px";
		$tableHeads[]	= HtmlTag::create( 'div', 'Zeit', ['data-column' => 'time'] );
		if( $showActions && $tense ){
			$colgroup[]		= "65px";
			$tableHeads[]	= HtmlTag::create( 'div', ''/*'Aktion'*/, ['class' => 'right', 'data-column' => NULL] );
		}
		$colgroup		= HtmlElements::ColumnGroup( $colgroup );
		$tableHeads		= HtmlTag::create( 'thead', HtmlElements::TableHeads( $tableHeads ) );
		$tableBody	= HtmlTag::create( 'tbody', $list );
		$list		= HtmlTag::create( 'table', $colgroup.$tableHeads.$tableBody, ['class' => 'table table-striped work-mission-list table-fixed'] );
//		$list		= HtmlTag::create( 'h4', 'Termine' ).$list;
		return $list;
	}

	/**
	 *	@param		$tense
	 *	@param		$day
	 *	@param		bool		$showStatus
	 *	@param		bool		$showPriority
	 *	@param		bool		$showDate
	 *	@param		bool		$showActions
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renderDayListOfTasks( $tense, $day, bool $showStatus = FALSE, bool $showPriority = FALSE, bool $showDate = FALSE, bool $showActions = FALSE ): string
	{
		$list			= $this->renderRows( $day, $showStatus, $showPriority, $showDate, $showActions && $tense, 0 );
		if( !strlen( $list ) )
			return '';
		$colgroup		= [];
		$tableHeads		= [];

//		if( 0 && $showCheckbox ){
//			$colgroup[]		= "20px";
//			$tableHeads[]	= "";
//		}

		if( $showPriority ){
			$colgroup[]		= "30px";
			$tableHeads[]	= HtmlTag::create( 'div', 'Prio'/*'Priorität'*/, ['class' => 'sortable', 'data-column' => 'priority'] );
		}
		$colgroup[]		= "";
		$tableHeads[]	= HtmlTag::create( 'div', 'Titel', ['class' => 'sortable', 'data-column' => 'title'] );
		$colgroup[]		= "160px";
		$tableHeads[]	= HtmlTag::create( 'div', 'Bearbeiter', ['class' => 'sortable', 'data-column' => 'workerId'] );
		$colgroup[]		= "160px";
		$tableHeads[]	= HtmlTag::create( 'div', 'Projekt', ['class' => 'sortable', 'data-column' => 'projectId'] );
		if( $showDate ){
			$colgroup[]		= "80px";
			$tableHeads[]	= HtmlTag::create( 'div', 'Datum', ['class' => 'sortable', 'data-column' => 'dayStart'] );
		}
		$colgroup[]		= "120px";
		$tableHeads[]	= HtmlTag::create( 'div', 'Zustand', ['class' => 'sortable', 'data-column' => 'status'] );
		if( $showActions && $tense ){
			$colgroup[]		= "65px";
			$tableHeads[]	= HtmlTag::create( 'div', ''/*'Aktion'*/, ['class' => 'not-sortable right', 'data-column' => NULL] );
		}
		$colgroup	= HtmlElements::ColumnGroup( $colgroup );
		$tableHeads	= HtmlTag::create( 'thead', HtmlElements::TableHeads( $tableHeads ) );
		$tableBody	= HtmlTag::create( 'tbody', $list );
		$list		= HtmlTag::create( 'table', $colgroup.$tableHeads.$tableBody, ['class' => 'table table-striped work-mission-list table-fixed'] );
//		$list		= HtmlTag::create( 'h4', 'Aufgaben' ).$list;
		return $list;
	}

	/**
	 *	@param		$tense
	 *	@param		$day
	 *	@param		bool		$showStatus
	 *	@param		bool		$showPriority
	 *	@param		bool		$showDate
	 *	@param		bool		$showActions
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renderDayList( $tense, $day, bool $showStatus = FALSE, bool $showPriority = FALSE, bool $showDate = FALSE, bool $showActions = FALSE ): string
	{
		$list0		= $this->renderDayListOfTasks( $tense, $day, $showStatus, $showPriority, $showDate, $showActions && $tense );
		$list1		= $this->renderDayListOfEvents( $tense, $day, $showStatus, $showPriority, $showDate, $showActions && $tense );
		if( !strlen( $list0.$list1 ) )
			return '';
		return HtmlTag::create( 'div', $list1.$list0, ['class' => "table-day", 'id' => 'table-'.$day] );
	}

	public function renderRowButtonEdit( Entity_Mission $mission ): string
	{
		if( !$this->isEditor )
			return '';
		return HtmlTag::create( 'a', $this->icons['edit'], [
			'href'		=> "./work/mission/edit/".$mission->missionId,
			'class'		=> 'btn btn-mini work-mission-list-row-button work-mission-list-row-button-edit',
			'title'		=> $this->words['list-actions']['edit'],
		] );
	}

	public function renderRowButtons( Entity_Mission $mission, $days ): string
	{
		$buttonToggle	= HtmlTag::create( 'button', HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-caret-down'] ), [
			'type'				=> 'button',
			'class'				=> 'btn btn-small dropdown-toggle',
			'data-toggle'		=> 'dropdown',
			'data-mission-id'	=> $mission->missionId,
		] );

		$link	= HtmlTag::create( 'a', $this->icons['right'].'&nbsp;'.$this->words['list-actions']['moveRight'], [
			'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'+1'); return false;",
			'href'		=> '#',
		] );
		$list[]	= HtmlTag::create( 'li', $link );

		if( $days ){
			$link	= HtmlTag::create( 'a', $this->icons['left'].'&nbsp;'.$this->words['list-actions']['moveLeft'], [
				'href'		=> '#',
				'onclick'	=> "WorkMissions.moveMissionStartDate(".$mission->missionId.",'-1'); return false;",
				'title'		=> $this->words['list-actions']['moveLeft'],
			] );
			$list[]	= HtmlTag::create( 'li', $link );
		}
		$dropdown		= HtmlTag::create( 'ul', $list, ['class' => 'dropdown-menu'] );
		return HtmlTag::create( 'div', [$buttonToggle.$dropdown], ['class' => 'btn-group pull-right'] );
	}

	public function renderRowLabel( Entity_Mission $mission, bool $edit = TRUE ): string
	{
//		$label		= TextTrimmer::trimCentric( $mission->title, $this->titleLength, '...' );
		$label		= htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' );
		$label		= preg_replace( "/^--(.+)--$/", "<del>\\1</del>", $label );
		$url		= $this->baseUrl.'work/mission/view/'.$mission->missionId;
		if( $this->isEditor && $edit )
			$url	= $this->baseUrl.'work/mission/edit/'.$mission->missionId;
		$class		= 'mission-icon-label mission-type-'.$mission->type;
		$class		= "";
		$icon		= HtmlTag::create( 'i', '', ['class' => 'icon-'.( $mission->type ? 'time' : 'wrench' )] );
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$icon		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-'.( $mission->type ? 'clock-o' : 'thumb-tack' )] );
		$label		= $icon."&nbsp;".$label;
		return HtmlTag::create( 'a', $label, ['href' => $url, 'class' => $class] );
	}

	/**
	 *	@param		object		$event
	 *	@param		$days
	 *	@param		bool		$showStatus
	 *	@param		bool		$showPriority
	 *	@param		bool		$showDate
	 *	@param		bool		$showActions
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		Exception
	 */
	public function renderRowOfEvent( object $event, $days, bool $showStatus, bool $showPriority, bool $showDate, bool $showActions ): string
	{
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
		$times		= HtmlTag::create( 'div', $times.$badgeO.$badgeS.$badgeU, ['class' => 'cell-time'] );
	//	$worker		= $this->renderUserWithAvatar( $event->workerId, 120 );
		$worker		= $this->renderUser( $modelUser->get( $event->workerId ) );
		$project	= $event->projectId ? $this->projects[$event->projectId]->title : '-';
		$buttonEdit	= $showActions ? $this->renderRowButtonEdit( $event ) : '';
		$cells		= [];

/*		$checkbox	= HtmlTag::create( 'input', '', [
			'type'	=> 'checkbox',
			'name'	=> 'missionIds[]',
			'value'	=> $event->missionId,
		] );
		$cells[]	= HtmlTag::create( 'td', $checkbox );*/
		if( $showPriority ){
			$priority	= $this->words['priorities'][$event->priority];
			$cells[]	= HtmlTag::create( 'td', $event->priority, ['class' => 'cell-priority', 'title' => $priority] );
		}
		$cells[]	= HtmlTag::create( 'td', $link.'&nbsp;'.$buttonEdit, ['class' => 'cell-title autocut'] );
		$cells[]	= HtmlTag::create( 'td', $worker, ['class' => 'cell-workerId'] );
		$cells[]	= HtmlTag::create( 'td', $project, ['class' => 'cell-project autocut', 'title' => $project] );
		if( $showDate ){
			$date		= date( "d.m", strtotime( $event->dayStart ) );
			$year		= HtmlTag::create( 'small', date( ".Y", strtotime( $event->dayStart ) ), ['class' => 'muted'] );
			$cells[]	= HtmlTag::create( 'td', $date.$year, ['class' => 'cell-date'] );
		}
		if( $showStatus )
			$cells[]	= HtmlTag::create( 'td', $times, ['class' => 'cell-time'] );
		if( $showActions ){
			$buttons	= $this->renderRowButtons( $event, $days );
			$cells[]	= HtmlTag::create( 'td', $buttons, ['class' => 'cell-actions'] );
		}
		$attributes	= ['class' => 'mission-row row-priority priority-'.$event->priority];
		return HtmlTag::create( 'tr', join( $cells ), $attributes );
	}

	/**
	 *	@param		Entity_Mission	$task
	 *	@param		$days
	 *	@param		bool		$showStatus
	 *	@param		bool		$showPriority
	 *	@param		bool		$showDate
	 *	@param		bool		$showActions
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		Exception
	 */
	public function renderRowOfTask( Entity_Mission $task, $days, bool $showStatus, bool $showPriority, bool $showDate, bool $showActions ): string
	{
		$modelUser	= new Model_User( $this->env );
		$link		= $this->renderRowLabel( $task, FALSE );
		$badgeO		= $this->renderBadgeDaysOverdue( $task );
		$badgeS		= $this->renderBadgeDaysStill( $task );
		$badgeU		= $this->renderBadgeDaysUntil( $task );
		$graph		= $this->indicator->build( $task->status, 4, 60 );
		$graph		= HtmlTag::create( 'div', $graph.$badgeO.$badgeS.$badgeU, ['class' => 'cell-graph'] );
//		$worker		= $this->renderUserWithAvatar( $task->workerId, 120 );
		$worker		= $this->renderUser( $modelUser->get( $task->workerId ) );
		$project	= $task->projectId ? $this->projects[$task->projectId]->title : '-';
		$buttonEdit	= $this->renderRowButtonEdit( $task );
		$cells		= [];

/*		$checkbox	= HtmlTag::create( 'input', '', [
			'type'	=> 'checkbox',
			'name'	=> 'missionIds[]',
			'value'	=> $task->missionId,
		] );
		$cells[]	= HtmlTag::create( 'td', $checkbox );*/
		if( $showPriority ){
			$priority	= $this->words['priorities'][$task->priority];
			$cells[]	= HtmlTag::create( 'td', $task->priority/*$priority*/, ['class' => 'cell-priority', 'title' => $priority] );
		}
		$cells[]	= HtmlTag::create( 'td', $link.' '.$buttonEdit, ['class' => 'cell-title'] );
		$cells[]	= HtmlTag::create( 'td', $worker, ['class' => 'cell-workerId'] );
		$cells[]	= HtmlTag::create( 'td', $project, ['class' => 'cell-project autocut', 'title' => $project] );
		if( $showDate ){
			$date		= date( "d.m", strtotime( $task->dayStart ) );
			$year		= HtmlTag::create( 'small', date( ".Y", strtotime( $task->dayStart ) ), ['class' => 'muted'] );
			$cells[]	= HtmlTag::create( 'td', $date.$year, ['class' => 'cell-date'] );
		}
		if( $showStatus )
			$cells[]	= HtmlTag::create( 'td', $graph, ['class' => 'cell-graph'] );
		if( $showActions ){
			$buttons	= $this->renderRowButtons( $task, $days );
			$cells[]	= HtmlTag::create( 'td', $buttons, ['class' => 'cell-actions'] );
		}
		$attributes	= ['class' => 'mission-row row-priority priority-'.$task->priority];
		return HtmlTag::create( 'tr', join( $cells ), $attributes );
	}

	/**
	 *	@param		$day
	 *	@param		bool		$showStatus
	 *	@param		bool		$showPriority
	 *	@param		bool		$showDate
	 *	@param		bool		$showActions
	 *	@param		int			$typeOnly
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renderRows( $day, bool $showStatus = FALSE, bool $showPriority = FALSE, bool $showDate = FALSE, bool $showActions = FALSE, $typeOnly = NULL ): string
	{
		$list	= [];
		foreach( $this->missions as $nr => $mission ){
			$nr	= str_pad( $nr, 4, 0, STR_PAD_LEFT );
			if( ( is_null( $typeOnly ) || $typeOnly == (int) $mission->type ) && Model_Mission::TYPE_TASK === (int) $mission->type ){
				$key	= 'task_'.$nr;
				$list[$key]	= $this->renderRowOfTask( $mission, $day, $showPriority, $showStatus, $showDate, $showActions );
			}
			else if( ( is_null( $typeOnly ) || $typeOnly == $mission->type ) && Model_Mission::TYPE_EVENT === (int) $mission->type ){
				$key	= 'event_'.str_replace( ':', '_', $mission->timeStart ).'_'.$nr;
				$list[$key]	= $this->renderRowOfEvent( $mission, $day, $showPriority, $showStatus, $showDate, $showActions );
			}
		}
//		ksort( $list );
		return join( $list );
	}

	/**
	 *	@param		array<Entity_Mission>		$missions
	 *	@return		self
	 */
	public function setMissions( array $missions ): self
	{
		$this->missions		= $missions;
		return $this;
	}

	/**
	 *	@param		array		$words
	 *	@return		self
	 */
	public function setWords( array $words ): self
	{
		$this->words	= $words;
		return $this;
	}

	/**
	 *	@param		bool		$showPast
	 *	@param		bool		$showFuture
	 *	@param		bool		$colored
	 *	@return		self
	 */
	public function setBadges( bool $showPast = TRUE, bool $showFuture = TRUE, bool $colored = TRUE ): self
	{
		$this->badgesShowPast	= $showPast;
		$this->badgesShowFuture	= $showFuture;
		$this->badgesColored	= $colored;
		return $this;
	}

	/**
	 *	@param		int				$days
	 *	@param		string|NULL		$class
	 *	@return		string
	 */
	protected function renderBadgeDays( int $days, ?string $class = NULL ): string
	{
		$label	= HtmlTag::create( 'small', $this->formatDays( $days ) );
		$class	= 'badge'.( $class ? ' badge-'.$class : '' );
		return HtmlTag::create( 'span', $label, ['class' => $class] );
	}
}
