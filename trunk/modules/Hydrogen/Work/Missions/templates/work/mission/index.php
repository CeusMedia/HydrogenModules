<?php
$w				= (object) $words['index'];

$panelFilter	= $view->loadTemplateFile( 'work/mission/index.filter.php' );

switch( $filterTense ){
	case 0:
		$panelContent	= $view->loadTemplateFile( 'work/mission/index.days.php' );
		break;
	case 1:
		if( count( $missions ) ){
			$panelList		= $view->loadTemplateFile( 'work/mission/index.days.php' );
			$panelContent	= '<div id="mission-folders" style="position: relative; width: 100%">'.$panelList.'</div>';
		}
		else
			$panelContent	= $view->loadContentFile( 'html/work/mission/index.empty.html' );
		break;
	case 2:
		$panelContent	= $view->loadTemplateFile( 'work/mission/index.days.php' );
		break;
}

$panelContent	.= '<div class="clearfix"></div>';

/*	$panelExport	= '';
	if( 0 && $filterStates != array( 4 ) ){
		$panelExport	= '<fieldset>
			<legend class="icon export">Export / Import</legend>
			<b>Export als:</b>&nbsp;<br/>
			'.UI_HTML_Elements::LinkButton( './work/mission/export/ical', 'ICS', 'button icon export ical' ).'
			'.UI_HTML_Elements::LinkButton( './work/mission/export', 'Archiv', 'button icon export archive' ).'
			<hr/>
			<form action="./work/mission/import" method="post" enctype="multipart/form-data">
				<b>Import aus:</b>&nbsp;
				<input type="text" name="import" id="input-import" class="m" readonly="readonly"/>
				<input type="file" name="serial" id="input-serial" accept="application/gzip"/>
			</form>
		</fieldset>';
	}*/

//if( !$h->countMissions( (int) $currentDay ) )
//    $currentDay = $h->getNearestFallbackDay( (int) $currentDay );

$script	= '
<script>
$(document).ready(function(){
//	WorkMissions.init('.( (int) $filterTense ).');
	WorkMissionsList.sortBy = "'.$filterOrder.'";
	WorkMissionsList.sortDir = "'.$filterDirection.'";
	WorkMissionsList.init();
	WorkMissions.currentDay = '.$currentDay.';
	WorkMissionFilter.__init();
});
console.log("'.session_id().'");
</script>';
$env->getPage()->addHead( $script );

return '<div id="work_mission_tense_'.$filterTense.'">'.$panelFilter.$panelContent.'</div>';
?>
