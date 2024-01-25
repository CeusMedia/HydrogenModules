<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Page extends Hook
{
	/**
	 *	@return		bool|NULL
	 *	@throws		ReflectionException
	 */
	public function onAppDispatch(): ?bool
	{
		/** @var Web $env */
		$env		= $this->env;
		if( $env->getModules()->has( 'Resource_Frontend' ) )										//  frontend resource exists
			if( $env->getConfig()->get( 'module.resource_frontend.path' ) !== './' )				//  this app is a backend
				return FALSE;																		//  no (frontend) pages for backend

		$request	= $env->getRequest();
		/** @var Logic_Page $logic */
		$logic		= $env->getLogic()->get( 'page' );												//  get page logic instance

		$path		= trim( $request->get( '__path', '' ), '/' );									//  get requested path
		$pagePath	= strlen( $path ) ? $path : 'index';											//  ensure page path is not empty
		$page		= $logic->getPageFromRequest( TRUE, FALSE );
		if( !$page )																				//  no page found for called page path
			return FALSE;																			//  quit hook call and return without result

		if( (int) $page->status === Model_Page::STATUS_DISABLED ){									//  page is deactivated
			$previewCode	= $request->get( 'preview' );											//  get preview code if requested (iE. by page management)
			if( $previewCode != $page->createdAt.$page->modifiedAt )								//  no valid preview code => no bypassing disabled state
				return FALSE;																		//  quit hook call and return without result
		}

		switch( (int) $page->type ){
			case Model_Page::TYPE_COMPONENT:
				break;
			case Model_Page::TYPE_CONTENT:
				$request->set( '__redirected', TRUE );												//  note redirection for access check
				static::redirect( $env, 'info/page', 'index', [$pagePath] );		//  redirect to page controller and quit hook
				return TRUE;
			case Model_Page::TYPE_BRANCH:
				if( !( $children = $logic->getChildren( $page->pageId ) ) )							//  identified branch page has children
					throw new RangeException( 'Page branch '.$page->title.' has no pages' );
				if( (int) $children[0]->status === Model_Page::STATUS_DISABLED )					//  child page is disabled
					throw new RangeException( 'Page branch '.$page->title.' has no active pages' );
				static::restart( $env, $page->identifier.'/'.$children[0]->identifier );			//  redirect to child page and exit hook
				return TRUE;
			case Model_Page::TYPE_MODULE:
				if( !$page->controller )															//  but no module controller has been selected
					throw new RangeException( 'Module page '.$page->title.' has no assigned controller' );
				$controllerName	= strtolower( str_replace( "_", "/", $page->controller ) );			//  get module controller path
				if( substr( $pagePath, 0, strlen( $controllerName ) ) === $controllerName )			//  module has been addressed by page link
					return TRUE;																	//  let the general dispatcher do the job
				$page->arguments	= $page->arguments ?? [];
				$action				= $page->action ?: 'index';										//  default action is 'index'
				if( count( $page->arguments ) > 1 && count( $page->arguments ) !== 1 ){				//  but there are path arguments
					$classMethods	= get_class_methods( 'Controller_'.$page->controller );			//  get methods of module controller class
					if( in_array( $page->arguments[0], $classMethods ) )							//  first argument seems to be a controller method
						$action	= array_shift( $page->arguments );							//  set first argument as action and remove it from argument list
				}
				static::redirect( $env, $controllerName, $action, $page->arguments );		//  redirect to module controller action
				return TRUE;
			default:
				throw new RangeException( 'Page type '.$page->type.' is unsupported' );				//  quit with exception
		}
		return FALSE;																				//  continue ongoing dispatching
	}

	public function onAppGetMasterTemplate()
	{
		$page	= $this->env->getLogic()->get( 'page' )->getPageFromRequest( TRUE, FALSE );
		if( $page ){
			$parents	= $page->parents;
			while( $page->template === 'inherit' && $parents )
				$page	= array_shift( $parents );
			$template		= $page->template;
			$valuesToSkip	= ['', 'default', 'inherit', 'theme'];
			if( !in_array( $template, $valuesToSkip, TRUE ) ){
				$this->payload['filename']	= 'info/page/masters/'.$template;
				return 'info/page/masters/'.$template;
			}
		}
		return NULL;
	}

	public function onControllerDetectPath()
	{
		$modelPage			= new Model_Page( $this->env );
		$controllerPages	= $modelPage->getAllByIndices( array(
			'status'		=> [Model_Page::STATUS_HIDDEN, Model_Page::STATUS_VISIBLE],				//  hidden or visible, only (not disabled)
			'type'			=> Model_Page::TYPE_MODULE,												//  type 'module', only
			'controller'	=> $this->payload['controllerName'],
		) );
		if( $controllerPages ){
			$pages				= [];
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
			$this->payload['fullpath']	= $pages[0]->fullpath;
			return $pages[0]->fullpath;
		}
		return FALSE;
	}

	public function onEnvConstructEnd()
	{
		if( !$this->env->getModules()->has( 'Resource_Authentication' ) )
			return;
		if( file_exists( 'config/pages.json' ) )
			return;
		$acl	= $this->env->getAcl();
		$model	= new Model_Page( $this->env );
		$paths	= [
			'public'	=> ['info_page_index'],
			'inside'	=> [],
			'outside'	=> []
		];
		$pages	= $model->getAll( ['type' => Model_Page::TYPE_MODULE] );						//  get all module based pages
		foreach( $pages as $page ){																	//  iterate pages
			$className	= 'Controller_'.$page->controller;											//  page delivers unprefixed controller class name
			if( !class_exists( $className ) )														//  controller class is not existing
				continue;																			//  skip this page
			$path		= strtolower( $page->controller );											//  derive path from shortened controller class name
			$reflection = new ReflectionClass( $className );										//  reflect controller class
			foreach( $reflection->getMethods( ReflectionMethod::IS_PUBLIC ) as $method ){			//  iterate reflected public methods
				if( $method->isStatic() || substr( $method->name, 0, 1 ) === '_' )					//  static or protected method
					continue;																		//  skip this method
				if( $method->class !== $reflection->getName() )										//  inherited method
					continue;																		//  skip this method
				if( strtolower( substr( $method->name, 0, 4 ) ) === 'ajax' )						//  ajax method
					continue;																		//  skip this method
				if( array_key_exists( $page->access, $paths ) )										//  valid page visibility
					$paths[$page->access][] = $path.'_'.$method->name;								//  append page path to path index
			}
		}
		$acl->setPublicLinks( $paths['public'], 'append' );											//  append collected public paths to ACL
		$acl->setPublicInsideLinks( $paths['inside'], 'append' );									//  append collected inside paths to ACL
		$acl->setPublicOutsideLinks( $paths['outside'], 'append' );									//  append collected outside paths to ACL
	}

	public function onRegisterSitemapLinks()
	{
		try{
			$moduleConfig	= $this->env->getConfig()->getAll( 'module.info_pages.', TRUE );		//  get configuration of module
			if( $moduleConfig->get( 'sitemap' ) ){													//  sitemap is enabled
				$urls		= [];
				$orders		= ['scope' => 'ASC', 'rank' => 'ASC', 'modifiedAt' => 'DESC'];	//  collect latest changed pages first
				for( $scopeId = 0; $scopeId < 10; $scopeId++ ){
					$model		= new Model_Page( $this->env );										//  get model of pages
					$indices	= array(															//  focus on ...
						'status'	=> Model_Page::STATUS_VISIBLE,									//  ... visible pages ...
						'parentId'	=> 0,															//  ... in top level ...
						'scope'		=> $scopeId,													//  ... of scoped navigation
						'access'	=> ['public', 'outside'],										//  ... accessible by everyone
					);
					$pages		= $model->getAllByIndices( $indices, $orders );						//  get all active top level pages
					foreach( $pages as $page ){														//  iterate found pages
						if( (int) $page->type === Model_Page::TYPE_BRANCH ){						//  page is a branch only (without content)
							$indices	= array(													//  focus on ...
								'status'	=> [Model_Page::STATUS_VISIBLE],						//  ... visible pages ...
								'parentId'	=> $page->pageId,										//  ... on sub level
								'access'	=> ['public', 'outside'],								//  ... accessible by everyone
							);
							$subpages	= $model->getAllByIndices( $indices, $orders );				//  get all active sub level pages of top level page
							foreach( $subpages as $subpage ){										//  iterate found pages
								$url		= $this->env->url.$page->identifier.'/'.$subpage->identifier;	//  build absolute URI of sub level page
								if( in_array( $url, $urls ) )
									continue;
								$urls[]		= $url;
								$timestamp	= max( $subpage->createdAt, $subpage->modifiedAt );		//  get timestamp of last action
								$priority	= $subpage->priority;									//  get page priority
								$frequency	= $subpage->changefreq;									//  get page change frequency
								$this->context->addLink( $url, $timestamp, $priority, $frequency );		//  append URI to sitemap
							}
						}
						else{																		//  page is static of dynamic (using a module)
							$url	= $this->env->url.$page->identifier;							//  build absolute URI of top level page
							if( in_array( $url, $urls ) )
								continue;
							$urls[]		= $url;
							$timestamp	= max( $page->createdAt, $page->modifiedAt );				//  get timestamp of last action
							$priority	= $page->priority;											//  get page priority
							$frequency	= $page->changefreq;										//  get page change frequency
							$this->context->addLink( $url, $timestamp, $priority, $frequency );		//  append URI to sitemap
						}
					}
				}
			}
		}
		catch( Exception $e ){																		//  an exception occurred during data collection
			die( $e->getMessage() );																//  display exception message and quit
		}
	}

	/**
	 *	@todo		log errors
	 *	@todo		localize error messages
	 *	@todo		remove old code
	 */
	public function onRenderContent()
	{
		//  OLD CODE
		$this->applyStrategy1();
		//  NEW CODE USING UI:SHORTCODE
		$this->applyStrategy2();
	}


	/**
	 *	OLD CODE
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function applyStrategy1()
	{
		$pattern	= "/^(.*)(\[page:(.+)\])(.*)$/sU";
		$logic		= $this->env->getLogic()->get( 'page' );
		$matches	= [];
		while( preg_match( $pattern, $this->payload['content'], $matches ) ){
			\CeusMedia\HydrogenFramework\Deprecation::getInstance()
				->setVersion( $this->env->getModules()->get( 'Info_Pages' )->version )
				->setErrorVersion( '0.7.7' )
				->setExceptionVersion( '0.9' )
				->message( 'Page inclusion should use shortcode with id or nr attribute (having: page:'.$matches[3].')' );

			$path			= trim( preg_replace( $pattern, "\\3", $this->payload['content'] ) );
			$page			= $logic->getPageFromPath( $path, TRUE );
			$replacement	= '';
			if( !$page )
				$this->env->getMessenger()->noteFailure( 'Die eingebundene Seite "'.$path.'" existiert nicht.' );
			else{
				$content	= $page->content;															//  load nested page content
				$content	= preg_replace( "/<h1>.*<\/h1>/u", '', $content );		//  remove headings @ level 1
				$content	= preg_replace( "/<h2>.*<\/h2>/u", '', $content );		//  remove headings @ level 2
				$replacement	= "\\1".$content."\\4";													//  insert content of nested page...
			}
			$this->payload['content']	= preg_replace( $pattern, $replacement, $this->payload['content'] );	//  ...into page content
		}
	}

	/**
	 *	NEW CODE USING UI:SHORTCODE
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function applyStrategy2()
	{
		$logic = $this->env->getLogic()->get('page');
		if( !$this->env->getModules()->has( 'UI_Shortcode' ) )
			return;
		$processor		= new Logic_Shortcode( $this->env );
		$processor->setContent( $this->payload['content'] );
		$shortCodes		= [
			'page'		=> [
				'nr'		=> 0,
				'id'		=> '',
				'disabled'	=> FALSE,
				'ignore'	=> FALSE,
			]
		];
		$words	= $this->env->getLanguage()->getWords( 'info/pages' );
		$msgs	= (object) $words['hook-dispatch'];
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			while( ( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				try{
					if( $attr['disabled'] || $attr['ignore'] ){										//  appearance is to be disabled
						$processor->removeNext( $shortCode );										//  remove disabled shortcode
						continue;																	//  skip to next appearance
					}
					if( (int) $attr['nr'] ){														//  page is defined by number
						if( !( $page = $logic->getPage( $attr['nr'] ) ) ){							//  no page found by number
							$message	= $msgs->errorInvalidId;									//  get error message
							$this->env->getMessenger()->noteFailure( $message, $attr['nr'] );		//  note failure in UI
							$processor->removeNext( $shortCode );									//  remove erroneous shortcode
							continue;																//  skip to next appearance
						}
						$attr['id']	= $page->identifier;											//  override requested page path
					}
					if( !strlen( ( $pagePath = trim( $attr['id'] ) ) ) ){							//  no page path given
						$processor->removeNext( $shortCode );										//  remove erroneous shortcode
						continue;																	//  skip to next appearance
					}
					if( !( $page = $logic->getComponentFromPath( $pagePath, FALSE ) ) ){			//  no page of type component found by full path
						$message	= $msgs->errorInvalidPath;										//  get error message
						$this->env->getMessenger()->noteFailure( $message, $pagePath );				//  note failure in UI
						$processor->removeNext( $shortCode );										//  remove erroneous shortcode
						continue;																	//  skip to next appearance
					}
					if( (int) $page->type == Model_Page::TYPE_COMPONENT ){
						if( (int) $page->status == Model_Page::STATUS_HIDDEN ){						//  page component is hidden
							$processor->removeNext( $shortCode );									//  remove hidden shortcode
							continue;																//  skip to next appearance
						}
						if( (int) $page->status == Model_Page::STATUS_DISABLED ){					//  page component is disabled
							$processor->removeNext( $shortCode );									//  remove hidden shortcode
							continue;																//  skip to next appearance
						}
					}
					if( (int) $page->status == Model_Page::STATUS_DISABLED ){
						$message	= $msgs->errorPageDisabled;										//  get error message
						$this->env->getMessenger()->noteFailure( $message, $pagePath );				//  note failure in UI
						$processor->removeNext( $shortCode );										//  remove erroneous shortcode
						continue;																	//  skip to next appearance
					}
					if( (int) $page->type === Model_Page::TYPE_BRANCH ){
						$message	= $msgs->errorPageIsBranch;										//  get error message
						$this->env->getMessenger()->noteFailure( $message, $pagePath );				//  note failure in UI
						$processor->removeNext( $shortCode );										//  remove erroneous shortcode
						continue;																	//  skip to next appearance
					}
					$processor->replaceNext(														//  replace next appearance
						$shortCode,																	//  ... of short code
						$page->content																//  ... by page content
					);
				}
				catch( Exception $e ){
					$this->env->getMessenger()->noteFailure( 'Short code failed: '.$e->getMessage() );
					break;
				}
			}
		}
		$this->payload['content']	= $processor->getContent();
	}

	public function onRenderSearchResults()
	{
		$logic		= $this->env->getLogic()->get( 'page' );
		$options	= $this->env->getConfig()->getAll( 'module.info_pages.', TRUE );
		$words		= $this->env->getLanguage()->getWords( 'main' );

		foreach( $this->payload['documents'] as $resultDocumentNr => $resultDocument  ){
			if( isset( $resultDocument->facts ) )
				continue;
			$page	= $logic->getPageFromPath( $resultDocument->path );
			if( !$page )
				continue;

			$suffix	= $options->get( 'title.separator' ).$words['main']['title'];
			$title	= preg_replace( '/'.preg_quote( $suffix, '/' ).'$/', '', $resultDocument->title );

			$this->payload['documents'][$resultDocumentNr]->facts	= (object) [
				'category'		=> 'Seite:',
				'title'			=> $title,
				'link'			=> $resultDocument->path,
				'image'			=> NULL,
			];
		}
	}
}
