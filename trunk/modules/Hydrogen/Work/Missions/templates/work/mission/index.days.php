<?php
$w			= (object) $words['index'];

class_exists( 'View_Helper_MissionList' );
//if( !$h->countMissions( (int) $currentDay ) )
//	$currentDay	= $h->getNearestFallbackDay( (int) $currentDay );

switch( $filterTense ){
	case 1:
		$helperDays		= new View_Helper_Work_Mission_List_Days( $env );								//  renderer for day lists
		$helperDays->setMissions( $missions );
		$helperDays->setWords( $words );

		$helperDayButtons	= new View_Helper_Work_Mission_List_DayControls( $this->env );			//  renderer for day buttons
		$helperDayButtons->setWords( $words );
		$helperDayButtons->setDayMissions( $helperDays->getDayMissions() );

		$content	= '
<div>
	<div id="day-controls">'.$helperDayButtons->render().'</div>
	<div id="day-lists">'.$helperDays->render().'</div>
</div>';
		break;
	case 0:
	case 2:
		$helperList		= new View_Helper_Work_Mission_List( $env );
		$helperList->setMissions( $missions );
		$helperList->setWords( $words );
		$lists		= $helperList->renderDayList( 2, 0, TRUE, TRUE, TRUE, FALSE );									//  render list for fday 0
		$content	= '<div id="day-lists">'.$lists.'</div>';
		break;
}

return $content;
/*
<script type="text/javascript">
var missionShowDay = '.$currentDay.';
</script>
';*/
?>
