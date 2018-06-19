<?php
class Hook_Info_Page/* extends CMF_Hydrogen_Hook*/{

	static public function onAppDispatch( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( $env->getModules()->has( 'Resource_Frontend' ) )										//  frontend resource exists
			if( $env->getConfig()->get( 'module.resource_frontend.path' ) !== './' )				//  this app is a backend
				return;																				//  no (frontend) pages for backend

		$request	= $env->getRequest();
		$path		= $request->get( '__path' );													//  get requested path
		$logic		= new Logic_Page( $env );														//  get page logic instance
		$pagePath	= strlen( trim( $path ) ) ? trim( $path ) : 'index';							//  ensure page path is not empty
		$page		= $logic->getPageFromPath( $pagePath, TRUE );									//  try to get page by called page path

		if( !$page )																				//  no page found for called page path
			return FALSE;																			//  quit hook call and return without result

		if( $page->status < 0 ){																	//  page is deactivated
			if( ( $previewCode = $request->get( 'preview' ) ) ){									//  page has been requested for preview
				if( $previewCode != $page->createdAt.$page->modifiedAt )							//  not a valid preview request (from page management)
					return FALSE;																	//  quit hook call and return without result
			}
		}

		$controller	= new Controller_Info_Page( $env, FALSE );										//  get controller instance
		switch( (int) $page->type ){
			case 0:																					//  page is static
				$request->set( '__redirected', TRUE );												//  note redirection for access check
				$controller->redirect( 'info/page', 'index', array( $pagePath ) );					//  redirect to page controller
				break;
			case 1:																					//  page is node (and has no content)
				$children	= $logic->getChildren( $page->pageId );									//  get direct child pages
				if( $children )																		//  idetified node has children
					if( $children[0]->status > 0 )													//  child page is active (hidden or visible)
						$controller->restart( $page->identifier.'/'.$children[0]->identifier );		//  redirect to child page
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
				$controller->redirect( $controllerName, $action, $page->arguments );				//  redirect to module controller action
				break;
			default:
				throw new RangeException( 'Page type '.$page->type.' is unsupported' );
		}
		return TRUE;																				//  stop ongoing dispatching
	}

	static public function onControllerDetectPath( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$modelPage			= new Model_Page( $env );
		$controllerPages	= $modelPage->getAllByIndices( array(
			'status'		=> array( 0, 1 ),														//  invisible or active, only (no disabled)
			'type'			=> 2,																	//  type 'module', only
			'controller'	=> $data['controllerName'],
		) );
		if( $controllerPages ){
			$pages				= array();
			foreach( $controllerPages as $page ){
				$page->fullpath	= $page->identifier;
				if( $page->parentId ){
					do{
						$parent	= $modelPage->get( $page->parentId );
						$page->fullpath	= $parent->identifier.'/'.$page->fullpath;
					}
					while( $parent->parentId );
				}
				$pages[]	= $page;
			}
			return $pages[0]->fullpath;
		}
		return FALSE;
	}

	static public function onEnvConstructEnd( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		if( !$env->getModules()->has( 'Resource_Authentication' ) )
			return;
		if( file_exists( 'config/pages.json' ) )
			return;
		$acl	= $env->getAcl();
		$model	= new Model_Page( $env );
		$pages	= $model->getAll( array( 'type' => 2 ) );

		$paths	= array( 'public' => array(), 'inside' => array(), 'outside' => array() );
		$paths['public'][] = 'info_page_index';

		foreach( $pages as $page ){
			$path	= strtolower( $page->controller );
			$methods = [];
			$reflection = new ReflectionClass( 'Controller_'.$page->controller );
			foreach( $reflection->getMethods( ReflectionMethod::IS_PUBLIC ) as $method )
				if( !$method->isStatic() && substr( $method->name, 0, 1 ) !== '_' )
					if( $method->class == $reflection->getName() )
						if( array_key_exists( $page->access, $paths ) )
							$paths[$page->access][] = $path.'_'.$method->name;
		}
			$acl->setPublicLinks( $paths['public'], 'append' );
			$acl->setPublicInsideLinks( $paths['inside'], 'append' );
			$acl->setPublicOutsideLinks( $paths['outside'], 'append' );
	}

	static public function onRegisterSitemapLinks( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		try{
			$moduleConfig	= $env->getConfig()->getAll( 'module.info_pages.', TRUE );				//  get configuration of module
			if( $moduleConfig->get( 'sitemap' ) ){													//  sitemap is enabled
				$model		= new Model_Page( $env );												//  get model of pages
				$indices	= array( 'status' => '>0', 'parentId' => 0, 'scope' => 0 );				//  focus on active top pages of main navigation scope
				$orders		= array( 'scope' => 'ASC', 'rank' => 'ASC', 'modifiedAt' => 'DESC' );										//  collect latest changed pages first
				$pages		= $model->getAllByIndices( $indices, $orders );							//  get all active top level pages
				foreach( $pages as $page ){															//  iterate found pages
					if( (int) $page->type === 1 ){													//  page is a junction only (without content)
						$indices	= array(														//  focus on active pages on sub level
							'status'	=> '>0',
							'access'	=> array( 'public', 'outside' ),
							'parentId'	=> $page->pageId
						);
						$subpages	= $model->getAllByIndices( $indices, $orders );					//  get all active sub level pages of top level page
						foreach( $subpages as $subpage ){											//  iterate found pages
							$url		= $env->url.$page->identifier.'/'.$subpage->identifier;		//  build absolute URI of sub level page
							$timestamp	= max( $subpage->createdAt, $subpage->modifiedAt );			//  get timestamp of last action
							$priority	= $subpage->priority;										//  get page priority
							$frequency	= $subpage->changefreq;										//  get page change frequency
							$context->addLink( $url, $timestamp, $priority, $frequency );			//  append URI to sitemap
						}
					}
					else{																			//  page is static of dynamic (using a module)
						$url	= $env->url.$page->identifier;										//  build absolute URI of top level page
						$timestamp	= max( $page->createdAt, $page->modifiedAt );					//  get timestamp of last action
						$priority	= $page->priority;												//  get page priority
						$frequency	= $page->changefreq;											//  get page change frequency
						$context->addLink( $url, $timestamp, $priority, $frequency );				//  append URI to sitemap
					}
				}

				$indices	= array(																//  focus on active top pages of main navigation scope
					'status'	=> '>0',
					'access'	=> array( 'public', 'outside' ),
					'parentId'	=> 0,
					'scope'		=> 1
				);
				$orders		= array( 'modifiedAt' => 'DESC' );										//  collect latest changed pages first
				$pages		= $model->getAllByIndices( $indices, $orders );							//  get all active top level pages
				foreach( $pages as $page ){															//  iterate found pages
					if( (int) $page->type === 1 ){													//  page is a junction only (without content)
						$indices	= array( 'status' => '>0', 'parentId' => $page->pageId );		//  focus on active pages on sub level
						$subpages	= $model->getAllByIndices( $indices, $orders );					//  get all active sub level pages of top level page
						foreach( $subpages as $subpage ){											//  iterate found pages
							$url		= $env->url.$page->identifier.'/'.$subpage->identifier;		//  build absolute URI of sub level page
							$timestamp	= max( $subpage->createdAt, $subpage->modifiedAt );			//  get timestamp of last action
							$priority	= $subpage->priority;										//  get page priority
							$frequency	= $subpage->changefreq;										//  get page change frequency
							$context->addLink( $url, $timestamp, $priority, $frequency );			//  append URI to sitemap
						}
					}
					else{																			//  page is static of dynamic (using a module)
						$url	= $env->url.$page->identifier;										//  build absolute URI of top level page
						$timestamp	= max( $page->createdAt, $page->modifiedAt );					//  get timestamp of last action
						$priority	= $page->priority;												//  get page priority
						$frequency	= $page->changefreq;											//  get page change frequency
						$context->addLink( $url, $timestamp, $priority, $frequency );				//  append URI to sitemap
					}
				}
			}
		}
		catch( Exception $e ){																		//  an exception occured during data collection
			die( $e->getMessage() );																//  display exception message and quit
		}
	}

	static public function onRenderContent( CMF_Hydrogen_Environment $env, $context, $modules, $data ){
		$pattern	= "/^(.*)(\[page:(.+)\])(.*)$/sU";
		$logic		= new Logic_Page( $env );
		while( preg_match( $pattern, $data->content ) ){
			$path	= trim( preg_replace( $pattern, "\\3", $data->content ) );
			$page	= $logic->getPageFromPath( $path, TRUE );
			if( !$page ){
				$data->content	= preg_replace( $pattern, "", $data->content );
				$env->getMessenger()->noteFailure( 'Die eingebundene Seite "'.$path.'" existiert nicht.' );
			}
			else{
				$subcontent		= $page->content;													//  load nested page content
				$subcontent		= preg_replace( "/<h(1|2)>.*<\/h(1|2)>/", "", $subcontent );		//  remove headings above level 3
				$replacement	= "\\1".$subcontent."\\4";											//  insert content of nested page...
				$data->content	= preg_replace( $pattern, $replacement, $data->content );		//  ...into page content
			}
		}
	}


	static public function onRenderSearchResults( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$logic		= new Logic_Page( $env );
		$options	= $env->getConfig()->getAll( 'module.info_pages.', TRUE );
		$words		= $env->getLanguage()->getWords( 'main' );

		foreach( $data->documents as $resultDocument  ){
			if( isset( $resultDocument->facts ) )
				continue;
			$page	= $logic->getPageFromPath( $resultDocument->path );
			if( !$page )
				continue;

			$suffix	= $options->get( 'title.separator' ).$words['main']['title'];
			$title	= preg_replace( '/'.preg_quote( $suffix, '/' ).'$/', '', $resultDocument->title );

			$resultDocument->facts	= (object) array(
				'category'		=> 'Seite:',
				'title'			=> $title,
				'link'			=> $resultDocument->path,
				'image'			=> NULL,
			);
		}
	}
}
?>
