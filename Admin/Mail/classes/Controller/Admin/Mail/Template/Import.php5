<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

class Controller_Admin_Mail_Template_Import extends Controller
{
	protected $messenger;
	protected $request;
	protected $modelTemplate;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env			Application Environment Object
	 *	@return		void
	 */
    public function __construct( Environment $env )
	{
		parent::__construct( $env, FALSE );
		$this->messenger			= $this->env->getMessenger();
		$this->request				= $this->env->getRequest();
		$this->modelTemplate		= $this->getModel( 'Mail_Template' );
	}

	public function index()
	{
		if( $this->request->getMethod()->isPost() ){
			$upload	= $this->env->getLogic()->upload;
			try{
				$upload->setUpload( $this->request->get( 'template' ) );
			}
			catch( Exception $e ){
				$this->messenger->noteFailure( $e->getMessage() );
				$this->restart( 'admin/mail/template' );
			}
			try{
//				$this->messenger->noteNotice( 'MIME: '.$upload->getMimeType() );
//				$upload->checkMimeType( 'application/json', TRUE  );
				$upload->checkSize( $upload->getMaxUploadSize(), TRUE  );
				$upload->checkVirus( TRUE );
				if( $upload->getError() )
					throw new RuntimeException( 'Upload failed' );
				$template	= json_decode( $upload->getContent() );
				if( !$template )
					throw new InvalidArgumentException( 'Uploaded file is not valid JSON' );
				if( empty( $template->type ) || $template->type !== 'mail-template' )
					throw new InvalidArgumentException( 'Uploaded file does not contain a template' );

				if( !empty( $template->version ) && $template->version == 2 )
					$data	= $this->getDataFromExportV2( $template );
				else if( empty( $template->entity ) )
					$data	= $this->getDataFromExportV1( $template );
				else{
					$this->messenger->noteError( 'File is not compatible' );
					$this->restart( 'admin/mail/template' );
				}
				$templateId	= $this->modelTemplate->add( $data, FALSE );
				$this->messenger->noteSuccess( 'Template imported as '.$title );
				$this->restart( 'admin/mail/template/edit/'.$templateId );
			}
			catch( Exception $e ){
				$helper		= new View_Helper_UploadError( $this->env );
				$message	= $helper->setUpload( $upload )->render();
				if( !$message )
					$message	= $e->getMessage();
				$this->messenger->noteError( $message );
			}
		}
		$this->restart( 'admin/mail/template' );
	}

	//  --  PROTECTED  --  //

	protected function getDataFromExportV1( $template )
	{
		$title		= $this->getNextTitle( $template->title );
		$data	= array(
			'status'		=> Model_Mail_Template::STATUS_IMPORTED,
			'title'			=> $title,
			'version'		=> $template->version,
			'language'		=> $template->language,
			'plain'			=> $template->contents->text,
			'html'			=> $template->contents->html,
			'css'			=> $template->contents->css,
			'styles'		=> $template->files->css ? json_encode( $template->files->css ) : NULL,
			'images'		=> $template->links->image ? json_encode( $template->links->image ) : NULL,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		);
		return $data;
	}

	protected function getDataFromExportV2( $template )
	{
		$entity		= $template->entity;
		$title		= $this->getNextTitle( $entity->title );
		$files		= array(
			'styles'	=> array(),
			'images'	=> array()
		);
		foreach( array_keys( $files ) as $topic ){
			foreach( $entity->files->$topic as $item ){
				if( !file_exists( $item->filePath )){
					new FS_Folder( dirname( $item->filePath ), TRUE );
					$file	= new FS_File( $item->filePath, TRUE );
					$file->setContent( base64_decode( $item->content ) );
				}
				$files[$topic][]	= $item->filePath;
			}
		}
		$data	= array(
			'status'		=> Model_Mail_Template::STATUS_IMPORTED,
			'title'			=> $title,
			'version'		=> $entity->version,
			'language'		=> $entity->language,
			'plain'			=> $entity->contents->text,
			'html'			=> $entity->contents->html,
			'css'			=> $entity->contents->css,
			'styles'		=> json_encode( $files['styles'] ),
			'images'		=> json_encode( $files['images'] ),
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		);
		return $data;
	}

	protected function getNextTitle( string $title ): string
	{
		$counter	= 0;
		$current	= $title;
		$date		= date( 'Y-m-d' );
		while( $this->modelTemplate->countByIndex( 'title', $current ) ){
			$suffix		= ' ('.$date.( $counter ? '-'.$counter : '' ).')';
			$current	= $title.$suffix;
			$counter++;
		}
		return $current;
	}
}
