<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View\Helper\Timestamp;

/** @var object[] $companies */
/** @var Dictionary $config */
/** @var Web $env */
/** @var array<array<string,string>> $words */

$heading	= HtmlTag::create( 'h2', $words['index']['heading'] );
$add 		= HtmlElements::LinkButton( './manage/company/add', $words['index']['buttonAdd'], 'button add' );

$helperTime	= new View_Helper_TimePhraser( $env );

$rows		= [];
$number		= 0;
foreach( $companies as $entry ){
	$class				= ( $number % 2 ) ? 'even' : 'odd';
	$format				= $config->get( 'layout.format.timestamp' );

	$timeHelper			= new Timestamp( $entry->createdAt );
	$createdAt			= $helperTime->convert( $entry->createdAt );
	$modifiedAt			= $entry->modifiedAt ? $helperTime->convert( $entry->modifiedAt ) : "-";

	$url				= './manage/company/edit/'.$entry->companyId;
	$uriEdit			= './manage/company/edit/'.$entry->companyId;
	$uriRemove			= './manage/company/remove/'.$entry->companyId;
	$uriActivate		= './manage/company/activate/'.$entry->companyId;
	$uriDeactivate		= './manage/company/deactivate/'.$entry->companyId;

	$link				= HtmlElements::Link( $url, $entry->title );
	$buttonEdit			= HtmlElements::LinkButton( $uriEdit, '<i class="icon-pencil"></i>', 'btn btn-mini' );
	$buttonRemove		= HtmlElements::LinkButton( $uriRemove, '', 'btn btn-mini' );
	$buttonActivate		= HtmlElements::LinkButton( $uriActivate, '<i class="icon-check icon-white"></i>', 'btn btn-mini btn-success', NULL, $entry->status == 1 );
	$buttonDeactivate	= HtmlElements::LinkButton( $uriDeactivate, '<i class="icon-remove icon-white"></i>', 'btn btn-mini btn-danger', NULL, $entry->status == -1 );

	$check		= HtmlElements::Checkbox( 'companyId', $entry->companyId );
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

$heads	= [
//	'<input type="checkbox" class="toggler"/>',
	$words['index']['headTitle'],
	$words['index']['headCreatedAt'],
	$words['index']['headModifiedAt'],
	$words['index']['headAction'],
];
$heads		= HtmlElements::TableHeads( $heads );
$colgroup	= HtmlElements::ColumnGroup( /*'3%', */'57%', '15%', '15%', '10%' );

return '
<!--'.$heading.'-->
<fieldset>
	<legend class="list">'.$words['index']['legend'].'</legend>
	<table class="table">
		'.$colgroup.'
		'.HtmlTag::create( 'thead', $heads ).'
		'.HtmlTag::create( 'tbody', $rows ).'
	</table>
	<div class="buttonbar">
		'.$add.'
	</div>
</fieldset>
';
