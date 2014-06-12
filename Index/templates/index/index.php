<?php
if( $user ){
	if( $view->hasContent( 'index', 'index.inside', 'html/' ) ){
		$content	= $view->loadContent( 'index', 'index.inside',  array( 'user' => $user ) );
		if( $env->getModules()->has( 'UI_Helper_Content' ) )
			$content	= View_Helper_ContentConverter::render( $env, $content );
		return $content;
	}
}

if( $view->hasContent( 'index', 'index', 'html/' ) ){
	$content	= $view->loadContent( 'index', 'index' );
	if( $env->getModules()->has( 'UI_Helper_Content' ) )
		$content	= View_Helper_ContentConverter::render( $env, $content );
	return $content;
}
return '
Hello World!
';
?>
