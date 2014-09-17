<?php
if( empty( $wordsFilter ) )
	$wordsFilter	= $words;

$badge		= '<span id="number-total" class="badge badge-success"><i class="icon-refresh icon-white"></i></span>';

$toolbar1	= new View_Helper_MultiButtonGroupMultiToolbar();
$toolbar2	= new View_Helper_MultiButtonGroupMultiToolbar();

$toolbar1->addButtonGroup( 'tb_0', 'add', array(
	'<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown"><i class="icon-plus icon-white"></i></button>
	<ul class="dropdown-menu">
		<li><a href="./work/mission/add?type=0"><i class="icon-wrench"></i> Aufgabe</a></li>
		<li><a href="./work/mission/add?type=1"><i class="icon-time"></i> Termin</a></li>
	</ul>'
) );

//  @todo	re-implement this modde switch, re-think and re-position counter
if( 1 || $filterMode == '__not_implemented_yet__' ){
	$toolbar1->addButtonGroup( 'tb_1', 'view-type', array(
//		'<button type="button" disabled="disabled" class="btn">'.$badge.'</button>',
		'<button type="button" id="work-mission-view-type-0" disabled="disabled" class="btn"><i class="icon-calendar"></i> Monat</button>',
		'<button type="button" id="work-mission-view-type-1" disabled="disabled" class="btn"><i class="icon-tasks"></i> Liste</button>',
	) );
}

if( in_array( $filterMode, array( 'archive', 'now', 'future' ) ) ){
	$toolbar1->addButtonGroup( 'tb_1', 'view-mode', array(
		'<button type="button" id="work-mission-view-mode-archive" disabled="disabled" class="btn -btn-small"><i class="icon-arrow-left"></i> Archiv</button>',
		'<button type="button" id="work-mission-view-mode-now" disabled="disabled" class="btn -btn-small"><i class="icon-star"></i> Aktuell</button>',
		'<button type="button" id="work-mission-view-mode-future" disabled="disabled" class="btn -btn-small"><i class="icon-arrow-right"></i> Zukunft</button>',
	) );
}

//  --  FILTER BUTTONS  --  //
/*  -- mission types  --  */
/*$iconTask			= UI_HTML_Tag::create( 'i', "", array( 'class' => "icon-wrench" ) )." ";
$iconEvent			= UI_HTML_Tag::create( 'i', "", array( 'class' => "icon-time" ) )." ";
$changedTypes	= array_diff( $defaultFilterValues['types'], $filterTypes );
$buttonTypes	= new View_Helper_MultiCheckDropdownButton( 'types', $filterTypes, 'Missionstypen' );
$buttonTypes->useItemIcons( TRUE );
$buttonTypes->setButtonClass( $changedTypes ? "btn-info" : "" );
$buttonTypes->addItem( 0, $iconTask.'Aufgabe', '', 'wrench' );
$buttonTypes->addItem( 1, $iconEvent.'Termin', '', 'time' );
$toolbar2->addButton( 'tb_2', 'types', $buttonTypes->render() );
*/

/*  -- mission types  --  */
$types			= $defaultFilterValues['types'];
$changedTypes	= array_diff( $types, $filterTypes );
$typeIcons	= array(
	0	=> UI_HTML_Tag::create( 'i', "", array( 'class' => "icon-wrench" ) ),
	1	=> UI_HTML_Tag::create( 'i', "", array( 'class' => "icon-time" ) ),
);
$list	= array();
foreach( $types as $type ){
	$input	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'name'		=> 'types[]',
		'id'		=> 'type-'.$type,
		'value'		=> $type,
		'checked'	=> in_array( $type, $filterTypes ) ? "checked" : NULL
	) );
	$label	= $input.'&nbsp;'.$typeIcons[$type].'&nbsp;'.$wordsFilter['types'][$type];
	$label	= UI_HTML_Tag::create( 'label', $label, array( 'class' => 'checkbox' ) );
	$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'filter-type type-'.$type ) );
}
$buttonLabel		= 'Aufgabentypen <span class="caret"></span>';
$buttonClass		= 'dropdown-toggle btn '.( $changedTypes ? "btn-info" : "" );
$buttonTypes	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'button', $buttonLabel, array( 'class'	=> $buttonClass, 'data-toggle' => 'dropdown' ) ),
	UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) ),
), array( 'class' => 'btn-group', 'id' => 'types' ) );

$toolbar2->addButton( 'tb_2', 'types', $buttonTypes );


/*  -- mission priorities  --  */
/*$changedPriorities	= array_diff( $defaultFilterValues['priorities'], $filterPriorities );
$buttonPriorities	= new View_Helper_MultiCheckDropdownButton( 'priorities', $filterPriorities, 'Prioritäten' );
$buttonPriorities->setButtonClass( $changedPriorities ? "btn-info" : "" );
foreach( $wordsFilter['priorities'] as $priority => $label )
	$buttonPriorities->addItem( $priority, $label, 'filter-priority priority-'.$priority );
$toolbar2->addButton( 'tb_2', 'priorities', $buttonPriorities->render() );
*/

/*  -- mission priorities  --  */
$priorities			= $defaultFilterValues['priorities'];
$changedPriorities	= array_diff( $priorities, $filterPriorities );
$list	= array();
foreach( $priorities as $priority ){
	$input	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'name'		=> 'priorities[]',
		'id'		=> 'priority-'.$priority,
		'value'		=> $priority,
		'checked'	=> in_array( $priority, $filterPriorities ) ? "checked" : NULL
	) );
	$label	= UI_HTML_Tag::create( 'label', $input.' '.$wordsFilter['priorities'][$priority], array( 'class' => 'checkbox' ) );
	$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'filter-priority priority-'.$priority ) );
}
$buttonLabel		= 'Prioritäten <span class="caret"></span>';
$buttonClass		= 'dropdown-toggle btn '.( $changedPriorities ? "btn-info" : "" );
$buttonPriorities	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'button', $buttonLabel, array( 'class'	=> $buttonClass, 'data-toggle' => 'dropdown' ) ),
	UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) ),
), array( 'class' => 'btn-group', 'id' => 'priorities' ) );
$toolbar2->addButton( 'tb_2', 'priorities', $buttonPriorities );


/*  -- mission states  --  */
/*$states			= $defaultFilterValues['states'];
$changedStates	= array_diff( $states, $filterStates );
$buttonStates	= new View_Helper_MultiCheckDropdownButton( 'states', $filterStates, 'Zustände' );
$buttonStates->setButtonClass( $changedStates ? "btn-info" : "" );
foreach( $states as $status ){
	$label		= $wordsFilter['states'][$status];
	$buttonStates->addItem( $status, $label, 'filter-status status-'.$status );
}
$toolbar2->addButton( 'tb_2', 'states', $buttonStates->render() );
*/

/*  -- mission states  --  */
$states			= $defaultFilterValues['states'];
$changedStates	= array_diff( $states, $filterStates );
$list	= array();
foreach( $states as $status ){
	$input	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'name'		=> 'states[]',
		'id'		=> 'status-'.$status,
		'value'		=> $status,
		'checked'	=> in_array( $status, $filterStates ) ? "checked" : NULL
	) );
	$label	= UI_HTML_Tag::create( 'label', $input.' '.$wordsFilter['states'][$status], array( 'class' => 'checkbox' ) );
	$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'filter-status status-'.$status ) );
}
$buttonLabel	= 'Zustände <span class="caret"></span>';
$buttonClass	= 'dropdown-toggle btn '.( $changedStates ? "btn-info" : "" );
$buttonStates	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'button', $buttonLabel, array( 'class'	=> $buttonClass, 'data-toggle' => 'dropdown' ) ),
	UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) ),
), array( 'class' => 'btn-group', 'id' => 'states' ) );

$toolbar2->addButton( 'tb_2', 'states', $buttonStates );


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
		$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'project status'.$project->status ) );
	}
	$listClass		= 'dropdown-menu condensed-width';
	if( count( $userProjects ) > 9 )
		$listClass	.= ' condensed-height';
	$listAttr		= array( 'class' => $listClass );
	$buttonLabel	= 'Projekte <span class="caret"></span>';
	$buttonClass	= 'dropdown-toggle btn '.( $changedProjects ? "btn-info" : "" );
	$buttonAttr		= array( 'class' => $buttonClass, 'data-toggle' => 'dropdown' );
	$buttonProjects	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'button', $buttonLabel, $buttonAttr ),
		UI_HTML_Tag::create( 'ul', $list, $listAttr ),
	), array( 'class' => 'btn-group', 'id' => 'projects' ) );
	$toolbar2->addButton( 'tb_2', 'projects', $buttonProjects );
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
	'placeholder'	=> $wordsFilter['index']['labelQuery'],
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
$toolbar2->addButton( 'tb_2', 'search', $search );

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

$toolbar1->sort();
$toolbar2->sort();
$buttons	= '<div id="work-mission-buttons">'.$toolbar1->render().'<div class="clearfix"></div>'.$toolbar2->render().'</div><div class="clearfix"></div>';
return '
<div class="work_mission_control">'.$buttons.'</div>
<script>
$(document).ready(function(){
	$("#work-mission-buttons ul.dropdown-menu li label").hover(
		function(){
			var icon = $(this).children("i");
			if(icon){
				var wasWhite = icon.hasClass("icon-white");
				if(wasWhite)
					icon.data("wasWhite", wasWhite);
				else
					icon.addClass("icon-white");
			}
		}, function(){
			var icon = $(this).children("i");
			if(icon)
				if(!icon.data("wasWhite"))
					icon.removeClass("icon-white");
		}
	);
});
</script>
';
?>
