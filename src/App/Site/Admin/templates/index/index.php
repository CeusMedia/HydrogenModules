<?php

//  --  LOAD STATIC HTML CONTENT FILE ...

//  --  ... BY REQUESTED PATH  --  //
if( !empty( $path ) ){
	if( $view->hasContentFile( 'html/'.$path.'.html' ) )
		return $view->loadContentFile( 'html/'.$path.'.html' );
}

//  --  ... OR DEFAULT INDEX  --  //
if( $isInside ){
	if( $view->hasContentFile( 'html/index/index.inside.html' ) )
		if( $content = $view->loadContentFile( 'html/index/index.inside.html' ) )
			return $content;
}
if( $view->hasContentFile( 'html/index/index.html' ) )
	if( $content = $view->loadContentFile( 'html/index/index.html' ) )
		return $content;


//  --  ... OR RETURN PLACEHOLDER CONTENT --  //
return '
<h2>Hello World!</h2>
<p>
	It seems you just have installed the (rather empty) <cite>Hydrogen</cite> module <cite>App:Site:Admin</cite>.<br/>
	To go on, consider to install an application module or start creating HTML files in locale HTML folders.<br/>
</p>';
