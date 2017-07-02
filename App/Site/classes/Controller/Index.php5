<?php
class Controller_Index extends CMF_Hydrogen_Controller{

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL){
		$request		= $this->env->getRequest();
		$language		= $this->env->getLanguage()->getLanguage();
		$pathLocales	= $this->env->getConfig()->get( 'path.locales' );
		$pathByArgs		= $pathLocales.$language.'/html/index/'.join( "/", func_get_args() ).'.html';
		$pathByFrom		= $request->get( 'from' );

		if( $pathByFrom ){
			$this->restart( $pathByFrom );
		}
		else if( $arg1 && file_exists( $pathByArgs ) ){
			$this->addData( 'path', $pathByArgs );
		}
		else{
			$path	= $request->get( '__path' );
			if(  !in_array( $path, array( '', 'index', 'index/index' ) ) ){
				$words	= (object) $this->getWords( 'index', 'main' );
				$this->env->getMessenger()->noteNotice( $words->msgPageNotFound );
				$this->env->getResponse()->setStatus( 404 );
			}
		}

		$this->addData( 'isInside', $this->env->getSession()->has( 'userId' ) );
	}
}
?>
