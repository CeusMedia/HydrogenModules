<?php
class Controller_Manage_Page extends CMF_Hydrogen_Controller{

	protected $model;
	protected $request;
	protected $messenger;
	protected $session;
	protected $words;
	protected $frontend;
	protected $patternIdentifier	= '@[^a-z0-9_/-]@';

	protected function __onInit(){
		$config				= $this->env->getConfig()->getAll( 'module.manage_pages.', TRUE );

		$this->model		= new Model_Page( $this->env );
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->words		= $this->getWords();

		$this->session		= $this->env->getSession();
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->languages	= $this->frontend->getLanguages();
		$this->defaultLanguage	= $this->frontend->getDefaultLanguage();
		if( !$this->session->get( 'module.manage_pages.language' ) ){
			if( $this->frontend->getDefaultLanguage() )
				$this->setLanguage( $this->frontend->getDefaultLanguage() );
		}

		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'languages', $this->languages );
		$this->addData( 'language', $this->session->get( 'module.manage_pages.language' ) );
		$this->addData( 'useAuth', $this->frontend->hasModule( 'Resource_Authentication' ) );

		if( !$this->frontend->hasModule( 'Info_Pages' ) ){
			$this->messenger->noteFailure( 'No support for pages available in frontend environment. Access denied.' );
			$this->restart();
		}
	}

	static public function ___onTinyMCE_getLinkList( CMF_Hydrogen_Environment $env, $context, $module, $arguments = array() ){
		$frontend		= Logic_Frontend::getInstance( $env );
		if( !$frontend->hasModule( 'Info_Pages' ) )
			return;

		$words		= $env->getLanguage()->getWords( 'manage/page' );
		$model		= new Model_Page( $env );
		$list		= array();
		foreach( $model->getAllByIndex( 'status', 1, array( 'rank' => 'ASC' ) ) as $nr => $page ){
			$page->level		= 0;
			if( $page->parentId ){
				$parent = $model->get( $page->parentId );
				$page->level		= 1;
				if( $parent->parentId ){
					$grand  = $model->get( $parent->parentId );
					$parent->identifier = $grand->identifier.'/'.$parent->identifier;
					$parent->title		= $grand->title.' / '.$parent->title;
					$page->level		= 2;
				}
				$page->identifier   = $parent->identifier.'/'.$page->identifier;
				$page->title		= $parent->title.' / '.$page->title;
			}
			$list[$page->title.$nr]	= (object) array(
				'title'	=> $page->title,
				'value'	=> './'.$page->identifier,
			);
		}
		if( $list ){
			ksort( $list );
			$list	= array( (object) array(
				'title'	=> $words['tinyMCE']['prefix'],
				'menu'	=> array_values( $list ),
			) );
	//		$context->list	= array_merge( $context->list, array_values( $list ) );
			$context->list	= array_merge( $context->list, $list );
		}
	}

	public function add( $parentId = 0 ){
		$parent	= $parentId ? $this->checkPageId( $parentId ) : NULL;
		if( $this->request->has( 'save' ) ){
			$data	= array();
			foreach( $this->model->getColumns() as $column ){
				if( $this->request->has( 'page_'.$column ) ){
					$value	= $this->request->get( 'page_'.$column );
					if( $column == 'identifier' )
						$value	= preg_replace( $this->patternIdentifier, '', $value );
					$data[$column]	= $value;
				}
			}
			$data['createdAt']	= time();
			unset( $data['pageId'] );

			$indices		= array( 'parentId' => 0, 'identifier' => $data['identifier'] );
			if( $this->model->getByIndices( $indices ) )
				$this->messenger->noteError( 'Identifier "'.$data['identifier'].'" already taken' );
			else{
				$pageId		= $this->model->add( $data );
				$this->env->getMessenger()->noteSuccess( 'Neue Seite "'.$data['title'].'" angelegt.' );
				$this->restart( 'edit/'.$pageId, TRUE );
			}
		}

		$page	= (object) array(
			'pageId'		=> 0,
			'parentId'		=> $parentId ? $parentId : (int) $this->request->get( 'page_parentId' ),
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
			'createdAt'		=> time(),
		);

		$path		= $this->frontend->getUri();
		if( $parentId && $parent->type && $parent->type == 1 )
			$path	.= $parent->identifier.'/';


		$this->addData( 'path', $path );
		$this->addData( 'page', $page );
		$this->addData( 'parentId', $parentId );
		$this->addData( 'parent', $parent );
		$this->addData( 'scope', $this->session->get( 'module.manage_pages.scope' ) );
		$this->addData( 'modules', $this->frontend->getModules() );
		$this->addData( 'controllers', $this->getFrontendControllers() );
		$this->preparePageTree();
	}

	public function ajaxOrderPages(){
		$pageIds	= $this->request->get( 'pageIds' );
		foreach( $pageIds as $nr => $pageId )
			$this->model->edit( $pageId, array( 'rank' => $nr + 1 ) );
		header( "Content-Type: application/json" );
		print( json_encode( TRUE ) );
		exit;
	}

	public function ajaxSaveContent(){
		$content	= $this->request->get( 'content' );
		$pageId		= $this->request->get( 'pageId' );
		$result		= array( 'status' => FALSE );
		try{
			/*	@todo remove this old string-based solution soon */
			if( preg_match( '/[a-z]/', $pageId ) ){
				if( $page = $this->model->getByIndex( 'identifier', $pageId ) ){
					$this->model->edit( $page->pageId, array(
						'content'		=> $content,
						'modifiedAt'	=> time(),
					), FALSE );
					$result	= array( 'pageId' => $pageId, 'content' => $content );
					$result	= array( 'status' => TRUE );
				}
			}
			else if( $pageId ){
				if( $page = $this->model->get( (int) $pageId ) ){
					$this->model->edit( $page->pageId, array(
						'content'		=> $content,
						'modifiedAt'	=> time(),
					), FALSE );
					$result	= array( 'status' => TRUE );
				}
			}
		}
		catch( Exception $e ){
			$result['error']	= $e->getMessage();
		}
		header( "Content-Type: application/json" );
		print( json_encode( $result ) );
		exit;
	}

	public function ajaxSetEditor( $editor ){
		$this->env->getSession()->set( 'module.manage_pages.editor', $editor );
		exit;
	}

	public function ajaxSetTab( $tabKey ){
		$this->env->getSession()->set( 'module.manage_pages.tab', $tabKey );
		exit;
	}

	public function ajaxBlacklistSuggestedKeywords(){
		try{
			$pageId		= $this->request->get( 'pageId' );						//  get page ID from request
			$page		= $this->checkPageId( $pageId );						//  check if page ID is valid
			$blacklist	= 'config/terms.blacklist.txt';
			$words		= trim( $this->request->get( 'words' ) );				//  get string of whitespace concatenated words from request
			if( $words ){														//  atleast one word is given
				if( !file_exists( $blacklist ) )								//  blacklist file is not existing, yet
					touch( $blacklist );										//  create empty list file
				$editor	= new \FS_File_List_Editor( $blacklist );				//  start list editor
				foreach( preg_split( '/\s*(,|\s)\s*/', $words ) as $word )		//  iterate trimmed words
					$editor->add( trim( $word ) );								//  add word to list and save
			}
			print( json_encode( array(											//  respond to client
				'status'	=> 'data',											//  that this operation has been successful
				'data'		=> is_array( $words ) ? count( $words ) : 0,		//  provider number of added words
			) ) );
		}
		catch( Exception $e ){													//  an exception has been thrown
			print( json_encode( array(											//  respond to client
				'status'	=> 'exception',										//  that this is an error
				'data'		=> $e->getMessage(),								//  provider exception message as error message
			) ) );
		}
		exit;																	//  quit anyways since this is an AJAX request
	}

	public function ajaxSuggestKeywords(){
		$pageId	= $this->request->get( 'pageId' );
		$page	= $this->checkPageId( $pageId );
		$html	= Alg_Text_Filter::stripComments( $page->content );
		$html	= Alg_Text_Filter::stripScripts( $html );
		$html	= Alg_Text_Filter::stripStyles( $html );
		$html	= Alg_Text_Filter::stripEventAttributes( $html );
		//$html	= Alg_Text_Filter::stripTags( $html );
//		$html	= htmlspecialchars_decode( $html );
		$html	= preg_replace( "@<[\/\!]*?[^<>]*?>@si", " ", $html );
		$html	= str_replace( "&nbsp;", " ", $html );
		$blacklist	= 'config/terms.blacklist.txt';
		if( file_exists( $blacklist ) )
			Alg_Text_TermExtractor::loadBlacklist( $blacklist );
		$terms	= Alg_Text_TermExtractor::getTerms( $html );
		$list	= array();
		foreach( $terms as $term => $count )
			if( preg_match( '/^[A-Z]/', $term ) )
				if( preg_match( '/[A-Z]$/i', $term ) )
					$list[]	= htmlspecialchars_decode( $term );
		print( json_encode( array(
			'status'	=> 'data',
			'data'		=> $list
		) ) );
		exit;
	}

	protected function checkPageId( $pageId, $strict = FALSE ){
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

	public function copy( $pageId ){
		if( !$pageId )
			throw new OutOfRangeException( 'No page ID given' );
		$page	= $this->model->get( $pageId );
		if( !$page )
			throw new OutOfRangeException( 'Invalid page ID given' );
		foreach( $page as $key => $value )
			$this->request->set( 'page_'.$key, $value );
		$this->redirect( 'manage/page', 'add' );
	}

	public function edit( $pageId, $version = NULL ){
		$page		= $this->checkPageId( $pageId );
		$session	= $this->env->getSession();
		$model		= new Model_Page( $this->env );
		$scope		= (int) $session->get( 'module.manage_pages.scope' );

//		$logic		= Logic_Versions::getInstance( $this->env );

		if( !$session->get( 'module.manage_pages.editor' ) )
			$session->set( 'module.manage_pages.editor', $this->env->getConfig()->get( 'module.manage_pages.editor' ) );

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
				'pageId'		=> '!='.$pageId,
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
					$localization->setLanguage( $this->session->get( 'module.manage_pages.language' ) );
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


				$data		= array();
				foreach( $this->model->getColumns() as $column )
					if( $this->request->has( 'page_'.$column ) )
						$data[$column]	= $this->request->get( 'page_'.$column );
				if( $scope != $page->scope )														//  switched scope
					$data['parentId']	= 0;														//  clear parent page
				$data['modifiedAt']	= time();
				unset( $data['pageId'] );
				$model->edit( $pageId, $data, FALSE );
				$this->env->getMessenger()->noteSuccess( $words->successEdited, $data['title'] );
				$this->restart( 'edit/'.$pageId, TRUE );
			}
		}

		$pages	= array();
		foreach( $model->getAllByIndex( 'status', 1, array( 'title' => "ASC" ) ) as $item ){
			if( $item->parentId ){
				$parent	= $model->get( $item->parentId );
				if( $parent && $parent->parentId ){
					$grand	= $model->get( $parent->parentId );
					$parent->identifier	= $grand->identifier.'/'.$parent->identifier;
				}
				$item->identifier	= $parent->identifier.'/'.$item->identifier;
			}
/*			if( class_exists( 'Logic_Localization' ) ){							//  localization module is installed
				$localization	= new Logic_Localization( $this->env );
				$id	= 'page.'.$item->identifier.'-title';
				$item->title	= $localization->translate( $id, $item->title );
			}*/
			$pages[]	= $item;
		}

		$path		= $this->frontend->getUri();
		$versions	= array();
		$this->session->set( 'module.manage_pages.scope', $page->scope );
		if( $page->parentId ){
			$parent	= $model->get( (int) $page->parentId );
			if( $parent )
				$path	.= $parent->identifier.'/';
		}
		if( $this->env->getModules()->has( 'Resource_Versions' ) ){
			$logic		= Logic_Versions::getInstance( $this->env );
			$orders		= array( 'version' => 'DESC' );
			$limits		= array( 0, 10 );
			$versions	= $logic->getAll( 'Info_Pages', $pageId, array(), $orders, $limits );
			if( !is_null( $version ) ){
				$entry	= $logic->get( 'Info_Pages', $pageId, $version );
				if( $entry )
					$page->content	= $entry->content;
			}
		}

		$editors	= array( 'none' );
		if( $this->env->getModules()->has( 'JS_TinyMCE' ) )
			$editors[]	= 'TinyMCE';
		if( $this->env->getModules()->has( 'JS_Ace' ) )
			$editors[]	= 'Ace';
		if( $this->env->getModules()->has( 'JS_CodeMirror' ) )
			$editors[]	= 'CodeMirror';

		$this->addData( 'current', $pageId );
		$this->addData( 'pageId', $pageId );
		$this->addData( 'pages', $pages );
		$this->addData( 'page', $page );
		$this->addData( 'path', $path );
		$this->addData( 'version', $version );
		$this->addData( 'versions', $versions );
		$this->addData( 'pagePreviewUrl', $path.$page->identifier.'?preview='.$page->createdAt.$page->modifiedAt );
		$this->addData( 'tab', max( 1, (int) $session->get( 'module.manage_pages.tab' ) ) );
		$this->addData( 'scope', $this->session->get( 'module.manage_pages.scope' ) );
		$this->addData( 'editor', $session->get( 'module.manage_pages.editor' ) );
		$this->addData( 'editors', $editors );
		$this->addData( 'modules', $this->frontend->getModules() );
		$this->addData( 'controllers', $this->getFrontendControllers() );
		$this->preparePageTree( $pageId );

		if( !$this->frontend->hasModule( 'UI_MetaTags' ) )
			$this->env->getMessenger()->noteError( 'Das Modul "UI:MetaTags" muss in der Zielinstanz installiert sein, ist es aber nicht.' );
		else{
			$meta	= $this->frontend->getModuleConfigValues( "UI_MetaTags", array(
				'default.description',
				'default.keywords',
				'default.author',
				'default.publisher'
			) );
			if( file_exists( $this->frontend->getPath().$meta['default.keywords'] ) ){
				$list	= array();
				foreach( explode( "\n", file_get_contents( $this->frontend->getPath().$meta['default.keywords'] ) ) as $line )
					if( trim( $line ) )
						$list[]	= trim( $line );
				$meta['default.keywords']	= join( ", ", $list );
			}
			$this->addData( 'meta', $meta );
		}

//		$helper	= new View_Helper_TinyMceResourceLister( $this->env );
		$script	= '
ModuleManagePages.PageEditor.frontendUri = "'.$this->frontend->getUri().'";
ModuleManagePages.PageEditor.pageId = "'.$page->pageId.'";
ModuleManagePages.PageEditor.pageIdentifier = "'.$page->identifier.'";
ModuleManagePages.PageEditor.parentPageId = "'.$page->parentId.'";
ModuleManagePages.PageEditor.editor = "'.$session->get( 'module.manage_pages.editor' ).'";
ModuleManagePages.PageEditor.editors = '.json_encode( array_keys( $this->getWords( 'editors' ) ) ).';
ModuleManagePages.PageEditor.format = "'.$page->format.'";
ModuleManagePages.PageEditor.init();
';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	protected function getFrontendControllers(){
		$controllers	= array();
		$modulePath		= $this->frontend->getPath( 'modules' );
		foreach( $this->frontend->getModules() as $moduleId ){
			$module	= CMF_Hydrogen_Environment_Resource_Module_Reader::load( $modulePath.$moduleId.'.xml', $moduleId );
			foreach( $module->files->classes as $classFile ){
				if( preg_match( "/^Controller/", $classFile->file ) ){
					$name	= preg_replace( "/^Controller\/(.+)\.php.?$/", "$1", $classFile->file );
					$controllers[]	= str_replace( "/", "_", $name );
				}
			}
		}
		return array_unique( $controllers );
	}

	public function getJsImageList(){
		$pathFront	= $this->frontend->getPath();
		$pathImages	= $this->frontend->getPath( 'images' );
		$index	= new FS_File_RecursiveRegexFilter( $pathFront.$pathImages, "/\.jpg$/i" );
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

	public function index(){
		$this->preparePageTree();
		$this->addData( 'scope', $this->session->get( 'module.manage_pages.scope' ) );
		$this->addData( 'parentId', 0 );
	}

	protected function translatePage( $page ){
		if( !class_exists( 'Logic_Localization' ) )							//  localization module is not installed
			return $page;
		$localization	= new Logic_Localization( $this->env );
		$localization->setLanguage( $this->session->get( 'module.manage_pages.language' ) );
		remark( $localization->getLanguage() );
		$id	= 'page.'.$page->identifier.'-title';
		remark( $id );
		$page->title	= $localization->translate( $id, $page->title );
		$id	= 'page.'.$page->identifier.'-content';
		$page->content	= $localization->translate( $id, $page->content );
		return $page;
	}

	protected function preparePageTree( $currentPageId = NULL ){
		$scope		= (int) $this->session->get( 'module.manage_pages.scope' );
		$model		= new Model_Page( $this->env );
		$indices	= array( 'parentId' => 0, 'status' => '>-2', 'scope' => $scope );
		$pages		= $model->getAllByIndices( $indices, array( 'rank' => "ASC" ) );
		$tree		= array();
		$parentMap	= array( '0' => '-' );
		foreach( $pages as $item ){
			$item	= $this->translatePage( $item );
//			print_m( $item );die;
			if( $item->pageId != $currentPageId && $item->type == 1 )
				$parentMap[$item->pageId]	= $item->title;
			$indices		= array( 'parentId' => $item->pageId );
			$item->subpages	= $model->getAllByIndices( $indices, array( 'rank' => "ASC" ) );
			foreach( $item->subpages as $nr => $subitem )
				$subitem	= $this->translatePage( $subitem );
			$tree[]		= $item;
		}
		$this->addData( 'tree', $tree );
		$this->addData( 'parentMap', $parentMap );
	}

	public function remove( $pageId ){
		$page		= $this->checkPageId( $pageId );
		$model		= new Model_Page( $this->env );
		$model->remove( $pageId );
		$this->messenger->noteSuccess( $this->getWords( 'msg' )['successRemoved'], $page->title );
		$this->restart( NULL, TRUE );
	}

	public function setLanguage( $language ){
		$this->session->set( 'module.manage_pages.language', (string) $language );
		$this->restart( NULL, TRUE );
	}

	public function setScope( $scope ){
		$this->session->set( 'module.manage_pages.scope', (int) $scope );
		$this->restart( NULL, TRUE );
	}
}
?>
