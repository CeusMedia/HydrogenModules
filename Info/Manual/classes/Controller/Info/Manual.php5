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
			$fileName	= str_replace( './info/manual/page/', '', $matches[2] );
			if( file_exists( $this->path.urldecode( $fileName ).$this->ext ) )
				return $matches[1].'('.'./info/manual/page/'.urlencode( $fileName ).')';
			return '<strike>'.$matches[1].'('.'./info/manual/page/'.urlencode( $fileName ).').</strike>';
		}
		return '<strike>'.$matches[1].'('.urlencode( $matches[2] ).')</strike>';
	}

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_manual.', TRUE );
		$this->path			= $this->moduleConfig->get( 'path' );
		$this->order		= new ADT_List_Dictionary();
		$this->rights		= $this->env->getAcl()->index( 'info/manual' );
		$this->isEditable	= $this->moduleConfig->get( 'editor' );
		$this->helperUrl	= new View_Helper_Info_Manual_Url( $this->env );

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
		else if( count( $this->files ) ){
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
		if( !$this->categories ){
			$data	= array(
				'creatorId'		=> (int) $this->userId,
				'status'		=> Model_Manual_Category::STATUS_NEW,
				'format'		=> Model_Manual_Category::FORMAT_TEXT,
				'version'		=> 1,
				'rank'			=> 1,
				'title'			=> 'Default',
				'content'		=> '',
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			);
			$this->modelCategory->add( $data );
			$this->restart( NULL, TRUE );
		}
		if( count( $this->categories ) === 1 ){
			if( !$this->session->get( 'filter_info_manual_categoryId' ) ){
				$categories	= array_values( $this->categories );
				$category	= $categories[0];
				$this->session->set( 'filter_info_manual_categoryId', $category->manualCategoryId );
				$this->restartToCategory( $category );
/*				$this->restart( vsprintf( 'category/%d-%s', array(
					$category->manualCategoryId,
					$this->urlencode( $category->title ),
				) ), TRUE );*/
			}
		}
		$this->addData( 'categories', $this->categories );
		$this->addData( 'categoryId', $this->session->get( 'filter_info_manual_categoryId' ) );
	}

	public function import( $fileHash = NULL ){
		if( $this->request->isPost() && $this->request->has( 'save' ) ){
			$categoryId	= $this->request->get( 'categoryId' );
			$format		= $this->request->get( 'format' );
			$files		= $this->request->get( 'files' );
			$newPages	= array();
			foreach( $files as $fileHash ){
				$fileName	= base64_decode( $fileHash );
				if( file_exists( $this->path.$fileName ) ){
					$content	= FS_File_Reader::load( $this->path.$fileName );
					$category	= $this->checkCategoryId( $categoryId );
					$nextRank	= $this->modelCategory->countByIndex( 'manualCategoryId', $categoryId ) + 1;
					$newPages[]	= $this->modelPage->add( array(
						'manualCategoryId'	=> $categoryId,
						'creatorId'			=> $this->userId,
						'status'			=> Model_Manual_Page::STATUS_NEW,
						'format'			=> $format,
						'version'			=> 1,
						'rank'				=> $nextRank,
						'title'				=> rtrim( $fileName, '.md' ),
						'content'			=> $content,
						'createdAt'			=> time(),
						'modifiedAt'		=> time(),
					) );
					@unlink( $this->path.$fileName );
				}
			}
			$message	= vsprintf( 'Imported %d pages into category "%s".', array(
				count( $newPages ),
				$category->title,
			) );
			$this->messenger->noteSuccess( $message );
			$this->restart( 'import', TRUE );
		}
		if( $fileHash ){
			$fileName	= base64_decode( $fileHash );
			if( file_exists( $this->path.$fileName ) ){
				$content	= FS_File_Reader::load( $this->path.$fileName );
				$categoryId	= $this->session->get( 'filter_info_manual_categoryId' );
				$nextRank	= $this->modelCategory->countByIndex( 'manualCategoryId', $categoryId ) + 1;
				$this->modelPage->add( array(
					'manualCategoryId'	=> $categoryId,
					'creatorId'			=> $this->userId,
					'status'			=> Model_Manual_Page::STATUS_NEW,
					'format'			=> Model_Manual_Page::FORMAT_MARKDOWN,
					'version'			=> 1,
					'rank'				=> $nextRank,
					'title'				=> rtrim( $fileName, '.md' ),
					'content'			=> $content,
					'createdAt'			=> time(),
					'modifiedAt'		=> time(),
				) );
				@unlink( $this->path.$fileName );
				$this->restart( 'import', TRUE );
			}
		}
//		print_m( $this->files );
		$this->addData( 'files', $this->files );
	}

	public function add(){
		if( !$this->isEditable || !in_array( 'add', $this->rights ) )
			$this->restart( NULL, TRUE );

		$title		= trim( $this->request->get( 'title' ) );
		$content	= trim( $this->request->get( 'content' ) );

		$format		= $this->request->get( 'format' );
		if( !$format )
			$format	= Model_Manual_Page::FORMAT_MARKDOWN;						// @todo support module config default or user setting

		$categoryId	= $this->request->get( 'categoryId' );
		if( !$categoryId )
			$categoryId	= $this->session->get( 'filter_info_manual_categoryId' );

		$version	= max( $this->request->get( 'version' ), 1 );

		$nextRank	= $this->modelCategory->countByIndex( 'manualCategoryId', $categoryId ) + 1;
		$rank		= max( $this->request->get( 'rank' ), $nextRank );

		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'add' );
			if( !strlen( trim( $title ) ) )
				$this->messenger->noteError( $words->msgErrorFilenameMissing );
			else{
				$pageId	= $this->modelPage->add( array(
					'manualCategoryId'	=> $categoryId,
					'creatorId'			=> $this->userId,
					'status'			=> Model_Manual_Page::STATUS_NEW,
					'format'			=> $format,
					'version'			=> $version,
					'rank'				=> $rank,
					'title'				=> $title,
					'content'			=> $content,
					'createdAt'			=> time(),
					'modifiedAt'		=> time(),
				) );
				$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $title, ENT_QUOTES, 'UTF-8' ) );
				$this->restartToPage( $this->modelPage->get( $pageId ) );
			}
		}
		$this->addData( 'categoryId', $categoryId );
		$this->addData( 'format', $format );
		$this->addData( 'version', $version );
		$this->addData( 'rank', $rank );
		$this->addData( 'title', $title );
		$this->addData( 'content', $content );
	}

	public function category( $categoryId ){
		$category	= $this->checkCategoryId( $categoryId );
		$conditions	 = array(
			'manualCategoryId'	=> $category->manualCategoryId,
			'status'			=> '>='.Model_Manual_Category::STATUS_NEW,
		);
		$orders		= array( 'rank' => 'ASC' );
		$pages		= $this->modelPage->getAll( $conditions, $orders );
		if( !$pages ){
//			throw new RuntimeException( 'No page found in category' );
			$data	= array(
				'manualCategoryId'	=> $category->manualCategoryId,
				'creatorId'			=> $this->userId,
				'status'			=> Model_Manual_Page::STATUS_NEW,
				'format'			=> Model_Manual_Page::FORMAT_MARKDOWN,
				'version'			=> 0,
				'rank'				=> 0,
				'title'				=> 'Start',
				'content'			=> '...',
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			);
			$this->modelPage->add( $data );
			$this->restartToCategory( $category );
		}
		$firstPage	= current( $pages );
		$this->restartToPage( $firstPage );
	}

	protected function checkCategoryId( $categoryId ){
		if( !strlen( trim( $categoryId ) ) )
			throw new InvalidArgumentException( 'No category ID given' );
		$category	= $this->modelCategory->get( $categoryId );
		if( !$category )
			throw new InvalidArgumentException( 'Invalid category ID given' );
		return $category;
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
			$this->restartToPage( $page );

		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'edit' );
			$title		= $this->request->get( 'title' );
			$content	= $this->request->get( 'content' );
			if( $page->title === $title && $page->content === $content ){
				$this->messenger->noteNotice( $words->msgNoChanges );
				$this->restartToPage( $page );
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
			$this->restartToPage( $page );
		}
		$this->addData( 'file', $page->title );
		$this->addData( 'content', $page->content );
		$this->addData( 'page', $page );
		$this->addData( 'pageId', $page->manualPageId );
	}

	protected function restartToCategory( $categoryOrId ){
		$this->restart( View_Helper_Info_Manual_Url::spawn( $this->env )
			->setCategory( $categoryOrId )
			->render() );
	}

	protected function restartToPage( $pageOrId ){
		$this->restart( View_Helper_Info_Manual_Url::spawn( $this->env )
			->setPage( $pageOrId )
			->render() );
	}

	public function index( $categoryId = NULL ){
		$categoryId	= (int) $categoryId;

		if( !$categoryId ){
			if( $this->session->get( 'filter_info_manual_categoryId' ) ){
				$categoryId	= $this->session->get( 'filter_info_manual_categoryId' );
				$category	= $this->modelCategory->get( $categoryId );
				$this->restartToCategory( $category );
			}
			else if( count( $this->categories ) === 1 ){
				$categories	= array_values( $this->categories );
				$category	= $categories[0];
				$this->restartToCategory( $category );
			}

			else{
			}
		}

		$conditions	= array( 'status' => '>='.Model_Manual_Page::STATUS_NEW );
		$orders		= array();
		$pages	= $this->modelPage->getAll( $conditions, $orders );
		$this->addData( 'pages', $pages );
	}

	public function movePageDown( $pageId ){
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'move' );

		if( !$this->isEditable || !in_array( 'moveDown', $this->rights ) )
			$this->restartToPage( $page );

		// @todo implement
		if( $page->manualCategoryId )
			$this->rankPagesOfCategory( $page->manualCategoryId );
		$this->restartToPage( $page );
	}

	public function movePageUp( $pageId ){
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'move' );

		if( !$this->isEditable || !in_array( 'moveUp', $this->rights ) )
			$this->restartToPage( $page );

		// @todo implement
		if( $page->manualCategoryId )
			$this->rankPagesOfCategory( $page->manualCategoryId );
		$this->restartToPage( $page );
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
//		$this->saveOrder();
		$this->restart( getEnv( 'HTTP_REFERER' ) );
	}

	public function removePage( $pageId ){
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'remove' );

		if( !$this->isEditable || !in_array( 'remove', $this->rights ) )
			$this->restartToPage( $page );

//		$filePath	= $this->path.$fileName.$this->ext;
		$this->modelPage->remove( $page->manualPageId );
		if( $page->manualCategoryId )
			$this->rankPagesOfCategory( $page->manualCategoryId );
		$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ) );
			$this->restartToCategory( $this->modelCategory->get( $page->manualCategoryId ) );
		$this->restart( NULL, TRUE );
	}

/*	protected function saveOrder(){
		$orderFile	= $this->path.'order.list';
		FS_File_Writer::save( $orderFile, implode( "\n", $this->order->getAll() ) );
	}*/

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

	public function page( $pageId ){
		$pageId		= (int) $pageId;
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'index' );

/*		foreach( $this->files as $entry ){
			$entry	= preg_replace( "/\.md$/", "", $entry );
			$urlPage	= $this->helperUrl->setPage( $entry )->render();
			$page->content	= str_replace( "](".$entry.")", "](".$urlPage.")", $page->content );
			$page->content	= str_replace( "]: ".$entry."\r\n", "]: ".$urlPages."\r\n", $page->content );
		}
		$page->content	= preg_replace_callback( "@(\[.+\])\((.+)\)@Us", array( $this, '__callbackEncode' ), $page->content );
*/
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
		$this->addData( 'categoryId', $page->manualCategoryId );
	}
}
?>
