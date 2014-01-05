<?php
$count	= 0;
$list	= '-';
if( $module->sql ){
	$list	= array();
	foreach( $module->sql as $type => $sql ){
		if( !strlen( trim( $sql->sql ) ) )
			continue;
		$count++;
		$versions	= $sql->event === 'update' ? '<br/>v'.$sql->from.' &rArr; v'.$sql->to : '';
		$label		= ucFirst( $sql->event ).$versions.'<br/>DBMS: '.$sql->type;
		$list[]		= UI_HTML_Tag::create( 'dt', $label );
		$list[]		= UI_HTML_Tag::create( 'dd', UI_HTML_Tag::create( 'xmp', trim( $sql->sql ) ) );
	}
	$list	= UI_HTML_Tag::create( 'dl', join( $list ), array( 'class' => 'database' ) );
}
return $list.'<div class="clearfix"></div>';
?>