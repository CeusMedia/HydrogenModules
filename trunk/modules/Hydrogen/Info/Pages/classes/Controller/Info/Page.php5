<?php
class Controller_Info_Page extends CMF_Hydrogen_Controller{

	static public function ___onAppDispatch( $env, $context, $module, $data = array() ){
		$path		= $env->getRequest()->get( '__path' );											//  get requested path
		$logic		= new Logic_Page( $env );														//  get page logic instance
		$pageId		= strlen( trim( $path ) ) ? trim( $path ) : 'index';							//  ensure page ID is not empty
		if( !( $page = $logic->getPageFromPath( $pageId, TRUE ) ) )									//  no page for path found
			return FALSE;																			//  quit hook

		$controller	= new Controller_Info_Page( $env, FALSE );										//  get controller instance
		if( $page->type == 0 ){																		//  page is static
			$env->getRequest()->set( '__redirected', TRUE );										//  note redirection for access check
			$controller->redirect( 'info/page', 'index', array( $pageId ) );						//  redirect to page controller
		}
		if( $page->type == 2 ){																		//  page is a module
			$module	= strtolower( str_replace( "_", "/", $page->module ) );							//  get module controller path
			$controller->redirect( $module, 'index' );												//  redirect to module
		}
		return TRUE;																				//  stop ongoing dispatching
	}

	public function index( $pageId = 'index' ){
		$directAccess	= $this->env->getConfig()->get( 'module.info_page.direct' );				//  get right to directly access page controller
		$isRedirected	= $this->env->getRequest()->get( '__redirected' );							//  check if page controller has been redirected to
		$accessGranted	= $directAccess === "allowed" || $isRedirected;								//

		$logic		= new Logic_Page( $this->env );													//  get page logic instance
		$pageId		= strlen( trim( $pageId ) ) ? trim( $pageId ) : 'index';						//  ensure page ID is not empty
		$page		= $logic->getPageFromPath( $pageId, TRUE );										//  try to find page for page ID
		if( $accessGranted && $page ){																//  access allowed and valid page ID
			$this->addData( 'page', $page );														//  provide page object to view
		}
		else{																						//  otherwise
			$this->redirect();																		//  redirect to index controller
/*			$words	= (object) $this->getWords( 'index', 'info/pages' );
			$words	= (object) $this->getWords( 'index', 'main' );
			$this->env->getMessenger()->noteNotice( $words->msgPageNotFound );
			$this->env->getResponse()->setStatus( 404 );*/
		}
	}
}
?>
