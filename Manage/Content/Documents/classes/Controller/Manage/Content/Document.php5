<?php
class Controller_Manage_Content_Document extends CMF_Hydrogen_Controller{

	protected $frontend;
	protected $moduleConfig;
	protected $path;
	protected $model;
	protected $rights;

	public function __onInit(){
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( "module.manage_content_documents.", TRUE );
		$this->path			= $this->frontend->getPath().$this->moduleConfig->get( 'path.documents' );
		$this->messenger	= $this->env->getMessenger();

		$words				= (object) $this->getWords( 'msg' );
		if( !$this->path ){
			$this->messenger->noteFailure( $words->failureNoPathSet );
			$this->restart();
		}
		if( !file_exists( $this->path ) || !is_dir( $this->path ) ){
			$this->messenger->noteFailure( $words->failurePathNotExisting, $this->path );
			$this->restart();
		}
		if( !is_writable( $this->path ) ){
			$this->messenger->noteFailure( $words->failurePathNotWritable, $this->path );
			$this->restart();
		}
		$this->model	= new Model_Document( $this->env, $this->path );
		$this->rights	= $this->env->getAcl()->index( 'manage/content/document' );
		$this->addData( 'rights', $this->rights );
	}

	public static function ___onRegisterHints( $env, $context, $module, $arguments = NULL ){
		$words	= $env->getLanguage()->getWords( 'manage/content/document' );
		View_Helper_Hint::registerHints( $words['hints'], 'Manage_Content_Documents' );
	}

	static public function ___onTinyMCE_getLinkList( $env, $context, $module, $arguments = array() ){
		$frontend		= Logic_Frontend::getInstance( $env );
		$moduleConfig	= $env->getConfig()->getAll( "module.manage_content_documents.", TRUE );
		$pathFront		= $frontend->getPath();
		$pathDocuments	= $moduleConfig->get( 'path.documents' );

		$words			= $env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes		= (object) $words['link-prefixes'];

		$list			= array();
		if( file_exists( $pathFront ) && is_dir( $pathFront ) ){
			$model			= new Model_Document( $env, $pathFront.$pathDocuments );
			foreach( $model->index() as $nr => $entry ){
				$list[$entry.$nr]	= (object) array(
					'title'	=> /*$prefixes->document.*/$entry,
					'value'	=> $pathDocuments.$entry,
				);
			}
		}
		ksort( $list );
		$list	= array( (object) array(
			'title'	=> $prefixes->document,
			'menu'	=> array_values( $list ),
		) );

//		$context->list	= array_merge( $context->list, array_values( $list ) );
		$context->list	= array_merge( $context->list, $list );
	}

	public function add(){
		if( !in_array( 'add', $this->rights ) )
			$this->restart( NULL, TRUE );
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		if( $request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'msg' );
			$upload		= $request->get( 'upload' );
			$filename	= $request->get( 'filename' );

			if( $request->get( 'upload' ) ){
				if( $filename )
					$upload['name']	= $filename;
				try{
					$logicUpload	= new Logic_Upload( $this->env );
					$logicUpload->setUpload( $upload );
					$logicUpload->sanitizeFileName();
					$logicUpload->checkVirus( TRUE );
					if( $logicUpload->getError() ){
						$helper	= new View_Helper_UploadError( $this->env );
						$helper->setUpload( $logicUpload );
						$messenger->noteError( $helper->render() );
					}
					else{
						if( $filename )
							unlink( $this->path.$filename );
						$filename	= $logicUpload->getFileName();
						$logicUpload->saveTo( $this->path.$filename );
						$messenger->noteSuccess( $words->successDocumentUploaded, $filename );
					}
				}
				catch( Exception $e ){
					$messenger->noteFailure( $words->errorUploadFailed );
				}
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function index(){
		if( !in_array( 'index', $this->rights ) )
			$this->restart();
		$this->addData( 'frontendPath', $this->frontend->getPath() );
		$this->addData( 'frontendUrl', $this->frontend->getUri() );
		$this->addData( 'pathDocuments', $this->moduleConfig->get( 'path.documents' ) );
		$this->addData( 'documents', $this->model->index() );
	}

	public function rename( $documentId ){
		$document	= $this->env->getRequest()->get( 'document' );
		$name		= $this->env->getRequest()->get( 'name' );
		$path		= $this->moduleConfig->get( 'path.documents' );
		if( !file_exists( $path.$document ) ){
			$this->messenger->noteError( "Dokument nicht gefunden." );
			$this->restart( NULL, TRUE );
		}
		rename( $path.$document, str_replace( " ", "_", $name ) );
		$this->restart( NULL, TRUE );
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
