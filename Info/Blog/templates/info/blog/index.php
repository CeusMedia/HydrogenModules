<?php

$list	= array();
foreach( $posts as $post ){
	$list[]	= UI_HTML_Tag::create( 'div', $title.$abstract.$info );
}

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/blog/' ) );

return $textIndexTop.$list.$textIndexBottom;
