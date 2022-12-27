<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array $words */

$w				= (object) $words['index'];

$panelFilter	= $view->loadTemplateFile( 'work/mission/index.filter.php' );

switch( $filterMode ){
	case 'archive':
		$panelContent	= $view->loadTemplateFile( 'work/mission/index.days.php' );
		break;
	case 'now':
//		if( count( $missions ) ){
			$panelContent		= $view->loadTemplateFile( 'work/mission/index.days.php' );
//			$panelContent	= '<div id="mission-folders" style="position: relative; width: 100%; display: none;">'.$panelList.'</div>';
//		}
//		else
		$panelEmpty		= $view->loadContentFile( 'html/work/mission/index.empty.html' );
		$panelContent	.= '<div id="day-lists-empty" style="display: none">'.$panelEmpty.'</div>';
		break;
	case 'future':
		$panelContent	= $view->loadTemplateFile( 'work/mission/index.days.php' );
		break;
}

$panelContent	.= '<div class="clearfix"></div>';

/*	$panelExport	= '';
	if( 0 && $filterStates != [4] ){
		$panelExport	= '<fieldset>
			<legend class="icon export">Export / Import</legend>
			<b>Export als:</b>&nbsp;<br/>
			'.HtmlElements::LinkButton( './work/mission/export/ical', 'ICS', 'button icon export ical' ).'
			'.HtmlElements::LinkButton( './work/mission/export', 'Archiv', 'button icon export archive' ).'
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
//	WorkMissions.init('.( (int) $filterMode ).');
	WorkMissions.currentDay = '.$currentDay.';
	WorkMissionsList.sortBy = "'.$filterOrder.'";
	WorkMissionsList.sortDir = "'.$filterDirection.'";
	WorkMissionsList.init();
//	WorkMissionsFilter.__init();
});
//console.log("'.session_id().'");
</script>';
$env->getPage()->addHead( $script );

return '<div id="work_mission_mode_'.$filterMode.'">'.$panelFilter.$panelContent.'</div>';
