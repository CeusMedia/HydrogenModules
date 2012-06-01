<?php

$wf			= (object) $words['index'];

$imgEdit	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => 'http://img.int1a.net/famfamfam/silk/pencil.png', 'alt' => $wf->buttonEdit, 'title' => $wf->buttonEdit ) );
$imgRemove	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => 'http://img.int1a.net/famfamfam/silk/delete.png', 'alt' => $wf->buttonRemove, 'title' => $wf->buttonRemove ) );

$rows	= array();
if( $env->has( 'cache' ) ){
	$cache	= $env->getCache();

#	$module	= $env->getModules()->get( 'Database' );
#	$cache->set( 'timestamp', time() );
#	$cache->set( 'date', date( "r" ) );
	
	foreach( $cache->index() as $key ){
		$value	= unserialize( $cache->get( $key ) );
		$type	= gettype( $value );
		switch( $type ){
			case 'object':
#				$value	= UI_VariableDumper::dump( $value, UI_VariableDumper::MODE_PRINT );
				$value	= 'Instance of class <cite>'.get_class( $value ).'</cite>';
				break;
			default:
				$value	= '<input type="text" value="'.htmlentities( $value ).'" class="max"/>';
		}
		$buttonSave		= UI_HTML_Tag::create( 'button', $imgEdit, array( 'type' => 'button', 'class' => 'button tiny edit' ) );
		$buttonRemove	= UI_HTML_Tag::create( 'button', $imgRemove, array( 'type' => 'button', 'class' => 'button tiny remove' ) );
		$cells	= array(
			UI_HTML_Tag::create( 'td', $key ),
			UI_HTML_Tag::create( 'td', $type ),
			UI_HTML_Tag::create( 'td', $value ),
			UI_HTML_Tag::create( 'td', $buttonSave.$buttonRemove ),
		);
		$rows[]	= UI_HTML_Tag::create( 'tr', $cells, array( "data-key" => $key ) );
	}
}
$columns	= UI_HTML_Elements::ColumnGroup( array( '20%', '5%', '65%', '10%' ) );
$heads		= array( $wf->headKey, $wf->headType, $wf->headValue, $wf->headAction );
$heads		= UI_HTML_Elements::TableHeads( $heads );
$table		= UI_HTML_Tag::create( 'table', $columns.$heads.implode( $rows ), array( 'class' => "list" ) ); 


$panelEdit	= '
	<fieldset>
		<legend class="icon edit">'.$wf->legend.'</legend>
		'.$table.'
	</fieldset>
';



$wf			= (object) $words['add'];
$optType	= UI_HTML_Elements::Options( $words['types'] );
$panelAdd	= '
	<form action="./admin/cache/add" method="post">
		<fieldset>
			<legend class="icon add">'.$wf->legend.'</legend>
			<ul class="input">
				<li>
					<label for="input_key" class="mandatory">'.$wf->labelKey.'</label><br/>
					<input type="text" name="key" id="input_key" class="max mandatory" value=""/>
				</li>
				<li>
					<label for="input_value" class="mandatory">'.$wf->labelValue.'</label><br/>
					<input type="text" name="value" id="input_value" class="max mandatory" value=""/>
				</li>
				<li>
					<label for="input_type">'.$wf->labelType.'</label><br/>
					<select name="type" id="input_type" class="max">'.$optType.'</select>
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::Button( 'add', $wf->buttonAdd, 'button add' ).'
			</div>
		</fieldset>
	</form>
';

return '
<div class="column-right-25">
	'.$panelAdd.'
</div>
<div class="column-left-75">
	'.$panelEdit.'
</div>
<div class="column-clear"></div>
';
?>