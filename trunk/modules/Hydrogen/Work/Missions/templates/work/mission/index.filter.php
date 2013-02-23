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
if( array_intersect( array( -3, -2, -1, 4), $filterStates ) )
	$filterStatesMap	= array(
		-3	=> $words['states'][-3],
		-2	=> $words['states'][-2],
		-1	=> $words['states'][-1],
		4	=> $words['states'][4],
	);
else
	$filterStatesMap	= array(
		0	=> $words['states'][0],
		1	=> $words['states'][1],
		2	=> $words['states'][2],
		3	=> $words['states'][3],
	);

$a	= $helperFilter->renderCheckboxFilter( 'switch_status', 'status', NULL, 'states', $filterStatesMap, $filterStates, $missions, 'status', 'filter-status' );
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
$buttonUp	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=ASC', '<i class="icon-arrow-up"></i>', 'btn btn-mini', NULL, $disabled );
$buttonDown	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=DESC', '<i class="icon-arrow-down"></i>', 'btn btn-mini', NULL, !$disabled );

$wordsAccess	= array( 'owner' => 'meine', 'worker' => 'mir zugewiesen' );

$optAccess	= UI_HTML_Elements::Options( $wordsAccess, $session->get( 'filter_mission_access' ) );

//  --  FILTER: TIME PERSPECTIVE  --  //
$optView	= array(
	'0' => 'ausstehend',
	'1' => 'beendet (+/-)',
);
$optView	= UI_HTML_Elements::Options( $optView, $filterStates == array( 4 ) ? 1 : 0 );

$panelFilter	= '
<script>
$(document).ready(function(){
	WorkMissionFilter.__init();
	if(!parseInt($("#switch_view").val()))
		$("div.filter_status").show();
	if($("div.filter_project>ul").size())
		$("div.filter_project").show();
});
</script>
<form id="form_mission_filter" action="./work/mission/filter?reset" method="post" onsubmit="return WorkMissions.filter(this);">
	<fieldset>
		<legend class="icon filter">Filter</legend>
		<div class="row-fluid">
			<div class="span12">
				<div class="dropdown">
					<div class="btn-group">
						<button class="btn dropdown-toggle" data-toggle="dropdown">
							Missionstypen
							<span class="caret"></span>
						</button>
	<!--			<label for="switch_type" style="font-weight: bold" class="checkbox">
					'.$inputSwitchType.'
					<span>Missionstypen</span>
				</label>-->
						'.$optListTypes.'
					</div>
				</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
                <div class="dropdown">
                    <div class="btn-group">
                        <button class="btn dropdown-toggle" data-toggle="dropdown">
                            Prioritäten
                            <span class="caret"></span>
                        </button>
						'.$optListPriorities.'
					</div>
				</div>

<!--				<label for="switch_priority" style="font-weight: bold" class="checkbox">
					'.$inputSwitchPriority.'
					<span>Prioritäten</span>
				</label>
				'.$optListPriorities.'-->
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12 filter_status" style="display: none">
                <div class="dropdown">
                    <div class="btn-group">
                        <button class="btn dropdown-toggle" data-toggle="dropdown">
                            Zustände
                            <span class="caret"></span>
                        </button>
						'.$optListStates.'
					</div>
				</div>
<!--				<label for="switch_status" style="font-weight: bold" class="checkbox">
					'.$inputSwitchStatus.'
					<span>Zustände</span>
				</label>
				'.$optListStates.'-->
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12 filter_project">
                <div class="dropdown">
                    <div class="btn-group">
                        <button class="btn dropdown-toggle" data-toggle="dropdown">
                            Projekte
                            <span class="caret"></span>
                        </button>
						'.$optListProjects.'
					</div>
				</div>
<!--				<label for="switch_project" style="font-weight: bold" class="checkbox">
					'.$inputSwitchProject.'
					<span>Projekte</span>
				</label>
				'.$optListProjects.'-->
			</div>
		</div>
		<br/>
		<div class="row-fluid">
			<div class="span12">
				<label for="filter_query">'.$w->labelQuery.'</label>
				<div style="position: relative; display: none;" id="reset-button-container">
					<img id="reset-button-trigger" src="themes/custom/img/clearSearch.png" style="position: absolute; right: 3%; top: 9px; cursor: pointer"/>
				</div>
				<input name="query" id="filter_query" value="'.$session->get( 'filter_mission_query' ).'" class="span12 -max"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="switch_view" style="">Sichtweise</label>
				<select name="view" id="switch_view" onchange="WorkMissionFilter.changeView(this);" class="span12 -max">'.$optView.'</select>
			</div>
		</div>
<!--		<div class="row-fluid">
			<div class="span12">
				<label for="filter_access">???</label>
				<select name="access" id="filter_access" class="max" onchange="this.form.submit();">'.$optAccess.'</select>
			</div>
		</div>-->
		<div class="row-fluid">
			<div class="span8 -column-left-60">
				<label for="filter_order">'.$w->labelOrder.'</label>
				<select name="order" id="filter_order" class="span12 -max" onchange="this.form.submit();">'.$optOrder.'</select>
			</div>
			<div class="span4 -column-right-40">
				<label>&nbsp;</label>
				<div class="btn-group">
					'.$buttonUp.$buttonDown.'
				</div>
			</div>
		</div>
		<div class="row-fluid">
		</div>
		<div class="buttonbar -form-actions">
			'.UI_HTML_Elements::Button( 'filter', '<i class="icon-search icon-white"></i> '.$w->buttonFilter, 'btn btn-primary' ).'
			'.UI_HTML_Elements::LinkButton( './work/mission/filter?reset', '<i class="icon-zoom-out icon-white"></i> '.$w->buttonReset, 'btn btn-inverse' ).'
<!--			'.UI_HTML_Elements::Button( 'filter', $w->buttonFilter, 'button filter' ).'
			'.UI_HTML_Elements::LinkButton( './work/mission/filter?reset', $w->buttonReset, 'button reset' ).'-->
		</div>
	</fieldset>
</form>';
return $panelFilter;
?>
