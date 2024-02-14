<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\Common\FS\File;
use CeusMedia\Common\FS\Folder;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class Controller_Admin_Mail_Template_Import extends Controller
{
	protected MessengerResource $messenger;
	protected Dictionary $request;
	protected Model_Mail_Template $modelTemplate;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		WebEnvironment		$env			Application Environment Object
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function __construct( WebEnvironment $env )
	{
		parent::__construct( $env, FALSE );
		$this->messenger			= $this->env->getMessenger();
		$this->request				= $this->env->getRequest();
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->modelTemplate		= $this->getModel( 'Mail_Template' );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index(): void
	{
		if( $this->request->getMethod()->isPost() ){
			$upload	= $this->env->getLogic()->get( 'upload' );
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
//				$this->messenger->noteSuccess( 'Template imported as '.$title );
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

	protected function getDataFromExportV1( object $template ): array
	{
		$title		= $this->getNextTitle( $template->title );
		return [
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
		];
	}

	/**
	 *	@param		object		$template
	 *	@return		array
	 *	@throws		IoException
	 */
	protected function getDataFromExportV2( object $template ): array
	{
		$entity		= $template->entity;
		$title		= $this->getNextTitle( $entity->title );
		$files		= [
			'styles'	=> [],
			'images'	=> []
		];
		foreach( array_keys( $files ) as $topic ){
			foreach( $entity->files->$topic as $item ){
				if( !file_exists( $item->filePath )){
					new Folder( dirname( $item->filePath ), TRUE );
					$file	= new File( $item->filePath, TRUE );
					$file->setContent( base64_decode( $item->content ) );
				}
				$files[$topic][]	= $item->filePath;
			}
		}
		return [
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
		];
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
