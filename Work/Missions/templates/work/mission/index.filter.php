<?php
if( empty( $wordsFilter ) )
	$wordsFilter	= $words;

$toolbar1		= new View_Helper_MultiButtonGroupMultiToolbar();
$toolbar2		= new View_Helper_MultiButtonGroupMultiToolbar();

$helperFilter	= new View_Helper_Work_Mission_Filter( $env, $defaultFilterValues, $wordsFilter );

$toolbar1->addButtonGroup( 'toolbar-add', 'add', array(
	'<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" title="Neuer Eintrag"><i class="icon-plus icon-white"></i></button>
	<ul class="dropdown-menu">
		<li><a href="./work/mission/add?type=0"><i class="icon-wrench"></i> Aufgabe</a></li>
		<li><a href="./work/mission/add?type=1"><i class="icon-time"></i> Termin</a></li>
	</ul>'
) );

//  --  FILTER BUTTONS  --  //
$toolbar1->addButton( 'toolbar-views', 'view-type', $helperFilter->renderViewTypeSwitch() );
$toolbar1->addButton( 'toolbar-views', 'view-mode', $helperFilter->renderViewModeSwitch( $filterMode ) );

$toolbar1->addButtonGroup( 'toolbar-sync', 'sync', array(
	'<a href="./work/mission/export" class="btn not-btn-info" title="Synchronisation"><i class="icon-refresh not-icon-white"></i></a>'
) );

$toolbar2->addButton( 'toolbar-filters', 'types', $helperFilter->renderTypeFilter( $filterTypes ) );
$toolbar2->addButton( 'toolbar-filters', 'priorities', $helperFilter->renderPriorityFilter( $filterPriorities ) );
$toolbar2->addButton( 'toolbar-filters', 'states', $helperFilter->renderStateFilter( $filterStates ) );
if( $useProjects && !empty( $userProjects ) )
	$toolbar2->addButton( 'toolbar-filters', 'projects', $helperFilter->renderProjectFilter( $filterProjects, $userProjects ) );
$toolbar2->addButton( 'toolbar-filters', 'search', UI_HTML_Tag::create( 'div', array(
        $helperFilter->renderSearch( $filterQuery ),
        $helperFilter->renderReset()
    ), array( 'class' => 'input-append' ) )
);

//$toolbar1->sort();
$toolbar2->sort();

return '
<div class="work_mission_control">
	<div id="work-mission-buttons">
		'.$toolbar1->render().'<div class="clearfix"></div>
		'.$toolbar2->render().'<div class="clearfix"></div>
	</div>
</div>';
?>
