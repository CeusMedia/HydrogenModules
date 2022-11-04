<?php

use CeusMedia\Common\Net\HTTP\UploadErrorHandler;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

class Controller_Manage_Catalog_Author extends Controller
{
	protected $frontend;
	protected $logic;
	protected $messenger;
	protected $request;
	protected $session;

	public static function ___onTinyMCE_getImageList( Environment $env, $context, $module, $arguments = [] )
	{
		$cache		= $env->getCache();
		if( !( $list = $cache->get( 'catalog.tinymce.images.authors' ) ) ){
			$logic		= new Logic_Catalog( $env );
			$frontend	= Logic_Frontend::getInstance( $env );
			$config		= $env->getConfig()->getAll( 'module.manage_catalog.', TRUE );				//  focus module configuration
			$pathImages	= $frontend->getPath( 'contents' ).$config->get( 'path.authors' );			//  get path to author images
			$pathImages	= substr( $pathImages, strlen( $frontend->getPath() ) );					//  strip frontend base path
			$list		= [];
			$authors	= $logic->getAuthors( [], ['lastname' => 'ASC', 'firstname' => 'ASC'] );
			foreach( $authors as $item ){
				if( $item->image ){
					$id		= str_pad( $item->authorId, 5, 0, STR_PAD_LEFT );
//					$label	= $item->lastname.( $item->firstname ? ', '.$item->firstname : "" );
					$label	= ( $item->firstname ? $item->firstname.' ' : '' ).$item->lastname;
					$list[] = (object) array(
						'title'	=> $label,
						'value'	=> $pathImages.$id.'_'.$item->image,
					);
				}
			}
			$cache->set( 'catalog.tinymce.images.authors', $list );
		}
		$context->list  = array_merge( $context->list, array( (object) array(	//  extend global collection by submenu with list of items
			'title'	=> 'Autoren:',												//  label of submenu @todo extract
			'menu'	=> array_values( $list ),								//  items of submenu
		) ) );
	}

	public static function ___onTinyMCE_getLinkList( Environment $env, $context, $module, $arguments = [] )
	{
		$cache		= $env->getCache();
		if( !( $authors = $cache->get( 'catalog.tinymce.links.authors' ) ) ){
			$logic		= new Logic_Catalog( $env );
			$config		= $env->getConfig()->getAll( 'module.manage_catalog.', TRUE );
			$authors	= $logic->getAuthors( [], ['lastname' => 'ASC', 'firstname' => 'ASC'] );
			foreach( $authors as $nr => $item ){
				$label		= ( $item->firstname ? $item->firstname.' ' : '' ).$item->lastname;
				$url		= $logic->getAuthorUri( $item );
				$authors[$nr] = (object) ['title' => $label, 'value' => $url];
			}
			$cache->set( 'catalog.tinymce.links.authors', $authors );
		}
		$context->list  = array_merge( $context->list, array( (object) array(	//  extend global collection by submenu with list of items
			'title'	=> 'Autoren:',												//  label of submenu @todo extract
			'menu'	=> array_values( $authors ),								//  items of submenu
		) ) );
	}

	public function add()
	{
		if( $this->request->has( 'save' ) ){
			$words	= (object) $this->getWords( 'add' );
			$data	= $this->request->getAll();
			if( !strlen( $data['lastname'] ) )
				$this->messenger->noteError( $words->msgErrorLastnameMissing );
			else{
				$authorId	= $this->logic->addAuthor( $data );
				$this->restart( 'manage/catalog/author/edit/'.$authorId );
			}
		}
		$model		= new Model_Catalog_Author( $this->env );
		$author		= [];
		foreach( $model->getColumns() as $column )
			$author[$column]	= $this->request->get( $column );
		$this->addData( 'author', (object) $author );
		$this->addData( 'authors', $this->logic->getAuthors() );
	}

	public function ajaxSetTab( $tabKey )
	{
		$this->session->set( 'manage.catalog.author.tab', $tabKey );
		exit;
	}

	public function edit( $authorId )
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
				$this->restart( 'manage/catalog/author/edit/'.$authorId );
			}
		}
		$author		= $this->logic->getAuthor( $authorId );
		$this->addData( 'author', $author );
		$this->addData( 'authors', $this->logic->getAuthors() );
		$this->addData( 'articles', $this->logic->getArticlesFromAuthor( $author ) );
	}

	public function index()
	{
#		if( !( $authors	= $this->env->getCache()->get( 'authors' ) ) ){
			$authors	= $this->logic->getAuthors();
#			$this->env->getCache()->set( 'authors', $authors );
#		}
		$this->addData( 'authors', $authors );
	}

	public function remove( $authorId )
	{
		$words	= $this->getWords( 'remove' );
		if( $this->logic->getArticlesFromAuthor( $authorId ) )
			$this->messenger->noteError( $words->msgErrorNotEmpty );
		else{
			$this->logic->removeAuthor( $authorId );
			$this->restart( 'manage/catalog/author' );
		}
	}

	public function removeImage( $authorId )
	{
		$this->logic->removeAuthorImage( $authorId );
		$this->restart( 'manage/catalog/author/edit/'.$authorId );
	}

	protected function __onInit()
	{
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Author::init start' );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->logic		= new Logic_Catalog( $this->env );
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_catalog.', TRUE );
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'pathAuthors', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.authors' ) );
		$this->addData( 'pathCovers', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.covers' ) );
		$this->addData( 'pathDocuments', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.documents' ) );
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Author::init done' );
	}

	protected function uploadImage( $authorId, $file )
	{
		$words		= (object) $this->getWords( 'upload' );
		if( !isset( $file['name'] ) || empty( $file['name'] ) )
			return;
		if( $file['error']	!= 0 ){
			$handler	= new UploadErrorHandler();
			$handler->setMessages( $this->getWords( 'uploadErrors' ) );
			$this->messenger->noteError( $handler->getErrorMessage( $file['error'] ) );
			return FALSE;
		}

		/*  --  CHECK NEW IMAGE  --  */
		$info		= pathinfo( $file['name'] );
		$extension	= $info['extension'];
		$extensions	= ['jpe', 'jpeg', 'jpg', 'png', 'gif'];
		if( !in_array( strtolower( $extension ), $extensions ) ){
			$this->messenger->noteError( $words->msgErrorExtensionInvalid );
			return FALSE;
		}

		try{
			$this->logic->removeAuthorImage( $authorId );											//  remove older image if set
			$this->logic->addAuthorImage( $authorId, $file );										//  set newer image
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $words->msgErrorUpload, $e->getMessage() );
		}
	}
}
