<?php

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/info/dashboard/' ) );

$helperAdd	= new View_Helper_Info_Dashboard_Modal_Add( $env );
$helperAdd->setPanels( $panels );
$modalAdd	= $helperAdd->render();

if( empty( $dashboard ) ){
	$content	= $view->loadContentFile( 'html/info/dashboard/empty.html' );
	return $textTop.$content.$textBottom.$modalAdd;
}

$helper		= new View_Helper_Info_Dashboard( $env );
$helper->setDashboard( $dashboard );
$helper->setPanels( $panels );

$iconAddBoard	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-th' ) );
$iconAddPanel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-square' ) );
$iconEditTitle	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$buttonAddBoard		= UI_HTML_Tag::create( 'button', $iconAddBoard, array(
	'type'		=> 'button',
	'class'		=> 'btn not-btn-small btn-success trigger-myModalInfoDashboardAdd',
	'title'		=> 'neues Dashboard',
) );
$buttonAddPanel		= UI_HTML_Tag::create( 'button', $iconAddPanel, array(
	'type'		=> 'button',
	'class'		=> 'btn not-btn-small btn-success trigger-myModalInfoDashboardAddPanel',
	'title'		=> 'neues Panel im Dashboard',
) );
$buttonEditTitle	= UI_HTML_Tag::create( 'button', $iconEditTitle, array(
	'type'				=> 'button',
	'id'				=> 'button-rename-board',
	'class'				=> 'btn btn-small',
	'data-dashboard-id'	=> $dashboard->dashboardId,
	'data-title'		=> htmlentities( $dashboard->title, ENT_QUOTES, 'UTF-8' ),
) );
$buttonRemoveBoard	= UI_HTML_Tag::create( 'a', $iconRemove, array(
	'href'		=> './info/dashboard/remove/'.$dashboard->dashboardId,
	'class'		=> 'btn btn-small btn-inverse',
	'title'		=> 'aktuelles Dashboard verwerfen',
	'onclick'	=> 'if(!confirm(\'Wirklich das aktuelle Dashboard auflösen?\'))return false;',
) );

$optDashboardId	= array();
foreach( $dashboards as $entry )
	$optDashboardId[$entry->dashboardId]	= $entry->title;
$optDashboardId	= UI_HTML_Elements::Options( $optDashboardId, $dashboard->dashboardId );


$helperAddPanel	= new View_Helper_Info_Dashboard_Modal_AddPanel( $env );
$helperAddPanel->setDashboard( $dashboard );
$helperAddPanel->setPanels( $panels );
$modalAddPanel	= $helperAddPanel->render();

return $textTop.'
<div class="row-fluid">
	<div class="span7">
		<h3><span class="muted">Dashboard: </span> '.$dashboard->title.'</h3>
	</div>
	<div class="span5">
		<div class="pull-right">
			<form class="form-inline">
				<select name="dashboardId" id="input_dashboardId" onchange="InfoDashboard.select(jQuery(this).val());">'.$optDashboardId.'</select>
				<div class="btn-group">
					'.$buttonEditTitle.'
					'.$buttonRemoveBoard.'
				</div>
				&nbsp;|&nbsp;
				<div class="btn-group">
					'.$buttonAddBoard.'
					'.$buttonAddPanel.'&nbsp;
				</div>
			</form>
		</div>
	</div>
</div>
'.$helper->render().'
<hr/>
'.$textBottom.$modalAdd.$modalAddPanel;
?>
