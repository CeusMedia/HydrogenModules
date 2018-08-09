<?php
class Controller_Info_Page extends CMF_Hydrogen_Controller{

	public function dispatchPage( $page ){
		$request	= $this->env->getRequest();
		$logic		= new Logic_Page( $this->env );														//  get page logic instance
		$path		= $request->get( '__path' );													//  get requested path
		$pagePath	= strlen( trim( $path ) ) ? trim( $path ) : 'index';							//  ensure page path is not empty

		switch( (int) $page->type ){
			case 0:																					//  page is static
				$request->set( '__redirected', TRUE );												//  note redirection for access check
				$this->redirect( 'info/page', 'index', array( $pagePath ) );					//  redirect to page controller
				break;
			case 1:																					//  page is node (and has no content)
				$children	= $logic->getChildren( $page->pageId );									//  get direct child pages
				if( $children )																		//  idetified node has children
					if( $children[0]->status > 0 )													//  child page is active (hidden or visible)
						$this->restart( $page->identifier.'/'.$children[0]->identifier );		//  redirect to child page
				return FALSE;																		//  otherwise quit hook call and return without result
				break;
			case 2:																					//  page is a module
				if( !$page->controller )															//  but no module controller has been selected
					return FALSE;																	//  quit hook call and return without result
				$controllerName	= strtolower( str_replace( "_", "/", $page->controller ) );			//  get module controller path
				if( substr( $pagePath, 0, strlen( $controllerName ) ) === $controllerName )			//  module has been addresses by page link
					return TRUE;																	//  nothing to do here
				$action	= $page->action ? $page->action : 'index';									//  default action is 'index'
				if( $page->arguments ){																//  but there are path arguments
					$classMethods	= get_class_methods( 'Controller_'.$page->controller );			//  get methods of module controller class
					if( in_array( $page->arguments[0], $classMethods ) ){							//  first argument seems to be a controller method
						$action	= $page->arguments[0];												//  set first argument as action
						array_shift( $page->arguments );											//  remove first argument from argument list
					}
				}
				$this->redirect( $controllerName, $action, $page->arguments );				//  redirect to module controller action
				break;
			default:
				throw new RangeException( 'Page type '.$page->type.' is unsupported' );
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

		if( !$logic->isAccessible( $page ) )
			throw new RuntimeException( 'Access denied', 403 );

		if( $accessGranted && $page ){																//  access allowed and valid page ID
			$this->addData( 'page', $page );														//  provide page object to view
		}
		else{																						//  otherwise
			$this->restart( NULL );																	//  redirect to index controller
/*			$words	= (object) $this->getWords( 'index', 'info/pages' );
			$words	= (object) $this->getWords( 'index', 'main' );
			$this->env->getMessenger()->noteNotice( $words->msgPageNotFound );
			$this->env->getResponse()->setStatus( 404 );*/
		}
	}
}
?>
