<?php
$w	= (object) $words['index'];

$helperFilter	= new View_Helper_MissionFilter( $this->env );

//  --  FILTER: TYPES  --  //
if( $filterTypes === NULL )
	$filterTypes	= array(
		Model_Mission::TYPE_TASK,
		Model_Mission::TYPE_EVENT,
	);
$a	= $helperFilter->renderCheckboxFilter( 'switch_type', 'type', NULL, 'types', $words['types'], $filterTypes, $missions, 'type', 'filter-type' );
$inputSwitchType	= $a[0];
$optListTypes		= $a[1];


//  --  FILTER: PRIORITIES  --  //
if( $filterPriorities === NULL )
	$filterPriorities	= array( 0, 1, 2, 3, 4, 5 );

$a	= $helperFilter->renderCheckboxFilter( 'switch_priority', 'priority', NULL, 'priorities', $words['priorities'], $filterPriorities, $missions, 'priority', 'filter-priority' );
$inputSwitchPriority	= $a[0];
$optListPriorities		= $a[1];


//  --  FILTER: STATES  --  //
if( $filterStates === NULL )
	$filterStates	= array( 0, 1, 2, 3 );

$a	= $helperFilter->renderCheckboxFilter( 'switch_status', 'status', NULL, 'states', $words['states'], $filterStates, $missions, 'status', 'filter-status' );
$inputSwitchStatus	= $a[0];
$optListStates		= $a[1];


//  --  FILTER: PROJECTS  --  //
$inputSwitchProject	= "";
$optListProjects	= "";

if( $useProjects && !empty( $userProjects ) ){
	$mapProjects		= array();
	if( $filterProjects === NULL )
		$filterProjects	= array_keys( $userProjects );
	foreach( $userProjects as $project )
		$mapProjects[$project->projectId]	= $project->title;

	$a	= $helperFilter->renderCheckboxFilter( 'switch_project', 'project', NULL, 'projects', $mapProjects, $filterProjects, $missions, 'projectId', 'filter-project' );
	$inputSwitchProject	= $a[0];
	$optListProjects	= $a[1];
}

//  --  FILTER: ORDER & DIRECTION  --  //
$optOrder	= $words['filter-orders'];
$optOrder	= UI_HTML_Elements::Options( $optOrder, $session->get( 'filter_mission_order' ) );

$iconUp		= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/arrow_up.png', $words['filter-directions']['ASC'] );
$iconDown	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/arrow_down.png', $words['filter-directions']['DESC'] );

$disabled	= $session->get( 'filter_mission_direction' ) == 'ASC';
$buttonUp	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=ASC', $iconUp, 'tiny', NULL, $disabled );
$buttonDown	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=DESC', $iconDown, 'tiny', NULL, !$disabled );

$wordsAccess	= array( 'owner' => 'meine', 'worker' => 'mir zugewiesen' );

$optAccess	= UI_HTML_Elements::Options( $wordsAccess, $session->get( 'filter_mission_access' ) );

//  --  FILTER: TIME PERSPECTIVE  --  //
$optView	= array(
	'0' => 'ausstehend',
	'1' => 'geschlossen',
);
$optView	= UI_HTML_Elements::Options( $optView, $filterStates == array( 4 ) ? 1 : 0 );

$panelFilter	= '
<script>
$(document).ready(function(){
	WorkMissionFilter.__init();
	if(!parseInt($("#switch_view").val()))
		$("li.filter_status").show();
	if($("li.filter_project>ul").size())
		$("li.filter_project").show();
});
</script>
<form id="form_mission_filter" action="./work/mission/filter?reset" method="post">
	<fieldset>
		<legend class="icon filter">Filter</legend>
		<ul class="input">
			<li>
				<label for="filter_query">'.$w->labelQuery.'</label><br/>
				<div style="position: relative; display: none;" id="reset-button-container">
					<img id="reset-button-trigger" src="themes/custom/img/clearSearch.png" style="position: absolute; right: 3%; top: 9px; cursor: pointer"/>
				</div>
				<input name="query" id="filter_query" value="'.$session->get( 'filter_mission_query' ).'" class="max"/>
			</li>
			<li>
				<label for="switch_view" style="">Sichtweise</label><br/>
				<select name="view" id="switch_view" onchange="WorkMissionFilter.changeView(this);" class="max">'.$optView.'</select>
			</li>
<!--			<li>
				<label for="filter_access">???</label><br/>
				<select name="access" id="filter_access" class="max" onchange="this.form.submit();">'.$optAccess.'</select>
			</li>-->
			<li>
				<label for="switch_type" style="font-weight: bold">
					'.$inputSwitchType.'
					<span>Missionstypen</span>
				</label><br/>
				'.$optListTypes.'
			</li>
			<li>
				<label for="switch_priority" style="font-weight: bold">
					'.$inputSwitchPriority.'
					<span>Prioritäten</span>
				</label><br/>
				'.$optListPriorities.'
			</li>
			<li class="filter_status" style="display: none">
				<label for="switch_status" style="font-weight: bold">
					'.$inputSwitchStatus.'
					<span>Zustände</span>
				</label><br/>
				'.$optListStates.'
			</li>
			<li class="filter_project" style="display: none">
				<label for="switch_project" style="font-weight: bold">
					'.$inputSwitchProject.'
					<span>Projekte</span>
				</label><br/>
				'.$optListProjects.'
			</li>
			<li>
				<label for="filter_order">'.$w->labelOrder.'</label><br/>
				<div class="column-left-60">
					<select name="order" id="filter_order" class="max" onchange="this.form.submit();">'.$optOrder.'</select>
				</div>
				<div class="column-right-40">
					'.$buttonUp.$buttonDown.'
				</div>
				<div class="column-clear"></div>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'filter', $w->buttonFilter, 'button filter' ).'
			'.UI_HTML_Elements::LinkButton( './work/mission/filter?reset', $w->buttonReset, 'button reset' ).'
		</div>
	</fieldset>
</form>';
return $panelFilter;
?>
