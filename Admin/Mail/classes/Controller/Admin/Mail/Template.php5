<?php
class Controller_Admin_Mail_Template extends CMF_Hydrogen_Controller{

	protected $request;
	protected $messenger;
	protected $modelTemplate;
	protected $appPath;
	protected $appUrl;

	public function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->modelTemplate	= new Model_Mail_Template( $this->env );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend	= Logic_Frontend::getInstance( $this->env );
			$this->appPath	= $frontend->getPath();
			$this->appUrl	= $frontend->getUri();
		}
		else{
			$this->appPath	= $this->env->path;
			$this->appUrl	= $this->env->url;
		}
		$this->addData( 'appPath', $this->appPath );
		$this->addData( 'appUrl', $this->appUrl );
		$logicMail	= Logic_Mail::getInstance( $this->env );
	}

	public function ajaxSetTab( $tabId ){
		if( strlen( trim( $tabId ) ) && $tabId != "undefined" )
			$this->env->getSession()->set( 'admin-mail-template-edit-tab', $tabId );
		exit;
	}

	public function ajaxRender( $templateId ){
		$template	= $this->checkTemplate( $templateId );
		$mail		= new Mail_Test( $this->env, array( 'mailTemplateId' => $templateId ) );
		$helper		= new View_Helper_Mail_View_HTML( $this->env );
		$helper->setMailObjectInstance( $mail );
		$html		= $helper->render();
		print( $html );
		exit;
	}

	public function ajaxSaveHtml( $templateId ){
		$content	= $this->env->getRequest()->get( 'content' );
		$this->modelTemplate->edit( $templateId, array(
			'html'			=> trim( $content ),
			'modifiedAt'	=> time(),
		), FALSE );
		print( json_encode( TRUE ) );
		exit;
	}

	public function ajaxSavePlain( $templateId ){
		$content	= $this->env->getRequest()->get( 'content' );
		$this->modelTemplate->edit( $templateId, array(
			'plain'			=> trim( $content ),
			'modifiedAt'	=> time(),
		), FALSE );
		print( json_encode( TRUE ) );
		exit;
	}

	public function ajaxSaveCss( $templateId ){
		$content	= $this->env->getRequest()->get( 'content' );
		$this->modelTemplate->edit( $templateId, array(
			'css'			=> trim( $content ),
			'modifiedAt'	=> time(),
		), FALSE );
		print( json_encode( TRUE ) );
		exit;
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$title		= strip_tags( trim( $this->request->get( 'template_title' ) ) );
			$found		= $this->modelTemplate->getByIndices( array(
				'title'				=> $title,
			) );

			if( !trim( $title ) )
				$this->messenger->noteError( 'No title given.' );
			else if( $found )
				$this->messenger->noteError( 'Template with this title already existing.' );
			else{
				$templateId	= $this->modelTemplate->add( array(
					'status'		=> Model_Mail_Template::STATUS_NEW,
					'title'			=> $title,
					'language'		=> $this->request->get( 'template_language' ),
					'plain'			=> strip_tags( $this->request->get( 'template_plain' ) ),
					'html'			=> trim( $this->request->get( 'template_html' ) ),
					'css'			=> trim( $this->request->get( 'template_css' ) ),
					'createdAt'		=> time(),
					'modifiedAt'	=> time(),
				), FALSE );
				$this->messenger->noteSuccess( 'Template added.' );
				$this->restart( './admin/mail/template/edit/'.$templateId );
			}
		}
		$data	= (object) array();
		foreach( $this->modelTemplate->getColumns() as $key ){
			if( !in_array( $key, array( 'mailTemplateId' ) ) ){
				$data->$key		= '';
				if( $this->request->has( 'template_'.$key ) ){
					$value	= trim( $this->request->get( 'template_'.$key ) );
					if( strlen( $value ) )
						$data->$key = $value;
				}
			}
		}
		$this->addData( 'template', $data );
	}

	protected function checkTemplate( $templateId, $strict = TRUE ){
		$template	= $this->modelTemplate->get( $templateId );
		if( $template )
			return $template;
		if( $strict )
			throw new RangeException( 'Invalid template ID' );
		return FALSE;
	}

	public function copy( $templateId ){
		if( $this->request->isPost() ){
			$title	= trim( $this->request->get( 'title' ) );
			$exists	= $this->modelTemplate->getByIndex( 'title', $title );
			if( $exists ){
				$this->messenger->noteError( 'Dieser Titel ist bereits vergeben.' );
				$this->restart( 'edit/'.$templateId, TRUE );
			}
			$template	= $this->modelTemplate->get( $templateId );
			$templateId	= $this->modelTemplate->add( array(
				'status'		=> Model_Mail_Template::STATUS_NEW,
				'language'		=> $template->language,
				'title'			=> $title,
				'plain'			=> $template->plain,
				'html'			=> $template->html,
				'css'			=> $template->css,
				'styles'		=> $template->styles,
				'images'		=> $template->images,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			), FALSE );
			$this->messenger->noteSuccess( 'Vorlage "'.$template->title.'" nach "'.$title.'" kopiert.' );
		}
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	public function edit( $templateId ){
		$modelMail		= new Model_Mail( $this->env );
		$template		= $this->checkTemplate( $templateId );
		$template->used	= $modelMail->countByIndex( 'mailTemplateId', $templateId );
		$tabId			= $this->env->getSession()->get( 'admin-mail-template-edit-tab' );
		$this->addData( 'tabId', $tabId );

		if( $this->request->has( 'save' ) ){
			if( strlen( $this->request->get( 'template_title' ) ) ){
				$title		= strip_tags( trim( $this->request->get( 'template_title' ) ) );
				$found		= $this->modelTemplate->getByIndices( array(
					'title'				=> $title,
					'mailTemplateId'	=> '!= '.$templateId
				) );
				if( !trim( $title ) )
					$this->messenger->noteError( 'No title given.' );
				else if( $found )
					$this->messenger->noteError( 'Template with this title already existing.' );
				else{
					$this->modelTemplate->edit( $templateId, array(
						'title'			=> $title,
						'language'		=> $this->request->get( 'language' ),
	//					'plain'			=> strip_tags( $this->request->get( 'template_plain' ) ),
	//					'html'			=> trim( $this->request->get( 'template_html' ) ),
	//					'css'			=> trim( $this->request->get( 'template_css' ) ),
						'modifiedAt'	=> time(),
					), FALSE );
				}
				$this->messenger->noteSuccess( 'Template information saved.' );
				$this->restart( './admin/mail/template/edit/'.$templateId );
			}
			else if( $this->request->get( 'template_style' ) ){
				$template	= $this->modelTemplate->get( $templateId );
				if( strlen( trim( $template->styles ) ) && preg_match( "/^[a-z0-9]", $template->styles ) )
					$template->styles	= json_encode( explode( ",", $template->styles ) );
				$list		= trim( $template->styles ) ? json_decode( $template->styles, TRUE ) : array();
				$list[]		= trim( $this->request->get( 'template_style' ) );
				$this->modelTemplate->edit( $templateId, array(
					'styles'	=> json_encode( $list )
				) );
				$this->restart( './admin/mail/template/edit/'.$templateId );
			}
			else if( $this->request->get( 'template_image' ) ){
				$template	= $this->modelTemplate->get( $templateId );
				if( strlen( trim( $template->images ) ) && preg_match( "/^[a-z0-9]", $template->images ) )
					$template->images	= json_encode( explode( ",", $template->images ) );
				$list		= trim( $template->images ) ? json_decode( $template->images, TRUE ) : array();
				$list[]		= trim( $this->request->get( 'template_image' ) );
				$this->modelTemplate->edit( $templateId, array(
					'images'	=> json_encode( $list )
				) );
				$this->restart( './admin/mail/template/edit/'.$templateId );
			}
			foreach( $this->modelTemplate->getColumns() as $key ){
				if( !in_array( $key, array( 'mailTemplateId' ) ) ){
					if( $this->request->has( 'template_'.$key ) ){
						$value	= trim( $this->request->get( 'template_'.$key ) );
						if( strlen( $value ) )
							$template->$key = $value;
					}
				}
			}
		}
		$this->addData( 'template', $template );
	}

	/**
	 *	Export mail template as JSON.
	 *	Will provide file download by default.
	 *	@access		public
	 *	@param		integer		$templateId		ID of template to export
	 *	@return		void
	 */
	public function export( $templateId, $output = 'download' ){
		$template	= $this->checkTemplate( $templateId );
//print_m( $template );

		$files	= array( 'styles' => array(), 'images' => array() );
		foreach( array_keys( $files ) as $topic ){
			if( $template->{$topic}[0] === '[' )
				$source	= json_decode( $template->$topic );
			else
				$source	= preg_split( '/\s*,\s*/', $template->$topic );

			foreach( $source as $item ){
				if( !file_exists( $item ) ){
					$this->messenger->noteError( 'File is missing: '.$item );
					continue;
				}
				$files[$topic][]	= array(
					'filePath'	=> $item,
					'content'	=> base64_encode( FS_File_Reader::load( $item ) ),
				);
			}
		}

		$title		= new ADT_String( $template->title );
		$titleKey	= $title->hyphenate();											//  preserve whitespace in title as hyphen

		$data	= array(
			'type'		=> 'mail-template',
			'version'	=> 2,
			'entity'	=> array(
				'title'		=> $template->title,
				'key'		=> $titleKey,
				'version'	=> '0',
				'language'	=> $template->language ? $template->language : '*',
				'contents'	=> array(
					'text'		=> $template->plain,
					'html'		=> $template->html,
					'css'		=> $template->css,
				),
				'files'		=> array(
					'styles'	=> $files['styles'],
					'images'	=> $files['images'],
				),
				'dates'		=> array(
					'createdAt'		=> $template->createdAt,
					'modifiedAt'	=> $template->modifiedAt,
				)
			)
		);
		$json		= json_encode( $data, JSON_PRETTY_PRINT );
		$fileName	= vsprintf( '%s%s%s%s', array(
			'MailTemplate_',													//  file name prefix @todo make configurable
			preg_replace( '/[: "\']/', '', $titleKey ),							//  template title as file ID (stripped invalid characters)
			'_'.date( 'Y-m-d' ),
			'.json',															//  file extension @todo make configurable
		) );
		switch( $output ){
			case 'print':
			case 'dev':
				remark( 'Ttile: '.$template->title );
				remark( 'File: '.$fileName );
				remark( 'JSON:' );
				xmp( $json );
				die;
			case 'download':
			default:
				Net_HTTP_Download::sendString( $json, $fileName, TRUE );
		}
	}

	public function import(){
		if( $this->request->isPost() ){
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

	public function index(){
		$templates			= $this->modelTemplate->getAll();
		$moduleTemplateId	= $this->env->getConfig()->get( 'module.resource_mail.template' );
		$modelMail			= new Model_Mail( $this->env );
		foreach( $templates as $template ){
			$template->used	= $modelMail->countByIndex( 'templateId', $template->mailTemplateId );
			$template->activeByModule = ( $template->mailTemplateId == $moduleTemplateId );
		}
		$this->addData( 'templates', $templates );
		$this->addData( 'moduleTemplateId', $moduleTemplateId );
	}

	public function preview( $templateId, $mode = NULL ){
		try{
			$template	= $this->checkTemplate( $templateId );
//			print_m( $template);die;

			$env	= $this->env;
			if( $this->env->getModules()->has( 'Resource_Frontend' ) )
				$env	= Logic_Frontend::getRemoteEnv( $this->env );
			$mail		= new Mail_Test( $env, array( 'forceTemplateId' => $templateId ) );
			switch( strtolower( $mode ) ){
				case 'html':
					$helper	= new View_Helper_Mail_View_HTML( $this->env );
					$helper->setMailObjectInstance( $mail );
					print( $helper->render() );
					break;
				case 'plain':
				case 'text':
					$helper	= new View_Helper_Mail_View_Text( $this->env );
					$helper->setMailObjectInstance( $mail );
					$text	= $helper->render();
					print( UI_HTML_Tag::create( 'html', array(
						UI_HTML_Tag::create( 'body', array(
							UI_HTML_Tag::create( 'xmp', $text ),
						) ),
					) ) );
					break;
				default:
					if( strlen( trim( $template->html ) ) )
						return $this->preview( $templateId, 'html' );
					else
						return $this->preview( $templateId, 'text' );
			}
		}
		catch( Exception $e ){
			print( $e->getMessage() );
		}
		exit;
	}

	public function remove( $templateId ){
		$template	= $this->checkTemplate( $templateId );
		if( $template->status == Model_Mail_Template::STATUS_ACTIVE ){
			$this->env->getMessenger()->noteSuccess( 'Template "'.$template->title.'" entfernt.' );
			$this->restart( 'edit/'.$templateId, TRUE );
		}
		$this->modelTemplate->remove( $templateId );
		$this->env->getMessenger()->noteSuccess( 'Template "'.$template->title.'" entfernt.' );
		$this->restart( NULL, TRUE );
	}

	public function removeImage( $templateId, $pathBase64 ){
		$template	= $this->checkTemplate( $templateId );
		$images		= json_decode( $template->images, TRUE );
		foreach( $images as $nr => $image )
			if( base64_encode( $image ) === $pathBase64 )
				unset( $images[$nr] );
		$this->modelTemplate->edit( $templateId, array(
			'images'		=> json_encode( $images ),
			'modifiedAt'	=> time(),
		) );
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	public function removeStyle( $templateId, $pathBase64 ){
		$template	= $this->checkTemplate( $templateId );
		$styles		= json_decode( $template->styles, TRUE );
		foreach( $styles as $nr => $style )
			if( base64_encode( $style ) === $pathBase64 )
				unset( $styles[$nr] );
		$this->modelTemplate->edit( $templateId, array(
			'styles'		=> json_encode( $styles ),
			'modifiedAt'	=> time(),
		) );
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	public function setStatus( $templateId, $status ){
		$template	= $this->checkTemplate( $templateId );
		if( $status == Model_Mail_Template::STATUS_ACTIVE ){
			if( $template->status != $status ){
				$active		= $this->modelTemplate->getByIndex( 'status', $status );
				if( $active )
					$this->modelTemplate->edit( $active->mailTemplateId, array(
						'status'	=> Model_Mail_Template::STATUS_USABLE
					) );
				$this->modelTemplate->edit( $templateId, array( 'status' => $status ) );
				$this->env->getMessenger()->noteSuccess( sprintf(
					'Template "%s" aktiviert.',
					$template->title
				) );
			}
		}
		if( $status == Model_Mail_Template::STATUS_USABLE ){
			if( $template->status != $status ){
				$this->modelTemplate->edit( $templateId, array( 'status' => $status ) );
			}
		}
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	public function test( $templateId ){
		$email		= trim( $this->request->get( 'email' ) );
		if( !strlen( trim( $email ) ) ){
			$this->messenger->noteError( 'Keine E-Mail-Adresse angegeben.' );
			$this->restart( 'edit/'.$templateId, TRUE );
		}
		$logicMail	= Logic_Mail::getInstance( $this->env );
		$mail		= $logicMail->createMail( 'Test', array( 'mailTemplateId' => $templateId ) );
		$logicMail->sendMail( $mail, (object) array( 'email' => $email ) );
		$this->messenger->noteSuccess( 'E-Mail für Test an "%s" versendet.', htmlentities( $email, ENT_QUOTES, 'UTF-8' ) );
		$this->restart( 'edit/'.$templateId, TRUE );
	}
}
?>
