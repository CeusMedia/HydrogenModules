<?php

$badge		= '<span id="number-total" class="badge badge-success"><i class="icon-refresh icon-white"></i></span>';

$toolbar	= new View_Helper_MultiButtonGroupMultiToolbar();

$toolbar->addButtonGroup( 'tb_0', 'add', array(
	'<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown"><i class="icon-plus icon-white"></i></button>
	<ul class="dropdown-menu">
		<li><a href="./work/mission/add?type=0"><i class="icon-wrench"></i> Aufgabe</a></li>
		<li><a href="./work/mission/add?type=1"><i class="icon-time"></i> Termin</a></li>
	</ul>'
) );

if( $filterTense == 1 ){
	$toolbar->addButtonGroup( 'tb_1', 'view-type', array(
		'<button type="button" disabled="disabled" class="btn">'.$badge.'</button>',
		'<button type="button" id="work-mission-view-type-0" disabled="disabled" class="btn"><i class="icon-tasks"></i> Liste</button>',
		'<button type="button" id="work-mission-view-type-1" disabled="disabled" class="btn"><i class="icon-calendar"></i> Monat</button>'
	) );
}

$toolbar->addButtonGroup( 'tb_1', 'view-tense', array(
	'<button type="button" id="work-mission-view-tense-0" disabled="disabled" class="btn -btn-small"><i class="icon-arrow-left"></i> Archiv</button>',
	'<button type="button" id="work-mission-view-tense-1" disabled="disabled" class="btn -btn-small"><i class="icon-star"></i> Aktuell</button>',
	'<button type="button" id="work-mission-view-tense-2" disabled="disabled" class="btn -btn-small"><i class="icon-arrow-right"></i> Zukunft</button>',
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
$changedProjects	= array();
if( $useProjects && !empty( $userProjects ) ){
	$changedProjects	= array_diff( array_keys( $userProjects ), $filterProjects );
	$list	= array();
	foreach( $userProjects as $project ){
		$input	= UI_HTML_Tag::create( 'input', NULL, array(
			'type'		=> 'checkbox',
			'name'		=> 'projects[]',
			'id'		=> 'project-'.$project->projectId,
			'value'		=> $project->projectId,
			'checked'	=> in_array( $project->projectId, $filterProjects ) ? "checked" : NULL
		) );
		$label	= UI_HTML_Tag::create( 'label', $input.' '.$project->title, array( 'class' => 'checkbox' ) );
		$list[]	= UI_HTML_Tag::create( 'li', $label );
	}
	$buttonLabel	= 'Projekte <span class="caret"></span>';
	$buttonClass	= 'dropdown-toggle btn '.( $changedProjects ? "btn-info" : "" );
	$buttonProjects	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'button', $buttonLabel, array( 'class'	=> $buttonClass, 'data-toggle' => 'dropdown' ) ),
		UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) ),
	), array( 'class' => 'btn-group', 'id' => 'projects' ) );
	$toolbar->addButton( 'tb_2', 'projects', $buttonProjects );
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
$label				= '<i class="icon-remove-circle '.( $changedFilters ? 'icon-white' : '' ).'"></i>';
$buttonSearchReset	= UI_HTML_Tag::create( 'button', $label, array(
	'type'		=> "button",
	'disabled'	=> $changedFilters ? NULL : "disabled",
	'class'		=> 'btn '.( $changedFilters ? 'btn-inverse' : "" ),
	'id'		=> 'button_filter_reset',					//  remove query only: 'button_filter_search_reset',
	'title'		=> 'alle Filter zurücksetzen',
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
return '<div class="work_mission_control">'.$buttons.'</div>';
?>
