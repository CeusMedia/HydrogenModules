<?php
class View_Work_Mission_Calendar extends CMF_Hydrogen_View{

	public function __onInit(){
		$this->logic	= Logic_Work_Mission::getInstance( $this->env );
		$this->words	= $this->env->getLanguage()->load( 'work/mission' );
		$this->today	= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
	}

	public function ajaxRenderIndex(){
		extract( $this->getData() );

		$script	= '<script>
$(document).ready(function(){
	WorkMissionsCalendar.userId = '.(int) $this->env->getSession()->get( 'userId' ).';
	if(typeof cmContextMenu !== "undefined"){
		WorkMissionsCalendar.initContextMenu();
	};
});
</script>';
//		$this->env->getPage()->addHead( $script );

		$data			= array(
			'total'		=> 1,
			'buttons'	=> array(
				'large'	=> $this->renderControls( $year, $month ),
				'small'	=> $this->renderControls( $year, $month ),
			),
			'lists' => array(
				'large'	=> $this->renderCalendarLarge( $userId, $year, $month ).$script,
				'small'	=> $this->renderCalendarSmall( $userId, $year, $month ),
			)
		);
		print( json_encode( $data ) );
		exit;
	}

	public function index(){
		$page		= $this->env->getPage();
		$words		= $this->env->getLanguage()->load( 'work/mission' );

		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissions.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsFilter.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsList.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsCalendar.js' );

		$script		= '
WorkMissionsCalendar.monthNames = '.json_encode( $words['months'] ).';
WorkMissionsCalendar.monthNamesShort = '.json_encode( $words['months-short'] ).';
WorkMissions.init("calendar");
WorkMissionsCalendar.userId = '.(int) $this->env->getSession()->get( 'userId' ).';
WorkMissionsCalendar.monthCurrent	= '.date( "n" ).';
WorkMissionsCalendar.month			= '.(int) $this->getData( 'month' ).';
WorkMissionsCalendar.year			= '.(int) $this->getData( 'year' ).';
if(typeof cmContextMenu !== "undefined"){
	cmContextMenu.labels.priorities = '.json_encode( $words['priorities'] ).';
	cmContextMenu.labels.states = '.json_encode( $words['states'] ).';
};
WorkMissionsList.loadCurrentListAndDayControls();
';
		$page->js->addScript( $script, 'ready' );
		$page->js->addScriptOnReady( 'setInterval(WorkMissionsCalendar.checkForUpdate, 10000)', 'ready' );

		$this->addData( 'filter', $this->loadTemplateFile( 'work/mission/index.filter.php' ) );
	}

	public function renderCalendarLarge( $userId, $year, $month ){
		$this->projects	= $this->logic->getUserProjects( $userId );
		$showMonth		= str_pad( $month, 2, "0", STR_PAD_LEFT );
		$showScope		= $year.'-'.$showMonth.'-01';
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
		$colgroup	= UI_HTML_Elements::ColumnGroup( "3.75%", "13.75%", "13.75%", "13.75%", "13.75%", "13.75%", "13.75%", "13.75%" );
		$heads		= UI_HTML_Elements::TableHeads( array( "KW", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag" ) );
		$thead		= UI_HTML_Tag::create( 'thead', $heads );
		$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
		$tableLarge	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'id' => "mission-calendar-large" ) );
		return $tableLarge;
	}

	protected function renderCalendarSmall( $userId, $year, $month ){
		$this->projects	= $this->logic->getUserProjects( $userId );
		$showMonth		= str_pad( $month, 2, "0", STR_PAD_LEFT );
		$showScope		= $year.'-'.$showMonth.'-01';
		$monthDate		= new DateTime( $showScope );
		$monthDays		= date( "t", strtotime( $showScope ) );
		$offsetStart	= date( "w", strtotime( $showScope ) ) - 1;
		$offsetStart	= $offsetStart >= 0 ? $offsetStart : 6;
		$weeks			= ceil( ( $monthDays + $offsetStart ) / 7 );
		$orders			= array( 'priority' => 'ASC' );

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
		$colgroup	= UI_HTML_Elements::ColumnGroup( /*"5%", "95%"*/"100%" );
		$heads		= UI_HTML_Elements::TableHeads( array( "KW", "..." ) );
		$thead		= UI_HTML_Tag::create( 'thead', ""/*$heads*/ );
		$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
		$tableSmall	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'id' => "mission-calendar-small" ) );
		return $tableSmall;
	}

	protected function renderControls( $year, $month ){
		$isNow		= $year	=== date( "Y" ) && $month === date( "m" );
		$btnControlPrev	= UI_HTML_Tag::create( 'button', '&laquo;',  array(
			'type'		=> 'button',
			'class'		=> 'btn btn-large',
			'onclick'	=> 'WorkMissionsCalendar.setMonth(-1)',
			'title'		=> '1 Monat vor',
		) );
		$btnControlNext	= UI_HTML_Tag::create( 'button', '&raquo;',  array(
			'type'		=> 'button',
			'class'		=> 'btn btn-large',
			'onclick'	=> 'WorkMissionsCalendar.setMonth(1)',
			'title'		=> '1 Monat weiter',
		) );
		$btnControlNow	= UI_HTML_Tag::create( 'button', '&Omicron;',  array(
			'type'		=> 'button',
			'class'		=> 'btn btn-large '.( $isNow ? 'disabled' : NULL ),
			'onclick'	=> 'WorkMissionsCalendar.setMonth(0)',
			'title'		=> 'aktueller Monat',
			'disabled'	=> $isNow ? 'disabled' : NULL,
		) );

		$label			= $this->renderLabel( $year, $month );

		$btnExport		= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', 'Export <span class="caret"></span>', array( 'href' => '#', 'class' => 'btn dropdown-toggle', 'data-toggle' => 'dropdown' ) ),
			UI_HTML_Tag::create( 'ul', array(
				UI_HTML_Tag::create( 'li', array(
					UI_HTML_Tag::create( 'a', '<i class="fa fa-download"></i>&nbsp;im iCal-Format', array(
						'href'		=> './work/mission/export/ical',
						'target'	=> '_blank',
					) ),
				), array( 'style' => 'text-align: left' ) ),
				UI_HTML_Tag::create( 'li', array(
					UI_HTML_Tag::create( 'a', '<i class="fa fa-download"></i>&nbsp;im CSV-Format', array(
						'href'		=> './work/mission/export/csv',
						'target'	=> '_blank',
					) ),
				), array( 'style' => 'text-align: left' ) ),
				UI_HTML_Tag::create( 'li', array(
					UI_HTML_Tag::create( 'a', '<i class="fa fa-download"></i>&nbsp;im XML-Format', array(
						'href'		=> './work/mission/export/xml',
						'target'	=> '_blank',
					) ),
				), array( 'style' => 'text-align: left' ) ),
				UI_HTML_Tag::create( 'li', '', array( 'class' => 'divider' ) ),
				UI_HTML_Tag::create( 'li', array(
					UI_HTML_Tag::create( 'a', '<i class="fa fa-question-sign"></i>&nbsp;Anleitung', array(
						'href'		=> './work/mission/export/help',
						'target'	=> '_blank',
					) )
				), array( 'style' => 'text-align: left' ) )
			), array( 'class' => 'dropdown-menu pull-right' ) )
		), array( 'class' => 'btn-group' ) );
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

	protected function renderDay( $userId, DateTime $date, $orders, $cellClass = NULL ){
		$diff		= $this->today->diff( $date );
		$isPast		= $diff->invert;
		$isToday	= $diff->days == 0;
		$conditions	= $this->logic->getFilterConditions( 'filter.work.mission.calendar.' );
		$conditions['dayStart']	= $date->format( "Y-m-d" );
		$missions	= $this->logic->getUserMissions( $userId, $conditions, $orders );
		$list		= array();
		foreach( $missions as $mission ){
		//	$title		= Alg_Text_Trimmer::trim( $mission->title, 20 );
			$title		= htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' );
			$title		= preg_replace( "/^--(.+)--$/", "<strike>\\1</strike>", $title );
			$url		= './work/mission/view/'.$mission->missionId;
			$class		= 'mission-icon-label mission-type-'.$mission->type;
			$title		= '<a class="'.$class.'" href="'.$url.'">'.$title.'</a>';
			$overdue	= '';
			if( 0 && $isPast )
				$overdue	= $this->renderOverdue( $mission );
			$list[]	= UI_HTML_Tag::create( 'li', $overdue.$title, array(
				"class"			=> 'priority-'.$mission->priority,
				"data-id"		=> $mission->missionId,
				"data-type"		=> $mission->type,
				"data-priority"	=> $mission->priority,
				"data-status"	=> $mission->status,
				"data-title"	=> htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' ),
				"data-date"		=> date( "j.n. Y", strtotime( $mission->dayStart ) ),
				"data-time"		=> $mission->type ? $mission->timeStart.' - '.$mission->timeEnd : null,
				"data-project"	=> $mission->projectId ? $this->projects[$mission->projectId]->title : $mission->projectId,
			) );
		}
		$class	= $isToday ? 'active today' : ( $isPast ? 'past' : 'active future' );
		$class	= $cellClass ? $cellClass.' '.$class : $class;
		$list	= '<ul>'.join( $list ).'</ul>';
		$label	= '<div class="date-label '.$class.'">'.$date->format( "j.n." ).'</div>';
		return UI_HTML_Tag::create( 'td', $label.$list, array(
			"oncontextmenu"	=> "return false",
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
		return '<span id="mission-calendar-control-label">
	<span class="month-label">'.$this->words['months'][(int) $month].'</span>
	<span class="year-label">'.$year.'</span>
</span>';
	}

	/**
	 *	Render overdue container.
	 *	@access		protected
	 *	@param		object		$mission		Mission data object
	 *	@return		string		DIV container with number of overdue days or empty string
	 */
	protected function renderOverdue( $mission ){
		$end	= max( $mission->dayStart, $mission->dayEnd );										//  use maximum of start and end as due date
		$diff	= $this->today->diff( new DateTime( $end ) );										//  calculate date difference
		if( $diff->days > 0 && $diff->invert )														//  date is overdue and in past
			return UI_HTML_Tag::create( 'div', $diff->days, array( 'class' => "overdue" ) );		//  render overdue container
	}
}
?>
