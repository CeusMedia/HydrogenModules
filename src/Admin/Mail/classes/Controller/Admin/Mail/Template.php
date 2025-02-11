<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Mail_Template extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Model_Mail_Template $modelTemplate;
	protected string $appPath;
	protected string $appUrl;

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$title		= strip_tags( trim( $this->request->get( 'template_title' ) ) );
			$found		= $this->modelTemplate->getByIndices( ['title' => $title] );

			if( !trim( $title ) )
				$this->messenger->noteError( 'No title given.' );
			else if( $found )
				$this->messenger->noteError( 'Template with this title already existing.' );
			else{
				$templateId	= $this->modelTemplate->add( [
					'status'		=> Model_Mail_Template::STATUS_NEW,
					'title'			=> $title,
					'language'		=> $this->request->get( 'template_language' ),
					'plain'			=> strip_tags( $this->request->get( 'template_plain' ) ),
					'html'			=> trim( $this->request->get( 'template_html' ) ),
					'css'			=> trim( $this->request->get( 'template_css' ) ),
					'createdAt'		=> time(),
					'modifiedAt'	=> time(),
				], FALSE );
				$this->messenger->noteSuccess( 'Template added.' );
				$this->restart( './admin/mail/template/edit/'.$templateId );
			}
		}
		$data	= (object) [];
		foreach( $this->modelTemplate->getColumns() as $key ){
			if( !in_array( $key, ['mailTemplateId'], TRUE ) ){
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

	/**
	 *	@param		int|string		$templateId		Template ID
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function copy( int|string $templateId ): void
	{
		if( $this->request->getMethod()->isPost() ){
			$title	= trim( $this->request->get( 'title' ) );
			$exists	= $this->modelTemplate->getByIndex( 'title', $title );
			if( $exists ){
				$this->messenger->noteError( 'Dieser Titel ist bereits vergeben.' );
				$this->restart( 'edit/'.$templateId, TRUE );
			}
			/** @var Entity_Mail_Template $template */
			$template	= $this->modelTemplate->get( $templateId );
			$templateId	= $this->modelTemplate->add( [
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
			], FALSE );
			$this->messenger->noteSuccess( 'Vorlage "'.$template->title.'" nach "'.$title.'" kopiert.' );
		}
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	/**
	 *	@param		int|string		$templateId		Template ID
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $templateId ): void
	{
		$modelMail		= new Model_Mail( $this->env );
		$template		= $this->checkTemplate( $templateId );
		$template->used	= $modelMail->countByIndex( 'mailTemplateId', $templateId );
		$tabId			= $this->env->getSession()->get( 'admin-mail-template-edit-tab' );
		$this->addData( 'tabId', $tabId );

		if( $this->request->has( 'save' ) ){
			if( strlen( $this->request->get( 'template_title' ) ) ){
				$title		= strip_tags( trim( $this->request->get( 'template_title' ) ) );
				$found		= $this->modelTemplate->getByIndices( [
					'title'				=> $title,
					'mailTemplateId'	=> '!= '.$templateId
				] );
				if( !trim( $title ) )
					$this->messenger->noteError( 'No title given.' );
				else if( $found )
					$this->messenger->noteError( 'Template with this title already existing.' );
				else{
					$this->modelTemplate->edit( $templateId, [
						'title'			=> $title,
						'language'		=> $this->request->get( 'language' ),
	//					'plain'			=> strip_tags( $this->request->get( 'template_plain' ) ),
	//					'html'			=> trim( $this->request->get( 'template_html' ) ),
	//					'css'			=> trim( $this->request->get( 'template_css' ) ),
						'modifiedAt'	=> time(),
					], FALSE );
				}
				$this->messenger->noteSuccess( 'Template information saved.' );
				$this->restart( './admin/mail/template/edit/'.$templateId );
			}
			else if( $this->request->get( 'template_style' ) ){
				/** @var Entity_Mail_Template $template */
				$template	= $this->modelTemplate->get( $templateId );
				if( strlen( trim( $template->styles ) ) && preg_match( "/^[a-z0-9]/", $template->styles ) )
					$template->styles	= json_encode( explode( ",", $template->styles ) );
				$list		= trim( $template->styles ) ? json_decode( $template->styles, TRUE ) : [];
				$list[]		= trim( $this->request->get( 'template_style' ) );
				$this->modelTemplate->edit( $templateId, [
					'styles'	=> json_encode( $list )
				] );
				$this->restart( './admin/mail/template/edit/'.$templateId );
			}
			else if( $this->request->get( 'template_image' ) ){
				$template	= $this->modelTemplate->get( $templateId );
				if( strlen( trim( $template->images ) ) && preg_match( "/^[a-z0-9]/", $template->images ) )
					$template->images	= json_encode( explode( ",", $template->images ) );
				$list		= trim( $template->images ) ? json_decode( $template->images, TRUE ) : [];
				$list[]		= trim( $this->request->get( 'template_image' ) );
				$this->modelTemplate->edit( $templateId, [
					'images'	=> json_encode( $list )
				] );
				$this->restart( './admin/mail/template/edit/'.$templateId );
			}
			foreach( $this->modelTemplate->getColumns() as $key ){
				if( !in_array( $key, ['mailTemplateId'], TRUE ) ){
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
	 *	@return		void
	 */
	public function index(): void
	{
		/** @var Entity_Mail_Template[] $templates */
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

	/**
	 *	@param		int|string		$templateId		Template ID
	 *	@param		string|NULL		$mode
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function preview( int|string $templateId, ?string $mode = NULL ): void
	{
		try{
			$template	= $this->checkTemplate( $templateId );
//			print_m( $template);die;

			$env	= $this->env;
			if( $this->env->getModules()->has( 'Resource_Frontend' ) )
				$env	= Logic_Frontend::getRemoteEnv( $this->env );
			$mail		= new Mail_Test( $env, ['forceTemplateId' => $templateId] );
			switch( strtolower( $mode ?? 'auto' ) ){
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
					print( HtmlTag::create( 'html', [
						HtmlTag::create( 'body', [
							HtmlTag::create( 'xmp', $text ),
						] ),
					] ) );
					break;
				case 'auto':
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

	/**
	 *	@param		int|string		$templateId		Template ID
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $templateId ): void
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

	/**
	 *	@param		int|string		$templateId		Template ID
	 *	@param		string			$pathBase64
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeImage( int|string $templateId, string $pathBase64 ): void
	{
		$template	= $this->checkTemplate( $templateId );
		$images		= json_decode( $template->images, TRUE );
		foreach( $images as $nr => $image )
			if( base64_encode( $image ) === $pathBase64 )
				unset( $images[$nr] );
		$this->modelTemplate->edit( $templateId, [
			'images'		=> json_encode( $images ),
			'modifiedAt'	=> time(),
		] );
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	/**
	 *	@param		int|string		$templateId		Template ID
	 *	@param		string			$pathBase64
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeStyle( int|string $templateId, string $pathBase64 ): void
	{
		$template	= $this->checkTemplate( $templateId );
		$styles		= json_decode( $template->styles, TRUE );
		foreach( $styles as $nr => $style )
			if( base64_encode( $style ) === $pathBase64 )
				unset( $styles[$nr] );
		$this->modelTemplate->edit( $templateId, [
			'styles'		=> json_encode( $styles ),
			'modifiedAt'	=> time(),
		] );
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	/**
	 *	@param		int|string		$templateId		Template ID
	 *	@param		int				$status			Status to be set, one of Model_Mail_Template::STATUS_*
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setStatus( int|string $templateId, int $status ): void
	{
		$template	= $this->checkTemplate( $templateId );
		if( $status == Model_Mail_Template::STATUS_ACTIVE ){
			if( $template->status != $status ){
				$active		= $this->modelTemplate->getByIndex( 'status', $status );
				if( $active )
					$this->modelTemplate->edit( $active->mailTemplateId, [
						'status'	=> Model_Mail_Template::STATUS_USABLE
					] );
				$this->modelTemplate->edit( $templateId, ['status' => $status] );
				$this->env->getMessenger()->noteSuccess( sprintf(
					'Template "%s" aktiviert.',
					$template->title
				) );
			}
		}
		else if( $status == Model_Mail_Template::STATUS_USABLE ){
			if( $template->status != $status ){
				$this->modelTemplate->edit( $templateId, ['status' => $status] );
			}
		}
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	/**
	 *	@param		int|string		$templateId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function test( int|string $templateId ): void
	{
		$email		= trim( $this->request->get( 'email' ) );
		if( !strlen( trim( $email ) ) ){
			$this->messenger->noteError( 'Keine E-Mail-Adresse angegeben.' );
			$this->restart( 'edit/'.$templateId, TRUE );
		}
		$logicMail	= Logic_Mail::getInstance( $this->env );
		$mail		= $logicMail->createMail( 'Test', ['mailTemplateId' => $templateId] );
		$logicMail->sendMail( $mail, (object) ['email' => $email] );
		$this->messenger->noteSuccess( 'E-Mail für Test an "%s" versendet.', htmlentities( $email, ENT_QUOTES, 'UTF-8' ) );
		$this->restart( 'edit/'.$templateId, TRUE );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
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
	}

	/**
	 *	@param		int|string		$templateId			Template ID
	 *	@param		bool		$strict				Flag: throw exception if template ID is invalid, default: yes
	 *	@return		Entity_Mail_Template|FALSE
	 *	@throws		RangeException		if template ID is invalid
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkTemplate( int|string $templateId, bool $strict = TRUE ): Entity_Mail_Template|FALSE
	{
		/** @var Entity_Mail_Template $template */
		$template	= $this->modelTemplate->get( $templateId );
		if( $template )
			return $template;
		if( $strict )
			throw new RangeException( 'Invalid template ID' );
		return FALSE;
	}
}
