<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Admin_Mail_Template extends Controller
{
	protected $request;
	protected $messenger;
	protected $modelTemplate;
	protected $appPath;
	protected $appUrl;

	public function add()
	{
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


	public function copy( $templateId )
	{
		if( $this->request->getMethod()->isPost() ){
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

	public function edit( $templateId )
	{
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

	public function index()
	{
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

	public function preview( $templateId, $mode = NULL )
	{
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
					print( HtmlTag::create( 'html', array(
						HtmlTag::create( 'body', array(
							HtmlTag::create( 'xmp', $text ),
						) ),
					) ) );
					break;
				default:
					if( strlen( trim( $template->html ) ) )
						$this->preview( $templateId, 'html' );
					else
						$this->preview( $templateId, 'text' );
			}
		}
		catch( Exception $e ){
			print( $e->getMessage() );
		}
		exit;
	}

	public function remove( $templateId )
	{
		$template	= $this->checkTemplate( $templateId );
		if( $template->status == Model_Mail_Template::STATUS_ACTIVE ){
			$this->env->getMessenger()->noteSuccess( 'Template "'.$template->title.'" entfernt.' );
			$this->restart( 'edit/'.$templateId, TRUE );
		}
		$this->modelTemplate->remove( $templateId );
		$this->env->getMessenger()->noteSuccess( 'Template "'.$template->title.'" entfernt.' );
		$this->restart( NULL, TRUE );
	}

	public function removeImage( $templateId, $pathBase64 )
	{
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

	public function removeStyle( $templateId, $pathBase64 )
	{
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

	public function setStatus( $templateId, $status )
	{
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
		else if( $status == Model_Mail_Template::STATUS_USABLE ){
			if( $template->status != $status ){
				$this->modelTemplate->edit( $templateId, array( 'status' => $status ) );
			}
		}
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	public function test( $templateId )
	{
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

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->modelTemplate	= new Model_Mail_Template( $this->env );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend	= Logic_Frontend::getInstance( $this->env );
			$this->appPath	= $frontend->getPath();
			$this->appUrl	= $frontend->getUrl();
		}
		else{
			$this->appPath	= $this->env->uri;
			$this->appUrl	= $this->env->url;
		}
		$this->addData( 'appPath', $this->appPath );
		$this->addData( 'appUrl', $this->appUrl );
		$logicMail	= Logic_Mail::getInstance( $this->env );
	}

	/**
	 *	@param		string		$templateId
	 *	@param		bool		$strict
	 *	@return		object|false
	 */
	protected function checkTemplate( string $templateId, bool $strict = TRUE )
	{
		$template	= $this->modelTemplate->get( $templateId );
		if( $template )
			return $template;
		if( $strict )
			throw new RangeException( 'Invalid template ID' );
		return FALSE;
	}
}
