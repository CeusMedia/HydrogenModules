<?php


//  --  LOAD STATIC HTML CONTENT FILE  --  //
if( $view->hasContentFile( 'html/index.html' ) ){
	xmp( $view->getContentUri( 'html/index.html' ) );
	return $view->loadContentFile( 'html/index.html' );
}

//  --  OR RETURN TEMPLATE CONTENT --  //
return "<h2>Hello World!</h2>";

?>
