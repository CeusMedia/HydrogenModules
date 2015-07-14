<?php
/*
$list	= array();
foreach( $config as $key => $value ){
	$list[]	= UI_HTML_Tag::create( 'li', '<b>'.$key.': </b>'.$value );
}
$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'not-unstyled' ) );
return $words['index']['heading'].'
'.$list.'
';
*/

//print_m( $config );

$iconLock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-lock', 'title' => 'protected' ) );
$iconUnlock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-unlock', 'title' => 'unprotected' ) );
$iconUser		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-user', 'title' => 'configurable by user' ) );

$rows	= array();
foreach( $config as $moduleId => $module ){
	if( !$module->config )
		continue;
	$rows[]	= new UI_HTML_Tag( 'tr', new UI_HTML_Tag( 'th', $module->title, array( 'colspan' => 5 ) ) );
	foreach( $module->config as $item ){

		if( is_bool( $item->value ) )
			$item->value	= $item->value ? '<em>TRUE</em>' : '<em>FALSE</em>';
		$value	= new UI_HTML_Tag( 'span', $item->value, array(
			'class'		=> 'item-value',
			'data-id'	=> 'input-'.$moduleId.'-'.$item->key,
		) );
		if( $item->values ){
			$values		= array_combine( $item->values, $item->values );
			$options	= UI_HTML_Elements::Options( $values, $item->value );
			$input		= new UI_HTML_Tag( 'select', $options, array(
				'name'	=> $moduleId.'|'.$item->key,
				'class'	=> 'span12',
			) );
		}
		else if( $item->type === "boolean" ){
			$values		= array_combine( array( 'yes', 'no' ), array( 'yes', 'no' ) );
			$options	= UI_HTML_Elements::Options( $values, $item->value );
			$input		= new UI_HTML_Tag( 'select', $options, array(
				'name'	=> $moduleId.'|'.$item->key,
				'id'	=> 'input-'.$moduleId.'-'.$item->key,
				'class'	=> 'span12',
			) );
		}
		else{
			$input		= new UI_HTML_Tag( 'input', NULL, array(
				'type'	=> 'text',
				'name'	=> $moduleId.'|'.$item->key,
				'class'	=> 'span12',
			) );
		}

		$protection	= $iconUnlock;
		if( $item->protected === "user" )
			$protection	= $iconUser;
		if( $item->protected === "yes" )
			$protection	= $iconLock;

		$key	= $item->mandatory ? '<b>'.$item->key.'</b>' : $item->key;

		$rows[]	= new UI_HTML_Tag( 'tr', array(
			new UI_HTML_Tag( 'td', $protection ),
			new UI_HTML_Tag( 'td', $item->type ),
			new UI_HTML_Tag( 'td', $key ),
			new UI_HTML_Tag( 'td', $value ),
			new UI_HTML_Tag( 'td', $item->title ),
		) );
	}
}
$cols	= UI_HTML_Elements::ColumnGroup( "30px", "60px" );
$tbody	= new UI_HTML_Tag( 'tbody', $rows );
$table	= new UI_HTML_Tag( 'table', $cols.$tbody, array( 'class' => 'table' ) );

return '<h3>'.$words['index']['heading'].'</h3>
'.$table.'';
