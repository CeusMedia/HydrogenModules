<?php
class Controller_Manage_Content_Document extends CMF_Hydrogen_Controller{

	protected $path;
	
	public function __onInit(){
		$config			= $this->env->getConfig()->getAll( "module.manage_content_documents.", TRUE );
		$this->path		= $config->get( 'frontend.path' ).$config->get( 'path.documents' );
		if( !$this->path )
			throw new RuntimeException( 'No document path set in module configuration' );
#		$words			= $this->getWords( "exceptions", "manage/content/documents" );
		if( !file_exists( $this->path ) || !is_dir( $this->path ) )
			throw new RuntimeException( 'Documents folder "'.$this->path.'" is not existing' );
		if( !is_writable( $this->path ) )
			throw new RuntimeException( 'Documents folder "'.$this->path.'" is not writable' );
		$this->model	= new Model_Document( $this->env, $this->path );
		$this->rights	= $this->env->getAcl()->index( 'manage/content/document' );
		$this->addData( 'rights', $this->rights );
	}

	public static function ___onRegisterHints( $env, $context, $module, $arguments = NULL ){
		$words	= $env->getLanguage()->getWords( 'manage/content/document' );
		View_Helper_Hint::registerHints( $words['hints'], 'Manage_Content_Documents' );
	}

	static public function ___onTinyMCE_getLinkList( $env, $context, $module, $arguments = array() ){
		$config			= $env->getConfig()->getAll( 'module.manage_content_documents.', TRUE );
		$pathFront		= $config->get( 'frontend.path' );
		$pathDocuments	= $config->get( 'path.documents' );

		$words			= $env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes		= (object) $words['link-prefixes'];

		$list			= array();
		$model			= new Model_Document( $env, $pathFront.$pathDocuments );
		foreach( $model->index() as $nr => $entry ){
			$list[$entry.$nr]	= (object) array(
				'title'	=> $prefixes->document.$entry,
				'url'	=> $pathDocuments.$entry,
			);
		}
		ksort( $list );
		$context->list	= array_merge( $context->list, array_values( $list ) );
	}
	
	public function add(){
		if( !in_array( 'add', $this->rights ) )
			$this->restart( NULL, TRUE );
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		if( $request->has( 'save' ) ){
			$upload	= (object) $request->get( 'upload' );
			if( $upload->error ){
                $handler    = new Net_HTTP_UploadErrorHandler();
                $handler->setMessages( $this->getWords( 'msgErrorUpload' ) );
				$messenger->noteError( $handler->getErrorMessage( $upload->error ) );
			}
			else{
				if( !@move_uploaded_file( $upload->tmp_name, $this->path.$upload->name ) )
					$messenger->noteFailure( 'Moving uploaded file to documents folder failed' );
				else
					$messenger->noteSuccess( 'Datei "%s" hochgeladen.', $upload->name );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function index(){
		if( !in_array( 'index', $this->rights ) )
			$this->restart();
		$config		= $this->env->getConfig()->getAll( "module.manage_content_documents.", TRUE );
		$this->addData( 'frontendPath', $config->get( 'frontend.path' ) );
		$this->addData( 'frontendUrl', $config->get( 'frontend.url' ) );
		$this->addData( 'pathDocuments', $config->get( 'path.documents' ) );
		$this->addData( 'documents', $this->model->index() );
	}

	public function remove(){
		if( !in_array( 'remove', $this->rights ) )
			$this->restart( NULL, TRUE );
		$document	= base64_decode( $this->env->getRequest()->get( 'documentId' ) );
		if( file_exists( $this->path.$document ) )
			unlink( $this->path.$document );
		$this->restart( NULL, TRUE );
	}
}
?>
