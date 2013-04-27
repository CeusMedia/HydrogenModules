<?php
class Controller_Manage_Page extends CMF_Hydrogen_Controller{

	protected $model;

	protected function __onInit(){
		$this->baseUri		= "viva-yoga.de/";
		$this->model		= new Model_Page( $this->env );
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->words		= $this->getWords();
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
		$this->preparePageTree();
	}

	public function edit( $pageId ){
		$model		= new Model_Page( $this->env );
		$words		= (object) $this->getWords( 'edit' );

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
				$this->restart( 'manage/page/edit/'.$pageId );
			}
		}

		$page		= (object) array( 'pageId' => 0 );
		$path		= $this->baseUri;
		if( $pageId ){
			$page		= $model->get( (int) $pageId );
			if( $page->parentId ){
				$parent	= $model->get( (int) $page->parentId );
				$path	= $this->baseUri.$parent->identifier.'/';
			}
		}
		$this->addData( 'current', $pageId );
		$this->addData( 'page', $page );
		$this->addData( 'path', $path );
		$this->preparePageTree( $pageId );
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
	}

	protected function preparePageTree( $currentPageId = NULL ){
		$model		= new Model_Page( $this->env );
		$indices	= array( 'parentId' => 0, 'status' => '>-2' );
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
}
?>
