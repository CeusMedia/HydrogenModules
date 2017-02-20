<?php

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/info/dashboard/' ) );

$panelAddPanel	= '';
$control		= '';

if( !empty( $dashboard ) ){
	$helper		= new View_Helper_Info_Dashboard( $env );
	$helper->setDashboard( $dashboard );
	$helper->setPanels( $panels );

	$optDashboardId	= array();
	foreach( $dashboards as $entry )
		$optDashboardId[$entry->dashboardId]	= $entry->title;
	$optDashboardId	= UI_HTML_Elements::Options( $optDashboardId, $dashboard->dashboardId );

	$control	= '
<div>
	<form class="form-inline">
		<select name="dashboardId" id="input_dashboardId" onchange="InfoDashboard.select(jQuery(this).val());">'.$optDashboardId.'</select>
		<a href="./info/dashboard/remove/'.$dashboard->dashboardId.'" class="btn btn-small btn-inverse"><i class="fa fa-fw fa-trash"></i></a>
		<button class="btn btn-small btn-success"><i class="fa fa-fw fa-plus"></i></a>
	</form>
</div>';

	$optPanelId	= array();
	foreach( $panels as $panel )
		if( !in_array( $panel->id, explode( ',', $dashboard->panels ) ) )
			$optPanelId[$panel->id]	= $panel->title;
	$optPanelId	= UI_HTML_Elements::Options( $optPanelId );

	$panelAddPanel	= '
		<div class="content-panel">
			<h3>Add Panel</h3>
			<div class="content-panel-inner">
				<form action="./info/dashboard/addPanel" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_panelId">Panel</label>
							<select name="panelId" id="input_panelId">'.$optPanelId.'</select>
						</div>
					</div>
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;add</button>
					</div>
				</form>
			</div>
		</div>';
	$content	= $helper->render();
}
else{
	$content	= UI_HTML_Tag::create( 'div', 'No dashboard yet... Please create one!', array( 'class' => 'alert alert-info' ) );
}


$panelAdd	= '<div class="content-panel">
	<h3>Add Dashboard</h3>
	<div class="content-panel-inner">
		<form action="./info/dashboard/add" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Dashboard Title</label>
					<input type="text" name="title" id="input_title" value=""/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;add</button>
			</div>
		</form>
	</div>
</div>';

$style	= '
<style>
.dashboard-panel {}
.dashboard-panel .dashboard-panel-handle {
	background: #EEE;
	padding: 0.2em 0.4em;
	margin-bottom: 0.5em;
	border-radius: 0.2em;
	cursor: move;
	}
.dashboard-panel .dashboard-panel-handle h4 {
	padding: 0;
	margin: 0;
	font-size: 1.2em;
	font-weight: lighter;
	}
.dashboard-panel .dashboard-panel-container {
	height: 240px;
	overflow-x: hidden;
	overflow-y: auto;
	}

.dashboard-panel .dashboard-panel-handle .handle-icon {
	float: right;
	display: none;
	}
.dashboard-panel .dashboard-panel-handle:hover .handle-icon {
	display: inline;
	}
.dashboard-panel .dashboard-panel-handle .handle-icon.handle-button-move {
	float: right;
	margin-right: 0.5em;
	}
.sortable-placeholder {
	background-color: rgba(127, 127, 127, 0.15);
	height: 280px;
	width: 30.5%;
	border: 1px dashed rgba(127, 127, 127, 0.25);
	display: block;
}
</style>';
$script	= '
<script>
var InfoDashboard = {
	init: function(){
		if(jQuery(".thumbnails.sortable>li").size() > 1){
			jQuery(".thumbnails.sortable").sortable({
				containment_: "parent",
				opacity: 0.5,
				revert: true,
				cursor: "move",
				forceHelperSize: true,
				placeholder: "sortable-placeholder",
				handle: ".dashboard-panel-handle .handle-button-move",
				handle: ".dashboard-panel-handle",
				stop: function( event, ui ) {
					var list = [];
					$("ul.thumbnails>li").each(function(){
						list.push($(this).data("panel-id"))
					});
					jQuery.ajax({
						url: "./info/dashboard/ajaxSaveOrder",
						mathodType: "post",
						dataType: "json",
						data: {list: list.join(",")}
					});
				}
			});
		}
	},
	select: function(dashboardId){
		document.location = "./info/dashboard/select/"+dashboardId;
	}
};
jQuery(document).ready(function(){
	InfoDashboard.init();
});
</script>';


return /*$textTop.*/'
	<h3>'.( empty( $dashboard ) ? 'Dashboard' : '<span class="muted">Dashboard: </span> '.$dashboard->title ).'</h3>
	'.$content.'
	<hr/>
	'.$control.'
	<div class="row-fluid">
	<div class="span6">
		'.$panelAdd.'
	</div>
	<div class="span6">
		'.$panelAddPanel.'
	</div>
</div>'.$textBottom.$style.$script;
?>
