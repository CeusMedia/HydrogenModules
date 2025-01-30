<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Controller_Ajax_Work_Mission_Calendar extends Controller_Ajax_Work_Mission
{
	protected string $filterKeyPrefix		= 'filter.work.mission.calendar.';

	protected DateTime $today;
	protected string $year;
	protected string $month;

	/**
	 *	@return		void
	 *	@throws		DateMalformedStringException
	 *	@throws		JsonException
	 */
	public function renderIndex(): void
	{
		$script	= '<script>
$(document).ready(function(){
	WorkMissionsCalendar.userId = '.(int) $this->userId.';
	if(typeof cmContextMenu !== "undefined"){
		WorkMissionsCalendar.initContextMenu();
	}
});
</script>';
//		$this->env->getPage()->addHead( $script );

		$data			= [
			'total'		=> 1,
			'buttons'	=> [
				'large'	=> $this->renderControls( $this->year, $this->month ),
				'small'	=> $this->renderControls( $this->year, $this->month ),
			],
			'lists' => [
				'large'	=> $this->renderCalendarLarge( $this->userId, $this->year, $this->month ).$script,
				'small'	=> $this->renderCalendarSmall( $this->userId, $this->year, $this->month ),
			]
		];
		$this->respondData( $data );
	}

	//  --  PROTECTED  --  //
	protected function __onInit(): void
	{
		parent::__onInit(); // TODO: Change the autogenerated stub
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->today	= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
		$date	= explode( "-", $this->session->get( $this->filterKeyPrefix.'month' ) );
		$this->year		= $date[0];
		$this->month	= $date[1];
	}

	/**
	 *	@param		int|string		$userId
	 *	@param		string			$year
	 *	@param		string			$month
	 *	@return		string
	 *	@throws		DateMalformedStringException
	 */
	protected function renderCalendarLarge( int|string $userId, string $year, string $month ): string
	{
		$this->projects	= $this->logic->getUserProjects( $userId );
		$showMonth		= str_pad( $month, 2, "0", STR_PAD_LEFT );
		$showScope		= $year.'-'.$showMonth.'-01';
		/** @noinspection PhpUnhandledExceptionInspection */
		$monthDate		= new DateTime( $showScope );
		$monthDays		= date( "t", strtotime( $showScope ) );
		$offsetStart	= date( "w", strtotime( $showScope ) ) - 1;
		$offsetStart	= $offsetStart >= 0 ? $offsetStart : 6;
		$weeks			= ceil( ( (int) $monthDays + (int) $offsetStart ) / 7 );
		$orders			= ['priority' => 'ASC'];

		$rows			= [];
		for( $i=0; $i<$weeks; $i++ ){
			$row	= [];
			$j		= 0;
			$class	= '';
			if( $i == 0 ){
				for( $j=0; $j<$offsetStart; $j++ ){
					$preDate	= clone $monthDate;
					$preDate	= $preDate->modify( "-".( $offsetStart - $j )." days" );
					$row[]		= $this->renderDay( $userId, $preDate, $orders, 'inactive' );
				}
			}
			while( $j < 7 ){
				$day		= $i * 7 - $offsetStart + $j +1;
				$showYear	= $year;
				$showMonth	= $month;
				if( $day > $monthDays ){
					$class	= "inactive";
					$day	-= $monthDays;
					$showMonth++;
					if( $showMonth > 12 ){
						$showMonth	-= 12;
						$showYear++;
					}
				}
				$date	= $showYear.'-'.$showMonth.'-'.$day;
				/** @noinspection PhpUnhandledExceptionInspection */
				$row[]	= $this->renderDay( $userId, new DateTime( $date ), $orders, $class );
				$j++;
			}
			$weekNr	= date( "W", strtotime( $date ) );
			array_unshift( $row, '<th class="week-number"><span>'.$weekNr.'</span></th>' );
			$rows[]	= '<tr>'.join( $row ).'</tr>';
		}
		$colgroup	= HtmlElements::ColumnGroup( "3.75%", "13.75%", "13.75%", "13.75%", "13.75%", "13.75%", "13.75%", "13.75%" );
		$heads		= HtmlElements::TableHeads( ["KW", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"] );
		$thead		= HtmlTag::create( 'thead', $heads );
		$tbody		= HtmlTag::create( 'tbody', $rows );
		return HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['id' => "mission-calendar-large"] );
	}

	/**
	 *	@param		int|string		$userId
	 *	@param		string			$year
	 *	@param		string			$month
	 *	@return		string
	 *	@throws		DateMalformedStringException
	 */
	protected function renderCalendarSmall( int|string $userId, string $year, string $month ): string
	{
		$this->projects	= $this->logic->getUserProjects( $userId );
		$showMonth		= str_pad( $month, 2, "0", STR_PAD_LEFT );
		$showScope		= $year.'-'.$showMonth.'-01';
		$monthDays		= date( "t", strtotime( $showScope ) );
		$orders			= ['priority' => 'ASC'];

		$rows		= [];
		for( $i=1; $i<=$monthDays; $i++ ){
			/** @noinspection PhpUnhandledExceptionInspection */
			$date	= new DateTime( $year.'-'.$month.'-'.$i );
			$rows[]	= '<tr>'.$this->renderDay( $userId, $date, $orders ).'</tr>';
		}
		$colgroup	= HtmlElements::ColumnGroup( /*"5%", "95%"*/"100%" );
//		$heads		= HtmlElements::TableHeads( ["KW", "..."] );
		$thead		= HtmlTag::create( 'thead', ""/*$heads*/ );
		$tbody		= HtmlTag::create( 'tbody', $rows );
		return HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['id' => "mission-calendar-small"] );
	}

	protected function renderControls( $year, $month ): string
	{
		$isNow		= $year	=== date( "Y" ) && $month === date( "m" );
		$btnControlPrev	= HtmlTag::create( 'button', '&laquo;', [
			'type'		=> 'button',
			'class'		=> 'btn btn-large',
			'onclick'	=> 'WorkMissionsCalendar.setMonth(-1)',
			'title'		=> '1 Monat vor',
		] );
		$btnControlNext	= HtmlTag::create( 'button', '&raquo;', [
			'type'		=> 'button',
			'class'		=> 'btn btn-large',
			'onclick'	=> 'WorkMissionsCalendar.setMonth(1)',
			'title'		=> '1 Monat weiter',
		] );
		$btnControlNow	= HtmlTag::create( 'button', '&Omicron;', [
			'type'		=> 'button',
			'class'		=> 'btn btn-large '.( $isNow ? 'disabled' : NULL ),
			'onclick'	=> 'WorkMissionsCalendar.setMonth(0)',
			'title'		=> 'aktueller Monat',
			'disabled'	=> $isNow ? 'disabled' : NULL,
		] );

		$label			= $this->renderLabel( $year, $month );

		$btnExport		= HtmlTag::create( 'div', [
			HtmlTag::create( 'a', 'Export <span class="caret"></span>', ['href' => '#', 'class' => 'btn dropdown-toggle', 'data-toggle' => 'dropdown'] ),
			HtmlTag::create( 'ul', [
				HtmlTag::create( 'li', [
					HtmlTag::create( 'a', '<i class="fa fa-download"></i>&nbsp;im iCal-Format', [
						'href'		=> './work/mission/export/ical',
						'target'	=> '_blank',
					] ),
				], ['style' => 'text-align: left'] ),
				HtmlTag::create( 'li', [
					HtmlTag::create( 'a', '<i class="fa fa-download"></i>&nbsp;im CSV-Format', [
						'href'		=> './work/mission/export/csv',
						'target'	=> '_blank',
					] ),
				], ['style' => 'text-align: left'] ),
				HtmlTag::create( 'li', [
					HtmlTag::create( 'a', '<i class="fa fa-download"></i>&nbsp;im XML-Format', [
						'href'		=> './work/mission/export/xml',
						'target'	=> '_blank',
					] ),
				], ['style' => 'text-align: left'] ),
				HtmlTag::create( 'li', '', ['class' => 'divider'] ),
				HtmlTag::create( 'li', [
					HtmlTag::create( 'a', '<i class="fa fa-question-sign"></i>&nbsp;Anleitung', [
						'href'		=> './work/mission/export/help',
						'target'	=> '_blank',
					] )
				], ['style' => 'text-align: left'] )
			], ['class' => 'dropdown-menu pull-right'] )
		], ['class' => 'btn-group'] );
		return '
	<div id="mission-calendar-control" class="row-fluid">
		<div class="span8">
			<div class="btn-group">
				'.$btnControlPrev.'
				'.$btnControlNow.'
				'.$btnControlNext.'
<!--				<button type="button" class="btn btn" onclick="WorkMissionsCalendar.setMonth(-1)" title="1 Monat vor">&laquo;</button>-->
<!--				<button type="button" class="btn btn" onclick="WorkMissionsCalendar.setMonth(0)" title="aktueller Monat">&Omicron;</button>-->
<!--				<button type="button" class="btn btn" onclick="WorkMissionsCalendar.setMonth(1)" title="1 Monat weiter">&raquo;</button>-->
			</div>
			'.$label.'
		</div>
		<div class="span4" style="text-align: right">
			'.$btnExport.'
<!--			<a href="./work/mission/export/ical" target="_blank" class="btn not-btn-small" style="font-weight: normal"><i class="icon-calendar"></i> iCal-Export</a>-->
		</div>
	</div>';
	}

	/**
	 *	@param		int|string		$userId
	 *	@param		DateTime		$date
	 *	@param		array			$orders
	 *	@param		?string			$cellClass
	 *	@return		string
	 *	@throws		DateMalformedStringException
	 */
	protected function renderDay( int|string $userId, DateTime $date, array $orders, ?string $cellClass = NULL ): string
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
			if( 0 && $isPast )
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
	 *	@param		Entity_Mission		$mission		Mission data object
	 *	@return		string				DIV container with number of overdue days or empty string
	 *	@throws		DateMalformedStringException
	 */
	protected function renderOverdue( Entity_Mission $mission ): string
	{
		$end	= max( $mission->dayStart, $mission->dayEnd );										//  use maximum of start and end as due date
		/** @noinspection PhpUnhandledExceptionInspection */
		$diff	= $this->today->diff( new DateTime( $end ) );										//  calculate date difference
		if( $diff->days > 0 && $diff->invert )														//  date is overdue and in past
			return HtmlTag::create( 'div', $diff->days, ['class' => "overdue"] );				//  render overdue container
		return '';
	}
}