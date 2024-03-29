<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Info_Manual extends Controller
{
	protected HttpRequest $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected string $docPath;
	protected array $files					= [];
	protected string $userId				= '0';
	protected Model_Manual_Category $modelCategory;
	protected Model_Manual_Page $modelPage;
	protected Model_User $modelUser;
	protected Model_Manual_Version $modelVersion;
	protected View_Helper_Info_Manual_Url $helperUrl;
	protected Dictionary $order;
	protected string $ext					= ".md";
	protected bool $isEditable				= FALSE;
	protected array $rights					= [];
	protected array $categories				= [];

	public function add(): void
	{
		if( !$this->isEditable || !in_array( 'add', $this->rights ) )
			$this->restart( NULL, TRUE );

		$title		= trim( $this->request->get( 'title' ) );
		$content	= trim( $this->request->get( 'content' ) );
		$parentId	= $this->request->get( 'parentId' );
		$format		= $this->request->get( 'format' );
		if( !$format )
			$format	= Model_Manual_Page::FORMAT_MARKDOWN;						// @todo support module config default or user setting

		$categoryId	= (int) $this->request->get( 'categoryId' );
		if( !$categoryId )
			$categoryId	= (int) $this->session->get( 'filter_info_manual_categoryId' );

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
					'parentId'			=> $parentId,
					'creatorId'			=> (int) $this->userId,
					'status'			=> Model_Manual_Page::STATUS_NEW,
					'format'			=> $format,
					'version'			=> $version,
					'rank'				=> $rank,
					'title'				=> $title,
					'content'			=> $content,
					'createdAt'			=> time(),
					'modifiedAt'		=> time(),
				), FALSE );
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

        $folders    = [];
        $allPages   = $this->modelPage->getAll( [], ['title' => 'ASC'] );
        foreach( $allPages as $folder )
            $folders[]  = $folder;
        $this->addData( 'folders', $folders );
	}

	public function category( $categoryId )
	{
		$categoryId	= (int) $categoryId;
		$category	= $this->checkCategoryId( $categoryId );
		$this->session->set( 'filter_info_manual_categoryId', $categoryId );
		$conditions	 = [
			'manualCategoryId'	=> $category->manualCategoryId,
			'status'			=> '>= '.Model_Manual_Category::STATUS_NEW,
		];
		$orders		= ['rank' => 'ASC'];
		$pages		= $this->modelPage->getAll( $conditions, $orders );
		if( !$pages ){
//			throw new RuntimeException( 'No page found in category' );
			$pageId	= $this->modelPage->add( array(
				'manualCategoryId'	=> $category->manualCategoryId,
				'creatorId'			=> (int) $this->userId,
				'status'			=> Model_Manual_Page::STATUS_NEW,
				'format'			=> Model_Manual_Page::FORMAT_MARKDOWN,
				'version'			=> 0,
				'rank'				=> 1,
				'title'				=> 'Start Page',
				'content'			=> "## Start Page ##\nNo content, yet.",
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			) );
			$this->restartToPage( (int) $pageId );
		}
		$firstPage	= current( $pages );
		$this->restartToPage( $firstPage );
	}

	public function edit( $pageId, $version = NULL )
	{
		$page	= $this->checkPageId( $pageId );
		if( !$this->isEditable || !in_array( 'edit', $this->rights ) )
			$this->restartToPage( $page );

		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'edit' );
			$title		= $this->request->get( 'title' );
			$content	= $this->request->get( 'content' );
			$parentId	= $this->request->get( 'parentId' );
//			if( $page->title === $title && $page->content === $content ){
//				$this->messenger->noteNotice( $words->msgNoChanges );
//				$this->restartToPage( $page );
//			}
			$this->modelVersion->add( array(
				'userId'	=> $this->userId,
				'objectId'	=> $page->manualPageId,
				'type'		=> Model_Manual_Version::TYPE_PAGE,
				'version'	=> $page->version,
				'object'	=> serialize( $page ),
				'timestamp'	=> time(),
			), FALSE );

			$data	= array(
				'title'			=> $title,
				'content'		=> $content,
				'version'		=> $page->version + 1,
				'modifiedAt'	=> time(),
			);
			if( strlen( trim( $parentId ) ) )
				$data['parentId']	= $parentId;
			$this->modelPage->edit( $page->manualPageId, $data, FALSE );
			$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ) );
			$this->restartToPage( $page );
		}
		$this->addData( 'file', $page->title );
		$this->addData( 'content', $page->content );
		$this->addData( 'page', $page );
		$this->addData( 'pageId', $page->manualPageId );

		$folders	= [];
		$allPages	= $this->modelPage->getAll( [], ['title' => 'ASC'] );
		foreach( $allPages as $folder ){
			if( $folder->manualPageId == $page->manualPageId )
				continue;
			$folders[]	= $folder;
		}
		$this->addData( 'folders', $folders );

	}

	public function import( $fileHash = NULL )
	{
		if( $this->request->getMethod()->isPost() && $this->request->has( 'save' ) ){
			$categoryId	= $this->request->get( 'categoryId' );
			$format		= $this->request->get( 'format' );
			$files		= $this->request->get( 'files' );
			$category	= $this->checkCategoryId( $categoryId );
			$newPages	= [];
			foreach( $files as $fileHash ){
				$fileName	= base64_decode( $fileHash );
				if( file_exists( $this->docPath.$fileName ) ){
					$content	= FileReader::load( $this->docPath.$fileName );
					$nextRank	= $this->modelCategory->countByIndex( 'manualCategoryId', $categoryId ) + 1;
					$newPages[]	= $this->modelPage->add( array(
						'manualCategoryId'	=> $categoryId,
						'creatorId'			=> (int) $this->userId,
						'status'			=> Model_Manual_Page::STATUS_NEW,
						'format'			=> $format,
						'version'			=> 1,
						'rank'				=> $nextRank,
						'title'				=> rtrim( $fileName, '.md' ),
						'content'			=> $content,
						'createdAt'			=> time(),
						'modifiedAt'		=> time(),
					) );
					@unlink( $this->docPath.$fileName );
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
			if( file_exists( $this->docPath.$fileName ) ){
				$content	= FileReader::load( $this->docPath.$fileName );
				$categoryId	= $this->session->get( 'filter_info_manual_categoryId' );
				$nextRank	= $this->modelCategory->countByIndex( 'manualCategoryId', $categoryId ) + 1;
				$this->modelPage->add( array(
					'manualCategoryId'	=> $categoryId,
					'creatorId'			=> (int) $this->userId,
					'status'			=> Model_Manual_Page::STATUS_NEW,
					'format'			=> Model_Manual_Page::FORMAT_MARKDOWN,
					'version'			=> 1,
					'rank'				=> $nextRank,
					'title'				=> rtrim( $fileName, '.md' ),
					'content'			=> $content,
					'createdAt'			=> time(),
					'modifiedAt'		=> time(),
				) );
				@unlink( $this->docPath.$fileName );
				$this->restart( 'import', TRUE );
			}
		}
//		print_m( $this->files );
		$this->addData( 'files', $this->files );
	}

	public function index( $categoryId = NULL )
	{
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

		$conditions	= ['status' => '>= '.Model_Manual_Page::STATUS_NEW];
		$orders		= [];
		$pages	= $this->modelPage->getAll( $conditions, $orders );
		$this->addData( 'pages', $pages );
	}

	public function movePageDown( $pageId )
	{
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'move' );

		if( !$this->isEditable || !in_array( 'moveDown', $this->rights ) )
			$this->restartToPage( $page );

		// @todo implement
//		if( $page->manualCategoryId )
//			$this->rankPagesOfCategory( $page->manualCategoryId );
		$this->restartToPage( $page );
	}

	public function movePageUp( $pageId )
	{
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'move' );

		if( !$this->isEditable || !in_array( 'moveUp', $this->rights ) )
			$this->restartToPage( $page );

		// @todo implement
//		if( $page->manualCategoryId )
//			$this->rankPagesOfCategory( $page->manualCategoryId );
		$this->restartToPage( $page );
	}

	public function reload()
	{
		if( !in_array( 'reload', $this->rights ) )
			$this->restart( NULL, TRUE );
		$orderFile	= $this->docPath.'order.list';
		$new		= array_diff( $this->files, $this->order->getAll() );
		$outdated	= array_diff( $this->order->getAll(), $this->files );
		foreach( $new as $entry )
			$this->order[]	= $entry;
		foreach( $outdated as $entry )
			$this->order->remove( $this->order->getKeyOf( $entry ) );
//		$this->saveOrder();
		$this->restart( getEnv( 'HTTP_REFERER' ) );
	}

	public function removePage( $pageId )
	{
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'remove' );

		if( !$this->isEditable || !in_array( 'remove', $this->rights ) )
			$this->restartToPage( $page );

//		$filePath	= $this->docPath.$fileName.$this->ext;
		$this->modelPage->remove( $page->manualPageId );
//		if( $page->manualCategoryId )
//			$this->rankPagesOfCategory( $page->manualCategoryId );
		$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ) );
			$this->restartToCategory( $this->modelCategory->get( $page->manualCategoryId ) );
		$this->restart( NULL, TRUE );
	}

/*	protected function saveOrder(){
		$orderFile	= $this->docPath.'order.list';
		FileWriter::save( $orderFile, implode( "\n", $this->order->getAll() ) );
	}*/

	public function scanFiles()
	{
		$this->files	= [];
		$index	= new RecursiveRegexFileIndex( $this->docPath, "/\\".$this->ext."$/" );
		foreach( $index as $entry ){
			$pathName	= substr( $entry->getPathname(), strlen( $this->docPath ) );
			$this->files[]	= $pathName;
			natcasesort( $this->files );
		}
	}

	public function page( $pageId )
	{
		$pageId		= (int) $pageId;
		$page		= $this->checkPageId( $pageId );
		$words		= (object) $this->getWords( 'index' );

/*		foreach( $this->files as $entry ){
			$entry	= preg_replace( "/\.md$/", "", $entry );
			$urlPage	= $this->helperUrl->setPage( $entry )->render();
			$page->content	= str_replace( "](".$entry.")", "](".$urlPage.")", $page->content );
			$page->content	= str_replace( "]: ".$entry."\r\n", "]: ".$urlPages."\r\n", $page->content );
		}
		$page->content	= preg_replace_callback( "@(\[.+\])\((.+)\)@Us", [$this, '__callbackEncode'], $page->content );
*/
		/*  --  EVALUATE RENDERER  --  */
		$renderer			= $this->moduleConfig->get( 'renderer' );
		$markdownOnServer	= $this->env->getModules()->has( 'UI_Markdown' );
		$markdownOnClient	= $this->env->getModules()->has( 'JS_Markdown' );
		if( !$markdownOnServer && preg_match( "/^server/", $renderer ) )
			$renderer	= 'client';
		if( !$markdownOnClient && $renderer === 'client' )
			$this->messenger->noteFailure( 'No Markdown renderer installed.' );

		$this->addData( 'file', $page->title );
		$this->addData( 'files', $this->files );
		$this->addData( 'renderer', $renderer );
		$this->addData( 'content', $page->content );
		$this->addData( 'page', $page );
		$this->addData( 'categoryId', $page->manualCategoryId );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_manual.', TRUE );
		$this->docPath		= $this->moduleConfig->get( 'path' );
		$this->order		= new Dictionary();
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
		$orderFile	= $this->docPath.'order.list';
		if( file_exists( $this->docPath.'order.list' ) ){
			$order			= trim( FileReader::load( $orderFile ) );
			$this->order	= new Dictionary( explode( "\n", $order ) );
		}
		else if( count( $this->files ) ){
			$this->order	= new Dictionary( array_values( $this->files ) );
//			$this->saveOrder();
		}

		$this->addData( 'path', $this->docPath );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'files', $this->files );
		$this->addData( 'order', $this->order );
		$this->addData( 'rights', $this->rights );

		$this->categories	= [];
		$conditions			= ['status' => '>= '.Model_Manual_Category::STATUS_NEW];
		$orders				= ['rank' => 'ASC'];
		foreach( $this->modelCategory->getAll( $conditions, $orders ) as $category )
			$this->categories[$category->manualCategoryId]	= $category;
		if( !$this->categories ){
			$this->modelCategory->add( array(
				'creatorId'		=> (int) $this->userId,
				'status'		=> Model_Manual_Category::STATUS_NEW,
				'format'		=> Model_Manual_Category::FORMAT_TEXT,
				'version'		=> 1,
				'rank'			=> 1,
				'title'			=> 'Default',
				'content'		=> '',
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
			$this->restart( NULL, TRUE );
		}
		$sessionKeyCategoryId	= 'filter_info_manual_categoryId';
		if( !$this->session->get( $sessionKeyCategoryId ) ){
			$categories		= array_values( $this->categories );
			$category		= $categories[0];
			$this->session->set( $sessionKeyCategoryId, $category->manualCategoryId );
			$this->restartToCategory( $category );
		}
		$this->addData( 'categories', $this->categories );
		$this->addData( 'categoryId', $this->session->get( $sessionKeyCategoryId ) );
	}

	protected function __callbackEncode( array $matches ): string
	{
		if( preg_match( "/^[a-z]+:\/\//i", $matches[2] ) )
			return $matches[1].'('.$matches[2].')';
		if( preg_match( "/^\.\/info\/manual\/view\//i", $matches[2] ) ){
			$fileName	= str_replace( './info/manual/page/', '', $matches[2] );
			if( file_exists( $this->docPath.urldecode( $fileName ).$this->ext ) )
				return $matches[1].'('.'./info/manual/page/'.urlencode( $fileName ).')';
			return '<del>' .$matches[1].'('.'./info/manual/page/'.urlencode( $fileName ). ').</del>';
		}
		return '<del>' .$matches[1].'('.urlencode( $matches[2] ). ')</del>';
	}

	protected function checkCategoryId( $categoryId ): object
	{
		if( !strlen( trim( $categoryId ) ) )
			throw new InvalidArgumentException( 'No category ID given' );
		$category	= $this->modelCategory->get( $categoryId );
		if( !$category )
			throw new InvalidArgumentException( 'Invalid category ID given' );
		return $category;
	}

	protected function checkPageId( $pageId ): object
	{
		if( !strlen( trim( $pageId ) ) )
			throw new InvalidArgumentException( 'No page ID given' );
		$page	= $this->modelPage->get( (int) $pageId );
		if( !$page )
			throw new InvalidArgumentException( 'Invalid page ID given' );
		if( $page->manualCategoryId )
			$page->category	= $this->modelCategory->get( $page->manualCategoryId );
		return $page;
	}

	protected function relink( string $oldName, string $newName ): array
	{
		$list	= [];
		$this->scanFiles();
		foreach( $this->files as $entry ){
			$filePath	= $this->docPath.$entry;
			$content	= FileReader::load( $filePath );
			$relinked	= str_replace( "](".$oldName.")", "](".$newName.")", $content );
			$relinked	= str_replace( "]: ".$oldName."\r\n", "]: ".$newName."\r\n", $relinked );
			if( $relinked !== $content ){
				FileWriter::save( $filePath, $relinked );
				$list[]	= $entry;
			}
		}
		return $list;
	}

	protected function restartToCategory( $categoryOrId )
	{
		$this->restart( View_Helper_Info_Manual_Url::spawn( $this->env )
			->setCategory( $categoryOrId )
			->render() );
	}

	protected function restartToPage( $pageOrId )
	{
		$this->restart( View_Helper_Info_Manual_Url::spawn( $this->env )
			->setPage( $pageOrId )
			->render() );
	}

	protected function urlencode( string $name ): string
	{
		return urlencode( $name );
/*		$name	= rawurldecode( $name );
		$name	= str_replace( "%2F", "/", $name );
		$name	= str_replace( " ", "%20", $name );
		return $name;*/
	}
}
