<?php
if( $view->hasContent( 'index', 'index', 'html/' ) ){
	$content	= $view->loadContent( 'index', 'index', 'html/' );
	if( $env->getModules()->has( 'UI_Helper_Content' ) )
		$content	= View_Helper_ContentConverter::render( $env, $content );
	return $content;
}
return '
Hello World!
';
?>
