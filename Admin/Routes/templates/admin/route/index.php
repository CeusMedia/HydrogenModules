<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$table		= HtmlTag::create( 'div', '<em>Keine Routen definiert.</em>', array( 'alert alert-info' ) );
if( $routes ){
	$rows	= [];
	foreach( $routes as $id => $route ){
		$regex	= $route->regex ? 'ja' : 'nein';
		$code	= $route->code ? HtmlTag::create( 'abbr', $route->code, array( 'title' => Net_HTTP_Status::getText( $route->code ) ) ) : '-';
		$status	= $route->status ? 'aktiv' : 'inaktiv';
		$status	= HtmlTag::create( 'span', $status, array( 'class' => 'label '.( $route->status ? 'label-success' : 'label-important' ) ) );

		$buttons = [];
		if( !$route->regex )
			$buttons[]	= HtmlTag::create( 'a', '<i class="icon-eye-open"></i>', array( 'href' => $route->source, 'target' => '_blank', 'class' => 'btn btn-small' ) );
		$buttons[]	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-pencil"></i>', array( 'href' => './admin/route/edit/'.$id, 'class' => 'btn btn-small' ) );
//		if( !$route->status )
//			$buttons[]	= HtmlTag::create( 'a', '<i class="icon-ok icon-white"></i>', array( 'href' => './admin/route/activate/'.$id, 'class' => 'btn btn-small btn-success', 'title' => 'activieren' ) );
//		else
//			$buttons[]	= HtmlTag::create( 'a', '<i class="icon-remove icon-white"></i>', array( 'href' => './admin/route/deactivate/'.$id, 'class' => 'btn btn-small btn-inverse', 'title' => 'deaktivieren' ) );
		$buttons	= HtmlTag::create( 'div', $buttons, array( 'class' => 'btn-group pull-right' ) );

		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $status, array( 'class' => 'cell-route-status' ) ),
			HtmlTag::create( 'td', $code, array( 'class' => 'cell-route-code' ) ),
			HtmlTag::create( 'td', $route->source, array( 'class' => 'cell-route-source autocut' ) ),
			HtmlTag::create( 'td', $route->target, array( 'class' => 'cell-route-target autocut' ) ),
			HtmlTag::create( 'td', $buttons, array( 'class' => 'cell-route-actions' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "8%", "7%", "", "", "8%" );
	$thead	= HtmlTag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Aktiv', 'Code', 'Quelle', 'Ziel', '' ) ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped table-fixed' ) );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;neue Route', array(
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
