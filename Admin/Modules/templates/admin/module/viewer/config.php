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
		$label	= $item->key;
		if( strlen( trim( $title = htmlentities( $item->title, ENT_QUOTES, 'UTF-8' ) ) ) )
			$label	= UI_HTML_Tag::create( 'acronym', $item->key, array( 'title' => $title ) );
		$key		= UI_HTML_Tag::create( 'td', $label );
		$value		= UI_HTML_Tag::create( 'td', $item->value, array( 'class' => 'config-type-'.$item->type ) );
		$rows[strtolower( $item->key )]	= '<tr>'.$key.$value.'</tr>';
	}
	ksort( $rows );
	$heads	= UI_HTML_Elements::TableHeads( array( $w->headKey, $w->headValue ) );
	$table	= UI_HTML_Tag::create( 'table', $heads.join( $rows ), array( 'class' => 'striped' ) );
}
return $table;
?>
