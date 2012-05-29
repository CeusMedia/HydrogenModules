<?php
$heading	= UI_HTML_Tag::create( 'h2', $words['index']['heading'] );
$add 		= UI_HTML_Elements::LinkButton( './manage/branch/add', $words['index']['buttonAdd'], 'button add' );

$rows		= array();
$number		= 0;
foreach( $branches as $entry )
{
	$class		= ( $number % 2 ) ? 'even' : 'odd';
	$format		= $config->get( 'layout.format.timestamp' );
	$timeHelper	= new CMF_Hydrogen_View_Helper_Timestamp( $entry->createdAt );
	$createdAt	= $timeHelper->toPhrase( $env, TRUE );
	$timeHelper	= new CMF_Hydrogen_View_Helper_Timestamp( $entry->modifiedAt );
	$modifiedAt	= $entry->modifiedAt ? $timeHelper->toPhrase( $env, TRUE ) : '';
	$url		= './manage/branch/edit/'.$entry->branchId;
	$link		= UI_HTML_Elements::Link( $url, $entry->title );
	$company	= $entry->company->title;
	$uriEdit		= './manage/branch/edit/'.$entry->branchId;
	$buttonEdit		= UI_HTML_Elements::LinkButton( $uriEdit, '', 'tiny edit' );
	$uriRemove		= './manage/branch/remove/'.$entry->branchId;
	$buttonRemove	= UI_HTML_Elements::LinkButton( $uriEdit, '', 'tiny remove' );
	$uriActivate		= './manage/branch/activate/'.$entry->branchId;
	$buttonActivate		= UI_HTML_Elements::LinkButton( $uriActivate, '', 'tiny accept', NULL, $entry->status == 1 );
	$uriDeactivate		= './manage/branch/deactivate/'.$entry->branchId;
	$buttonDeactivate	= UI_HTML_Elements::LinkButton( $uriDeactivate, '', 'tiny decline', NULL, $entry->status == -1 );
	$check		= UI_HTML_Elements::Checkbox( 'branchId', $entry->branchId );
	$rows[]		= '	<tr class="'.$class.'">
<!--		<td>'.$check.'</td>-->
		<td>'.$link.'</td>
		<td>'.$company.'</td>
		<td>'.$createdAt.'</td>
		<td>'.$modifiedAt.'</td>
		<td>'.$buttonEdit./*$buttonRemove.*/$buttonActivate.$buttonDeactivate.'</td>
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
	$words['index']['headAction'],
);
$heads		= UI_HTML_Elements::TableHeads( $heads );
$colgroup	= UI_HTML_Elements::ColumnGroup( /*'3%', */'32%', '25%', '15%', '15%', '10%' );


return '
<!--'.$heading.'-->
<fieldset>
	<legend>'.$words['index']['legend'].'</legend>
	<table width="100%">
		'.$colgroup.'
		'.$heads.'
		'.$rows.'
	</table>
	<div class="buttonbar">
		'.$add.'
	</div>
</fieldset>
';
?>