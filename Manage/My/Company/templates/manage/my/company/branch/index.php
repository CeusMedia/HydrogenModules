<?php

$iconAdd	= HTML::Icon( 'plus', TRUE );

$heading	= UI_HTML_Tag::create( 'h2', $words['index']['heading'] );
$add 		= UI_HTML_Elements::LinkButton( './manage/my/company/branch/add', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], 'btn btn-small btn-primary' );


$timeHelper	= new View_Helper_TimePhraser( $env );
$rows		= array();
$number		= 0;
foreach( $branches as $entry ){
	$class		= ( $number % 2 ) ? 'even' : 'odd';
	$createdAt	= $timeHelper->convert( $entry->createdAt, TRUE );
	$modifiedAt	= $entry->modifiedAt ? $timeHelper->convert( $entry->modifiedAt, TRUE ) : '';
	$url			= './manage/my/company/branch/edit/'.$entry->branchId;
	$uriEdit		= './manage/my/company/branch/edit/'.$entry->branchId;
	$uriRemove		= './manage/my/company/branch/remove/'.$entry->branchId;
	$uriActivate	= './manage/my/company/branch/activate/'.$entry->branchId;
	$uriDeactivate	= './manage/my/company/branch/deactivate/'.$entry->branchId;
	$link		= UI_HTML_Elements::Link( $url, $entry->title );
	$company	= $entry->company ? $entry->company->title : '-';
	$buttonEdit			= UI_HTML_Elements::LinkButton( $uriEdit, '', 'tiny edit' );
	$buttonRemove		= UI_HTML_Elements::LinkButton( $uriEdit, '', 'tiny remove' );
	$buttonActivate		= UI_HTML_Elements::LinkButton( $uriActivate, '', 'tiny accept', NULL, $entry->status == 1 );
	$buttonDeactivate	= UI_HTML_Elements::LinkButton( $uriDeactivate, '', 'tiny decline', NULL, $entry->status == -1 );
	$check		= UI_HTML_Elements::Checkbox( 'branchId', $entry->branchId );
	$rows[]		= '	<tr class="'.$class.'">
<!--		<td>'.$check.'</td>-->
		<td>'.$link.'</td>
		<td>'.$company.'</td>
		<td>'.$createdAt.'</td>
		<td>'.$modifiedAt.'</td>
<!--		<td>'.$buttonEdit./*$buttonRemove.*/$buttonActivate.$buttonDeactivate.'</td>-->
	</tr>';
	$number		++;
}
$rows	= implode( "\n", $rows );

$heads	= array(
//	'<input type="checkbox" class="toggler"/>',
	$words['index']['headTitle'],
	$words['index']['headCompany'],
	$words['index']['headCreatedAt'],
	$words['index']['headModifiedAt'],
//	$words['index']['headAction'],
);
$heads		= UI_HTML_Elements::TableHeads( $heads );
$colgroup	= UI_HTML_Elements::ColumnGroup( /*'3%', */'32%', '25%', '15%', '15%'/*, '10%'*/ );


return '
<!--'.$heading.'-->
<div class="content-panel">
	<h3>'.$words['index']['legend'].'</h3>
	<div class="content-panel-inner">
		<table width="100%" class="table">
			'.$colgroup.'
			'.$heads.'
			'.$rows.'
		</table>
		<div class="buttonbar">
			'.$add.'
		</div>
	</div>
</div>';
?>
