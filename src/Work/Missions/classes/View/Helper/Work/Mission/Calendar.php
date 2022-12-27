<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class View_Helper_Work_Mission_Calendar
{
	protected WebEnvironment $env;
	protected Logic_Work_Mission $logic;
	protected array $projects	= [];
	protected DateTime $today;
	protected array $words;

	public function __construct( WebEnvironment $env )
	{
		$this->env		= $env;
		$this->logic	= Logic_Work_Mission::getInstance( $this->env );
		$this->today	= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
		$this->words	= $this->env->getLanguage()->load( 'work/mission' );
	}

	public function render( $userId, $year, $month ): string
	{
		$this->projects	= $this->logic->getUserProjects( $userId );
		$showMonth		= str_pad( $month, 2, "0", STR_PAD_LEFT );
		$showScope		= $year.'-'.$showMonth.'-01';
		$monthDate		= new DateTime( $showScope );
		$monthDays		= date( "t", strtotime( $showScope ) );
		$offsetStart	= date( "w", strtotime( $showScope ) ) - 1;
		$offsetStart	= $offsetStart >= 0 ? $offsetStart : 6;
		$weeks			= ceil( ( $monthDays + $offsetStart ) / 7 );
		$orders			= ['priority' => 'ASC'];

		$rows			= [];
		for( $i=0; $i<$weeks; $i++ ){
			$row	= [];
			$j	= 0;
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
				$row[]	= $this->renderDay( $userId, new DateTime( $date ), $orders, $class );
				$j++;
			}
			$weekNr	= date( "W", strtotime( $date ) );
			array_unshift( $row, '<th class="week-number"><span>'.$weekNr.'</span></th>' );
			$rows[]	= '<tr>'.join( $row ).'</tr>';
		}
		$colgroup	= HtmlElements::ColumnGroup( "2%", "14%", "14%", "14%", "14%", "14%", "14%", "14%" );
		$heads		= HtmlElements::TableHeads( ["KW", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"] );
		$thead		= HtmlTag::create( 'thead', $heads );
		$tbody		= HtmlTag::create( 'tbody', $rows );
		$tableLarge	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['id' => "mission-calendar-large"] );


		$rows			= [];
		for( $i=0; $i<$weeks; $i++ ){
			$row	= [];
			$j		= 0;
			$class	= '';
			if( $i == 0 ){
				for( $j=0; $j<$offsetStart; $j++ ){
//					$preDate	= clone $monthDate;
//					$preDate	= $preDate->modify( "-".( $offsetStart - $j )." days" );
//					$row[]		= $this->renderDay( $userId, $preDate, $orders, 'inactive' );
				}
			}
			while( $j < 7 ){
				$day		= $i * 7 - $offsetStart + $j +1;
				$showYear	= $year;
				$showMonth	= $month;
				if( $day <= $monthDays ){
					$date	= $showYear.'-'.$showMonth.'-'.$day;
					$row[]	= '<tr>'.$this->renderDay( $userId, new DateTime( $date ), $orders, $class ).'</tr>';
  				}
/*
					$class	= "inactive";
					$day	-= $monthDays;
					$showMonth++;
					if( $showMonth > 12 ){
						$showMonth	-= 12;
						$showYear++;
					}
				}
*/				$j++;
			}
//			$weekNr	= date( "W", strtotime( $date ) );
//			array_unshift( $row, '<th class="week-number"><span>'.$weekNr.'</span></th>' );
			$rows[]	= join( $row );
		}
		$colgroup	= HtmlElements::ColumnGroup( /*"5%", "95%"*/"100%" );
		$heads		= HtmlElements::TableHeads( ["KW", "..."] );
		$thead		= HtmlTag::create( 'thead', ""/*$heads*/ );
		$tbody		= HtmlTag::create( 'tbody', $rows );
		$tableSmall	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['id' => "mission-calendar-small"] );

//		$tableSmall = '<div class="muted"><em><small>Noch nicht implementiert.</small></em></div>';


		$controls		= $this->renderControls( $year, $month );
		$table	= '
<div id="mission-folders">
	'.$controls.'
	<div id="mission-calendar">
		<div class="visible-desktop">'.$tableLarge.'</div>
		<div class="hidden-desktop">'.$tableSmall.'</div>
	</div>
</div>
';
		$script	= '<script>
$(document).ready(function(){
//	WorkMissionsCalendar.userId = '.(int) $this->env->getSession()->get( 'auth_user_id' ).';
	WorkMissionsCalendar.monthCurrent = '.date( "n" ).';
	WorkMissionsCalendar.monthShow    = '.(int) $showMonth.';
//	$("table#mission-calendar tr td ul li").draggable({containment: "#mission-calendar tbody", revert: true});
	if(typeof cmContextMenu !== "undefined"){
		cmContextMenu.labels.priorities = '.json_encode( $this->words['priorities'] ).';
		cmContextMenu.labels.states = '.json_encode( $this->words['states'] ).';
		WorkMissionsCalendar.initContextMenu();
	};
});
</script>';

//		$this->env->getPage()->addThemeStyle( 'cmContextMenu.css' );
//		$this->env->getPage()->js->addUrl( 'javascripts/cmContextMenu.js' );
		$this->env->getPage()->addHead( $script );
		return $table;
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

		$label		= $this->renderLabel( $year, $month );

		$btnExport		= HtmlTag::create( 'a', '<i class="icon-calendar icon-white"></i> iCal-Export', [
			'href'		=> './work/mission/export/ical',
			'target'	=> '_blank',
			'class'		=> 'btn not-btn-small btn-warning',
			'style'		=> 'font-weight: normal',
		] );
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

	protected function renderDay( $userId, DateTime $date, $orders, $cellClass = NULL ): string
	{
		$diff		= $this->today->diff( $date );
		$isPast		= $diff->invert;
		$isToday	= $diff->days == 0;
		$conditions	= ['dayStart' => $date->format( "Y-m-d" ), 'status' => [0, 1, 2, 3]];
		$missions	= $this->logic->getUserMissions( $userId, $conditions, $orders );
		$list		= [];
		foreach( $missions as $mission ){
		//	$title		= TextTrimmer::trim( $mission->title, 20 );
			$title		= htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' );
			$title		= preg_replace( "/^--(.+)--$/", "<del>\\1</del>", $title );
			$url		= './work/mission/edit/'.$mission->missionId;
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
	 *	@access		public
	 *	@param		object		$mission		Mission data object
	 *	@return		string		DIV container with number of overdue days or empty string
	 */
	public function renderOverdue( object $mission ): string
	{
		$end	= max( $mission->dayStart, $mission->dayEnd );										//  use maximum of start and end as due date
		$diff	= $this->today->diff( new DateTime( $end ) );										//  calculate date difference
		if( $diff->days > 0 && $diff->invert )														//  date is overdue and in past
			return HtmlTag::create( 'div', $diff->days, ['class' => "overdue"] );		//  render overdue container
		return '';
	}
}
