<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$heading	= HtmlTag::create( 'h2', $words['index']['heading'] );

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );

$buttonAdd 	= HtmlElements::LinkButton( './manage/company/add', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], 'btn btn-primary' );

$helperTime	= new View_Helper_TimePhraser( $env );

$rows		= [];
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

	$link				= HtmlElements::Link( $url, $entry->title );
	$buttonEdit			= HtmlElements::LinkButton( $uriEdit, '<i class="icon-pencil"></i>', 'btn btn-mini' );
	$buttonActivate		= HtmlElements::LinkButton( $uriActivate, '<i class="icon-check icon-white"></i>', 'btn btn-mini btn-success', NULL, $entry->status == 1 );
	$buttonDeactivate	= HtmlElements::LinkButton( $uriDeactivate, '<i class="icon-remove icon-white"></i>', 'btn btn-mini btn-danger', NULL, $entry->status == -1 );
	$buttons			= HtmlTag::create( 'div', $buttonEdit/*.$buttonActivate.$buttonDeactivate*/, ['class' => 'btn-group'] );
	$check		= HtmlElements::Checkbox( 'companyId', $entry->companyId );
	$rows[]		= HtmlTag::create( 'tr', array(
//		HtmlTag::create( 'td', $check, ['class' => 'cell-check'],
		HtmlTag::create( 'td', $link, ['class' => 'cell-title'] ),
		HtmlTag::create( 'td', $entry->city, ['class' => 'cell-city'] ),
		HtmlTag::create( 'td', $createdAt, ['class' => 'cell-created'] ),
		HtmlTag::create( 'td', $modifiedAt, ['class' => 'cell-modified'] ),
		HtmlTag::create( 'td', $buttons, ['class' => 'cell-action'] ),
	), ['class' => $class] );
	$number		++;
}
$rows	= implode( "\n", $rows );

$heads	= [
//	'<input type="checkbox" class="toggler"/>',
	$words['index']['headTitle'],
	$words['index']['headCity'],
	$words['index']['headCreatedAt'],
	$words['index']['headModifiedAt'],
	$words['index']['headAction'],
];
$heads		= HtmlElements::TableHeads( $heads );
$colgroup	= HtmlElements::ColumnGroup( '42%', '20%', '15%', '15%', '5%' );
$thead		= HtmlTag::create( 'thead', $heads );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-condensed'] );

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
