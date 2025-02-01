<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Info_Event_Calendar
{
	protected Environment $env;
	protected DateTime $today;
	protected string $year;
	protected string $month;
	protected array $words;
	protected array $projects	= [];
	protected array $events	= [];

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->today	= new DateTime( date( 'Y-m-d', time() ) );
		$this->words	= $this->env->getLanguage()->load( 'info/event' );
		$this->year		= date( "Y" );
		$this->month	= date( "m" );
	}

	public function render(): string
	{
		$showMonth		= str_pad( $this->month, 2, "0", STR_PAD_LEFT );
		$showScope		= $this->year.'-'.$showMonth.'-01';
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
					$row[]		= $this->renderDay( $preDate, $orders, 'inactive' );
				}
			}
			while( $j < 7 ){
				$day		= $i * 7 - $offsetStart + $j +1;
				$showYear	= $this->year;
				$showMonth	= $this->month;
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
				$row[]	= $this->renderDay( new DateTime( $date ), $orders, $class );
				$j++;
			}
			if( isset( $date ) ){
				$weekNr	= date( "W", strtotime( $date ) );
				array_unshift( $row, '<th class="week-number"><span>'.$weekNr.'</span></th>' );
				$rows[]	= '<tr>'.join( $row ).'</tr>';
			}
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
				$showYear	= $this->year;
				$showMonth	= $this->month;
				if( $day <= $monthDays ){
					$date	= $showYear.'-'.$showMonth.'-'.$day;
					$row[]	= '<tr>'.$this->renderDay( new DateTime( $date ), $orders, $class ).'</tr>';
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


		$controls		= $this->renderControls();
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
});
</script>';

//		$this->env->getPage()->addThemeStyle( 'cmContextMenu.css' );
//		$this->env->getPage()->js->addUrl( 'javascripts/cmContextMenu.js' );
		$this->env->getPage()->addHead( $script );
		return $table;
	}

	protected function renderControls(): string
	{
		$isNow		= $this->year	=== date( "Y" ) && $this->month === date( "m" );

		$nextYear		= $this->year;
		$nextMonth		= str_pad( $this->month + 1, 2, 0, STR_PAD_LEFT );
		if( $nextMonth > 12 ){
			$nextMonth	= '01';
			$nextYear++;
		}

		$prevYear		= $this->year;
		$prevMonth		= str_pad( $this->month - 1, 2, 0, STR_PAD_LEFT );
		if( $prevMonth < 1 ){
			$prevMonth	= 12;
			$prevYear--;
		}


		$btnControlPrev	= HtmlTag::create( 'a', '&laquo;',  [
			'href'		=> './info/event/setMonth/'.$prevYear.'/'.$prevMonth,
			'class'		=> 'btn btn-large',
			'title'		=> '1 Monat vor',
		] );
		$btnControlNext	= HtmlTag::create( 'a', '&raquo;',  [
			'href'		=> './info/event/setMonth/'.$nextYear.'/'.$nextMonth,
			'class'		=> 'btn btn-large',
			'title'		=> '1 Monat weiter',
		] );
		$btnControlNow	= HtmlTag::create( 'a', '&Omicron;',  [
			'href'		=> './info/event/setMonth/'.date( 'Y' ).'/'.date( 'm' ),
			'class'		=> 'btn btn-large '.( $isNow ? 'disabled' : NULL ),
			'title'		=> 'aktueller Monat',
			'disabled'	=> $isNow ? 'disabled' : NULL,
		] );

		$label		= $this->renderLabel( $this->year, $this->month );

		$btnExport		= HtmlTag::create( 'a', '<i class="icon-calendar icon-white"></i> iCal-Export', [
			'href'		=> './info/event/export/ical',
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
			</div>
			'.$label.'
		</div>
		<div class="span4" style="text-align: right">
			'.$btnExport.'
		</div>
	</div>';
	}

	protected function renderDay( DateTime $date, $orders, string $cellClass = NULL ): string
	{
		$diff		= $this->today->diff( $date );
		$isPast		= $diff->invert;
		$isToday	= $diff->days == 0;
		$list		= [];
		foreach( $this->events as $event ){
			$eventDate	= new DateTime( $event->dateStart );
			if( $eventDate->diff( $date )->days !== 0 )
				continue;
			$title		= htmlentities( $event->title, ENT_QUOTES, 'UTF-8' );
			$title		= HtmlTag::create( 'a', $title, [
				'href'			=> './ajax/info/event/modalView/'.$event->eventId,
				'data-toggle'	=> 'modal',
				'data-target'	=> "#modal-event-view",
			] );
			$list[]		= HtmlTag::create( 'li', $title, [
				"data-id"		=> $event->eventId,
				"data-status"	=> $event->status,
				"data-title"	=> htmlentities( $event->title, ENT_QUOTES, 'UTF-8' ),
				"data-date"		=> date( "j.n. Y", strtotime( $event->dateStart ) ),
				"data-time"		=> $event->timeStart.' - '.$event->timeEnd,
			] );
		}
		$class	= $isToday ? 'active today' : ( $isPast ? 'past' : 'active future' );
		$class	= $cellClass ? $cellClass.' '.$class : $class;
		return HtmlTag::create( 'td', [
			HtmlTag::create( 'div', $date->format( "j" ), ['class' => 'date-label '.$class] ),
			HtmlTag::create( 'ul', $list ),
		], [
			"class"			=> $class,
			"data-day"		=> $date->format( "j" ),
			"data-month"	=> $date->format( "n" ),
			"data-year"		=> $date->format( "Y" ),
			"data-date"		=> $date->format( "Y-m-d" )
		] );
	}

	protected function renderLabel( string $year, string $month ): string
	{
		$month	= (int) $month;
		if( $month < 1 || $month > 12 )
			throw new InvalidArgumentException( 'Invalid month' );
		return HtmlTag::create( 'span', [
			HtmlTag::create( 'span', $this->words['months'][$month], ['class' => "month-label"] ),
			HtmlTag::create( 'span', $year, ['class' => "year-label"] ),
		], ['id' => 'mission-calendar-control-label'] );
	}

	public function setEvents( array $events ): self
	{
		$this->events	= $events;
		return $this;
	}

	public function setMonth( string $year, string $month ): self
	{
		$this->year		= $year;
		$this->month	= $month;
		return $this;
	}
}
