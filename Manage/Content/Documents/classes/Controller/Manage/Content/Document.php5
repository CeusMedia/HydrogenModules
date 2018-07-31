<?php
class Controller_Manage_Content_Document extends CMF_Hydrogen_Controller{

	protected $frontend;
	protected $moduleConfig;
	protected $path;
	protected $model;
	protected $rights;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( "module.manage_content_documents.", TRUE );
		$this->path			= $this->frontend->getPath().$this->moduleConfig->get( 'path.documents' );

		$words				= (object) $this->getWords( 'msg' );
		if( !$this->path ){
			$this->messenger->noteFailure( $words->failureNoPathSet );
			$this->restart();
		}
		if( !file_exists( $this->path ) || !is_dir( $this->path ) )
			mkdir( $this->path, 0777, TRUE );
		if( !is_writable( $this->path ) ){
			$this->messenger->noteFailure( $words->failurePathNotWritable, $this->path );
			$this->restart();
		}
//		if( !file_exists( $this->path.'.htaccess' ) )
//			file_put_contents( $this->path.'.htaccess', 'Deny from all'.PHP_EOL );

		$this->model	= new Model_Document( $this->env, $this->path );
		$this->rights	= $this->env->getAcl()->index( 'manage/content/document' );
		$this->addData( 'rights', $this->rights );
	}

	public static function ___onRegisterHints( CMF_Hydrogen_Environment $env, $context, $module, $arguments = NULL ){
		$words	= $env->getLanguage()->getWords( 'manage/content/document' );
		View_Helper_Hint::registerHints( $words['hints'], 'Manage_Content_Documents' );
	}

	static public function ___onTinyMCE_getLinkList( CMF_Hydrogen_Environment $env, $context, $module, $arguments = array() ){
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
		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'msg' );
			$upload		= $this->request->get( 'upload' );
			$filename	= $this->request->get( 'filename' );

			if( $this->request->get( 'upload' ) ){
				if( $filename )
					$upload['name']	= $filename;
				try{
					$logicUpload	= new Logic_Upload( $this->env );
					$logicUpload->setUpload( $upload );
					$logicUpload->sanitizeFileName();
					$logicUpload->checkSize( Logic_Upload::getMaxUploadSize() );
					$logicUpload->checkVirus( !TRUE );
					if( $logicUpload->getError() ){
						$helper	= new View_Helper_UploadError( $this->env );
						$helper->setUpload( $logicUpload );
						$this->messenger->noteError( $helper->render() );
					}
					else{
						if( $filename )
							unlink( $this->path.$filename );
						$filename	= $logicUpload->getFileName();
						$logicUpload->saveTo( $this->path.$filename );
						$this->messenger->noteSuccess( $words->successDocumentUploaded, $filename );
					}
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( $words->errorUploadFailed );
				}
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0, $limit = 15 ){
		if( !in_array( 'index', $this->rights ) )
			$this->restart();
		$this->addData( 'frontendPath', $this->frontend->getPath() );
		$this->addData( 'frontendUrl', $this->frontend->getUri() );
		$this->addData( 'pathDocuments', $this->moduleConfig->get( 'path.documents' ) );
		$this->addData( 'documents', $this->model->index( $limit, $page * $limit ) );
		$this->addData( 'total', $this->model->count() );
		$this->addData( 'page', $page );
		$this->addData( 'limit', $limit );
	}

	public function rename( $documentId ){
		$document	= $this->request->get( 'document' );
		$name		= $this->request->get( 'name' );
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
		$document	= base64_decode( $this->request->get( 'documentId' ) );
		if( file_exists( $this->path.$document ) )
			unlink( $this->path.$document );
		if( ( $page = $this->request->get( 'page' ) ) )
			$this->restart( $page, TRUE );
		$this->restart( NULL, TRUE );
	}
}
?>
