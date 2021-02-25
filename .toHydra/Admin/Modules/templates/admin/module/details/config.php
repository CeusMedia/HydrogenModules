<?php

$w	= (object) $words['tab-config'];

$count	= 0;
$table	= '';
if( $module->config ){
	$rows	= array();
	foreach( $module->config as $item ){
		$count++;
		if( $item->type == 'boolean' )
			$item->value	= $words['boolean-values'][( $item->value ? 'yes' : 'no' )];
		if( preg_match( "/password/", $item->key ) )
			$item->value	= '<em class="muted">hidden</em>';
//		else if( $item->protected === "yes" )
//			$item->value	= '<em class="muted">protected</em>';
		$label	= View_Helper_Module::renderModuleConfigLabel( $module, $item );
		$key		= UI_HTML_Tag::create( 'td', $label );
		$value		= UI_HTML_Tag::create( 'td', $item->value, array( 'class' => 'config-type-'.$item->type ) );
		$rows[strtolower( $item->key )]	= '<tr>'.$key.$value.'</tr>';
	}
	ksort( $rows );
	$tbody		= UI_HTML_Tag::create( 'tbody', join( $rows ) );
	$heads		= UI_HTML_Elements::TableHeads( array( $w->headKey, $w->headValue ) );
	$thead		= UI_HTML_Tag::create( 'thead', $heads );
	$colgroup	= UI_HTML_Elements::ColumnGroup( '25%', '75%' );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'striped' ) );
}
return $table;
?>
