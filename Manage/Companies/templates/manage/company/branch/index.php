<?php
$w				= (object) $words['index'];

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
$iconActivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );
$iconDeactivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$heading	= UI_HTML_Tag::create( 'h2', $w->heading );
$buttonAdd 	= UI_HTML_Elements::LinkButton( './manage/company/branch/add', $iconAdd.'&nbsp;'.$w->buttonAdd, 'btn btn-primary' );

$rows		= array();
$number		= 0;
$helperTime	= new View_Helper_TimePhraser( $env );
foreach( $branches as $entry ){
	$class				= $entry->status == 2 ? 'success' : ( $entry->status < 0 ? 'error' : 'warning' );
	$createdAt			= $helperTime->convert( $entry->createdAt, TRUE, 'vor ' );
	$modifiedAt			= $entry->modifiedAt ? $helperTime->convert( $entry->modifiedAt, TRUE, 'vor ' ) : '-';
	$url				= './manage/company/branch/edit/'.$entry->branchId;
	$link				= UI_HTML_Elements::Link( $url, $entry->title );
	$company			= $entry->company->title;
	$uriEdit			= './manage/company/branch/edit/'.$entry->branchId;
	$buttonEdit			= UI_HTML_Elements::LinkButton( $uriEdit, $iconEdit, 'btn btn-mini' );

	$uriActivate		= './manage/company/branch/activate/'.$entry->branchId;
	$uriDeactivate		= './manage/company/branch/deactivate/'.$entry->branchId;
	$buttonActivate		= UI_HTML_Elements::LinkButton( $uriActivate, $iconActivate, 'btn btn-mini btn-success', NULL, $entry->status == 1 );
	$buttonDeactivate	= UI_HTML_Elements::LinkButton( $uriDeactivate, $iconDeactivate, 'btn btn-mini btn-inverse', NULL, $entry->status == -1 );
	$check		= UI_HTML_Elements::Checkbox( 'branchId', $entry->branchId );
	$rows[]		= '	<tr class="'.$class.'">
<!--		<td>'.$check.'</td>-->
		<td>'.$link.'</td>
		<td>'.$company.'</td>
		<td>'.$createdAt.'</td>
		<td>'.$modifiedAt.'</td>
		<td>'.UI_HTML_Tag::create( 'div', $buttonEdit/*.$buttonActivate.$buttonDeactivate*/, array( 'class' => 'btn-group' ) ).'</td>
	</tr>';
	$number		++;
}
$rows	= implode( "\n", $rows );

$heads	= array(
//	'<input type="checkbox" class="toggler"/>',
	$w->headTitle,
	$w->headCompany,
	$w->headCreatedAt,
	$w->headModifiedAt,
	$w->headAction,
);
$heads		= UI_HTML_Elements::TableHeads( $heads );
$colgroup	= UI_HTML_Elements::ColumnGroup( '', '', '120px', '120px', '100px' );

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
