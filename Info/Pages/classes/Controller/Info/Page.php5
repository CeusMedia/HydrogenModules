<?php
class Controller_Info_Page extends CMF_Hydrogen_Controller{

	public function index( $pageId = 'index' ){
		$directAccess	= $this->env->getConfig()->get( 'module.info_page.direct' );				//  get right to directly access page controller
		$isRedirected	= $this->env->getRequest()->get( '__redirected' );							//  check if page controller has been redirected to
		$accessGranted	= $directAccess === "allowed" || $isRedirected;								//

		$logic		= new Logic_Page( $this->env );													//  get page logic instance
		$pageId		= strlen( trim( $pageId ) ) ? trim( $pageId ) : 'index';						//  ensure page ID is not empty
		$page		= $logic->getPageFromPath( $pageId, TRUE );										//  try to find page for page ID

		if( !$logic->isAccessible( $page ) )
			throw new RuntimeException( 'Access denied', 403 );

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
