<?php

//  --  LOAD STATIC HTML CONTENT FILE  --  //
if( !empty( $path ) ){
	if( $view->hasContentFile( 'html/'.$path.'.html' ) ){
		return $view->loadContentFile( 'html/index.html' );
}
if( $view->hasContentFile( 'html/index.html' ) ){
	return $view->loadContentFile( 'html/index.html' );
}

//  --  OR RETURN TEMPLATE CONTENT --  //
return '
<h2>Hello World!</h2>
It seems you just have installed the (rather empty) <cite>Hydrogen</cite> module <cite>App:Site</cite>.<br/>
To go on, consider to install an application module or start creating HTML files in locale HTML folders.<br/>
';
?>
