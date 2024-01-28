<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Catalog_Bookstore_Author extends Controller
{
	protected Logic_Frontend $frontend;
	protected Logic_Catalog_BookstoreManager $logic;
	protected MessengerResource $messenger;
	protected Dictionary $request;
	protected Dictionary $session;

	public static function ___onTinyMCE_getImageList( Environment $env, object $context, object $module, array & $payload ): void
	{
		$cache		= $env->getCache();
		if( !( $list = $cache->get( 'catalog.tinymce.images.catalog.bookstore.authors' ) ) ){
			$logic		= new Logic_Catalog_BookstoreManager( $env );
			$frontend	= Logic_Frontend::getInstance( $env );
			$config		= $env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );				//  focus module configuration
			$pathImages	= $frontend->getPath( 'contents' ).$config->get( 'path.authors' );			//  get path to author images
			$pathImages	= substr( $pathImages, strlen( $frontend->getPath() ) );					//  strip frontend base path
			$list		= [];
			$authors	= $logic->getAuthors( [], ['lastname' => 'ASC', 'firstname' => 'ASC'] );
			foreach( $authors as $item ){
				if( $item->image ){
					$id		= str_pad( $item->authorId, 5, 0, STR_PAD_LEFT );
//					$label	= $item->lastname.( $item->firstname ? ', '.$item->firstname : "" );
					$label	= ( $item->firstname ? $item->firstname.' ' : '' ).$item->lastname;
					$list[] = (object) [
						'title'	=> $label,
//						'value'	=> $pathImages.$id.'_'.$item->image,
						'value'	=> 'file/bookstore/author/'.$item->image,
					];
				}
			}
			$cache->set( 'catalog.tinymce.images.catalog.bookstore.authors', $list );
		}
		$context->list	= array_merge( $context->list, [(object) [				//  extend global collection by submenu with list of items
			'title'	=> 'Autoren:',												//  label of submenu @todo extract
			'menu'	=> array_values( $list ),									//  items of submenu
		]] );
	}

	public static function ___onTinyMCE_getLinkList( Environment $env, object $context, object $module, array & $payload ): void
	{
		$cache		= $env->getCache();
		if( !( $authors = $cache->get( 'catalog.tinymce.links.catalog.bookstore.authors' ) ) ){
			$logic		= new Logic_Catalog_BookstoreManager( $env );
			$config		= $env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );
			$authors	= $logic->getAuthors( [], ['lastname' => 'ASC', 'firstname' => 'ASC'] );
			foreach( $authors as $nr => $item ){
				$label		= ( $item->firstname ? $item->firstname.' ' : '' ).$item->lastname;
				$url		= $logic->getAuthorUri( $item );
				$authors[$nr] = (object) ['title' => $label, 'value' => $url];
			}
			$cache->set( 'catalog.tinymce.links.catalog.bookstore.authors', $authors );
		}
		$words	= $env->getLanguage()->getWords( 'manage/catalog/bookstore' );
		$context->list  = array_merge( $context->list, [(object) [	//  extend global collection by submenu with list of items
			'title'	=> $words['tinymce-menu-links']['authors'],					//  label of submenu
			'menu'	=> array_values( $authors ),								//  items of submenu
		]] );
	}

	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$words	= (object) $this->getWords( 'add' );
			$data	= $this->request->getAll();
			if( !strlen( $data['lastname'] ) )
				$this->messenger->noteError( $words->msgErrorLastnameMissing );
			else{
				$authorId	= $this->logic->addAuthor( $data );
				$this->restart( 'manage/catalog/bookstore/author/edit/'.$authorId );
			}
		}
		$model		= new Model_Catalog_Bookstore_Author( $this->env );
		$author		= [];
		foreach( $model->getColumns() as $column )
			$author[$column]	= $this->request->get( $column );
		$this->addData( 'author', (object) $author );
		$this->addData( 'authors', $this->logic->getAuthors() );
	}

	public function ajaxSetTab( string $tabKey ): void
	{
		$this->session->set( 'manage.catalog.bookstore.author.tab', $tabKey );
		exit;
	}

	public function edit( string $authorId ): void
	{
		if( $this->request->has( 'save' ) ){
			$words	= (object) $this->getWords( 'edit' );
			$data	= $this->request->getAll();
			if( !strlen( $data['lastname'] ) )
				$this->messenger->noteError( $words->msgErrorLastnameMissing );
			else{
				$this->uploadImage( $authorId, $this->request->get( 'image' ) );
				unset( $data['image'] );
				$this->logic->editAuthor( $authorId, $data );
				$this->restart( 'manage/catalog/bookstore/author/edit/'.$authorId );
			}
		}
		$author		= $this->logic->getAuthor( $authorId );
		$this->addData( 'author', $author );
		$this->addData( 'authors', $this->logic->getAuthors() );
		$this->addData( 'articles', $this->logic->getArticlesFromAuthor( $author ) );
	}

	public function index(): void
	{
#		if( !( $authors	= $this->env->getCache()->get( 'authors' ) ) ){
			$authors	= $this->logic->getAuthors();
#			$this->env->getCache()->set( 'authors', $authors );
#		}
		$this->addData( 'authors', $authors );
	}

	public function remove( string $authorId ): void
	{
		$words	= $this->getWords( 'remove' );
		if( $this->logic->getArticlesFromAuthor( $authorId ) )
			$this->messenger->noteError( $words->msgErrorNotEmpty );
		else{
			$this->logic->removeAuthor( $authorId );
			$this->restart( 'manage/catalog/bookstore/author' );
		}
	}

	public function removeImage( string $authorId ): void
	{
		$this->logic->removeAuthorImage( $authorId );
		$this->restart( 'manage/catalog/bookstore/author/edit/'.$authorId );
	}

	protected function __onInit(): void
	{
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Bookstore_Author::init start' );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->logic		= new Logic_Catalog_BookstoreManager( $this->env );
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'pathAuthors', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.authors' ) );
		$this->addData( 'pathCovers', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.covers' ) );
		$this->addData( 'pathDocuments', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.documents' ) );
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Bookstore_Author::init done' );
	}

	protected function uploadImage( string $authorId, array $file ): void
	{
		$words		= (object) $this->getWords( 'upload' );
		if( !isset( $file['name'] ) || empty( $file['name'] ) )
			return;

		$extensions	= $this->moduleConfig->get( 'author.image.extensions' );
		$logic		= new Logic_Upload( $this->env );
		try{
			$logic->setUpload( $file );
			$logic->checkExtension( preg_split( '/\s*,\s*/', $extensions ), TRUE );
			$logic->checkIsImage( TRUE );
//			$logic->checkSize( $this->moduleConfig->get( 'article.image.size' )."M", TRUE );
//			$logic->sanitizeFileName();
			if( $logic->getError() ){
				$helper	= new View_Helper_UploadError( $this->env );
				$helper->setUpload( $logic );
				$this->messenger->noteError( $helper->render() );
			}
			else{
				$targetFile		= uniqid().'.'.$logic->getExtension( TRUE );
				$logic->saveTo( $targetFile );
				$this->logic->removeAuthorImage( $authorId );										//  remove older image if set
				$this->logic->addAuthorImage( $authorId, $targetFile, $logic->getMimeType() );		//  set newer image
				@unlink( $targetFile );																//  remove original
			}
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $words->msgErrorUpload, $e->getMessage() );
		}
	}
}
