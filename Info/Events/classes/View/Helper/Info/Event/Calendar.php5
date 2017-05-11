<?php
class View_Helper_Info_Event_Calendar{

	protected $env;
	protected $logic;
	protected $projects	= array();
	protected $today;
	protected $words;
	protected $events	= array();

	public function __construct( $env ){
		$this->env		= $env;
		$this->today	= new DateTime( date( 'Y-m-d', time() ) );
		$this->words	= $this->env->getLanguage()->load( 'info/event' );
		$this->year		= date( "Y" );
		$this->month	= date( "m" );
	}

	public function render(){
		$showMonth		= str_pad( $this->month, 2, "0", STR_PAD_LEFT );
		$showScope		= $this->year.'-'.$showMonth.'-01';
		$monthDate		= new DateTime( $showScope );
		$monthDays		= date( "t", strtotime( $showScope ) );
		$offsetStart	= date( "w", strtotime( $showScope ) ) - 1;
		$offsetStart	= $offsetStart >= 0 ? $offsetStart : 6;
		$weeks			= ceil( ( $monthDays + $offsetStart ) / 7 );
		$orders			= array( 'priority' => 'ASC' );

		$rows			= array();
		for( $i=0; $i<$weeks; $i++ ){
			$row	= array();
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
			$weekNr	= date( "W", strtotime( $date ) );
			array_unshift( $row, '<th class="week-number"><span>'.$weekNr.'</span></th>' );
			$rows[]	= '<tr>'.join( $row ).'</tr>';
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( "2%", "14%", "14%", "14%", "14%", "14%", "14%", "14%" );
		$heads		= UI_HTML_Elements::TableHeads( array( "KW", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag" ) );
		$thead		= UI_HTML_Tag::create( 'thead', $heads );
		$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
		$tableLarge	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'id' => "mission-calendar-large" ) );


		$rows			= array();
		for( $i=0; $i<$weeks; $i++ ){
			$row	= array();
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
		$colgroup	= UI_HTML_Elements::ColumnGroup( /*"5%", "95%"*/"100%" );
		$heads		= UI_HTML_Elements::TableHeads( array( "KW", "..." ) );
		$thead		= UI_HTML_Tag::create( 'thead', ""/*$heads*/ );
		$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
		$tableSmall	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'id' => "mission-calendar-small" ) );

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

	protected function renderControls(){
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


		$btnControlPrev	= UI_HTML_Tag::create( 'a', '&laquo;',  array(
			'href'		=> './info/event/setMonth/'.$prevYear.'/'.$prevMonth,
			'class'		=> 'btn btn-large',
			'title'		=> '1 Monat vor',
		) );
		$btnControlNext	= UI_HTML_Tag::create( 'a', '&raquo;',  array(
			'href'		=> './info/event/setMonth/'.$nextYear.'/'.$nextMonth,
			'class'		=> 'btn btn-large',
			'title'		=> '1 Monat weiter',
		) );
		$btnControlNow	= UI_HTML_Tag::create( 'a', '&Omicron;',  array(
			'href'		=> './info/event/setMonth/'.date( 'Y' ).'/'.date( 'm' ),
			'class'		=> 'btn btn-large '.( $isNow ? 'disabled' : NULL ),
			'title'		=> 'aktueller Monat',
			'disabled'	=> $isNow ? 'disabled' : NULL,
		) );

		$label      = $this->renderLabel( $this->year, $this->month );

		$btnExport		= UI_HTML_Tag::create( 'a', '<i class="icon-calendar icon-white"></i> iCal-Export', array(
			'href'		=> './info/event/export/ical',
			'target'	=> '_blank',
			'class'		=> 'btn not-btn-small btn-warning',
			'style'		=> 'font-weight: normal',
		) );
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

	protected function renderDay( DateTime $date, $orders, $cellClass = NULL ){
		$diff		= $this->today->diff( $date );
		$isPast		= $diff->invert;
		$isToday	= $diff->days == 0;
		$conditions	= array( 'dayStart' => $date->format( "Y-m-d" ), 'status' => array( 0, 1, 2, 3 ) );
		$list		= array();
		foreach( $this->events as $event ){
			$eventDate	= new DateTime( $event->dateStart );
			if( $eventDate->diff( $date )->days !== 0 )
				continue;
			$title		= htmlentities( $event->title, ENT_QUOTES, 'UTF-8' );
			$title		= UI_HTML_Tag::create( 'a', $title, array(
				'href'			=> './info/event/modalView/'.$event->eventId,
				'data-toggle'	=> 'modal',
				'data-target'	=> "#modal-event-view",
			) );
			$list[]		= UI_HTML_Tag::create( 'li', $title, array(
				"data-id"		=> $event->eventId,
				"data-status"	=> $event->status,
				"data-title"	=> htmlentities( $event->title, ENT_QUOTES, 'UTF-8' ),
				"data-date"		=> date( "j.n. Y", strtotime( $event->dateStart ) ),
				"data-time"		=> $event->timeStart.' - '.$event->timeEnd,
			) );
		}
		$class	= $isToday ? 'active today' : ( $isPast ? 'past' : 'active future' );
		$class	= $cellClass ? $cellClass.' '.$class : $class;
		return UI_HTML_Tag::create( 'td', array(
				$label	= UI_HTML_Tag::create( 'div', $date->format( "j" ), array( 'class' => 'date-label '.$class ) ),
				$list	= UI_HTML_Tag::create( 'ul', $list ),
			), array(
			"class"			=> $class,
			"data-day"		=> $date->format( "j" ),
			"data-month"	=> $date->format( "n" ),
			"data-year"		=> $date->format( "Y" ),
			"data-date"		=> $date->format( "Y-m-d" )
		) );
	}

	protected function renderLabel( $year, $month ){
		$month	= (int) $month;
		if( $month < 1 || $month > 12 )
			throw new InvalidArgumentException( 'Invalid month' );
		return UI_HTML_Tag::create( 'span', array(
			UI_HTML_Tag::create( 'span', $this->words['months'][(int) $month], array( 'class' => "month-label" ) ),
			UI_HTML_Tag::create( 'span', $year, array( 'class' => "year-label" ) ),
		), array( 'id' => 'mission-calendar-control-label' ) );
	}

	public function setEvents( $events ){
		$this->events	= $events;
	}

	public function setMonth( $year, $month ){
		$this->year		= $year;
		$this->month	= $month;
	}
}
?>
