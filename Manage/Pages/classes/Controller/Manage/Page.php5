<?php
class Controller_Manage_Page extends CMF_Hydrogen_Controller{

	protected $model;

	protected function __onInit(){
		$config		= $this->env->getConfig()->getAll( 'module.manage_pages.', TRUE );

		$this->baseUri		= $config->get( 'site.domain' ).$config->get( 'site.path' );
		$this->model		= new Model_Page( $this->env );
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->words		= $this->getWords();

		$this->session		= $this->env->getSession();
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			foreach( $this->model->getColumns() as $column ){
				if( $this->request->has( $column ) ){
					$value	= $this->request->get( $column );
					if( $column == 'identifier' )
						$value	= preg_replace( "/[^a-z0-9]/", "", $value );
					$data[$column]	= $value;
				}
			}
			$data['timestamp']	= time();
			unset( $data['pageId'] );

			$indices		= array( 'parentId' => 0, 'identifier' => $data['identifier'] );
			if( $this->model->getByIndices( $indices ) )
				$this->messenger->noteError( 'Identifier "'.$data['identifier'].'" already taken' );
			else{
				$pageId		= $this->model->add( $data );
				$this->env->getMessenger()->noteSuccess( 'Neue Seite "'.$data['title'].'" angelegt.' );
				$this->restart( 'manage/page/edit/'.$pageId );
			}
		}

		$page	= (object) array(
			'pageId'		=> 0,
			'parentId'		=> (int) $this->request->get( 'parentId' ),
			'type'			=> (int) $this->request->get( 'type' ),
			'scope'			=> (int) $this->request->get( 'scope' ),
			'status'		=> 0,
			'rank'			=> (int) $this->request->get( 'rank' ),
			'identifier'	=> $this->request->get( 'identifier' ),
			'title'			=> $this->request->get( 'title' ),
			'content'		=> $this->request->get( 'content' ),
		);
		$this->addData( 'path', $this->baseUri );
		$this->addData( 'page', $page );
		$this->addData( 'scope', $this->session->get( 'module.manage_pages.scope' ) );
		$this->preparePageTree();
	}

	public function ajaxSetEditor( $editor ){
		$this->env->getSession()->set( 'module.manage_pages.editor', $editor );
		exit;
	}

	public function ajaxSetTab( $tabKey ){
		$this->env->getSession()->set( 'module.manage_pages.tab', $tabKey );
		exit;
	}

	public function edit( $pageId ){
		$session	= $this->env->getSession();
		$model		= new Model_Page( $this->env );
		$words		= (object) $this->getWords( 'edit' );

		$editors	= array( 'none' );
		if( $this->env->getModules()->has( 'JS_TinyMCE' ) )
			$editors[]	= 'TinyMCE';
		if( $this->env->getModules()->has( 'JS_CodeMirror' ) )
			$editors[]	= 'CodeMirror';

		if( !$session->get( 'module.manage_pages.editor' ) )
			$session->set( 'module.manage_pages.editor', $this->env->getConfig()->get( 'module.manage_pages.editor' ) );

		if( !$pageId )
			throw new OutOfRangeException( 'No page ID given' );

		if( $this->request->has( 'save' ) ){
			$page	= $this->model->get( $pageId );
			if( !$pageId )
				throw new OutOfRangeException( 'Invalid page ID given' );

			$this->request->set( 'identifier', preg_replace( "/[^a-z0-9]/", "", $this->request->get( 'identifier' ) ) );

			$indices		= array(
				'parentId'		=> $this->request->get( 'parentId' ),
				'pageId'		=> '!='.$pageId,
				'identifier'	=> $this->request->get( 'identifier' )
			);
			if( $this->model->getByIndices( $indices ) ){
				if( $this->request->get( 'parentId' ) )
					$this->messenger->noteError( $words->msgErrorIdentifierInParentTaken, $this->request->get( 'identifier' ) );
				else
					$this->messenger->noteError( $words->msgErrorIdentifierTaken, $this->request->get( 'identifier' ) );
			}
			else{
				$data		= array();
				foreach( $this->model->getColumns() as $column )
					if( $this->request->has( $column ) )
						$data[$column]	= $this->request->get( $column );
				$data['timestamp']	= time();
				unset( $data['pageId'] );
				$model->edit( $pageId, $data, FALSE );
				$this->env->getMessenger()->noteSuccess( $words->msgSuccess, $data['title'] );
				$this->restart( './manage/page/edit/'.$pageId );
			}
		}

		$pages	= array();
		foreach( $model->getAllByIndex( 'status', 1, array( 'title' => "ASC" ) ) as $page ){
			if( $page->parentId ){
				$parent	= $model->get( $page->parentId );
				if( $parent->parentId ){
					$grand	= $model->get( $parent->parentId );
					$parent->identifier	= $grand->identifier.'/'.$parent->identifier;
				}
				$page->identifier	= $parent->identifier.'/'.$page->identifier;
			}
			$pages[]	= $page;
		}

		$page		= (object) array( 'pageId' => 0 );
		$path		= $this->baseUri;
		if( $pageId ){
			$page		= $model->get( (int) $pageId );
			$this->session->set( 'module.manage_pages.scope', $page->scope );
			if( $page->parentId ){
				$parent	= $model->get( (int) $page->parentId );
				$path	= $this->baseUri.$parent->identifier.'/';
			}
		}

		$this->addData( 'current', $pageId );
		$this->addData( 'pages', $pages );
		$this->addData( 'page', $page );
		$this->addData( 'path', $path );
		$this->addData( 'pagePreviewUrl', "http://".$path.$page->identifier );
		$this->addData( 'tab', max( 1, (int) $session->get( 'module.manage_pages.tab' ) ) );
		$this->addData( 'scope', $this->session->get( 'module.manage_pages.scope' ) );
		$this->addData( 'editor', $session->get( 'module.manage_pages.editor' ) );
		$this->addData( 'editors', $editors );
		$this->preparePageTree( $pageId );

		$enabled		= FALSE;
		$meta		= $this->getRemoteConfigValues( "UI_MetaTags", array( 'default.description', 'default.keywords', 'default.author', 'default.publisher' ) );
		if( !$meta )
			$this->env->getMessenger()->noteError( 'Das Modul "UI:MetaTags" muss in der Zielinstanz installiert sein, ist es aber nicht.' );
		$this->addData( 'meta', $meta );
	}

	protected function getRemoteConfigValues( $moduleId, $keys = array() ){
		$list		= array();
		$fileName	= "../config/modules/".$moduleId.".xml";
		if( file_exists( $fileName ) ){
			$lines	= explode( "\n", File_Reader::load( $fileName ) );
			foreach( $lines as $nr => $line ){
				if( preg_match( "@<config @", $line ) ){
					$key	= preg_replace( "@^.+name=\"(.+)\".+$@", "\\1", $line );
					if( in_array( $key, $keys ) )
						$list[$key]	= preg_replace( "@^.+>(.*)</.+$@", "\\1", $line );
				}
			}
		}
		return $list;
	}

	public function getJsImageList(){
		$pathFront	= "../";
		$pathImages	= "images/";
		$index	= new File_RecursiveRegexFilter( $pathFront.$pathImages, "/\.jpg$/i" );
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
	}

	protected function preparePageTree( $currentPageId = NULL ){
		$scope		= (int) $this->session->get( 'module.manage_pages.scope' );
		$model		= new Model_Page( $this->env );
		$indices	= array( 'parentId' => 0, 'status' => '>-2', 'scope' => $scope );
		$pages		= $model->getAllByIndices( $indices, array( 'rank' => "ASC" ) );
		$tree		= array();
		$parentMap	= array( '0' => '-' );
		foreach( $pages as $item ){
			if( $item->pageId != $currentPageId && $item->type == 1 )
				$parentMap[$item->pageId]	= $item->title;
			$indices		= array( 'parentId' => $item->pageId );
			$item->subpages	= $model->getAllByIndices( $indices, array( 'rank' => "ASC" ) );
			$tree[]		= $item;
		}
		$this->addData( 'tree', $tree );
		$this->addData( 'parentMap', $parentMap );
	}

	public function setScope( $scope ){
		$this->session->set( 'module.manage_pages.scope', (int) $scope );
		$this->restart( NULL, TRUE );
	}
}
?>
