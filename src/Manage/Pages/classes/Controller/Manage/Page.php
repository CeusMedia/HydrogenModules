<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;
use CeusMedia\Common\FS\File\Collection\Reader as ListFileReader;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Page extends Controller
{
	public static string $moduleId		= 'Manage_Pages';

	protected $model;
	protected $request;
	protected $messenger;
	protected $session;
	protected $words;
	protected Logic_Frontend $frontend;
	protected string $patternIdentifier	= '@[^a-z0-9_/-]@';
	protected string $sessionPrefix		= 'filter_manage_pages_';

	protected string $appFocus			= 'self';
	protected Dictionary $appSession;
	protected array $appLanguages;
	protected $envManaged;
	protected $defaultLanguage;

	public function add( $parentId = 0 )
	{
		$parent	= $parentId ? $this->checkPageId( $parentId ) : NULL;
		if( $this->request->has( 'save' ) ){
			$data	= [];
			foreach( $this->model->getColumns() as $column ){
				if( $this->request->has( 'page_'.$column ) ){
					$value	= $this->request->get( 'page_'.$column );
					if( $column == 'identifier' )
						$value	= preg_replace( $this->patternIdentifier, '', $value );
					$data[$column]	= $value;
				}
			}
			$data['createdAt']	= time();
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

		$page	= (object) [
			'pageId'		=> 0,
			'parentId'		=> $parentId ?: (int) $this->request->get( 'page_parentId' ),
			'type'			=> (int) $this->request->get( 'page_type' ),
			'scope'			=> (int) $this->request->get( 'page_scope' ),
			'status'		=> 0,
			'rank'			=> (int) $this->request->get( 'page_rank' ),
			'identifier'	=> $this->request->get( 'page_identifier' ),
			'title'			=> $this->request->get( 'page_title' ),
			'content'		=> $this->request->get( 'page_content' ),
			'format'		=> $this->request->get( 'page_format' ),
			'controller'	=> $this->request->get( 'page_controller' ),
			'action'		=> $this->request->get( 'page_action' ),
			'access'		=> $this->request->get( 'page_access' ),
			'icon'			=> $this->request->get( 'page_icon' ),
			'template'		=> $this->request->get( 'page_template' ),
			'createdAt'		=> time(),
		];

		$path		= $this->frontend ? $this->frontend->getUrl() : $this->env->url;
		if( $parentId && $parent->type && $parent->type == 1 )
			$path	.= $parent->identifier.'/';

		$moduleIds	= $this->frontend ? $this->frontend->getModules() : array_keys( $this->env->getModules() );

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

	public function copy( $pageId )
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

	public function edit( $pageId, $version = NULL )
	{
		$source			= $this->getData( 'source' );
		$isFromConfig	= $source == 'Config';
		$isFromDatabase	= $source == 'Database';
		$page			= $this->checkPageId( $pageId );
		$scope			= (int) $this->appSession->get( 'scope' );
		$logic			= new Logic_Page( $this->env );

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

			$indices		= array(
				'scope'			=> $scope,
				'parentId'		=> $this->request->get( 'page_parentId' ),
				'pageId'		=> '!= '.$pageId,
				'identifier'	=> $this->request->get( 'page_identifier' )
			);
			if( $this->model->getByIndices( $indices ) ){
				if( $this->request->get( 'page_parentId' ) ){
					$message	= $words->errorIdentifierInParentTaken;
					$identifier	= $this->request->get( 'page_identifier' );
					$this->messenger->noteError( $message, $identifier );
				}
				else{
					$message	= $words->errorIdentifierTaken;
					$identifier	= $this->request->get( 'page_identifier' );
					$this->messenger->noteError( $message, $identifier );
				}
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
						$logic		= Logic_Versions::getInstance( $this->env );					//  start versioning logic
						$versions	= $logic->getAll( 'Info_Pages', $pageId );
						$found		= FALSE;														//  init indicator if current page content is a version
						foreach( $versions as $version )											//  iterate all page versions
							if( $version->content === $page->content )								//  page content is a version
								$found = TRUE;														//  note this
						if( !$found )																//  page content is not a version
							$logic->add( 'Info_Pages', $pageId, $page->content );					//  store current page content as version
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
				$logic->updateFullpath( $pageId );
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
		$versions	= [];
		$this->session->set( $this->sessionPrefix.$this->appFocus.'.scope', $page->scope );
		if( $page->parentId ){
			$parent	= $this->model->get( (int) $page->parentId );
			if( $isFromDatabase && $parent )
				$path	.= $parent->identifier.'/';
		}
		if( $this->env->getModules()->has( 'Resource_Versions' ) ){
			$logic		= Logic_Versions::getInstance( $this->env );
			$orders		= ['version' => 'DESC'];
			$limits		= [0, 10];
			$versions	= $logic->getAll( 'Info_Pages', $pageId, [], $orders, $limits );
			if( !is_null( $version ) ){
				$entry	= $logic->get( 'Info_Pages', $pageId, $version );
				if( $entry )
					$page->content	= $entry->content;
			}
		}
		$editor	= $this->appSession->get( 'editor.'.strtolower( $page->format ) ) ?: current( array_keys( $editors ) );

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
		$this->addData( 'isAccessible', $logic->isAccessible( $page ) );
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

	public function getJsImageList()
	{
		$pathFront	= $this->frontend->getPath();
		$pathImages	= $this->frontend->getPath( 'images' );
		$index	= new RecursiveRegexFileIndex( $pathFront.$pathImages, "/\.jpg$/i" );
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

	public function index()
	{
//$this->messenger->noteNotice( 'App: '.$this->appFocus.' - Filter: '.json_encode( $this->appSession->getAll() ) );
		$this->preparePageTree();
		$this->addData( 'scope', $this->appSession->get( 'scope' ) );
		$this->addData( 'parentId', 0 );
		$script	= 'ModuleManagePages.PageEditor._initSortable();';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	public function remove( $pageId )
	{
		$page		= $this->checkPageId( $pageId );
		$this->model->remove( $pageId );
		$this->messenger->noteSuccess( $this->getWords( 'msg' )['successRemoved'], $page->title );
		$this->restart( NULL, TRUE );
	}


	public function setApp( $app )
	{
		$currentApp	= $this->session->get( $this->sessionPrefix.'app' );
		if( $app !== $currentApp )
			$this->session->set( $this->sessionPrefix.'app', (string) $app );
		$this->restart( NULL, TRUE );
	}

	public function setLanguage( $language )
	{
		$this->session->set( $this->sessionPrefix.$this->appFocus.'.language', (string) $language );
		$this->restart( NULL, TRUE );
	}

	public function setScope( $scope )
	{
		$this->session->set( $this->sessionPrefix.$this->appFocus.'.scope', (int) $scope );
		$this->restart( NULL, TRUE );
	}

	public function setSource( $source )
	{
		$currentSource	= $this->session->get( $this->sessionPrefix.'source' );
		if( $source !== $currentSource )
			$this->session->set( $this->sessionPrefix.$this->appFocus.'.source', (string) $source );
		$this->restart( NULL, TRUE );
	}

	//  --  PROTECTED  --  //

	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->words			= $this->getWords();
		$this->session			= $this->env->getSession();
		$this->frontend			= Logic_Frontend::getInstance( $this->env );

		$this->appSession		= $this->session->getAll( $this->sessionPrefix.$this->appFocus.'.', TRUE );
		$this->envManaged		= $this->env;
		$this->appLanguages		= $this->env->getLanguage()->getLanguages();
//		$this->env->getLog()->log("debug","found languages in env:".print_r($this->env->getLanguage()->getLanguages(),true),$this);
		$this->defaultLanguage	= current( array_values( $this->env->getLanguage()->getLanguages() ) );

		$apps	= [];

		if( realpath( $this->frontend->getPath() ) !== realpath( $this->env->uri ) ){				//  frontend is different from self
			$apps			= [
				'self'		=> 'Administration',
				'frontend'	=> 'Webseite',
			];
			$this->appFocus	= $this->session->get( $this->sessionPrefix.'app', $this->appFocus );
			if( !array_key_exists( $this->appFocus, $apps ) )
				$this->appFocus	= current( array_keys( $apps ) );
//			if( $this->appFocus !== $this->session->get( $this->sessionPrefix.'app' ) )
//				$this->session->remove( $this->sessionPrefix.'language' );

			if( $this->appFocus === 'frontend' ){
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
		if( $this->session->get( $this->sessionPrefix.'app' ) !== $this->appFocus )
			$this->session->set( $this->sessionPrefix.'app', $this->appFocus );

		$managesModules		= $this->envManaged->getModules( TRUE );
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
			$module			= $this->envManaged->getModules( TRUE )->get( 'UI_Navigation' );
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
		switch( $source ){
			case 'Database':
				$this->model	= new Model_Page( $this->envManaged );
				break;
			case 'Config':
				$this->model	= new Model_Config_Page( $this->envManaged );
				break;
			case 'Modules':
				$this->model	= new Model_Module_Page( $this->envManaged );
				break;
		}

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

	protected function checkPageId( string $pageId, bool $strict = FALSE ): object
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

	protected function getFrontendControllers(): array
	{
		$controllers	= [];
		$pathConfig		= $this->envManaged->getConfig()->get( 'path.config' );
		$pathModules	= $this->envManaged->getConfig()->get( 'path.modules' );
		$pathModules	= $pathModules ?: $pathConfig.'modules/';
		foreach( $this->envManaged->getModules()->getAll() as $moduleId => $module ){
			if( empty( $module->files->classes ) )
				continue;
			foreach( $module->files->classes as $moduleFile )
				if( preg_match( "/^Controller/", $moduleFile->file ) ){
					$name	= preg_replace( "/^Controller\/(.+)\.php.?$/", "$1", $moduleFile->file );
					$controllers[]	= str_replace( "/", "_", $name );
				}
		}
		return array_unique( $controllers );
	}

	protected function preparePageTree( ?string $currentPageId = NULL ): void
	{
		$scope		= (int) $this->appSession->get( 'scope' );
		$indices	= [
			'parentId'	=> 0,
			'status'	=> '> -2',
			'scope'		=> $scope,
		];
		$pages		= $this->model->getAllByIndices( $indices, ['rank' => "ASC"] );
		$tree		= [];
		$parentMap	= ['0' => '-'];
		foreach( $pages as $item ){
			$item	= $this->translatePage( $item );
			if( $item->pageId != $currentPageId && $item->type == 1 )
				$parentMap[$item->pageId]	= $item->title;
			$indices		= ['parentId' => $item->pageId];
			$item->subpages	= $this->model->getAllByIndices( $indices, ['rank' => "ASC"] );
			foreach( $item->subpages as $nr => $subitem )
				$subitem	= $this->translatePage( $subitem );
			$tree[]		= $item;
		}
		$this->addData( 'tree', $tree );
		$this->addData( 'parentMap', $parentMap );
	}

	/**
	 *	@param		object		$page
	 *	@return		object
	 */
	protected function translatePage( object $page ): object
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
