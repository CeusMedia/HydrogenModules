<?php
$heading	= UI_HTML_Tag::create( 'h2', $words['index']['heading'] );
$add 		= UI_HTML_Elements::LinkButton( './manage/company/add', $words['index']['buttonAdd'], 'button add' );

$helperTime	= new View_Helper_TimePhraser( $env );

$rows		= array();
$number		= 0;
foreach( $companies as $entry ){
	$class				= ( $number % 2 ) ? 'even' : 'odd';
	$format				= $config->get( 'layout.format.timestamp' );

	$timeHelper			= new CMF_Hydrogen_View_Helper_Timestamp( $entry->createdAt );
	$createdAt			= $helperTime->convert( $entry->createdAt );
	$modifiedAt			= $entry->modifiedAt ? $helperTime->convert( $entry->modifiedAt ) : "-";

	$url				= './manage/company/edit/'.$entry->companyId;
	$uriEdit			= './manage/company/edit/'.$entry->companyId;
	$uriRemove			= './manage/company/remove/'.$entry->companyId;
	$uriActivate		= './manage/company/activate/'.$entry->companyId;
	$uriDeactivate		= './manage/company/deactivate/'.$entry->companyId;

	$link				= UI_HTML_Elements::Link( $url, $entry->title );
	$buttonEdit			= UI_HTML_Elements::LinkButton( $uriEdit, '<i class="icon-pencil"></i>', 'btn btn-mini' );
	$buttonRemove		= UI_HTML_Elements::LinkButton( $uriRemove, '', 'btn btn-mini' );
	$buttonActivate		= UI_HTML_Elements::LinkButton( $uriActivate, '<i class="icon-check icon-white"></i>', 'btn btn-mini btn-success', NULL, $entry->status == 1 );
	$buttonDeactivate	= UI_HTML_Elements::LinkButton( $uriDeactivate, '<i class="icon-remove icon-white"></i>', 'btn btn-mini btn-danger', NULL, $entry->status == -1 );

	$check		= UI_HTML_Elements::Checkbox( 'companyId', $entry->companyId );
	$rows[]		= '	<tr class="'.$class.'">
<!--		<td>'.$check.'</td>-->
		<td>'.$link.'</td>
		<td>'.$createdAt.'</td>
		<td>'.$modifiedAt.'</td>
		<td>'.$buttonEdit/*.$buttonRemove*/.$buttonActivate.$buttonDeactivate.'</td>
	</tr>';
	$number		++;
}
$rows	= implode( "\n", $rows );

$heads	= array(
//	'<input type="checkbox" class="toggler"/>',
	$words['index']['headTitle'],
	$words['index']['headCreatedAt'],
	$words['index']['headModifiedAt'],
	$words['index']['headAction'],
);
$heads		= UI_HTML_Elements::TableHeads( $heads );
$colgroup	= UI_HTML_Elements::ColumnGroup( /*'3%', */'57%', '15%', '15%', '10%' );

return '
<!--'.$heading.'-->
<fieldset>
	<legend class="list">'.$words['index']['legend'].'</legend>
	<table class="table">
		'.$colgroup.'
		'.UI_HTML_Tag::create( 'thead', $heads ).'
		'.UI_HTML_Tag::create( 'tbody', $rows ).'
	</table>
	<div class="buttonbar">
		'.$add.'
	</div>
</fieldset>
';
?>
