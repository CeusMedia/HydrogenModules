<?php
class View_Helper_MissionCalendar{

	public function __construct( $env ){
		$this->env		= $env;
		$this->logic	= new Logic_Mission( $this->env );
		$this->today	= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
		$this->words	= $this->env->getLanguage()->load( 'work/mission' );
		
	}

	public function renderLabel( $year, $month ){
		return '<span id="mission-calendar-control">
	<span class="month-label">'.$this->words['months'][$month].'</span>
	<span class="year-label">'.$year.'</span>
</span>';
		
	}
	
	public function render( $userId, $year, $month ){
		$showMonth		= str_pad( $month, 2, "0", STR_PAD_LEFT );
		$showScope		= $year.'-'.$showMonth.'-01';
		$monthDays		= date( "t", strtotime( $showScope ) );
		$offsetStart	= max( 0, date( "w", strtotime( $showScope ) ) - 1 );
		$weeks			= ceil( ( $monthDays + $offsetStart ) / 7 );
		$rows			= array();
		$orders			= array( 'priority' => 'ASC' );
		for( $i=0; $i<$weeks; $i++ ){
			$row	= array();
			$j	= 0;
			if( $i == 0 )
				for( $j=0; $j<$offsetStart; $j++ )
					$row[]	= '<td class="inactive"></td>';
			while( $j<7 ){
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
				$day		= str_pad( $day, 2, "0", STR_PAD_LEFT );
				$showMonth	= str_pad( $showMonth, 2, "0", STR_PAD_LEFT );
				$date		= $showYear.'-'.$showMonth.'-'.$day;
				$diff		= $this->today->diff( new DateTime( $date ) );
				$isPast		= $diff->invert;
				$isToday	= $diff->days == 0;
				$conditions	= array( 'dayStart' => $date, 'status' => array( 0, 1, 2, 3 ) );
				$missions	= $this->logic->getUserMissions( $userId, $conditions, $orders );
				$list		= array();
				foreach( $missions as $mission ){
				//	$title		= Alg_Text_Trimmer::trim( $mission->content, 20 );
					$title		= $mission->content;
					$url		= './work/mission/edit/'.$mission->missionId;
					$class		= 'icon-label mission-type-'.$mission->type;
					$title		= '<a class="'.$class.'" href="'.$url.'">'.$title.'</a>';
					$overdue	= '';
					if( $isPast )
						$overdue	= $this->renderOverdue( $mission );
					$list[]	= '<li class="priority-'.$mission->priority.'">'.$overdue.$title.'</li>';
				}
				$class		= '';
				if( $isToday )
					$class	= 'today';

				$list	= '<ul>'.join( $list ).'</ul>';
				$label	= '<div class="date-label '.$class.'">'.date( "j.n.", strtotime( $date ) ).'</div>';
				$class	= strtotime( $date ) < strtotime( date( "Y-m-d 00:00" ) ) ? "past" : "";
				$row[]	= '<td class="'.$class.'">'.$label.$list.'</td>';
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
		$script	= '<script>
$(document).ready(function(){
	WorkMissionsCalendar.monthCurrent = '.date( "n" ).';
	WorkMissionsCalendar.monthShow    = '.(int) $showMonth.';
});
</script>';
		
		$this->env->getPage()->addHead( $script );
		return $table;
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
