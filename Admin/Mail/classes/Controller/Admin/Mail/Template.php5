<?php
class Controller_Admin_Mail_Template extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->modelTemplate	= new Model_Mail_Template( $this->env );
		$this->appUri			= '';
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$this->appUri		= Logic_Frontend::getInstance( $this->env )->getUri();
		$this->addData( 'appUri', $this->appUri );
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
					'status'		=> 1,
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

	public function edit( $templateId ){
		$template	= $this->checkTemplate( $templateId );

		if( $this->request->has( 'save' ) ){
			$title		= strip_tags( trim( $this->request->get( 'template_title' ) ) );
			$found		= $this->modelTemplate->getByIndices( array(
				'title'				=> $title,
				'mailTemplateId'	=> '!='.$templateId
			) );

			if( !trim( $title ) )
				$this->messenger->noteError( 'No title given.' );
			else if( $found )
				$this->messenger->noteError( 'Template with this title already existing.' );
			else{
				$this->modelTemplate->edit( $templateId, array(
					'title'			=> $title,
					'language'		=> $this->request->get( 'language' ),
					'plain'			=> strip_tags( $this->request->get( 'template_plain' ) ),
					'html'			=> trim( $this->request->get( 'template_html' ) ),
					'css'			=> trim( $this->request->get( 'template_css' ) ),
					'modifiedAt'	=> time(),
				), FALSE );

				if( $this->request->get( 'template_style' ) ){
					$template	= $this->modelTemplate->get( $templateId );
					$list		= explode( ',', $template->styles );
					$list[]		= trim( $this->request->get( 'template_style' ) );
					$this->modelTemplate->edit( $templateId, array(
						'styles'	=> join( ',', $list )
					) );
				}
				if( $this->request->get( 'template_image' ) ){
					$template	= $this->modelTemplate->get( $templateId );
					$list		= explode( ',', $template->images );
					$list[]		= trim( $this->request->get( 'template_image' ) );
					$this->modelTemplate->edit( $templateId, array(
						'images'	=> join( ',', $list )
					) );
				}
				$this->messenger->noteSuccess( 'Template information saved.' );
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

	public function index(){
		$this->addData( 'templates', $this->modelTemplate->getAll() );
	}

	public function preview( $templateId, $mode = NULL ){
		$this->logicMail	= new Logic_Mail( $this->env );
		$template	= $this->checkTemplate( $templateId );
		$mail		= new Mail_Test( $this->env, array( 'templateId' => $templateId ) );

		$parts		= $this->logicMail->getMailParts( (object) array( 'object' => $mail ) );
		switch( strtolower( $mode ) ){
			case 'html':
				$images	= array();
				foreach( $parts as $key => $part ){
					if( $part instanceof \CeusMedia\Mail\Part\InlineImage )
						$images[$part->getId()]	= $part;
					else if( $part instanceof \CeusMedia\Mail\Part\HTML )
						$html	= $part->getContent();
					else if( $part instanceof Net_Mail_Body )
						if( $part->getMimeType() === "text/html" )
							$html	= $part->getContent();
				}
				if( !$html )
					throw new Exception( 'No HTML part found' );
				foreach( $images as $imageId => $part ){
					$find	= '"CID:'.$imageId.'"';
					$subst	= '"data:'.$part->getMimeType().';base64,'.base64_encode( $part->getContent() ).'"';
					$html	= str_replace( $find, $subst, $html );
				}
				print( $html );
				break;
			case 'plain':
			case 'text':
				print( $mail->content['text'] );
				break;
			default:
				remark( 'Text:' );
				xmp( $mail->content['text'] );
				remark( 'HTML:' );
				xmp( $mail->getPage()->build() );
		}
		exit;
	}

	public function remove(){
		$template	= $this->checkTemplate( $templateId );
		if( $template->status == 3 ){
			$this->env->getMessenger()->noteSuccess( 'Template "'.$template->title.'" entfernt.' );
			$this->restart( 'edit/'.$templateId, TRUE );
		}
		$this->modelTemplate->remove( $templateId );
		$this->env->getMessenger()->noteSuccess( 'Template "'.$template->title.'" entfernt.' );
		$this->restart( NULL, TRUE );
	}

	public function removeImage( $templateId, $pathBase64 ){
		$template	= $this->checkTemplate( $templateId );
		$images		= explode( ',', $template->images );
		foreach( $images as $nr => $image )
			if( base64_encode( $image ) === $pathBase64 )
				unset( $images[$nr] );
		$this->modelTemplate->edit( $templateId, array(
			'images' => join( ',', $images ),
			'modifiedAt'	=> time(),
		) );
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	public function removeStyle( $templateId, $pathBase64 ){
		$template	= $this->checkTemplate( $templateId );
		$styles		= explode( ',', $template->styles );
		foreach( $styles as $nr => $style )
			if( base64_encode( $style ) === $pathBase64 )
				unset( $styles[$nr] );
		$this->modelTemplate->edit( $templateId, array(
			'styles' => join( ',', $styles ),
			'modifiedAt'	=> time(),
		) );
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	public function set( $templateId ){
		$template	= $this->checkTemplate( $templateId );
		$active		= $this->modelTemplate->getByStatus( 3 );
		if( $active )
			$this->modelTemplate->edit( $active->mailTemplateId, array( '2' ) );
		$this->modelTemplate->edit( $templateId, array( 'status' => 3 ) );
		$this->env->getMessenger()->noteSuccess( 'Template "'.$template->title.'" aktiviert.' );
	}

	public function test( $templateId ){
		$email		= trim( $this->request->get( 'email' ) );
		$email		= 'dev@ceusmedia.de';
		$mail		= new Mail_Test( $this->env, array( 'templateId' => $templateId ) );
		$logic		= new Logic_Mail( $this->env );
		$language	= $this->env->getLanguage()->getLanguage();
		$logic->handleMail( $mail, (object) array( 'email' => $email, $language, TRUE ) );
		$this->restart( 'edit/'.$templateId, TRUE );
	}
}
?>
