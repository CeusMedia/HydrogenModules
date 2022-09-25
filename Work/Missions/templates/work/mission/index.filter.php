<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

if( empty( $wordsFilter ) )
	$wordsFilter	= $words;

$toolbar1		= new View_Helper_MultiButtonGroupMultiToolbar();
$toolbar2		= new View_Helper_MultiButtonGroupMultiToolbar();

$helperFilter	= new View_Helper_Work_Mission_Filter( $env, $defaultFilterValues, $wordsFilter );

$iconAddEvent	= HtmlTag::create( 'i', '', array( 'class' => 'icon-time' ) );
$iconAddTask	= HtmlTag::create( 'i', '', array( 'class' => 'icon-wrench' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconAddEvent	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-clock-o' ) );
	$iconAddTask	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-thumb-tack' ) );
}
$toolbar1->addButton( 'toolbar-views', 'view-type', HtmlTag::create( 'div', array(
	'<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" title="Neuer Eintrag"><i class="fa fa-fw fa-plus"></i></button>
	<ul class="dropdown-menu">
		<li><a href="./work/mission/add?type=1">'.$iconAddEvent.'&nbsp;Termin</a></li>
		<li><a href="./work/mission/add?type=0">'.$iconAddTask.'&nbsp;Aufgabe</a></li>
	</ul>'
), array( 'class' => 'btn-group' ) ) );

//  --  FILTER BUTTONS  --  //
$toolbar1->addButton( 'toolbar-views', 'view-type', $helperFilter->renderViewTypeSwitch( $filterMode ) );
$toolbar1->addButton( 'toolbar-views', 'view-type', $helperFilter->renderViewModeSwitch( $filterMode ) );

/*
$toolbar1->addButtonGroup( 'toolbar-sync', 'sync', array(
	'<a href="./work/mission/help/sync" class="btn not-btn-info" title="Synchronisation"><i class="icon-refresh not-icon-white"></i></a>'
) );

$toolbar1->addButtonGroup( 'toolbar-sync', 'sync', array(
	'<a href="./work/mission/help" class="btn btn-info" title="Hilfe"><i class="icon-question-sign icon-white"></i></a>'
) );
*/

if( !empty( $userProjects ) )
	$toolbar2->addButton( 'toolbar-filters', 'projects', $helperFilter->renderProjectFilter( $filterProjects, $userProjects ) );
$toolbar2->addButton( 'toolbar-filters', 'workers', $helperFilter->renderWorkerFilter( $filterWorkers, $users ) );
$toolbar2->addButton( 'toolbar-filters', 'priorities', $helperFilter->renderPriorityFilter( $filterPriorities ) );
if( $filterMode !== "kanban" )
	$toolbar2->addButton( 'toolbar-filters', 'states', $helperFilter->renderStateFilter( $filterStates ) );
$toolbar2->addButton( 'toolbar-filters', 'types', $helperFilter->renderTypeFilter( $filterTypes ) );
$toolbar2->addButton( 'toolbar-filters', 'search', HtmlTag::create( 'div', array(
		$helperFilter->renderSearch( $filterQuery ),
		$helperFilter->renderReset()
	), array( 'class' => 'input-append' ) )
);

//$toolbar1->sort();
$toolbar2->sort();

$modals	= $helperFilter->renderModals();

return $modals.'
<div class="work_mission_control">
	<div id="work-mission-buttons">
		'.$toolbar1->render().'<div class="clearfix"></div>
		'.$toolbar2->render().'<div class="clearfix"></div>
	</div>
</div>';
?>
