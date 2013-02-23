<?php
$script			= array();
$buttonSets		= array(
	array(
//		'<button type="button" id="work-mission-view-type-0" disabled="disabled" class="button icon list"><span>Listenansicht</span></button>',
//		'<button type="button" id="work-mission-view-type-1" disabled="disabled" class="button icon calendar"><span>Monatsübersicht</span></button>'
		'<button type="button" id="work-mission-view-type-0" disabled="disabled" class="btn"><i class="icon-tasks"></i> Liste</button>',
		'<button type="button" id="work-mission-view-type-1" disabled="disabled" class="btn"><i class="icon-calendar"></i> Monat</button>'
	),
	array(
		UI_HTML_Elements::LinkButton( './work/mission/add?type=0', 'Aufgabe', 'button add task-add' ),
		UI_HTML_Elements::LinkButton( './work/mission/add?type=1', 'Termin', 'button add event-add' )
	)
);

$w				= (object) $words['index'];
$panelFilter	= $view->loadTemplateFile( 'work/mission/index.filter.php' );
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

if( count( $missions ) )
	$panelList	= $view->loadTemplateFile( 'work/mission/index.days.php' );
else
	$panelList	= $view->loadContentFile( 'html/work/mission/index.empty.html' );

$script[]	= '';
$script[]	= '';
$content	= '
<div class="column-left-20" style="float: left; width: 200px">
	'.$panelFilter.'
</div>
<div style="margin-left: 220px">
	<div id="mission-folders" style="position: relative; width: 100%">
		'.$panelList.'
	</div>
</div>
<div class="column-clear"></div>';

$script	= '
<script>
$(document).ready(function(){
	WorkMissions.init();
	WorkMissionsList.sortBy = "'.$filterOrder.'";
	WorkMissionsList.sortDir = "'.$filterDirection.'";
	WorkMissionsList.init();
});
</script>';
$env->getPage()->addHead( $script );

$buttons	= array();
foreach( $buttonSets as $buttonSet )
	$buttons[]	= '<div class="btn-group">'.join( " ", $buttonSet ).'</div>';
$buttons	= '<div id="work-mission-buttons">'.join( " | ", $buttons ).'</div><br/>';

return $buttons.$content;
?>
