<?php
class Controller_Info_Manual extends CMF_Hydrogen_Controller{

	protected $path;
	protected $request;
	protected $messenger;
	protected $config;
	protected $files		= array();
	protected $userId		= 0;
	protected $modelCategory;
	protected $modelPage;
	protected $modelUser;
	protected $modelVersion;

	/** @var	ADT_List_Dictionary	$order */
	protected $order;
	protected $ext			= ".md";

	protected function __callbackEncode( $matches ){
		if( preg_match( "/^[a-z]+:\/\//i", $matches[2] ) )
			return $matches[1].'('.$matches[2].')';
		if( preg_match( "/^\.\/info\/manual\/view\//i", $matches[2] ) ){
			$fileName	= str_replace( './info/manual/view/', '', $matches[2] );
			if( file_exists( $this->path.urldecode( $fileName ).$this->ext ) )
				return $matches[1].'('.'./info/manual/view/'.urlencode( $fileName ).')';
			return '<strike>'.$matches[1].'('.'./info/manual/view/'.urlencode( $fileName ).').</strike>';
		}
		return '<strike>'.$matches[1].'('.urlencode( $matches[2] ).')</strike>';
	}

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_manual.', TRUE );
		$this->path			= $this->moduleConfig->get( 'path' );
		$this->order		= new ADT_List_Dictionary();
		$this->rights		= $this->env->getAcl()->index( 'info/manual' );
		$this->isEditable	= $this->moduleConfig->get( 'editor' );

		$this->modelCategory	= new Model_Manual_Category( $this->env );
		$this->modelPage		= new Model_Manual_Page( $this->env );
		$this->modelVersion		= new Model_Manual_Version( $this->env );

		if( $this->env->getModules()->has( 'Resource_Users' ) ){
			$this->modelUser	= new Model_User( $this->env );
			$this->userId	= Logic_Authentication::getInstance( $this->env )->getCurrentUserId();
		}

		$this->scanFiles();
		$orderFile	= $this->path.'order.list';
		if( file_exists( $this->path.'order.list' ) ){
			$order			= trim( FS_File_Reader::load( $orderFile ) );
			$this->order	= new ADT_List_Dictionary( explode( "\n", $order ) );
		}
		else{
			$this->order	= new ADT_List_Dictionary( array_values( $this->files ) );
			$this->saveOrder();
		}

		$this->addData( 'path', $this->path );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'files', $this->files );
		$this->addData( 'order', $this->order );
		$this->addData( 'rights', $this->rights );

		$this->categories	= array();
		$conditions			= array( 'status' => '>='.Model_Manual_Category::STATUS_NEW );
		$orders				= array( 'rank' => 'ASC' );
		foreach( $this->modelCategory->getAll( $conditions, $orders ) as $category )
			$this->categories[$category->manualCategoryId]	= $category;
		$this->addData( 'categories', $this->categories );
	}

	public function add(){
		$categoryId		= 0;
		if( !$this->isEditable || !in_array( 'add', $this->rights ) )
			$this->restart( NULL, TRUE );
		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'add' );
			$title		= trim( $this->request->get( 'title' ) );
			$content	= trim( $this->request->get( 'content' ) );
			if( !strlen( trim( $title ) ) )
				$this->messenger->noteError( $words->msgErrorFilenameMissing );
			else{
				$pageId	= $this->modelPage->add( array(
					'manualCategoryId'	=> $categoryId,
					'creatorId'			=> $this->userId,
					'status'			=> Model_Manual_Page::STATUS_NEW,
					'format'			=> Model_Manual_Page::FORMAT_MARKDOWN,
					'version'			=> 1,
					'rank'				=> $this->modelCategory->countByIndex( 'manualCategoryId', $categoryId ) + 1,
					'title'				=> $title,
					'content'			=> $content,
					'createdAt'			=> time(),
					'modifiedAt'		=> time(),
				) );
				$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $title, ENT_QUOTES, 'UTF-8' ) );
				$this->restart( 'view/'.$pageId.'-'.$this->urlencode( $title ), TRUE );
			}
		}
		$this->addData( 'title', $this->request->get( 'title' ) );
		$this->addData( 'content', $this->request->get( 'content' ) );
	}

	protected function checkPageId( $pageId ){
		if( !strlen( trim( $pageId ) ) )
			throw new InvalidArgumentException( 'No page ID given' );
		$page	= $this->modelPage->get( $pageId );
		if( !$page )
			throw new InvalidArgumentException( 'Invalid page ID given' );
		if( $page->manualCategoryId )
			$page->category	= $this->modelCategory->get( $page->manualCategoryId );
		return $page;
	}

	public function edit( $pageId, $version = NULL ){
		$page	= $this->checkPageId( $pageId );
		if( !$this->isEditable || !in_array( 'edit', $this->rights ) )
			$this->restart( 'view/'.$page->manualPageId.'-'.$this->urlencode( $page->title ), TRUE );

		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'edit' );
			$title		= $this->request->get( 'title' );
			$content	= $this->request->get( 'content' );
			if( $page->title === $title && $page->content === $content ){
				$this->messenger->noteNotice( $words->msgNoChanges );
				$this->restart( 'view/'.$page->manualPageId.'-'.$this>urlencode( $page->title ), TRUE );
			}
			$this->modelVersion->add( array(
				'userId'	=> $this->userId,
				'objectId'	=> $page->manualPageId,
				'type'		=> Model_Manual_Version::TYPE_PAGE,
				'version'	=> $page->version,
				'object'	=> serialize( $page ),
				'timestamp'	=> time(),
			), FALSE );

			$this->modelPage->edit( $page->manualPageId, array(
				'title'			=> $title,
				'content'		=> $content,
				'version'		=> $page->version + 1,
				'modifiedAt'	=> time(),
			), FALSE );
			$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ) );
			$this->restart( 'view/'.$page->manualPageId.'-'.$this->urlencode( $page->title ), TRUE );
		}
		$this->addData( 'file', $page->title );
		$this->addData( 'content', $page->content );
		$this->addData( 'page', $page );
	}

	public function index( $categoryId = NULL ){
		$categoryId	= (int) $categoryId;

		if( !$categoryId ){
			if( $this->session->get( 'filter_info_manual_categoryId' ) ){
				$categoryId	= $this->session->get( 'filter_info_manual_categoryId' );
				$category	= $this->modelCategory->get( $categoryId );
				$this->restart( $category->manualCategoryId.'-'.$this->urlencode( $category->title ), TRUE );
			}
			else if( count( $this->categories ) === 1 ){
				$categories	= array_values( $this->categories );
				$category	= $categories[0];
				$this->restart( $category->manualCategoryId.'-'.$this->urlencode( $category->title ), TRUE );
			}
			else{

			}
		}

		$conditions	= array( 'status' => '>='.Model_Manual_Page::STATUS_NEW );
		$orders		= array();
		$pages	= $this->modelPage->getAll( $conditions, $orders );
		$this->addData( 'pages', $pages );
	}

	public function moveDown( $pageId ){
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'move' );

		if( !$this->isEditable || !in_array( 'moveDown', $this->rights ) )
			$this->restart( 'view/'.$page->manualPageId.'-'.$this->urlencode( $page->title ), TRUE );

		// @todo implement
		if( $page->manualCategoryId )
			$this->rankPagesOfCategory( $page->manualCategoryId );
		$this->restart( 'edit/'.$page->manualPageId.'-'.$this->urlencode( $page->title ), TRUE );
	}

	public function moveUp( $pageId ){
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'move' );

		if( !$this->isEditable || !in_array( 'moveUp', $this->rights ) )
			$this->restart( 'view/'.$page->manualPageId.'-'.$this->urlencode( $page->title ), TRUE );

		// @todo implement
		if( $page->manualCategoryId )
			$this->rankPagesOfCategory( $page->manualCategoryId );
		$this->restart( 'edit/'.$page->manualPageId.'-'.$this->urlencode( $page->title ), TRUE );
	}

	protected function relink( $oldName, $newName ){
		$this->scanFiles();
		foreach( $this->files as $entry ){
			$filePath	= $this->path.$entry;
			$content	= FS_File_Reader::load( $filePath );
			$relinked	= str_replace( "](".$oldName.")", "](".$newName.")", $content );
			$relinked	= str_replace( "]: ".$oldName."\r\n", "]: ".$newName."\r\n", $relinked );
			if( $relinked !== $content )
				FS_File_Writer::save( $filePath, $relinked );
		}
	}

	public function reload(){
		if( !in_array( 'reload', $this->rights ) )
			$this->restart( NULL, TRUE );
		$orderFile	= $this->path.'order.list';
		$new		= array_diff( $this->files, $this->order->getAll() );
		$outdated	= array_diff( $this->order->getAll(), $this->files );
		foreach( $new as $entry )
			$this->order[]	= $entry;
		foreach( $outdated as $entry )
			$this->order->remove( $this->order->getKeyOf( $entry ) );
		$this->saveOrder();
		$this->restart( getEnv( 'HTTP_REFERER' ) );
	}

	public function remove( $pageId ){
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'remove' );

		if( !$this->isEditable || !in_array( 'remove', $this->rights ) )
			$this->restart( 'view/'.$pageId.'-'.$this->urlencode( $page->title ), TRUE );
		$filePath	= $this->path.$fileName.$this->ext;

		$this->modelPage->remove( $page->manualPageId );
		if( $page->manualCategoryId )
			$this->rankPagesOfCategory( $page->manualCategoryId );
		$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ) );
		$this->restart( NULL, TRUE );
	}

	protected function saveOrder(){
		$orderFile	= $this->path.'order.list';
		FS_File_Writer::save( $orderFile, implode( "\n", $this->order->getAll() ) );
	}

	public function scanFiles(){
		$this->files	= array();
		$index	= new FS_File_RecursiveRegexFilter( $this->path, "/\\".$this->ext."$/" );
		foreach( $index as $entry ){
			$pathName	= substr( $entry->getPathname(), strlen( $this->path ) );
			$this->files[]	= $pathName;
			natcasesort( $this->files );
		}
	}

	protected function urlencode( $name ){
		return urlencode( $name );
		$name	= rawurldecode( $name );
		$name	= str_replace( "%2F", "/", $name );
		$name	= str_replace( " ", "%20", $name );
		return $name;
	}

	public function view( $pageId ){
		$pageId		= (int) $pageId;
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'index' );

		foreach( $this->files as $entry ){
			$entry	= preg_replace( "/\.md$/", "", $entry );
			$page->content	= str_replace( "](".$entry.")", "](./info/manual/view/".$this->urlencode( $entry ).")", $page->content );
			$page->content	= str_replace( "]: ".$entry."\r\n", "]: ./info/manual/view/".$this->urlencode( $entry )."\r\n", $page->content );
		}
		$page->content	= preg_replace_callback( "@(\[.+\])\((.+)\)@Us", array( $this, '__callbackEncode' ), $page->content );

		/*  --  EVALUATE RENDERER  --  */
		$renderer			= $this->moduleConfig->get( 'renderer' );
		$markdownOnServer	= $this->env->getModules()->has( 'UI_Markdown' );
		$markdownOnClient	= $this->env->getModules()->has( 'JS_Markdown' );
		if( !$markdownOnServer && preg_match( "/^server/", $renderer ) )
			$renderer	= 'client';
		if( !$markdownOnClient && $renderer === 'client' )
			$this->env->getMessenger()->noteFailure( 'No Markdown renderer installed.' );

		$this->addData( 'file', $page->title );
		$this->addData( 'files', $this->files );
		$this->addData( 'renderer', $renderer );
		$this->addData( 'content', $page->content );
		$this->addData( 'page', $page );
	}
}
?>
