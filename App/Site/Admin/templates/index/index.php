<?php

//  --  LOAD STATIC HTML CONTENT FILE  --  //
if( !empty( $path ) ){
	if( $view->hasContentFile( 'html/'.$path.'.html' ) )
		return $view->loadContentFile( 'html/index.html' );
}
if( $view->hasContentFile( 'html/index.html' ) ){
	return $view->loadContentFile( 'html/index.html' );
}
if( $view->hasContentFile( 'html/index/index.html' ) ){
    return $view->loadContentFile( 'html/index/index.html' );
}


//  --  OR RETURN TEMPLATE CONTENT --  //
return '
<h2>Hello World!</h2>
It seems you just have installed the (rather empty) <cite>Hydrogen</cite> module <cite>App:Site:Admin</cite>.<br/>
You can change this page by create a localized HTML files in "locales/[LANGUAGE]/html/index.html".<br/>
';
?>
