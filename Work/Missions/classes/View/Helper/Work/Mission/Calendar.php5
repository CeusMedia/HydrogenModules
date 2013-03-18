<?php
class View_Helper_Work_Mission_Calendar{

	protected $env;
	protected $logic;
	protected $projects	= array();
	protected $today;
	protected $words;

	public function __construct( $env ){
		$this->env		= $env;
		$this->logic	= new Logic_Mission( $this->env );
		$this->today	= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
		$this->words	= $this->env->getLanguage()->load( 'work/mission' );
	}

	public function renderLabel( $year, $month ){
		$month	= (int) $month;
		if( $month < 1 || $month > 12 )
			throw new InvalidArgumentException( 'Invalid month' );
		return '<span id="mission-calendar-control">
	<span class="month-label">'.$this->words['months'][(int) $month].'</span>
	<span class="year-label">'.$year.'</span>
</span>';
	}

	public function render( $userId, $year, $month ){
		$this->projects	= $this->logic->getUserProjects( $userId );
		$showMonth		= str_pad( $month, 2, "0", STR_PAD_LEFT );
		$showScope		= $year.'-'.$showMonth.'-01';
		$monthDate		= new DateTime( $showScope );
		$monthDays		= date( "t", strtotime( $showScope ) );
		$offsetStart	= max( 0, date( "w", strtotime( $showScope ) ) - 1 );
		$weeks			= ceil( ( $monthDays + $offsetStart ) / 7 );
		$rows			= array();
		$orders			= array( 'priority' => 'ASC' );
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
		$colgroup	= UI_HTML_Elements::ColumnGroup( "3%", "14%", "14%", "14%", "14%", "14%", "13%", "12%" );
		$label      = $this->renderLabel( $year, $month );

		$table	= '
<div id="mission-folders">
	<table id="mission-calendar">
		'.$colgroup.'
		<thead>
			<tr>
				<th colspan="8">
					<button type="button" class="button" onclick="WorkMissionsCalendar.setMonth(-1)">&laquo;</button>
					<button type="button" class="button" onclick="WorkMissionsCalendar.setMonth(0)">&Omicron;</button>
					<button type="button" class="button" onclick="WorkMissionsCalendar.setMonth(1)">&raquo;</button>
					'.$label.'
					<div style="float: right;">
						'.UI_HTML_Elements::LinkButton( './work/mission/export/ical', 'iCal-Export', 'button icon export ical' ).'
					</div>
				</th>
			</tr>
			<tr>
				<th>KW</th>
				<th>Montag</th>
				<th>Dienstag</th>
				<th>Mittwoch</th>
				<th>Donnerstag</th>
				<th>Freitag</th>
				<th>Samstag</th>
				<th>Sonntag</th>
			</tr>
		</thead>
		<tbody>
			'.join( $rows ).'
		</tbody>
	</table>
</div>
';
		$script	= '<script>
$(document).ready(function(){
	WorkMissionsCalendar.monthCurrent = '.date( "n" ).';
	WorkMissionsCalendar.monthShow    = '.(int) $showMonth.';
//	$("table#mission-calendar tr td ul li").draggable({containment: "#mission-calendar tbody", revert: true});
	if(typeof cmContextMenu !== "undefined"){
		var pathIcons	= "http://img.int1a.net/famfamfam/silk/";
		cmContextMenu.init("#mission-calendar tbody ul li");
		cmContextMenu.labels.priorities = '.json_encode( $this->words['priorities'] ).';
		cmContextMenu.labels.states = '.json_encode( $this->words['states'] ).';
		cmContextMenu.assignRenderer("#mission-calendar tbody tr td", function(menu, elem){
			menu.addItem("<h4><big>"+elem.data("day")+"."+elem.data("month")+"."+elem.data("year")+"</big></h4>");
			var url = "./work/mission/add/?type=0&dayStart="+elem.data("date")+"&dayEnd="+elem.data("date");
			menu.addLinkItem(url, "neue Aufgabe", pathIcons+"script_add.png");
			var url = "./work/mission/add/?type=1&dayStart="+elem.data("date")+"&dayEnd="+elem.data("date");
			menu.addLinkItem(url, "neuer Termin", pathIcons+"date_add.png");
		});
		cmContextMenu.assignRenderer("#mission-calendar tbody ul li", function(menu, elem){
			var missionId = elem.data("id");
			if(elem.data("title"))
				menu.addItem("<h4>"+elem.data("title")+"</h4>");
			if(elem.data("project"))
				menu.addItem("<small>Projekt: </small>"+elem.data("project"));
			if(elem.data("date"))
				menu.addItem("<small>Datum: </small>"+elem.data("date"));
			if(elem.data("time"))
				menu.addItem("<small>Zeit: </small>"+elem.data("time"));
			if(elem.data("priority"))
				menu.addItem("<small>Priorität: </small>"+menu.labels.priorities[elem.data("priority")]);
			if(elem.data("status"))
				menu.addItem("<small>Status: </small>"+menu.labels.states[elem.data("status")]);
			var url = "./work/mission/edit/"+missionId;
			menu.addLinkItem(url, "bearbeiten", pathIcons+"pencil.png");
			var url = "./work/mission/changeDay/"+missionId+"?date=-1";
			menu.addLinkItem(url, "1 Tag eher", pathIcons+"arrow_left.png");
			var url = "./work/mission/changeDay/"+missionId+"?date=%2B1";
			menu.addLinkItem(url, "1 Tag später", pathIcons+"arrow_right.png");
		});
	};
});
</script>';

		$this->env->getPage()->addThemeStyle( 'cmContextMenu.css' );
		$this->env->getPage()->js->addUrl( 'javascripts/cmContextMenu.js' );
		$this->env->getPage()->addHead( $script );
		return $table;
	}

	protected function renderDay( $userId, DateTime $date, $orders, $cellClass = NULL ){
		$diff		= $this->today->diff( $date );
		$isPast		= $diff->invert;
		$isToday	= $diff->days == 0;
		$conditions	= array( 'dayStart' => $date->format( "Y-m-d" ), 'status' => array( 0, 1, 2, 3 ) );
		$missions	= $this->logic->getUserMissions( $userId, $conditions, $orders );
		$list		= array();
		foreach( $missions as $mission ){
		//	$title		= Alg_Text_Trimmer::trim( $mission->title, 20 );
			$title		= htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' );
			$title		= preg_replace( "/^--(.+)--$/", "<strike>\\1</strike>", $title );
			$url		= './work/mission/edit/'.$mission->missionId;
			$class		= 'mission-icon-label mission-type-'.$mission->type;
			$title		= '<a class="'.$class.'" href="'.$url.'">'.$title.'</a>';
			$overdue	= '';
			if( $isPast )
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
}
?>
