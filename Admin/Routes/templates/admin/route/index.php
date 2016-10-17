<?php


$table		= UI_HTML_Tag::create( 'div', '<em>Keine Routen definiert.</em>', array( 'alert alert-info' ) );
if( $routes ){

	$rows	= array();
	foreach( $routes as $route ){
		$id		= md5( $route->source );
		$code	= $route->code ? UI_HTML_Tag::create( 'abbr', $route->code, array( 'title' => Net_HTTP_Status::getText( $route->code ) ) ) : '-';
		$status	= $route->status ? 'aktiv' : 'inaktiv';

		$buttons = array();
		$buttons[]	= UI_HTML_Tag::create( 'a', '<i class="icon-eye-open"></i>', array( 'href' => $route->source, 'target' => '_blank', 'class' => 'btn btn-small' ) );
		if( !$route->status )
			$buttons[]	= UI_HTML_Tag::create( 'a', '<i class="icon-ok icon-white"></i>', array( 'href' => './admin/route/activate/'.$id, 'class' => 'btn btn-small btn-success', 'title' => 'activieren' ) );
		else
			$buttons[]	= UI_HTML_Tag::create( 'a', '<i class="icon-remove icon-white"></i>', array( 'href' => './admin/route/deactivate/'.$id, 'class' => 'btn btn-small btn-inverse', 'title' => 'deaktivieren' ) );
		$buttons[]	= UI_HTML_Tag::create( 'a', '<i class="icon-trash icon-white"></i>', array( 'href' => './admin/route/remove/'.$id, 'class' => 'btn btn-small btn-danger', 'title' => 'entfernen' ) );
		$buttons	= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group pull-right' ) );

		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $route->source, array( 'class' => 'cell-route-source' ) ),
			UI_HTML_Tag::create( 'td', $route->target, array( 'class' => 'cell-route-target' ) ),
			UI_HTML_Tag::create( 'td', $code, array( 'class' => 'cell-route-code' ) ),
			UI_HTML_Tag::create( 'td', $status, array( 'class' => 'cell-route-status' ) ),
			UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'cell-route-actions' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "30%", "30%", "10%", "15%", "15%" );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Quelle', 'Ziel', 'Code', 'Zustand', 'Aktion' ) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}


return '
<style>
.cell-route-actions {
	text-align: right;
	}
</style>
<!--<h2>Routing-Manager</h2>-->
<div class="row-fluid">
	<div class="span3">
		<div class="content-panel content-panel-filter">
			<h3>Filters</h3>
			<div class="content-panel-inner">
				[Filters]
			</div>
		</div>
	</div>
	<div class="span9">
		<div class="content-panel content-panel-table">
			<h3>Routen</h3>
			<div class="content-panel-inner">
				'.$table.'
			</div>
		</div>
	</div>
</div>';
