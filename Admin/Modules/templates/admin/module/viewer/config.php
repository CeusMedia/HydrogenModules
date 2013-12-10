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
		$key		= UI_HTML_Tag::create( 'td', $item->key );
		$value		= UI_HTML_Tag::create( 'td', $item->value, array( 'class' => 'config-type-'.$item->type ) );
		$rows[strtolower( $item->key )]	= '<tr>'.$key.$value.'</tr>';
	}
	ksort( $rows );
	$heads	= UI_HTML_Elements::TableHeads( array( $w->headKey, $w->headValue ) );
	$table	= UI_HTML_Tag::create( 'table', $heads.join( $rows ) );
}
return $table;
?>
