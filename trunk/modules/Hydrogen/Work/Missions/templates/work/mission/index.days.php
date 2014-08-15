<?php
$w			= (object) $words['index'];

//class_exists( 'View_Helper_MissionList' );
//if( !$h->countMissions( (int) $currentDay ) )
//	$currentDay	= $h->getNearestFallbackDay( (int) $currentDay );

switch( $filterTense ){
	case 1:																						//  tense: current
/*		$helperDays		= new View_Helper_Work_Mission_List_Days( $env );						//  renderer for large day lists
		$helperDays->setMissions( $missions );
		$helperDays->setWords( $words );

		$helperDays2		= new View_Helper_Work_Mission_List_DaysSmall( $env );				//  renderer for small day lists
		$helperDays2->setMissions( $missions );
		$helperDays2->setWords( $words );

		$helperDayButtons	= new View_Helper_Work_Mission_List_DayControls( $this->env );		//  renderer for large day buttons
		$helperDayButtons->setWords( $words );
		$helperDayButtons->setDayMissions( $helperDays->getDayMissions() );

		$helperDayButtons2	= new View_Helper_Work_Mission_List_DayControlsSmall( $this->env );	//  renderer for small day buttons
		$helperDayButtons2->setWords( $words );
		$helperDayButtons2->setDayMissions( $helperDays->getDayMissions() );
*/
		$content	= '
<div>
<!--	<div id="message-loading-list" class="muted"><em><small>'.$w->messageLoadingList.'</small></em></div>-->
<!--	<div id="message-loading-list" class="not-muted"><em><i class="icon-refresh"></i> '.$w->messageLoadingList.'</em></div>-->
	<div id="message-loading-list" class="alert alert-info">'.$w->messageLoadingList.'</div>
	<div id="day-controls">
		<div id="day-controls-large" class="hidden-phone">'./*$helperDayButtons->render().*/'</div>
		<div id="day-controls-small" class="visible-phone">'./*$helperDayButtons2->render().*/'</div>
	</div>
	<div id="day-lists">
		<div id="day-list-large" class="hidden-phone">'./*$helperDays->render().*/'</div>
		<div id="day-list-small" class="visible-phone">'./*$helperDays2->render().*/'</div>
	</div>
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

return '
<div class="content-panel content-panel-list">
	<h3><span class="muted">Aufgaben: </span>6-Tage-Aussicht</h3>
	<div class="content-panel-inner">
		'.$content.'
		<!--<br/>-->
	</div>
</div>';
/*
<script type="text/javascript">
var missionShowDay = '.$currentDay.';
</script>
';*/
?>
