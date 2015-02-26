<?php
class Controller_Info extends CMF_Hydrogen_Controller{

	static public function ___onAppDispatch( $env, $context, $module, $data = array() ){
		$path	= $env->getRequest()->get( '__path' );
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
	
	public function index( $arg1, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL ){
		$this->addData( 'site', join( "/", func_get_args() ) );
	}
}
?>