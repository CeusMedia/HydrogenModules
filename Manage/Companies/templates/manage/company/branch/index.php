<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w				= (object) $words['index'];

$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$iconEdit		= HtmlTag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
$iconActivate	= HtmlTag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );
$iconDeactivate	= HtmlTag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$heading	= HtmlTag::create( 'h2', $w->heading );
$buttonAdd 	= HtmlElements::LinkButton( './manage/company/branch/add', $iconAdd.'&nbsp;'.$w->buttonAdd, 'btn btn-primary' );

$rows		= [];
$number		= 0;
$helperTime	= new View_Helper_TimePhraser( $env );
foreach( $branches as $entry ){
	$class				= $entry->status == 2 ? 'success' : ( $entry->status < 0 ? 'error' : 'warning' );
	$createdAt			= $helperTime->convert( $entry->createdAt, TRUE, 'vor ' );
	$modifiedAt			= $entry->modifiedAt ? $helperTime->convert( $entry->modifiedAt, TRUE, 'vor ' ) : '-';
	$url				= './manage/company/branch/edit/'.$entry->branchId;
	$link				= HtmlElements::Link( $url, $entry->title );
	$company			= $entry->company->title;
	$uriEdit			= './manage/company/branch/edit/'.$entry->branchId;
	$buttonEdit			= HtmlElements::LinkButton( $uriEdit, $iconEdit, 'btn btn-mini' );

	$uriActivate		= './manage/company/branch/activate/'.$entry->branchId;
	$uriDeactivate		= './manage/company/branch/deactivate/'.$entry->branchId;
	$buttonActivate		= HtmlElements::LinkButton( $uriActivate, $iconActivate, 'btn btn-mini btn-success', NULL, $entry->status == 1 );
	$buttonDeactivate	= HtmlElements::LinkButton( $uriDeactivate, $iconDeactivate, 'btn btn-mini btn-inverse', NULL, $entry->status == -1 );
	$check		= HtmlElements::Checkbox( 'branchId', $entry->branchId );
	$rows[]		= '	<tr class="'.$class.'">
<!--		<td>'.$check.'</td>-->
		<td>'.$link.'</td>
		<td>'.$company.'</td>
		<td>'.$createdAt.'</td>
		<td>'.$modifiedAt.'</td>
		<td>'.HtmlTag::create( 'div', $buttonEdit/*.$buttonActivate.$buttonDeactivate*/, array( 'class' => 'btn-group' ) ).'</td>
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
$heads		= HtmlElements::TableHeads( $heads );
$colgroup	= HtmlElements::ColumnGroup( '', '', '120px', '120px', '100px' );

$thead		= HtmlTag::create( 'thead', $heads );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed' ) );

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
