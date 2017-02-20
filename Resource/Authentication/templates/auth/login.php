<?php

$list	= array();
foreach( $backends as $backend ){
	$list[]	= UI_HTML_Tag::create( 'a', $backend->label, array(
		'href'	=> './auth/'.$backend->path.'/login',
		'class'	=> 'btn btn-primary'
	) );
}
$list	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'btn-bar' ) );

return $list;
