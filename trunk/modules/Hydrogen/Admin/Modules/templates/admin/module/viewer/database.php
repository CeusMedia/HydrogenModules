<?php

$count	= 0;
$sql	= '-';
if( $module->sql ){
	$sql	= array();
	foreach( $module->sql as $type => $content ){
		if( !strlen( trim( $content ) ) )
			continue;
		$count++;
		$driver		= preg_replace( "/^.*@/", "", $type );
		$versions	= substr_count( $type, ":" ) ? preg_replace( "/^.*:(.+)->(.+)@.*$/", "v\\1 &rArr; v\\2", $type ) : '';
		$type		= preg_replace( "/[:@].*$/", "", $type );
		$type		.= $versions ? '<br/>'.$versions : '';
		
		$type		= ucFirst( $type ).'<br/>DBMS: '.( $driver === '*' ? 'all' : $driver );
		$sql[]		= UI_HTML_Tag::create( 'dt', $type );
		$sql[]		= UI_HTML_Tag::create( 'dd', UI_HTML_Tag::create( 'xmp', trim( $content ) ) );
	}
	$sql	= UI_HTML_Tag::create( 'dl', join( $sql ), array( 'class' => 'database' ) );
}

return $sql.'<div class="clearfix"></div>';
?>