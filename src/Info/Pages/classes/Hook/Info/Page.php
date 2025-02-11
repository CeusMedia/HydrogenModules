<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Environment\Exception as EnvironmentException;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Page extends Hook
{
	/**
	 *	@return		bool|NULL
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onAppDispatch(): ?bool
	{
		/** @var Web $env */
		$env		= $this->env;
		if( $env->getModules()->has( 'Resource_Frontend' ) )								//  frontend resource exists
			if( $env->getConfig()->get( 'module.resource_frontend.path' ) !== './' )			//  this app is a backend
				return FALSE;																		//  no (frontend) pages for backend

		$request	= $env->getRequest();
		/** @var Logic_Page $logic */
		$logic		= $env->getLogic()->get( 'page' );												//  get page logic instance

		$path		= trim( $request->get( '__path', '' ), '/' );				//  get requested path
		$pagePath	= strlen( $path ) ? $path : 'index';											//  ensure page path is not empty
		$page		= $logic->getPageFromRequest( TRUE, FALSE );
		if( NULL === $page )																		//  no page found for called page path
			return FALSE;																			//  quit hook call and return without result

		if( Model_Page::STATUS_DISABLED === $page->status ){										//  page is deactivated
			$previewCode	= $request->get( 'preview' );										//  get preview code if requested (iE. by page management)
			if( $previewCode != $page->createdAt.$page->modifiedAt )								//  no valid preview code => no bypassing disabled state
				return FALSE;																		//  quit hook call and return without result
		}

		switch( $page->type ){
			case Model_Page::TYPE_COMPONENT:
				break;
			case Model_Page::TYPE_CONTENT:
				$request->set( '__redirected', TRUE );												//  note redirection for access check
				static::redirect( $env, 'info/page', 'index', [$pagePath] );			//  redirect to page controller and quit hook
				return TRUE;
			case Model_Page::TYPE_BRANCH:
				if( !( $children = $logic->getChildren( $page->pageId ) ) )							//  identified branch page has children
					throw new RangeException( 'Page branch '.$page->title.' has no pages' );
				if( Model_Page::STATUS_DISABLED === (int) $children[0]->status )					//  child page is disabled
					throw new RangeException( 'Page branch '.$page->title.' has no active pages' );
				static::restart( $env, $page->identifier.'/'.$children[0]->identifier );		//  redirect to child page and exit hook
				return TRUE;
			case Model_Page::TYPE_MODULE:
				if( !$page->controller )															//  but no module controller has been selected
					throw new RangeException( 'Module page '.$page->title.' has no assigned controller' );
				$controllerName	= strtolower( str_replace( "_", "/", $page->controller ) );			//  get module controller path
				if( str_starts_with( $pagePath, $controllerName ) )									//  module has been addressed by page link
					return TRUE;																	//  let the general dispatcher do the job
				$page->arguments	= $page->arguments ?? [];
				$action				= $page->action ?: 'index';										//  default action is 'index'
				if( count( $page->arguments ) > 1 ){												//  but there are path arguments
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

	/**
	 *	@return		string|NULL
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onAppGetMasterTemplate(): ?string
	{
		/** @var Logic_Page $logic */
		$logic	= $this->env->getLogic()->get( 'page' );
		$page	= $logic->getPageFromRequest( TRUE, FALSE );
		if( $page ){
			$parents	= $page->parents;
			while( 'inherit' === $page->template && $parents )
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

	/**
	 *	@return		FALSE
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onControllerDetectPath()
	{
		$modelPage			= new Model_Page( $this->env );
		$controllerPages	= $modelPage->getAllByIndices( [
			'status'		=> [Model_Page::STATUS_HIDDEN, Model_Page::STATUS_VISIBLE],				//  hidden or visible, only (not disabled)
			'type'			=> Model_Page::TYPE_MODULE,												//  type 'module', only
			'controller'	=> $this->payload['controllerName'],
		] );
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onEnvConstructEnd(): void
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
		$pages	= $model->getAll( ['type' => Model_Page::TYPE_MODULE] );							//  get all module based pages
		foreach( $pages as $page ){																	//  iterate pages
			if( str_starts_with( strtolower( $page->controller ), 'ajax' ) )						//  ajax controller
				continue;																			//  skip this controller
			$className	= 'Controller_'.$page->controller;											//  page delivers unprefixed controller class name
			if( !class_exists( $className ) )														//  controller class is not existing
				continue;																			//  skip this page
			$path		= strtolower( $page->controller );											//  derive path from shortened controller class name
			$reflection = new ReflectionClass( $className );										//  reflect controller class
			foreach( $reflection->getMethods( ReflectionMethod::IS_PUBLIC ) as $method ){		//  iterate reflected public methods
				if( $method->isStatic() || str_starts_with( $method->name, '_' ) )					//  static or protected method
					continue;																		//  skip this method
				if( $reflection->getName() !== $method->class )										//  inherited method
					continue;																		//  skip this method
				if( str_starts_with( strtolower( $method->name ), 'ajax' ) )						//  ajax method
					continue;																		//  skip this method
				if( array_key_exists( $page->access, $paths ) )										//  valid page visibility
					$paths[$page->access][] = $path.'_'.$method->name;								//  append page path to path index
			}
		}
		$acl->setPublicLinks( $paths['public'], 'append' );									//  append collected public paths to ACL
		$acl->setPublicInsideLinks( $paths['inside'], 'append' );								//  append collected inside paths to ACL
		$acl->setPublicOutsideLinks( $paths['outside'], 'append' );							//  append collected outside paths to ACL
	}

	public function onRegisterSitemapLinks(): void
	{
		try{
			$moduleConfig	= $this->env->getConfig()->getAll( 'module.info_pages.', TRUE );		//  get configuration of module
			if( $moduleConfig->get( 'sitemap' ) ){													//  sitemap is enabled
				$urls		= [];
				$orders		= ['scope' => 'ASC', 'rank' => 'ASC', 'modifiedAt' => 'DESC'];	//  collect latest changed pages first
				for( $scopeId = 0; $scopeId < 10; $scopeId++ ){
					$model		= new Model_Page( $this->env );										//  get model of pages
					$indices	= [																	//  focus on ...
						'status'	=> Model_Page::STATUS_VISIBLE,									//  ... visible pages ...
						'parentId'	=> 0,															//  ... in top level ...
						'scope'		=> $scopeId,													//  ... of scoped navigation
						'access'	=> ['public', 'outside'],										//  ... accessible by everyone
					];
					$pages		= $model->getAllByIndices( $indices, $orders );						//  get all active top level pages
					foreach( $pages as $page ){														//  iterate found pages
						if( (int) $page->type === Model_Page::TYPE_BRANCH ){						//  page is a branch only (without content)
							$indices	= [															//  focus on ...
								'status'	=> [Model_Page::STATUS_VISIBLE],						//  ... visible pages ...
								'parentId'	=> $page->pageId,										//  ... on sublevel
								'access'	=> ['public', 'outside'],								//  ... accessible by everyone
							];
							$subpages	= $model->getAllByIndices( $indices, $orders );				//  get all active sublevel pages of top level page
							foreach( $subpages as $subpage ){										//  iterate found pages
								$url		= $this->env->url.$page->identifier.'/'.$subpage->identifier;	//  build absolute URI of sublevel page
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
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onRenderContent(): void
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
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function applyStrategy1(): void
	{
		$pattern	= "/^(.*)(\[page:(.+)\])(.*)$/sU";
		/** @var Logic_Page $logic */
		$logic		= $this->env->getLogic()->get( 'page' );
		$matches	= [];
		while( preg_match( $pattern, $this->payload['content'], $matches ) ){
			\CeusMedia\HydrogenFramework\Deprecation::getInstance()
				->setVersion( $this->env->getModules()->get( 'Info_Pages' )->version->current )
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
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function applyStrategy2(): void
	{
		/** @var Logic_Page $logic */
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
		$words		= $this->env->getLanguage()->getWords( 'info/pages' );
		$messages	= (object) $words['hook-dispatch'];
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
							$message	= $messages->errorInvalidId;								//  get error message
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
						$message	= $messages->errorInvalidPath;									//  get error message
						$this->env->getMessenger()->noteFailure( $message, $pagePath );				//  note failure in UI
						$processor->removeNext( $shortCode );										//  remove erroneous shortcode
						continue;																	//  skip to next appearance
					}
					if( Model_Page::TYPE_COMPONENT === $page->type ){
						if( Model_Page::STATUS_HIDDEN === $page->status ){							//  page component is hidden
							$processor->removeNext( $shortCode );									//  remove hidden shortcode
							continue;																//  skip to next appearance
						}
						if( Model_Page::STATUS_DISABLED === $page->status ){						//  page component is disabled
							$processor->removeNext( $shortCode );									//  remove hidden shortcode
							continue;																//  skip to next appearance
						}
					}
					if( Model_Page::STATUS_DISABLED === $page->status ){
						$message	= $messages->errorPageDisabled;									//  get error message
						$this->env->getMessenger()->noteFailure( $message, $pagePath );				//  note failure in UI
						$processor->removeNext( $shortCode );										//  remove erroneous shortcode
						continue;																	//  skip to next appearance
					}
					if( Model_Page::TYPE_BRANCH === $page->type ){
						$message	= $messages->errorPageIsBranch;									//  get error message
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onRenderSearchResults(): void
	{
		/** @var Logic_Page $logic */
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
