<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View\Helper\Timestamp;

/** @var object[] $branches */
/** @var Dictionary $config */
/** @var Web $env */
/** @var array<array<string,string>> $words */

$heading	= HtmlTag::create( 'h2', $words['index']['heading'] );
$add 		= HtmlElements::LinkButton( './manage/my/branch/add', $words['index']['buttonAdd'], 'button add' );

$rows		= [];
$number		= 0;
foreach( $branches as $entry )
{
	$class		= ( $number % 2 ) ? 'even' : 'odd';
	$format		= $config->get( 'layout.format.timestamp' );
	$timeHelper	= new Timestamp( $entry->createdAt );
	$createdAt	= $timeHelper->toPhrase( $env, TRUE );
	$timeHelper	= new Timestamp( $entry->modifiedAt );
	$modifiedAt	= $entry->modifiedAt ? $timeHelper->toPhrase( $env, TRUE ) : '';
	$url			= './manage/my/branch/edit/'.$entry->branchId;
	$uriEdit		= './manage/my/branch/edit/'.$entry->branchId;
	$uriRemove		= './manage/my/branch/remove/'.$entry->branchId;
	$uriActivate	= './manage/my/branch/activate/'.$entry->branchId;
	$uriDeactivate	= './manage/my/branch/deactivate/'.$entry->branchId;
	$link		= HtmlElements::Link( $url, $entry->title );
	$company	= $entry->company ? $entry->company->title : '-';
	$buttonEdit			= HtmlElements::LinkButton( $uriEdit, '', 'tiny edit' );
	$buttonRemove		= HtmlElements::LinkButton( $uriEdit, '', 'tiny remove' );
	$buttonActivate		= HtmlElements::LinkButton( $uriActivate, '', 'tiny accept', NULL, $entry->status == 1 );
	$buttonDeactivate	= HtmlElements::LinkButton( $uriDeactivate, '', 'tiny decline', NULL, $entry->status == -1 );
	$check		= HtmlElements::Checkbox( 'branchId', $entry->branchId );
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
$heads		= HtmlElements::TableHeads( $heads );
$colgroup	= HtmlElements::ColumnGroup( /*'3%', */'32%', '25%', '15%', '15%'/*, '10%'*/ );


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