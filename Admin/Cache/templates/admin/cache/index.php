<?php

$wf		= (object) $words['index'];
$rows	= array();
if( !$hasCache )
	return '<div class="hint">'.$wf->hintNoCache.'</div>';
	
foreach( $list as $item ){
	$buttonRemove	= '<button type="button" class="btn btn-mini btn-danger btn-cache-remove"><i class="icon-remove icon-white" title="'.$wf->buttonRemove.'"></i>&nbsp;</button>';
	$value	= trim( print_m( $item->value, NULL, NULL, TRUE ) );
	$value	= preg_replace( "/^<br\/>(.*)<br\/>$/s", "\\1", $value );
	$cells			= array(
		UI_HTML_Tag::create( 'td', $item->key ),
		UI_HTML_Tag::create( 'td', '<em>'.$item->type.'</em>' ),
		UI_HTML_Tag::create( 'td', $value ),
		UI_HTML_Tag::create( 'td', $buttonRemove ),
	);
	$rows[]	= UI_HTML_Tag::create( 'tr', $cells, array( "data-key" => $item->key ) );
}

$columns	= UI_HTML_Elements::ColumnGroup( array( '20%', '5%', '65%', '10%' ) );
$heads		= array( $wf->headKey, $wf->headType, $wf->headValue, $wf->headAction );
$heads		= UI_HTML_Elements::TableHeads( $heads );
$table		= UI_HTML_Tag::create( 'table', $columns.$heads.implode( $rows ), array( 'class' => "table table-condensed table-striped" ) ); 

$panelEdit	= '
	<h3>'.$wf->legend.'</h3>
	'.$table;

$wf			= (object) $words['add'];
$optType	= UI_HTML_Elements::Options( $words['types'] );
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
			<button type="submit" class="button add btn btn-success"><i class="icon-ok icon-white"></i>&nbsp;'.$wf->buttonAdd.'</button>
		</div>
	</div>
</form>';

return '
<div class="row-fluid">
	'.$panelEdit.'
	'.$panelAdd.'
</div>
';
?>
