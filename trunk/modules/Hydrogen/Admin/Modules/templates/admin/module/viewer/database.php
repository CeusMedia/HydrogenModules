<?php

$count	= 0;
$sql	= '-';
if( $module->sql ){
	$sql	= array();
	foreach( $module->sql as $type => $content ){
		$count++;
		$sql[]	= UI_HTML_Tag::create( 'dt', $type ).UI_HTML_Tag::create( 'dd', UI_HTML_Tag::create( 'xmp', trim( $content ) ) );
	}
	$sql	= UI_HTML_Tag::create( 'dl', join( $sql ) );
}

return $sql.'<div class="clearfix"></div>';
?>