<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/*
	@deprecated		seems to be unused
	@todo			find/clarify out which component is handling this approach
	@todo			remove this file from module
*/
return "----";

/*
$toolbar	= new View_Helper_MultiButtonGroupMultiToolbar();

$iconAddEvent   = HtmlTag::create( 'i', '', ['class' => 'icon-time'] );
$iconAddTask    = HtmlTag::create( 'i', '', ['class' => 'icon-wrench'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
    $iconAddEvent   = HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-clock-o'] );
    $iconAddTask    = HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-thumb-tack'] );
}

$toolbar->addButtonGroup( 'tb_0', 'add', array(
	'<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown"><i class="icon-plus icon-white"></i></button>
	<ul class="dropdown-menu">
		<li><a href="./work/mission/add?type=0">'.$iconAddTask.'&nbsp;Aufgabe</a></li>
		<li><a href="./work/mission/add?type=1">'.$iconAddEvent.'&nbsp;Termin</a></li>
	</ul>'
) );

$toolbar->addButtonGroup( 'tb_1', 'view-type', array(
	'<button type="button" disabled="disabled" class="btn"><span id="number-total" class="badge badge-success"><i class="icon-refresh icon-white"></i></span></button>',
	'<button type="button" id="work-mission-view-type-0" class="btn"><i class="icon-tasks"></i> Liste</button>',
	'<button type="button" id="work-mission-view-type-1" disabled="disabled" class="btn"><i class="icon-calendar"></i> Monat</button>'
) );

$toolbar->sort();
$buttons	= '<div id="work-mission-buttons">'.$toolbar->render().'</div><div class="clearfix"></div>';
$buttons	= '<div class="work_mission_control">'.$buttons.'</div>';



$helper		= new View_Helper_Work_Mission_Calendar( $env );
$calendar	= $helper->render( $userId, $year, $month );
$content	= '
<div class="content-panel content-panel-table">
	<h3><span class="muted">Aufgaben: </span>Monatsansicht</h3>
	<div class="content-panel-inner">
		'.$calendar.'
	</div>
</div>';

$script	= '
<script>
$(document).ready(function(){
	WorkMissionsCalendar.userId = '.(int) $env->getSession()->get( 'auth_user_id' ).';
	WorkMissionsCalendar.year = '.$year.';
	WorkMissionsCalendar.month = '.$month.';
	WorkMissionsCalendar.init();
	WorkMissions.init("calendar");
});
</script>';
$env->getPage()->addHead( $script );

return $buttons.$content;
*/
?>
