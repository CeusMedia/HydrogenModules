<?php
class View_Helper_MissionCalendar{
	public function __construct( $env ){
		$this->env	= $env;
	}

	public function render(){
		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$userId		= $session->get( 'userId' );
		$words		= $this->env->getLanguage()->load( 'work/mission' );
		$model		= new Model_Mission( $this->env );
		$month		= $request->has( 'month' ) ? (int) $request->get( 'month' ) : (int) date( "n" );
		$year		= $request->has( 'year' ) ? (int) $request->get( 'year' ) : (int) date( "Y" );

		$showYear	= (int) date( "Y" );
		while( $month > 12 ){
			$showYear++;
			$month -= 12;
		}
		while( $month < 1 ){
			$showYear--;
			$month += 12;
		}

		$showMonth	= str_pad( $month, 2, "0", STR_PAD_LEFT );
		$showScope	= $showYear.'-'.$showMonth.'-01';
		ob_start();
		$monthDays	= date( "t", strtotime( $showScope ) );
		$offsetStart	= max( 0, date( "w", strtotime( $showScope ) ) - 1 );
		$weeks		= ceil( ( $monthDays + $offsetStart ) / 7 );
		$rows		= array();
		for( $i=0; $i<$weeks; $i++ ){
			$row	= array();
			$j	= 0;
			if( $i == 0 )
				for( $j=0; $j<$offsetStart; $j++ )
					$row[]	= '<td class="inactive"></td>';
			while( $j<7 ){
				$day	= $i * 7 - $offsetStart + $j +1;
				if( $day > $monthDays )
					$row[]	= '<td class="inactive"></td>';
				else{
					$days	= str_pad( $day, 2, "0", STR_PAD_LEFT );
					$date	= $showYear.'-'.$showMonth.'-'.$days;
					$today	= strtotime( $date ) == strtotime( date( "Y-m-d 00:00" ) );
					$tasks	= $model->getAll( array( "dayStart" => $date, 'ownerId' => $userId, 'status' => array( 0, 1, 2, 3 ) ), array( 'priority' => 'ASC' ) );
					$list	= array();
					foreach( $tasks as $task ){
					//	$title	= Alg_Text_Trimmer::trim( $task->content, 20 );
						$title	= $task->content;
						$title	= '<a class="icon-label mission-type-'.$task->type.'" href="./work/mission/edit/'.$task->missionId.'">'.$title.'</a>';
						$list[]	= '<li class="priority-'.$task->priority.'">'.$title.'</li>';
					}
					$list	= '<ul>'.join( $list ).'</ul>';
					$class	= $today ? 'today' : '';
					$label	= '<div class="date-label '.$class.'">'.date( "j.n.", strtotime( $date ) ).'</div>';
					$class	= strtotime( $date ) < strtotime( date( "Y-m-d 00:00" ) ) ? "past" : "";
					$row[]	= '<td class="'.$class.'">'.$label.$list.'</td>';
				}
				$j++;
			}
			$rows[]	= '<tr>'.join( $row ).'</tr>';
		}
		$table	= '
<div id="mission-folders">
	<table id="mission-calendar">
	<tr>
		<th>Montag</th>
		<th>Dienstag</th>
		<th>Mittwoch</th>
		<th>Donnerstag</th>
		<th>Freitag</th>
		<th>Samstag</th>
		<th>Sonntag</th>
	</tr>
	'.join( $rows ).'</table>
</div>
';
		return '<style>
#mission-calendar {
	border: 1px solid #7F7F7F;
	table-layout: fixed;
	}
#mission-calendar th {
	border: none;
	}
#mission-calendar td {
	border-width: 1px 1px 0px 0px;
	border-style: solid;
	border-color: #9F9F9F;
	padding: 0px 0px !important;
	overflow: hidden;
	position: relative;
	height: 50px;
	}
#mission-calendar td.inactive,
#mission-calendar td.past {
	height: 25px;
	}
#mission-calendar td.past {
	background-color: #F7F7F7;
	}
#mission-calendar tr td:last-child {
	border-width: 1px 0px 0px 0px;
	}
#mission-calendar td.inactive {
	background-color: #EFEFEF;
	color: #7F7F7F;
	}
#mission-calendar td div.date-label {
	position: absolute;
	right: 0px;
	top: 0px;
	border-width: 0px 0px 1px 1px;
	border-style: solid;
	border-color: rgba(192,192,192,1);
	background-color: rgba(242,242,242,0.85);
	color: #5F5F5F;
	font-size: 0.9em;
	text-align: right;
	padding: 0px 1px 0px 2px;
	}
#mission-calendar td div.date-label.today {
	font-weight: bold;
	}
#mission-calendar tr td ul {
	list-style: none;
	margin: 0px;
	padding: 0px;
	width: 100%;
	position: static;
	}
#mission-calendar tr td ul li {
	overflow: hidden;
	white-space: nowrap;
	width: 100%;
	overflow: hidden;
	text-overflow: ellipsis;
	padding: 1px 4px;
	}
#mission-calendar tr td ul li a {
	text-decoration: none;
	}
#mission-calendar li.priority-1 {
	background-color: rgba(255,0,0,0.20);
	}
#mission-calendar li.priority-2 {
	background-color: rgba(255,127,0,0.20);
	}
#mission-calendar li.priority-3 {
	background-color: rgba(255,255,63,0.20);
	}
#mission-calendar li.priority-4 {
	background-color: rgba(127,255,0,0.20);
	}
#mission-calendar li.priority-5 {
	background-color: rgba(0,255,0,0.20);
	}
#mission-calendar-control .month-label,
#mission-calendar-control .year-label {
	font-weight: bold;
	font-size: 1.1em;
	}
</style>
<script>
var monthCurrent = '.date( "n" ).';
var monthShow    = '.(int) $showMonth.';
function setMonth(change){
	var month = change == 0 ? monthCurrent : monthShow + change;
	document.location.href = "./?month="+month;
}
</script>
<h2>Übersicht</h2>
<div id="mission-calendar-control">
	<button type="button" onclick="setMonth(-1)">&laquo;</button>
	<button type="button" onclick="setMonth(0)">&Omicron;</button>
	<button type="button" onclick="setMonth(1)">&raquo;</button>
	<span class="month-label">'.$words['months'][$month].'</span>
	<span class="year-label">'.$showYear.'</span>
</div>
'.$table;
	}
}
?>
