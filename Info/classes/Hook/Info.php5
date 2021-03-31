<?php
class Hook_Info extends CMF_Hydrogen_Hook
{
	public static function onAppDispatch( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$path	= $env->getRequest()->get( '__path' );
		if( !preg_match( "/^info/", $path ) )
			return;

		$path	= preg_replace( "/^info\//", "", $path );
		$view	= new View_Info( $env );
		if( $view->hasContentFile( 'html/info/'.$path.".html" ) ){
			$controller	= new Controller_Info( $env, FALSE );
			$controller->redirect( 'info', 'index', array( $path ) );
			return TRUE;
		}
		else if( $env->getModules()->has( 'UI_Markdown' ) ){
			$fileKey	= 'html/info/'.$path.".md";
			if( $view->hasContentFile( $fileKey ) ){
				$controller	= new Controller_Info( $env, FALSE );
				$controller->redirect( 'info', 'index', array( $path ) );
				return TRUE;
			}
		}
	}
}
