<?php
class Controller_Info_Page extends CMF_Hydrogen_Controller{

	static public function ___onAppDispatch( $env, $context, $module, $data = array() ){
		$request	= $env->getRequest();
		$path		= $request->get( '__path' );											//  get requested path
		$logic		= new Logic_Page( $env );														//  get page logic instance
		$pageId		= strlen( trim( $path ) ) ? trim( $path ) : 'index';							//  ensure page ID is not empty
		if( !( $page = $logic->getPageFromPath( $pageId, TRUE ) ) )									//  no page for path found
			return FALSE;																			//  quit hook

		if( $page->status < 0 ){																	//  page is deactivated
			if( $request->get( 'preview' ) != $page->createdAt.$page->modifiedAt )					//  check for page management preview request
				return FALSE;																		//  avoid access
		}

		$controller	= new Controller_Info_Page( $env, FALSE );										//  get controller instance
		if( $page->type == 0 ){																		//  page is static
			$request->set( '__redirected', TRUE );													//  note redirection for access check
			$controller->redirect( 'info/page', 'index', array( $pageId ) );						//  redirect to page controller
		}
		if( $page->type == 2 ){																		//  page is a module
			if( !$page->module )																	//  but no module has been selected
				return FALSE;																		//  avoid access
			$module	= strtolower( str_replace( "_", "/", $page->module ) );							//  get module controller path
			$controller->redirect( $module, 'index' );												//  redirect to module
		}
		return TRUE;																				//  stop ongoing dispatching
	}

	static public function ___onRegisterSitemapLinks( $env, $context, $module, $data ){
		try{
			$moduleConfig	= $env->getConfig()->getAll( 'module.info_pages.', TRUE );				//  get configuration of module
			if( $moduleConfig->get( 'sitemap' ) ){													//  sitemap is enabled
				$model		= new Model_Page( $env );												//  get model of pages
				$indices	= array( 'status' => '>0', 'parentId' => 0, 'scope' => 'main' );		//  focus on active top pages of main navigation scope
				$orders		= array( 'modifiedAt' => 'DESC' );										//  collect latest changed pages first
				$pages		= $model->getAllByIndices( $indices, $orders );							//  get all active top level pages
				foreach( $pages as $page ){															//  iterate found pages
					if( (int) $page->type === 1 ){													//  page is a junction only (without content)
						$indices	= array( 'status' => '>0', 'parentId' => $page->pageId );		//  focus on active pages on sub level
						$subpages	= $model->getAllByIndices( $indices, $orders );					//  get all active sub level pages of top level page
						foreach( $subpages as $subpage ){											//  iterate found pages
							$url		= $env->url.$page->identifier.'/'.$subpage->identifier;		//  build absolute URI of sub level page
							$timestamp	= max( $subpage->createdAt, $subpage->modifiedAt );			//  get timestamp of last action
							$context->addLink( $url, $timestamp );									//  append URI to sitemap
						}
					}
					else{																			//  page is static of dynamic (using a module)
						$url	= $env->url.$page->identifier;										//  build absolute URI of top level page
						$context->addLink( $url, max( $page->createdAt, $page->modifiedAt ) );		//  append URI to sitemap
					}
				}
			}
		}
		catch( Exception $e ){																		//  an exception occured during data collection
			die( $e->getMessage() );																//  display exception message and quit
		}
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
