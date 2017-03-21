<?php
class Controller_Manage_Catalog_Bookstore_Author extends CMF_Hydrogen_Controller{

	protected $frontend;
	protected $logic;
	protected $messenger;
	protected $request;
	protected $session;

	protected function __onInit(){
		$this->env->clock->profiler->tick( 'Controller_Manage_Catalog_Bookstore_Author::init start' );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->logic		= new Logic_Catalog_Bookstore( $this->env );
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'pathAuthors', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.authors' ) );
		$this->addData( 'pathCovers', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.covers' ) );
		$this->addData( 'pathDocuments', $this->frontend->getPath( 'contents' ).$this->moduleConfig->get( 'path.documents' ) );
		$this->env->clock->profiler->tick( 'Controller_Manage_Catalog_Bookstore_Author::init done' );
	}

	static public function ___onTinyMCE_getImageList( $env, $context, $module, $arguments = array() ){
		$cache		= $env->getCache();
		if( !( $list = $cache->get( 'catalog.tinymce.images.catalog.bookstore.authors' ) ) ){
			$logic		= new Logic_Catalog_Bookstore( $env );
			$frontend	= Logic_Frontend::getInstance( $env );
			$config		= $env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );				//  focus module configuration
			$pathImages	= $frontend->getPath( 'contents' ).$config->get( 'path.authors' );			//  get path to author images
			$pathImages	= substr( $pathImages, strlen( $frontend->getPath() ) );					//  strip frontend base path
			$list		= array();
			$authors	= $logic->getAuthors( array(), array( 'lastname' => 'ASC', 'firstname' => 'ASC' ) );
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
			$cache->set( 'catalog.tinymce.images.catalog.bookstore.authors', $list );
		}
		$context->list  = array_merge( $context->list, array( (object) array(	//  extend global collection by submenu with list of items
			'title'	=> 'Autoren:',												//  label of submenu @todo extract
			'menu'	=> array_values( $list ),								//  items of submenu
		) ) );
	}

	static public function ___onTinyMCE_getLinkList( $env, $context, $module, $arguments = array() ){
		$cache		= $env->getCache();
		if( !( $authors = $cache->get( 'catalog.tinymce.links.catalog.bookstore.authors' ) ) ){
			$logic		= new Logic_Catalog_Bookstore( $env );
			$config		= $env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );
			$authors	= $logic->getAuthors( array(), array( 'lastname' => 'ASC', 'firstname' => 'ASC' ) );
			foreach( $authors as $nr => $item ){
				$label		= ( $item->firstname ? $item->firstname.' ' : '' ).$item->lastname;
				$url		= $logic->getAuthorUri( $item );
				$authors[$nr] = (object) array( 'title' => $label, 'value' => $url );
			}
			$cache->set( 'catalog.tinymce.links.catalog.bookstore.authors', $authors );
		}
		$context->list  = array_merge( $context->list, array( (object) array(	//  extend global collection by submenu with list of items
			'title'	=> 'Autoren:',												//  label of submenu @todo extract
			'menu'	=> array_values( $authors ),								//  items of submenu
		) ) );
	}

	public function add(){
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
		$author		= array();
		foreach( $model->getColumns() as $column )
			$author[$column]	= $this->request->get( $column );
		$this->addData( 'author', (object) $author );
		$this->addData( 'authors', $this->logic->getAuthors() );
	}

	public function ajaxSetTab( $tabKey ){
		$this->session->set( 'manage.catalog.bookstore.author.tab', $tabKey );
		exit;
	}

	public function edit( $authorId ){
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

	public function index(){
#		if( !( $authors	= $this->env->getCache()->get( 'authors' ) ) ){
			$authors	= $this->logic->getAuthors();
#			$this->env->getCache()->set( 'authors', $authors );
#		}
		$this->addData( 'authors', $authors );
	}

	public function remove( $authorId ){
		$words	= $this->getWords( 'remove' );
		if( $this->logic->getArticlesFromAuthor( $authorId ) )
			$this->messenger->noteError( $words->msgErrorNotEmpty );
		else{
			$this->logic->removeAuthor( $authorId );
			$this->restart( 'manage/catalog/bookstore/author' );
		}
	}

	public function removeImage( $authorId ){
		$this->logic->removeAuthorImage( $authorId );
		$this->restart( 'manage/catalog/bookstore/author/edit/'.$authorId );
	}

	protected function uploadImage( $authorId, $file ){
		$words		= (object) $this->getWords( 'upload' );
		if( !isset( $file['name'] ) || empty( $file['name'] ) )
			return;
		if( $file['error']	!= 0 ){
			$handler	= new Net_HTTP_UploadErrorHandler();
			$handler->setMessages( $this->getWords( 'uploadErrors' ) );
			$this->messenger->noteError( $handler->getErrorMessage( $file['error'] ) );
			return FALSE;
		}

		/*  --  CHECK NEW IMAGE  --  */
		$info		= pathinfo( $file['name'] );
		$extension	= $info['extension'];
		$extensions	= array( 'jpe', 'jpeg', 'jpg', 'png', 'gif' );
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
?>
