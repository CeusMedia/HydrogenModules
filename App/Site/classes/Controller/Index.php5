<?php
class Controller_Index extends CMF_Hydrogen_Controller{
	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL){

		$request		= $this->env->getRequest();

		if( $this->env->getModules()->has( 'Info_Pages' ) ){
			$logic		= new Logic_Page( $this->env );
			$path		= trim( $request->get( 'path' ) );
			$page		= $logic->getPageFromPath( strlen( $path ) ? $path : 'index', TRUE );
			if( $page ){
				switch( $page->type ){
					case 2:
						$this->redirect( strtolower( str_replace( "_", "/", $page->module ) ), 'index' );
						break;
					case 0:
					default:
						$this->addData( 'page', $page );
						break;
				}
			}
			else if( $logic->hasPage( 'index' ) ){
				if( !$request->get( 'path' ) ){
					$request->set( 'path', 'index' );
					$this->redirect( 'index', 'index' );
				}
				else{
					$words	= (object) $this->getWords( 'index', 'info/pages' );
					$this->env->getMessenger()->noteNotice( $words->msgPageNotFound );
					$this->env->getResponse()->setStatus( '404 Not found' );
					$request->set( 'path', 'index' );
					$this->redirect( 'index', 'index' );
				}
			}
	//		else{
	//			$this->env->getMessenger()->noteNotice( "Seite nicht gefunden. Weiterleitung zur Startseite." );
	//			$this->restart();
	//		}
		}
		
	}
}
?>
