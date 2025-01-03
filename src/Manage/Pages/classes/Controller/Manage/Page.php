<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;
use CeusMedia\Common\FS\File\Collection\Reader as ListFileReader;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Exception as EnvironmentException;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Page extends Controller
{
	public static string $moduleId		= 'Manage_Pages';

	protected Model_Page|Model_Config_Page|Model_Module_Page $model;
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected PartitionSession $session;
	protected Logic_Frontend $frontend;
	protected array $words;
	protected string $patternIdentifier	= '@[^a-z0-9_/-]@';
	protected string $sessionPrefix		= 'filter_manage_pages_';

	protected string $appFocus			= 'self';
	protected Dictionary $appSession;
	protected array $appLanguages;
	protected Environment $envManaged;
	protected string $defaultLanguage;
//	protected bool $isRemoteFrontend	= FALSE;

	/**
	 *	@param		int|string		$parentId
	 *	@return		void
	 *	@throws		Environment\Exception
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add( int|string $parentId = 0 ): void
	{
		$columns	= $this->model->getColumns();
		$parent		= $parentId ? $this->checkPageId( $parentId ) : NULL;
		if( $this->request->has( 'save' ) ){
			$data	= [];
			foreach( $columns as $column ){
				if( $this->request->has( 'page_'.$column ) ){
					$value	= $this->request->get( 'page_'.$column );
					if( 'identifier' === $column )
						$value	= preg_replace( $this->patternIdentifier, '', $value );
					$data[$column]	= $value;
				}
			}
			$data['fullpath']	= '';
			unset( $data['pageId'] );

			$indices		= ['parentId' => $parentId, 'identifier' => $data['identifier']];
			if( $this->model->getByIndices( $indices ) )
				$this->messenger->noteError( 'Identifier "'.$data['identifier'].'" already taken' );
			else{
				$pageId		= $this->model->add( $data );
				$logic		= new Logic_Page( $this->env );
				$logic->updateFullpath( $pageId );
				$this->env->getMessenger()->noteSuccess( 'Neue Seite "'.$data['title'].'" angelegt.' );
				$this->restart( 'edit/'.$pageId, TRUE );
			}
		}

		$data	= $this->request->getAll( 'page_', TRUE );
		$page	= Entity_Page::fromArray( [
			'pageId'		=> 0,
			'parentId'		=> $parentId ?: (int) $data->get( 'parentId' ),
			'type'			=> (int) $data->get( 'type', 0 ),
			'scope'			=> (int) $data->get( 'scope', 0 ),
			'rank'			=> (int) $data->get( 'rank', 0 ),
			'identifier'	=> $data->get( 'identifier' ),
			'title'			=> $data->get( 'title', '' ),
			'content'		=> $data->get( 'content', '' ),
			'format'		=> $data->get( 'format' ),
			'controller'	=> $data->get( 'controller', '' ),
			'action'		=> $data->get( 'action', '' ),
			'access'		=> $data->get( 'access' ),
			'icon'			=> $data->get( 'icon', '' ),
			'template'		=> $data->get( 'template' ),
		] );

		if( 'self' !== $this->appFocus ){
			$path		= $this->frontend->getUrl();
			$moduleIds	= $this->frontend->getModules();
		}
		else{
			$path		= $this->env->url;
			$moduleIds	= array_keys( $this->env->getModules()->getAll() );
		}
		if( $parentId && isset( $parent->type ) && Model_Page::TYPE_BRANCH === (int) $parent->type )
			$path	.= $parent->identifier.'/';

		$this->addData( 'path', $path );
		$this->addData( 'page', $page );
		$this->addData( 'parentId', $parentId );
		$this->addData( 'parent', $parent );
		$this->addData( 'scope', $this->appSession->get( 'scope' ) );
		$this->addData( 'modules', $moduleIds );
		$this->addData( 'controllers', $this->getFrontendControllers() );
		$this->preparePageTree();
		$this->collectMasterTemplates();
		$script	= '
ModuleManagePages.PageEditor.frontendUri = "'.$path.'";
ModuleManagePages.PageEditor.editor = "'.$this->appSession->get( 'editor.'.strtolower( $page->format ) ).'";
//ModuleManagePages.PageEditor.editors = '.json_encode( array_keys( $this->getWords( 'editors' ) ) ).';
ModuleManagePages.PageEditor.init();
';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	/**
	 *	@param		int|string		$pageId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function copy( int|string $pageId ): void
	{
		if( !$pageId )
			throw new OutOfRangeException( 'No page ID given' );
		$page	= $this->model->get( $pageId );
		if( !$page )
			throw new OutOfRangeException( 'Invalid page ID given' );
		foreach( $page as $key => $value )
			$this->request->set( 'page_'.$key, $value );
		$this->redirect( 'manage/page', 'add' );
	}

	/**
	 *	@param		int|string		$pageId
	 *	@param		string|NULL		$version
	 *	@return		void
	 *	@throws		Environment\Exception
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $pageId, ?string $version = NULL ): void
	{
		$source			= $this->getData( 'source' );
		$isFromConfig	= $source == 'Config';
		$isFromDatabase	= $source == 'Database';
		$page			= $this->checkPageId( $pageId );
		$scope			= (int) $this->appSession->get( 'scope' );
		$logicPage		= new Logic_Page( $this->env );

//		$logic		= Logic_Versions::getInstance( $this->env );

		$defaultEditor	= $this->moduleConfig->get( 'editor'.'.'.strtolower( $page->format ) );
		$currentEditor	= $this->session->get( $this->sessionPrefix.$this->appFocus.'.editor' );

		$editors	= [];
		$helper		= new View_Helper_Manage_Page_ContentEditor( $this->env );
		if( NULL !== $defaultEditor ){
			$helper->setDefaultEditor( $defaultEditor );
			$helper->setCurrentEditor( $currentEditor ?? $defaultEditor );
		}
		$helper->setFormat( $page->format );
		foreach( $this->getWords( 'editor-types' ) as $typeKey => $typeTemplate ){
			$helper->setType( $typeKey );
			$helper->setLabelTemplate( $typeTemplate );
			foreach( $helper->getEditors() as $editorKey => $editorLabel )
				$editors[$editorKey]	= $editorLabel;
		}
		$editors['source']	= 'Quellcode';

		if( !$this->appSession->get( 'editor' ) ){
			$this->session->set( $this->sessionPrefix.$this->appFocus.'.editor', $helper->getBestEditor() );
			$this->appSession->get( 'editor', $defaultEditor );
		}

		if( $this->request->has( 'save' ) ){
			$words	= (object) $this->getWords( 'msg' );

			if( $this->request->has( 'page_identifier' ) ){
				$identifier	= $this->request->get( 'page_identifier' );
				$identifier	= preg_replace( $this->patternIdentifier, '', $identifier );
				$this->request->set( 'page_identifier', $identifier );
			}

			$indices		= [
				'scope'			=> $scope,
				'parentId'		=> $this->request->get( 'page_parentId' ),
				'pageId'		=> '!= '.$pageId,
				'identifier'	=> $this->request->get( 'page_identifier' )
			];
			if( $this->model->getByIndices( $indices ) ){
				if( $this->request->get( 'page_parentId' ) )
					$message	= $words->errorIdentifierInParentTaken;
				else
					$message	= $words->errorIdentifierTaken;
				$identifier	= $this->request->get( 'page_identifier' );
				$this->messenger->noteError( $message, $identifier );
			}
			else{
//				if( $this->env->getModules()->has( 'Resource_Localization' ) ){							//  localization module is installed
				if( class_exists( 'Logic_Localization' ) ){							//  localization module is installed
					$localization	= new Logic_Localization( $this->env );
					$localization->setLanguage( $this->appSession->get( 'language' ) );
					$idTitle	= 'page.'.$page->identifier.'-title';
					$idContent	= 'page.'.$page->identifier.'-content';
					$title		= $this->request->get( 'page_title' );
					$content	= $this->request->get( 'page_content' );
//					print_m( $this->request->getAll() );die;
					if( $title && $localization->translate( $idTitle, NULL, $title ) )
						$this->request->remove( 'page_title' );
					if( $content && $localization->translate( $idContent, NULL, $content ) )
						$this->request->remove( 'page_content' );
				}
				else if( $this->env->getModules()->has( 'Resource_Versions' ) ){							//  versioning module is installed
					$contentNew	= $this->request->get( 'page_content' );
					if( $page->content !== $contentNew ){											//  new content differs from page content
						$logicVersion		= Logic_Versions::getInstance( $this->env );					//  start versioning logic
						$versions	= $logicVersion->getAll( 'Info_Pages', $pageId );
						$found		= FALSE;														//  init indicator if current page content is a version
						foreach( $versions as $_version )											//  iterate all page versions
							if( $_version->content === $page->content )								//  page content is a version
								$found = TRUE;														//  note this
						if( !$found )																//  page content is not a version
							$logicVersion->add( 'Info_Pages', $pageId, $page->content );					//  store current page content as version
					}
				}

				$data		= [];
				foreach( $this->model->getColumns() as $column )
					if( $this->request->has( 'page_'.$column ) )
						$data[$column]	= $this->request->get( 'page_'.$column );
				if( $scope != $page->scope )														//  switched scope
					$data['parentId']	= 0;														//  clear parent page
				$data['modifiedAt']	= time();
				unset( $data['pageId'] );
				$this->model->edit( $pageId, $data, FALSE );
				$logicPage->updateFullpath( $pageId );
				$this->env->getMessenger()->noteSuccess( $words->successEdited, $data['title'] );
				$this->restart( 'edit/'.$pageId, TRUE );
			}
		}

		$pages	= [];
		$visiblePages	= $this->model->getAllByIndices(
			array( 'status'	=> Model_Page::STATUS_VISIBLE ),
			array( 'title' => "ASC" )
		);
		foreach( $visiblePages as $item ){
			if( $isFromDatabase && $item->parentId ){
				$parent	= $this->model->get( $item->parentId );
				if( $parent && $parent->parentId ){
					$grand	= $this->model->get( $parent->parentId );
					$parent->identifier	= $grand->identifier.'/'.$parent->identifier;
				}
				$item->identifier	= $parent->identifier.'#'.$item->identifier;
			}
/*			if( class_exists( 'Logic_Localization' ) ){							//  localization module is installed
				$localization	= new Logic_Localization( $this->env );
				$id	= 'page.'.$item->identifier.'-title';
				$item->title	= $localization->translate( $id, $item->title );
			}*/
			$pages[]	= $item;
		}

		$path		= $this->envManaged->getBaseUrl();
		$this->session->set( $this->sessionPrefix.$this->appFocus.'.scope', $page->scope );
		if( $page->parentId ){
			$parent	= $this->model->get( (int) $page->parentId );
			if( $isFromDatabase && $parent )
				$path	.= $parent->identifier.'/';
		}
		$versions	= $this->getPageVersions( $page, $version );
		$editor		= $this->appSession->get( 'editor.'.strtolower( $page->format ) ) ?: current( array_keys( $editors ) );

		$this->addData( 'current', $pageId );
		$this->addData( 'pageId', $pageId );
		$this->addData( 'pages', $pages );
		$this->addData( 'page', $page );
		$this->addData( 'path', $path );
		$this->addData( 'version', $version );
		$this->addData( 'versions', $versions );
		$this->addData( 'pageUrl', $path.$page->identifier );
		$this->addData( 'pagePreviewUrl', $path.$page->identifier.'?preview='.$page->createdAt.$page->modifiedAt );
		$this->addData( 'tab', $this->appSession->get( 'tab' ) );
		$this->addData( 'scope', $this->appSession->get( 'scope' ) );
		$this->addData( 'source', $this->appSession->get( 'source' ) );
		$this->addData( 'editor', $editor );
		$this->addData( 'editors', $editors );
		$this->addData( 'isAccessible', $logicPage->isAccessible( $page ) );
		$this->addData( 'modules', $this->envManaged->getModules() );
		$this->addData( 'controllers', $this->getFrontendControllers() );
		$this->preparePageTree( $pageId );
		$this->collectMasterTemplates();


//$this->messenger->noteNotice(print_m($this->session->getAll( $this->sessionPrefix ), NULL, NULL, TRUE));

//		$helper	= new View_Helper_TinyMceResourceLister( $this->env );
		$script	= '
ModuleManagePages.PageEditor.frontendUri = "'.$this->envManaged->getBaseUrl().'";
ModuleManagePages.PageEditor.pageId = "'.$page->pageId.'";
ModuleManagePages.PageEditor.pageIdentifier = "'.$page->identifier.'";
ModuleManagePages.PageEditor.parentPageId = "'.$page->parentId.'";
ModuleManagePages.PageEditor.editor = "'.$editor.'";
ModuleManagePages.PageEditor.editors = '.json_encode( array_keys( $editors ) ).';
ModuleManagePages.PageEditor.format = "'.$page->format.'";
ModuleManagePages.PageEditor.init();
';
		$this->env->getPage()->js->addScriptOnReady( $script );

		/*  --  META: TAGS  --  */
		$appHasMetaModule	= FALSE;
		if( !$this->envManaged->getModules()->has( 'UI_MetaTags' ) ){
//			$this->env->getMessenger()->noteError( 'Das Modul "UI:MetaTags" muss in der Zielinstanz installiert sein, ist es aber nicht.' );
		}
		else{
			$appHasMetaModule	= TRUE;
			$configMetaTags	= $this->envManaged->getConfig()->getAll( 'module.ui_metatags.default.' );
			if( trim( $configMetaTags['keywords'] ) ){
				$possibleKeywordsFile	= $this->envManaged->uri.$configMetaTags['keywords'];
				if( file_exists( $possibleKeywordsFile ) ){
					$list	= preg_split( '/\r?\n/', trim( FileReader::load( $possibleKeywordsFile ) ) );
					foreach( $list as $nr => $item )
						$list[$nr]	= trim( $item );
					natcasesort( $list );
					$configMetaTags['keywords']	= join( ', ', $list );
				}
			}
			$this->addData( 'meta', $configMetaTags );
		}
		$this->addData( 'appHasMetaModule', $appHasMetaModule );

		/*  --  META: KEYWORD BLACKLIST  --  */
		$blacklist		= [];																	//  prepare empty blacklist
		$blacklistFile	= 'config/terms.blacklist.txt';												//  @todo make configurable
		if( file_exists( $blacklistFile ) )															//  blacklist file is existing
			$blacklist	= ListFileReader::read( $blacklistFile );								//  read blacklist
		natcasesort( $blacklist );
		$this->addData( 'metaBlacklist', $blacklist );
	}

	public function getJsImageList(): never
	{
		$pathFront	= $this->frontend->getPath();
		$pathImages	= $this->frontend->getPath( 'images' );
		$index	= new RecursiveRegexFileIndex( $pathFront.$pathImages, "/\.jpg$/i" );
		$list	= [];
		foreach( $index as $item ){
			$parts	= explode( "/", $item->getPathname() );
			$file	= array_pop( $parts );
			$path	= implode( ' / ', array_slice( $parts , 1 ) );
			$label	= $path ? $path.': '.$file : $file;
			$uri	= substr( $item->getPathname(), strlen( $pathFront ) );
			$list[$item->getPathname()]	= '["'.$label.'", "'.$uri.'"]';
		}
		ksort( $list );
		$list	= 'var tinyMCEImageList = new Array('.join( ',', $list ).');';
		header( "Content-type: text/javascript" );
		print( $list );
		exit;
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index(): void
	{
//$this->messenger->noteNotice( 'App: '.$this->appFocus.' - Filter: '.json_encode( $this->appSession->getAll() ) );
		$this->preparePageTree();
		$this->addData( 'scope', $this->appSession->get( 'scope' ) );
		$this->addData( 'parentId', 0 );
		$script	= 'ModuleManagePages.PageEditor._initSortable();';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	/**
	 *	@param		int|string		$pageId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $pageId ): void
	{
		$page		= $this->checkPageId( $pageId );
		$this->model->remove( $pageId );
		$this->messenger->noteSuccess( $this->getWords( 'msg' )['successRemoved'], $page->title );
		$this->restart( NULL, TRUE );
	}


	public function setApp( string $app ): void
	{
		$currentApp	= $this->session->get( $this->sessionPrefix.'app' );
		if( $app !== $currentApp ){
			$this->session->set( $this->sessionPrefix.'app', $app );
			$this->session->remove( $this->sessionPrefix.'language' );
		}
		$this->restart( NULL, TRUE );
	}

	public function setLanguage( string $language ): void
	{
		$this->session->set( $this->sessionPrefix.$this->appFocus.'.language', $language );
		$this->restart( NULL, TRUE );
	}

	public function setScope( int $scope ): void
	{
		$this->session->set( $this->sessionPrefix.$this->appFocus.'.scope', $scope );
		$this->restart( NULL, TRUE );
	}

	public function setSource( string $source ): void
	{
		$currentSource	= $this->session->get( $this->sessionPrefix.'source' );
		if( $source !== $currentSource )
			$this->session->set( $this->sessionPrefix.$this->appFocus.'.source', $source );
		$this->restart( NULL, TRUE );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->words			= $this->getWords();
		$this->session			= $this->env->getSession();
		$this->frontend			= Logic_Frontend::getInstance( $this->env );

		$apps	= $this->detectManagedApp();
		$source	= $this->detectSource();

//		$this->env->getLog()->log("debug","default language during init: ".print_r($this->defaultLanguage,true),$this);
		if( $this->defaultLanguage )
			if( !$this->appSession->get( 'language' ) )
				$this->setLanguage( $this->defaultLanguage );

		$this->addData( 'apps', $apps );
		$this->addData( 'app', $this->appFocus );
		$this->addData( 'source', $source );
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'languages', $this->appLanguages );
		$this->addData( 'language', $this->appSession->get( 'language' ) );
		$this->addData( 'useAuth', $this->envManaged->hasModule( 'Resource_Authentication' ) );
	}

	/**
	 *	@return		array|string[]
	 *	@throws		EnvironmentException
	 */
	protected function detectManagedApp(): array
	{
		$this->appFocus			= $this->session->get( $this->sessionPrefix.'app', $this->appFocus );
		$this->appSession		= $this->session->getAll( $this->sessionPrefix.$this->appFocus.'.', TRUE );
		$this->envManaged		= $this->env;
		$this->appLanguages		= $this->env->getLanguage()->getLanguages();
//		$this->env->getLog()->log("debug","found languages in env:".print_r($this->env->getLanguage()->getLanguages(),true),$this);
		$this->defaultLanguage	= current( array_values( $this->env->getLanguage()->getLanguages() ) );

		if( !$this->env->getModules()->has( 'Resource_Frontend' ) )
			return [];
		$frontendConfig	= $this->env->getModules()->get( 'Resource_Frontend' )->getConfigAsDictionary();
		if( in_array( $frontendConfig->get( 'path' ), ['', './'] ) )
			return [];

//		$this->isRemoteFrontend	= realpath( $this->frontend->getPath() ) !== realpath( $this->env->uri );
		$apps			= [
			'self'		=> 'Administration',
			'frontend'	=> 'Webseite',
		];

		if( 'self' !== $this->appFocus ){				//  frontend is different from self
			if( !array_key_exists( $this->appFocus, $apps ) ){
				$this->appFocus	= current( array_keys( $apps ) );
				$this->setApp( current( array_keys( $apps ) ) );
			}

			if( 'frontend' === $this->appFocus ){
				if( !$this->envManaged->hasModule( 'Resource_Pages' ) ){
					$this->messenger->noteFailure( 'No support for pages available in frontend environment. Access denied.' );
					$this->session->set( $this->sessionPrefix.'app', 'self' );
					$this->restart();
				}
				$this->envManaged	= $this->frontend->getEnv();
				$this->appSession	= $this->session->getAll( $this->sessionPrefix.$this->appFocus.'.', TRUE );
				$this->appLanguages	= $this->frontend->getLanguages();
				//			$source	= $this->envManaged->getModules( TRUE )->get( 'UI_Navigation' )->config['menu.source']->value;
				//			$source	= $this->frontend->getModuleConfigValue( 'UI_Navigation', 'menu.source' );
				$this->defaultLanguage	= $this->frontend->getDefaultLanguage();
			}
		}
		return $apps;
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function detectSource(): string
	{
		$managesModules		= $this->envManaged->getModules();
		$possibleSources	= [];
		if( $managesModules->has( 'Resource_Pages' ) )
			$possibleSources[]	= 'Database';
		if( file_exists( $this->envManaged->uri.'config/pages.json' ) )
			$possibleSources[]	= 'Config';
		$possibleSources[]	= 'Modules';
		if( $possibleSources !== $this->appSession->get( 'sources' ) )
			$this->appSession->set( 'sources', $possibleSources );
		$this->addData( 'sources', $possibleSources );

		$defaultSource	= reset( $possibleSources );
		if( $managesModules->has( 'UI_Navigation' ) ){
			$module			= $this->envManaged->getModules()->get( 'UI_Navigation' );
			$defaultSource	= $module->config['menu.source']->value;
			$this->addData( 'sources', [$defaultSource] );
		}
		$source		= $this->appSession->get( 'source', $defaultSource );
		if( !in_array( $source, $possibleSources ) )
			$source	= $defaultSource;
		if( $source !== $this->appSession->get( 'source' ) )
			$this->appSession->set( 'source', $source );
		$this->addData( 'source', $source );

		//  connect to model of source
		$this->model	= match( $source ){
			'Database'	=> new Model_Page($this->envManaged),
			'Config'	=> new Model_Config_Page($this->envManaged),
			'Modules'	=> new Model_Module_Page($this->envManaged),
			default		=> throw new RangeException('Unsupported source: '.$source ),
		};
		return $source;
	}

	/**
	 *	@param		int|string		$pageId
	 *	@param		bool			$strict
	 *	@return		object
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkPageId( int|string $pageId, bool $strict = FALSE ): object
	{
		if( !$pageId ){
			if( $strict )
				throw new OutOfRangeException( 'No page ID given' );
			$this->messenger->noteError( $this->getWords( 'msg' )['errorMissingPageId'] );
			$this->restart( NULL, TRUE );
		}
		$page	= $this->model->get( $pageId );
		if( !$page ){
			if( $strict )
				throw new OutOfRangeException( 'Invalid page ID given' );
			$this->messenger->noteError( $this->getWords( 'msg' )['errorInvalidPageId'] );
			$this->restart( NULL, TRUE );
		}
		return $this->translatePage( $page );
	}

	/**
	 *	@return		array
	 */
	protected function collectMasterTemplates(): array
	{
		$masterTemplates		= $this->getWords( 'templates' );
		$pathTemplates			= $this->envManaged->getConfig()->get( 'path.templates' );
		$pathMasterTemplates	= $pathTemplates.'info/page/masters/';
		if( is_dir( $pathMasterTemplates ) ){
			$list	= RecursiveFolderLister::getFileList( $pathMasterTemplates, '/\.php$/' );
			foreach( $list as $item ){
				$path	= substr( $item->getPathname(), strlen( $pathMasterTemplates ) );
				$masterTemplates[$path]	= 'Page Master: '.$path;
			}
		}
		$this->addData( 'masterTemplates', $masterTemplates );
		return $masterTemplates;
	}

	/**
	 *	@return		array
	 */
	protected function getFrontendControllers(): array
	{
		$controllers	= [];
//		$pathConfig		= $this->envManaged->getConfig()->get( 'path.config' );
//		$pathModules	= $this->envManaged->getConfig()->get( 'path.modules' );
//		$pathModules	= $pathModules ?: $pathConfig.'modules/';
		foreach( $this->envManaged->getModules()->getAll() as $module ){
			if( empty( $module->files->classes ) )
				continue;
			foreach( $module->files->classes as $moduleFile )
				if( str_starts_with( $moduleFile->file, 'Controller' ) ){
					$name	= preg_replace( "/^Controller\/(.+)\.php.?$/", "$1", $moduleFile->file );
					$controllers[]	= str_replace( "/", "_", $name );
				}
		}
		return array_unique( $controllers );
	}

	/**
	 *	@param		object		$page
	 *	@param		int|NULL	$version
	 *	@return		array
	 *	@throws		ReflectionException
	 */
	protected function getPageVersions( object $page, ?int $version = NULL ): array
	{
		$versions	= [];
		if( $this->env->getModules()->has( 'Resource_Versions' ) ){
			$logic		= Logic_Versions::getInstance( $this->env );
			$orders		= ['version' => 'DESC'];
			$limits		= [0, 10];
			$versions	= $logic->getAll( 'Info_Pages', $page->pageId, [], $orders, $limits );
			if( NULL !== $version ){
				$entry	= $logic->get( 'Info_Pages', $page->pageId, $version );
				if( $entry )
					$page->content	= $entry->content;
			}
		}
		return $versions;
	}

	/**
	 *	@param		int|string|NULL		$currentPageId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function preparePageTree( int|string|NULL $currentPageId = NULL ): void
	{
		$scope		= (int) $this->appSession->get( 'scope' );
		$indices	= [
			'parentId'	=> 0,
			'status'	=> '> -2',
			'scope'		=> $scope,
		];
		/** @var Entity_Page[] $pages */
		$pages		= $this->model->getAllByIndices( $indices, ['rank' => "ASC"] );
		$tree		= [];
		$parentMap	= ['0' => '-'];
		foreach( $pages as $item ){
			$item	= $this->translatePage( $item );
			if( $item->pageId != $currentPageId && $item->type == 1 )
				$parentMap[$item->pageId]	= $item->title;
			$indices		= ['parentId' => $item->pageId];
			$item->pages	= $this->model->getAllByIndices( $indices, ['rank' => "ASC"] );
			foreach( $item->pages as $nr => $subitem )
				$item->pages[$nr]	= $this->translatePage( $subitem );
			$tree[]		= $item;
		}
		$this->addData( 'tree', $tree );
		$this->addData( 'parentMap', $parentMap );
	}

	/**
	 *	@param		Entity_Page		$page
	 *	@return		Entity_Page
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function translatePage( Entity_Page $page ): Entity_Page
	{
		if( !class_exists( 'Logic_Localization' ) )							//  localization module is not installed
			return $page;
//		$this->env->getLog()->log("debug","env dump: ".print_r($this->env,true),$this);
		$localization	= new Logic_Localization( $this->env );
/*		$this->env->getLog()->log("debug","trying to set language from appSession to localization object during translatePage: ".print_r($this->appSession,true),$this);
		$this->env->getLog()->log("debug","trying to set language from appSession to localization object during translatePage: ".print_r($this->appSession->get( 'language' ),true),$this);
		$this->env->getLog()->log("debug",print_r($this->session->getAll(),true));
*/
		$localization->setLanguage( $this->appSession->get( 'language' ) );
//		remark( $localization->getLanguage() );
		$id	= 'page.'.$page->identifier.'-title';
//		remark( $id );
		$page->title	= $localization->translate( $id, $page->title );
		$id	= 'page.'.$page->identifier.'-content';
		$page->content	= $localization->translate( $id, $page->content );
		return $page;
	}
}
