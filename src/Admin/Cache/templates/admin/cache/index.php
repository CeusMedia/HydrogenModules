<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $list */
/** @var bool $hasCache */

$wf		= (object) $words['index'];
$rows	= [];
if( !$hasCache )
	return '<div class="hint">'.$wf->hintNoCache.'</div>';

$iconAdd		= new Icon( 'plus' );
$iconRemove		= new Icon( 'trash' );
$buttonRemove	= HtmlTag::create( 'button', $iconRemove, [
	'type'	=> 'button',
	'class'	=> 'btn btn-mini btn-danger btn-cache-remove',
	'title'	=> $wf->buttonRemove,
] );

foreach( $list as $item ){
	$value	= trim( print_m( $item->value, NULL, NULL, TRUE ) );
	$value	= preg_replace( "/^<br\/>(.*)<br\/>$/s", "\\1", $value );
	$cells	= [
		HtmlTag::create( 'td', $item->key ),
		HtmlTag::create( 'td', '<em>'.$item->type.'</em>' ),
		HtmlTag::create( 'td', $value ),
		HtmlTag::create( 'td', $buttonRemove ),
	];
	$rows[]	= HtmlTag::create( 'tr', $cells, ["data-key" => $item->key] );
}

$table		= HtmlTag::create( 'table', [
	HtmlElements::ColumnGroup( ['20%', '5%', '65%', '10%'] ),
	HtmlElements::TableHeads( [$wf->headKey, $wf->headType, $wf->headValue, $wf->headAction] ),
	implode( $rows )
], ['class' => "table table-condensed table-striped"] );

$panelEdit	= '
	<h3>'.$wf->legend.'</h3>
	'.$table;

$wf			= (object) $words['add'];
$optType	= HtmlElements::Options( $words['types'] );
$panelAdd	= '
<h3>'.$wf->legend.'</h3>
<form action="./admin/cache/add" method="post">
	<div class="row-fluid">
		<div class="span2">
			<label for="input_type">'.$wf->labelType.'</label>
			<select name="type" id="input_type" class="max span12">'.$optType.'</select>
		</div>
		<div class="span3">
			<label for="input_key" class="mandatory">'.$wf->labelKey.'</label>
			<input type="text" name="key" id="input_key" class="max mandatory span12" value=""/>
		</div>
		<div class="span5">
			<label for="input_value" class="mandatory">'.$wf->labelValue.'</label>
			<input type="text" name="value" id="input_value" class="max mandatory span12" value=""/>
		</div>
		<div class="span2">
			<label>&nbsp;</label>
			<button type="submit" class="button add btn btn-success">'.$iconAdd.'&nbsp;'.$wf->buttonAdd.'</button>
		</div>
	</div>
</form>';

return '
<div class="row-fluid">
	'.$panelEdit.'
	'.$panelAdd.'
</div>';
