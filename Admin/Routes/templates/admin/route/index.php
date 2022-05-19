<?php
$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$table		= UI_HTML_Tag::create( 'div', '<em>Keine Routen definiert.</em>', array( 'alert alert-info' ) );
if( $routes ){
	$rows	= [];
	foreach( $routes as $id => $route ){
		$regex	= $route->regex ? 'ja' : 'nein';
		$code	= $route->code ? UI_HTML_Tag::create( 'abbr', $route->code, array( 'title' => Net_HTTP_Status::getText( $route->code ) ) ) : '-';
		$status	= $route->status ? 'aktiv' : 'inaktiv';
		$status	= UI_HTML_Tag::create( 'span', $status, array( 'class' => 'label '.( $route->status ? 'label-success' : 'label-important' ) ) );

		$buttons = [];
		if( !$route->regex )
			$buttons[]	= UI_HTML_Tag::create( 'a', '<i class="icon-eye-open"></i>', array( 'href' => $route->source, 'target' => '_blank', 'class' => 'btn btn-small' ) );
		$buttons[]	= UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-pencil"></i>', array( 'href' => './admin/route/edit/'.$id, 'class' => 'btn btn-small' ) );
//		if( !$route->status )
//			$buttons[]	= UI_HTML_Tag::create( 'a', '<i class="icon-ok icon-white"></i>', array( 'href' => './admin/route/activate/'.$id, 'class' => 'btn btn-small btn-success', 'title' => 'activieren' ) );
//		else
//			$buttons[]	= UI_HTML_Tag::create( 'a', '<i class="icon-remove icon-white"></i>', array( 'href' => './admin/route/deactivate/'.$id, 'class' => 'btn btn-small btn-inverse', 'title' => 'deaktivieren' ) );
		$buttons	= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group pull-right' ) );

		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $status, array( 'class' => 'cell-route-status' ) ),
			UI_HTML_Tag::create( 'td', $code, array( 'class' => 'cell-route-code' ) ),
			UI_HTML_Tag::create( 'td', $route->source, array( 'class' => 'cell-route-source autocut' ) ),
			UI_HTML_Tag::create( 'td', $route->target, array( 'class' => 'cell-route-target autocut' ) ),
			UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'cell-route-actions' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "8%", "7%", "", "", "8%" );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Aktiv', 'Code', 'Quelle', 'Ziel', '' ) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Route', array(
	'href'	=> './admin/route/add',
	'class'	=> 'btn btn-success',
) );

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
				<div class="buttonbar">
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	</div>
</div>';
