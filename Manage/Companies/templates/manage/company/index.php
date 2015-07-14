<?php
$heading	= UI_HTML_Tag::create( 'h2', $words['index']['heading'] );

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );

$buttonAdd 	= UI_HTML_Elements::LinkButton( './manage/company/add', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], 'btn btn-primary' );

$helperTime	= new View_Helper_TimePhraser( $env );

$rows		= array();
$number		= 0;
$helperTime	= new View_Helper_TimePhraser( $env );
foreach( $companies as $entry ){
	$class				= $entry->status == 2 ? 'success' : ( $entry->status < 0 ? 'error' : 'warning' );

	$createdAt			= $helperTime->convert( $entry->createdAt, TRUE, 'vor ' );
	$modifiedAt			= $entry->modifiedAt ? $helperTime->convert( $entry->modifiedAt, TRUE, 'vor ' ) : "-";

	$url				= './manage/company/edit/'.$entry->companyId;
	$uriEdit			= './manage/company/edit/'.$entry->companyId;
	$uriRemove			= './manage/company/remove/'.$entry->companyId;
	$uriActivate		= './manage/company/activate/'.$entry->companyId;
	$uriDeactivate		= './manage/company/deactivate/'.$entry->companyId;

	$link				= UI_HTML_Elements::Link( $url, $entry->title );
	$buttonEdit			= UI_HTML_Elements::LinkButton( $uriEdit, '<i class="icon-pencil"></i>', 'btn btn-mini' );
	$buttonActivate		= UI_HTML_Elements::LinkButton( $uriActivate, '<i class="icon-check icon-white"></i>', 'btn btn-mini btn-success', NULL, $entry->status == 1 );
	$buttonDeactivate	= UI_HTML_Elements::LinkButton( $uriDeactivate, '<i class="icon-remove icon-white"></i>', 'btn btn-mini btn-danger', NULL, $entry->status == -1 );
	$buttons			= UI_HTML_Tag::create( 'div', $buttonEdit/*.$buttonActivate.$buttonDeactivate*/, array( 'class' => 'btn-group' ) );
	$check		= UI_HTML_Elements::Checkbox( 'companyId', $entry->companyId );
	$rows[]		= UI_HTML_Tag::create( 'tr', array(
//		UI_HTML_Tag::create( 'td', $check, array( 'class' => 'cell-check' ),
		UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-title' ) ),
		UI_HTML_Tag::create( 'td', $entry->city, array( 'class' => 'cell-city' ) ),
		UI_HTML_Tag::create( 'td', $createdAt, array( 'class' => 'cell-created' ) ),
		UI_HTML_Tag::create( 'td', $modifiedAt, array( 'class' => 'cell-modified' ) ),
		UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'cell-action' ) ),
	), array( 'class' => $class ) );
	$number		++;
}
$rows	= implode( "\n", $rows );

$heads	= array(
//	'<input type="checkbox" class="toggler"/>',
	$words['index']['headTitle'],
	$words['index']['headCity'],
	$words['index']['headCreatedAt'],
	$words['index']['headModifiedAt'],
	$words['index']['headAction'],
);
$heads		= UI_HTML_Elements::TableHeads( $heads );
$colgroup	= UI_HTML_Elements::ColumnGroup( '42%', '20%', '15%', '15%', '5%' );
$thead		= UI_HTML_Tag::create( 'thead', $heads );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed' ) );

$panelList	= '<div class="content-panel">
	<h3>'.$words['index']['legend'].'</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';

$panelFilter	= '<div class="content-panel">
	<h3 class="muted">Filter</h3>
	<div class="content-panel-inner">
		<p class="muted">...</p>
		<div class="buttonbar">
			<div class="btn-toolbar">
			</div>
		</div>
	</div>
</div>';

return HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span3',
		$panelFilter
	).
	HTML::DivClass( 'span9',
		$panelList
	)
);
?>
