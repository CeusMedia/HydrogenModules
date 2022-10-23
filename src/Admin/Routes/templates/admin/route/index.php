<?php

use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $routes */

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$table		= HtmlTag::create( 'div', '<em>Keine Routen definiert.</em>', ['alert alert-info'] );
if( $routes ){
	$rows	= [];
	foreach( $routes as $id => $route ){
		$regex	= $route->regex ? 'ja' : 'nein';
		$code	= $route->code ? HtmlTag::create( 'abbr', $route->code, ['title' => HttpStatus::getText( $route->code )] ) : '-';
		$status	= $route->status ? 'aktiv' : 'inaktiv';
		$status	= HtmlTag::create( 'span', $status, ['class' => 'label '.( $route->status ? 'label-success' : 'label-important' )] );

		$buttons = [];
		if( !$route->regex )
			$buttons[]	= HtmlTag::create( 'a', '<i class="icon-eye-open"></i>', ['href' => $route->source, 'target' => '_blank', 'class' => 'btn btn-small'] );
		$buttons[]	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-pencil"></i>', ['href' => './admin/route/edit/'.$id, 'class' => 'btn btn-small'] );
//		if( !$route->status )
//			$buttons[]	= HtmlTag::create( 'a', '<i class="icon-ok icon-white"></i>', ['href' => './admin/route/activate/'.$id, 'class' => 'btn btn-small btn-success', 'title' => 'aktivieren'] );
//		else
//			$buttons[]	= HtmlTag::create( 'a', '<i class="icon-remove icon-white"></i>', ['href' => './admin/route/deactivate/'.$id, 'class' => 'btn btn-small btn-inverse', 'title' => 'deaktivieren'] );
		$buttons	= HtmlTag::create( 'div', $buttons, ['class' => 'btn-group pull-right'] );

		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $status, ['class' => 'cell-route-status'] ),
			HtmlTag::create( 'td', $code, ['class' => 'cell-route-code'] ),
			HtmlTag::create( 'td', $route->source, ['class' => 'cell-route-source autocut'] ),
			HtmlTag::create( 'td', $route->target, ['class' => 'cell-route-target autocut'] ),
			HtmlTag::create( 'td', $buttons, ['class' => 'cell-route-actions'] ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( "8%", "7%", "", "", "8%" );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Aktiv', 'Code', 'Quelle', 'Ziel', ''] ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped table-fixed'] );
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
