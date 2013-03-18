<?php

//new View_Helper_MissionFilter( $this->env );
class_exists( 'View_Helper_Work_Mission_Filter' );

$toolbar	= new View_Helper_MultiButtonGroupMultiToolbar();
$toolbar->addButtonGroup( 'tb_0', 'view-tense', array(
	'<button type="button" id="work-mission-view-tense-0" disabled="disabled" class="btn -btn-small"><i class="icon-arrow-left"></i> Archiv</button>',
	'<button type="button" id="work-mission-view-tense-1" disabled="disabled" class="btn -btn-small"><i class="icon-star"></i> Aktuell</button>',
	'<button type="button" id="work-mission-view-tense-2" disabled="disabled" class="btn -btn-small"><i class="icon-arrow-right"></i> Zukunft</button>',
) );

$script			= array();
if( $filterTense == 1 ){
	$toolbar->addButtonGroup( 'tb_0', 'view-type', array(
		'<button type="button" id="work-mission-view-type-0" disabled="disabled" class="btn"><i class="icon-tasks"></i> Liste</button>',
		'<button type="button" id="work-mission-view-type-1" disabled="disabled" class="btn"><i class="icon-calendar"></i> Monat</button>'
	) );
}
$toolbar->addButtonGroup( 'tb_1', 'add', array(
	'<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown"><i class="icon-plus icon-white"></i></button>
	<ul class="dropdown-menu">
		<li><a href="./work/mission/add?type=0"><i class="icon-wrench"></i> Aufgabe</a></li>
		<li><a href="./work/mission/add?type=1"><i class="icon-time"></i> Termin</a></li>
	</ul>'
//		UI_HTML_Elements::LinkButton( './work/mission/add?type=0', 'Aufgabe', 'button add task-add' ),
//		UI_HTML_Elements::LinkButton( './work/mission/add?type=1', 'Termin', 'button add event-add' )
) );


//  --  FILTER BUTTONS  --  //
/*  -- mission types  --  */
$iconTask			= UI_HTML_Tag::create( 'i', "", array( 'class' => "icon-wrench" ) )." ";
$iconEvent			= UI_HTML_Tag::create( 'i', "", array( 'class' => "icon-time" ) )." ";
$changedTypes	= array_diff( $defaultFilterValues['types'], $filterTypes );
$buttonTypes	= new View_Helper_MultiCheckDropdownButton( 'types', $filterTypes, 'Missionstypen' );
$buttonTypes->useItemIcons( TRUE );
$buttonTypes->setButtonClass( $changedTypes ? "btn-info" : "" );
$buttonTypes->addItem( 0, $iconTask.'Aufgabe', '', 'wrench' );
$buttonTypes->addItem( 1, $iconEvent.'Termin', '', 'time' );
$toolbar->addButton( 'tb_2', 'types', $buttonTypes->render() );

/*  -- mission priorities  --  */
$changedPriorities	= array_diff( $defaultFilterValues['priorities'], $filterPriorities );
$buttonPriorities	= new View_Helper_MultiCheckDropdownButton( 'priorities', $filterPriorities, 'Prioritäten' );
$buttonPriorities->setButtonClass( $changedPriorities ? "btn-info" : "" );
foreach( $words['priorities'] as $priority => $label )
	$buttonPriorities->addItem( $priority, $label, 'filter-priority priority-'.$priority );
$toolbar->addButton( 'tb_2', 'priorities', $buttonPriorities->render() );

/*  -- mission states  --  */
$states			= $defaultFilterValues['states'][$filterTense];
$changedStates	= array_diff( $states, $filterStates );
$buttonStates	= new View_Helper_MultiCheckDropdownButton( 'states', $filterStates, 'Zustände' );
$buttonStates->setButtonClass( $changedStates ? "btn-info" : "" );
foreach( $states as $status ){
	$label		= $words['states'][$status];
	$buttonStates->addItem( $status, $label, 'filter-status status-'.$status );
}
$toolbar->addButton( 'tb_2', 'states', $buttonStates->render() );

/*  -- mission projects  --  */
if( $useProjects && !empty( $userProjects ) ){
	$changedProjects	= array_diff( array_keys( $userProjects ), $filterProjects );
	$buttonProjects		= new View_Helper_MultiCheckDropdownButton( 'projects', $filterProjects, 'Projekte' );
	$buttonProjects->setButtonClass( $changedProjects ? "btn-info" : "" );
	foreach( $userProjects as $project )
		$buttonProjects->addItem( $project->projectId, $project->title );
	$toolbar->addButton( 'tb_2', 'projects', $buttonProjects->render() );
}

/*  -- reset filters  --  */
$changedFilters		= $changedTypes || $changedPriorities || $changedStates || $changedProjects || $filterQuery;

/*  -- query search  --  */
$inputSearch	= UI_HTML_Tag::create( 'input', NULL, array(
	'type'			=> "text",
	'name'			=> "query",
	'id'			=> "filter_query",
	'class'			=> 'span2 '.( $filterQuery ? 'changed' : '' ),
	'value'			=> htmlentities( $filterQuery, ENT_QUOTES, 'UTF-8' ),
	'placeholder'	=> $words['index']['labelQuery'],
) );

$label				= '<i class="icon-search '.( $filterQuery ? 'icon-white' : '' ).'"></i>';
$buttonSearch	= UI_HTML_Tag::create( 'button', $label, array(
	'type'		=> "button",
	'class'		=> 'btn '.( $filterQuery ? 'btn-info' : '' ),
	'id'		=> 'button_filter_search'
) );
$label				= '<i class="icon-remove '.( $changedFilters ? 'icon-white' : '' ).'"></i> alles';
$buttonSearchReset	= UI_HTML_Tag::create( 'button', $label, array(
	'type'		=> "button",
	'disabled'	=> $changedFilters ? NULL : "disabled",
	'class'		=> 'btn '.( $changedFilters ? 'btn-inverse' : "" ),
	'id'		=> 'button_filter_reset',					//  remove query only: 'button_filter_search_reset'
) );

$search		= $inputSearch.$buttonSearch.$buttonSearchReset;
$search		= UI_HTML_Tag::create( 'div', $search, array( 'class' => 'input-append' ) );
$toolbar->addButton( 'tb_2', 'search', $search );

/*
if( $changedFilters ){
	$buttonReset	= UI_HTML_Tag::create( 'button', '<i class="icon-zoom-out"></i> alle', array(
		'type'		=> "button",
		'class'		=> 'btn btn-inverse',
		'id'		=> 'button_filter_reset',
	) );
	$buttonSets[]	= array( '<div class="btn-group">'.$buttonReset.'</div>' );
}
*/

$toolbar->sort();
$buttons	= '<div id="work-mission-buttons">'.$toolbar->render().'</div><div class="clearfix"></div>';
return '<div class="work_mission_control">'.$buttons.'
<!--&nbsp;<span class="badge" id="number-total"></span>-->

</div>';











$w	= (object) $words['index'];

//$helperFilter	= new View_Helper_MissionFilter( $this->env );

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
<form id="form_mission_filter" action="./work/mission/filter?reset" method="post" onsubmit="return WorkMissions.filter(this);">
	<fieldset>
		<legend class="icon filter">Filter</legend>
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
	</fieldset>
</form>';
return $panelFilter;
?>
