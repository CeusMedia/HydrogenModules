<?php
class Controller_Info_Page extends CMF_Hydrogen_Controller{

	public function index( $id = 'index' ){

		$logic		= new Logic_Page( $this->env );
		$page		= $logic->getPageFromPath( $id, TRUE );
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
		else{
			$words	= (object) $this->getWords( 'index', 'info/pages' );
			$this->env->getMessenger()->noteNotice( $words->msgPageNotFound );
			$this->env->getResponse()->setStatus( '404 Not found' );
//			$request->set( 'path', 'index' );
//			$this->redirect( 'index', 'index' );
		}
	}
}
?>
