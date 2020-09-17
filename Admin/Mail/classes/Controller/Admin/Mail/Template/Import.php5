<?php
class Controller_Admin_Mail_Template_Import extends CMF_Hydrogen_Controller
{
	public function index()
	{
		if( $this->request->getMethod()->isPost() ){
			$upload	= $this->env->getLogic()->upload;
			try{
				$upload->setUpload( $this->request->get( 'template' ) );
			}
			catch( Exception $e ){
				$this->messenger->noteFailure( $e->getMessage() );
				$this->restart( NULL, TRUE );
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
				if( empty( $template->entity ) ){
					$title		= $template->title;
					$counter	= 0;
					while( $this->modelTemplate->countByIndex( 'title', $title ) ){
						$suffix	= ' ('.date( 'Y-m-d' ).( $counter ? '-'.$counter : '' ).')';
						$title	= $template->title.$suffix;
						$counter++;
					}
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
				}
				else if( !empty( $template->version ) && $template->version == 2 ){
					$entity		= $template->entity;
					$title		= $entity->title;
					$counter	= 0;
					while( $this->modelTemplate->countByIndex( 'title', $title ) ){
						$suffix	= ' ('.date( 'Y-m-d' ).( $counter ? '-'.$counter : '' ).')';
						$title	= $entity->title.$suffix;
						$counter++;
					}
					$files	= array( 'styles' => array(), 'images' => array() );
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
				}
				else{
					$this->messenger->noteError( 'File is not compatible' );
					$this->restart( NULL, TRUE );
				}
				$templateId	= $this->modelTemplate->add( $data, FALSE );
				$this->messenger->noteSuccess( 'Template imported as '.$title );
				$this->restart( 'edit/'.$templateId, TRUE );
			}
			catch( Exception $e ){
				$helper		= new View_Helper_UploadError( $this->env );
				$message	= $helper->setUpload( $upload )->render();
				if( !$message )
					$message	= $e->getMessage();
				$this->messenger->noteError( $message );
			}
		}
		$this->restart( NULL, TRUE );
	}
}
