<?php
/**
 *	Abstract mail class.
 *	Prepares mail and mail transport object.
 *	Sends generated mail using configured mail transport.
 *	Attention: This class needs to be extended by method generate().
 */
abstract class Mail_Abstract{

	/**	@var		CeusMedia\Mail\Message				$mail			Mail objectm, build on construction */
	public $mail;

	/**	@var		ADT_List_Dictionary					$config			Application configuration object */
	protected $config;
	/*	@var		CMF_Hydrogen_Environment			$env			Environment object */
	protected $env;
	/** @var		Logic_Mail							$logicMail		Mail logic object */
	protected $logicMail;
	/** @var		UI_HTML_PageFrame					$page			Empty page oject for HTML mails */
	protected $page;
	/** @var		CeusMedia\Mail\Transport\SMTP		$transport		Mail transport object, build on construction */
	protected $transport;
	/** @var		CMF_Hydrogen_View					$view			General view instance */
	protected $view;
	/** @var		string								$baseUrl		Application base URL */
	protected $baseUrl;
	/** @var		array								$bodyClasses	List of Stylesheet files to integrate into HTML body */
	protected $addedStyles				= array();
	/** @var		array								$bodyClasses	List of classes to apply to HTML body */
	protected $bodyClasses				= array();
	/** @var		Model_Mail_Template					$modelTemplate	Mail template model object */
	protected $modelTemplate;

	protected $encodingHtml				= 'quoted-printable';

	protected $encodingSubject			= 'quoted-printable';

	protected $encodingText				= 'quoted-printable';

	protected $contents					= array(
		'htmlGenerated'		=> '',
		'htmlRendered'		=> '',
		'textGenerated'		=> '',
		'textRendered'		=> '',
	);


	/**
	 *	Contructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment			$env			Environment object
	 *	@param		array								$data			Map of template mail data
	 *	@param		boolean								$defaultStyle	Flag: load default mail style file
	 *	@todo		resolve todos below after all modules have adjusted
	 */
	public function __construct( CMF_Hydrogen_Environment $env, $data = array(), $defaultStyle = TRUE ){
		$this->setEnv( $env );
		$this->modelTemplate	= new Model_Mail_Template( $env );
		$this->mail				= new \CeusMedia\Mail\Message();
		$this->view				= new CMF_Hydrogen_View( $env );
		$this->page				= new UI_HTML_PageFrame();
		$this->logicMail		= $this->env->getLogic()->get( 'Mail' );
		$this->options			= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
		$this->config			= $this->env->getConfig();

		//  apply encoding settings from module config
		if( $this->options->get( 'encoding.html' ) )
			$this->encodingHtml		= $this->options->get( 'encoding.html' );
		if( $this->options->get( 'encoding.subject' ) )
			$this->encodingHtml		= $this->options->get( 'encoding.subject' );
		if( $this->options->get( 'encoding.text' ) )
			$this->encodingHtml		= $this->options->get( 'encoding.text' );

		$this->baseUrl	= !empty( $env->baseUrl ) ? $env->baseUrl : $this->config->get( 'app.base.url' );
		if( !$this->baseUrl )
			throw new RuntimeException( 'Mailing requires "app.base.url" to be set in application base config file' );
		$this->page->setBaseHref( $this->baseUrl );

		$this->initTransport();
		$this->mail->setSender( $this->options->get( 'sender.system' ) );
		$this->__onInit();
		$this->data		= $data;
		$this->generate( $data );												//  @todo remove argument, use $this->data instead
	}

	/**
	 *	Do not use all members for serialization.
	 *	@access		public
	 *	@return		array		List of allowed members during serialization
	 */
	public function __sleep(){
		return array( 'mail', 'page'/*, 'transport', 'options'*/ );
	}

/*	public function __wakeup(){
		return $this->initTransport();
	}*/

	public function addAttachment( $filePath, $mimeType = NULL, $encoding = NULL, $fileName = NULL ){
		$this->mail->addFile( $filePath, $mimeType, $encoding, $fileName );
	}

	public function getAttachments(){
		return $this->mail->getAttachments();
	}

	/**
	 *	Returns generated or rendered HTML or plain text content.
	 *	@param		string		$key		Content key (htmlGenerated, htmlRendered, textGenerated, textRendered)
	 *	@return		string|NULL
	 *	@throws		DomainException			if content key is not valid
	 */
	public function getContent( $key ){
		if( !array_key_exists( $key, $this->contents ) )
			throw new DomainException( 'Invalid content key' );
		return $this->contents[$key];
	}

	public function getPage(){
		return $this->page;
	}

	/**
	 *	Returns set subject of mail.
	 *	@access		public
	 *	@return		string		Subject set for mail
	 */
	public function getSubject(){
		return $this->mail->getSubject();
	}

	public function initTransport( $verbose = FALSE ){
		$options	= $this->env->getConfig()->getAll( 'module.resource_mail.transport.', TRUE );
		switch( strtolower( $options->get( 'type' ) ) ){
			case 'smtp':
				$hostname	= $options->get( 'hostname' );
				$port		= $options->get( 'port' );
				$username	= $options->get( 'username' );
				$password	= $options->get( 'password' );
				$this->transport	= new \CeusMedia\Mail\Transport\SMTP( $hostname, $port );
				$this->transport->setUsername( $username );
				$this->transport->setPassword( $password );
				$this->transport->setSecure( $options->get( 'secure' ) );
				$this->transport->setVerbose( $verbose );
				break;
			case 'local':
			case 'default':
			case 'sendmail':
				$this->transport	= new \CeusMedia\Mail\Transport\Local();
				break;
			default:
				throw new RuntimeException( 'No mail transport configured' );
		}
	}

	/**
	 *	Sends mail to an email address.
	 *	@access		public
	 *	@param		stdClass	$user		User model object
	 *	@return		boolean		TRUE if success
	 */
	public function sendTo( $user ){
		if( is_array( $user ) )
			$user	= (object) $user;
		if( empty( $user->email ) )
			throw new RuntimeException( 'User object invalid: no email address' );
		return $this->sendToAddress( $user->email );
	}

	/**
	 *	Sends mail to user by its user ID.
	 *	Attention: Module "Users" must be installed to use this feature.
	 *	@access		public
	 *	@param		integer		$userId		ID of user to send mail to
	 *	@return		boolean		TRUE if success
	 */
	public function sendToUser( $userId ){
		if( !$this->env->getModules()->has( 'Resource_Users' ) )
			throw new RuntimeException( 'Module "Resource_Users" is not installed' );
		$model	= new Model_User( $this->env );
		$user	= $model->get( $userId );
		if( !$user )
			throw new RuntimeException( 'User with ID '.$userId.' is not existing' );
		return $this->sendTo( $user );
	}

	public function setEnv( CMF_Hydrogen_Environment $env ){
		$this->env		= $env;
	}

	/**
	 *	Sets address of mail sender.
	 *	@access		public
	 *	@param		string		$sender		Mail address of sender
	 *	@return		void
	 */
	public function setSender( $sender ){
		$this->mail->setSender( $sender );
	}

	/**
	 *	Sets mail subject.
	 * 	It is possible to auto-prepend a prefix which can be defined by mail module.
	 *	It is also possible to insert subject into a template defined by mail module.
	 *	Attention: If both prefix and template are defined and enabled by method call, the prefix will be prepended to the template result.
	 *	@access		public
	 *	@param		string		$subject		Mail subject to set
	 *	@param		boolean		$usePrefix		Flag: Prepend mail subject prefix defined by mail module
	 *	@param		boolean		$useTemplate	Flag: Insert subject into mail subject prefix defined by mail module
	 *	@return		void
	 */
	public function setSubject( $subject, $usePrefix = TRUE, $useTemplate = TRUE ){
		if( $useTemplate ){
			$template	= $this->options->get( 'subject.template' );
			if( strlen( trim( $template ) ) )
				$subject	= sprintf( $template, $subject );
		}
		if( $usePrefix ){
			$prefix		= $this->options->get( 'subject.prefix' );
			if( strlen( trim( $prefix ) ) )
				$subject	= trim( $prefix ).' '.$subject;
		}
		$host		= isset( $this->env->host ) ? $this->env->host : parse_url( $this->baseUrl, PHP_URL_HOST );
		$data		= array(
			'app'	=> array(
				'title'	=> $this->env->getConfig()->get( 'app.name' ),
				'host'	=> $host,
			)
		);
		$subject	= UI_Template::renderString( $subject, $data );
		$this->mail->setSubject( $subject, $this->encodingSubject );
	}

	//  --  PROTECTED  --  //

	/**
	 *	This method is called after construction is done and right before generation takes place.
	 *	Extend this method in your mail class if you need to provide general resources to many methods.
	 *	Extend this method in an abstract module mail class if you have many mail classes in need of same resources.
	 *	ATTENTION: Please remember to mark new members as protected, if set by your extension!
	 *	ATTENTION: Please note possibly thrown resource exceptions!
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit(){
	}

	protected function addBodyClass( $class ){
		if( strlen( trim( $class ) ) )
			$this->bodyClasses[]	= $class;
	}

	/**
	 *	Adds HTML body part to mail. Alias for setHtml.
	 *	@access		protected
	 *	@param		string		$html		HTML mail body to add to mail
	 *	@return		void
	 *	@see		http://wiki.apache.org/spamassassin/Rules/BASE64_LENGTH_78_79
	 *	@deprecated	use setHtml instead
	 *	@todo		to be removed
	 */
	protected function addHtmlBody( $html ){
		CMF_Hydrogen_Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Resource_Mail' )->version )
			->setErrorVersion( '0.8.9' )
			->setExceptionVersion( '0.9' )
			->message( 'Abstract_Mail::addHtmlBody is deprecated, use ::setHtml instead' );
		$this->setHtml( $html );
	}

	/**
	 *	Adds plain text body part to mail. Alias for setText.
	 *	@access		protected
	 *	@param		string		$text		Plain text mail body to add to mail
	 *	@return		void
	 *	@deprecated	use setText instead
	 *	@todo		to be removed
	 */
	protected function addTextBody( $text ){
		CMF_Hydrogen_Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Resource_Mail' )->version )
			->setErrorVersion( '0.8.9' )
			->setExceptionVersion( '0.9' )
			->message( 'Abstract_Mail::addTextBody is deprecated, use ::setText instead' );
		$this->setText( $text );
	}

	protected function addPrimerStyle( $fileName ){
		$config		= $this->env->getConfig();
		$path		= $config->get( 'path.themes' ).$config->get( 'layout.primer' ).'/css/';
		return $this->addStyle( $path.$fileName );
	}

	protected function addScriptFile( $fileName ){
		$filePath	= $this->env->getConfig()->get( 'path.scripts' ).$fileName;
		if( !file_exists( $filePath ) )
			return FALSE;
		$script	= FS_File_Reader::load( $filePath );
		$script	= str_replace( '(/lib/', '(http://'.getEnv( 'HTTP_HOST' ).'/lib/', $script );
		$tag	= UI_HTML_Tag::create( 'script', $script, array( 'type' => 'text/javascript' ) );
		$this->page->addHead( $tag );
		return TRUE;
	}

	/**
	 *	Adds a style file (CSS) to page header by file URI from app root.
	 *	Ignores already added style files.
	 *	@access		protected
	 *	@param		string		$filePath		URI of file from app root
	 *	@return		boolean		TRUE if file was added, FALSE if not found or already added
	 */
	protected function addStyle( $filePath ){
		if( !file_exists( $filePath ) )
			return FALSE;
		if( in_array( $filePath, $this->addedStyles ) )
			return FALSE;
		$style	= FS_File_Reader::load( $filePath );
		$style	= str_replace( '(/lib/', '(http://'.getEnv( 'HTTP_HOST' ).'/lib/', $style );

		$path	= dirname( $filePath );
		$style	= str_replace( '(../../../', '('.$this->env->url.dirname( dirname( dirname( $path ) ) ).'/', $style );
		$style	= str_replace( '(../../', '('.$this->env->url.dirname( dirname( $path ) ).'/', $style );
		$style	= str_replace( '(../', '('.$this->env->url.dirname( $path ).'/', $style );
		$tag	= UI_HTML_Tag::create( 'style', $style, array( 'type' => 'text/css' ) );
		$this->page->addHead( $tag );
		return TRUE;
	}

	protected function addCommonStyle( $fileName ){
		$config		= $this->env->getConfig();
		$path		= $config->get( 'path.themes' ).'/common/css/';
		return $this->addStyle( $path.$fileName );
	}

	protected function addThemeStyle( $fileName ){
		$config		= $this->env->getConfig();
		$path		= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/css/';
		return $this->addStyle( $path.$fileName );
	}

	protected function applyTemplateToHtml( $content, $templateId = NULL ){
		$template	= $this->logicMail->detectTemplateToUse( $templateId, TRUE, FALSE );
		if( !$template )
			return $content;
		$wordsMain		= $this->env->getLanguage()->getWords( 'main' );
		$baseUrl		= $this->env->getBaseUrl();
		$replacements	= array(
			'content'		=> $content,
			'app.email'		=> $this->env->getConfig()->get( 'app.email' ),
			'app.url'		=> $baseUrl,
			'app.host'		=> parse_url( $baseUrl, PHP_URL_HOST ),
			'app.path'		=> rtrim( parse_url( $baseUrl, PHP_URL_PATH ), '/' ),
			'app.title'		=> $wordsMain['main']['title'],
		);
		$contentFull	= $template->html;
		foreach( $replacements as $key => $value )
		 	$contentFull	= str_replace( '[#'.$key.'#]', $value, $contentFull );

		if( $template->images ){
			if( strlen( trim( $template->images ) ) && preg_match( "/^[a-z0-9]/", $template->images ) )
				$template->images	= json_encode( explode( ",", $template->images ) );
			foreach( json_decode( $template->images, TRUE ) as $nr => $image ){
				if( preg_match( '/^http/', $image ) ){
					throw new Exception( 'Not implemented yet' );
				}
				else{
					if( !file_exists( $this->env->uri.$image ) ){
//						throw new RuntimeException( 'Loading image from "'.$image.'" failed' );
						$this->env->getMessenger()->noteError( 'Loading image from "'.$this->env->uri.$image.'" failed.' );
						continue;
					}
				}
				$this->mail->addHtmlImage( 'image'.( $nr + 1), $this->env->uri.$image );
			}
		}

		if( $template->styles ){
			if( strlen( trim( $template->styles ) ) && preg_match( "/^[a-z0-9]/", $template->styles ) )
				$template->styles	= json_encode( explode( ",", $template->styles ) );
			foreach( json_decode( $template->styles, TRUE ) as $style ){
				if( preg_match( '/^http/', $style ) ){
					try{
						$content = Net_Reader::readUrl( $style );
					}
					catch( Exception $e ){
						$this->env->getMessenger()->noteError( 'Loading mail style from "'.$style.'" failed.' );
						continue;
					}
				}
				else{
					if( !file_exists( $this->env->uri.$style ) ){
						$this->env->getMessenger()->noteError( 'Loading mail style from "'.$this->env->uri.$style.'" failed.' );
						continue;
					}
					$content	= FS_File_Reader::load( $this->env->uri.$style );
				}
	//			$content	= preg_replace( '/\/\*.*\*\//su', '', $content );
				$styleTag	= UI_HTML_Tag::create( 'style', $content, array( 'type' => 'text/css' ) );
				$this->page->addHead( $styleTag );
			}
		}
		if( $template->css ){
			$styleTag	= UI_HTML_Tag::create( 'style', $template->css, array( 'type' => 'text/css' ) );
			$this->page->addHead( $styleTag );
		}
		return $contentFull;
	}

	/**
	 *	Applies detected mail template to generated mail content, if available.
	 *
	 *	@access		protected
	 *	@param		string		$content		123
	 *	@param		integer		$templateId		ID of template to use in favor of defaults (must be usable)
	 *	@return		string						Fully rendered content
	 */
	protected function applyTemplateToText( $content, $templateId = NULL ){
		$template	= $this->logicMail->detectTemplateToUse( $templateId, TRUE, FALSE );
		if( !$template )
			return $content;
		$wordsMain		= $this->env->getLanguage()->getWords( 'main' );
		$baseUrl		= $this->env->getBaseUrl();
		$replacements	= array(
			'content'		=> $content,
			'app.email'		=> $this->env->getConfig()->get( 'app.email' ),
			'app.url'		=> $baseUrl,
			'app.host'		=> parse_url( $baseUrl, PHP_URL_HOST ),
			'app.path'		=> rtrim( parse_url( $baseUrl, PHP_URL_PATH ), '/' ),
			'app.title'		=> $wordsMain['main']['title'],
		);
		$contentFull	= $template->plain;
		foreach( $replacements as $key => $value )
		 	$contentFull	= str_replace( '[#'.$key.'#]', $value, $contentFull );
		return $contentFull;
	}

	/**
	 *	Create mail body and sets subject and body on mail object.
	 *	By using methods setText and setHtml to assign generated contents,
	 *	a detected mail template will be applied,
	 *	the mail object will receive the rendered contents as new mail parts and
	 *	generated and rendered contents will be stored in mail class as contents.
	 *	@abstract
	 *	@access		protected
	 *	@param		array		$data		Map of body template data
	 *	@return		self
	 *	@example
	 *		$wordsMain		= $this->env->getLanguage()->getWords( 'main' );
	 *		$wordsModule	= $this->env->getLanguage()->getWords( 'myModule' );
	 *
	 *		$configMain		= $this->env->getConfig();
	 *		$configModule	= $this->env->getConfig()->getAll( 'module.myModule.', TRUE );				//  @todo change this!
	 *
	 *		$templateId		= (int) $configModule->get( 'mailTemplateId' );
	 *
	 *		$this->setSubject( $wordsModule['mails']['onMyEvent'] );
	 *
	 *		$bodyText		= $this->view->loadContentFile( 'mails/myModule/myEvent.txt', $data );
	 *		$this->setText( $bodyText, $templateId );
	 *
	 *		$bodyHtml		= $this->view->loadContentFile( 'mails/myModule/myEvent.html', $data );
	 *		$this->setHtml( $bodyHtml, $templateId );
	 */
	abstract protected function generate();

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		string		$topic		Locale file key, eg. test/my
	 *	@param		string		$section	Section in locale file
	 *	@return		void
	 */
	protected function getWords( $topic, $section = NULL ){
		$language	= $this->env->getLanguage();
		if( !$language->hasWords( $topic ) )
			$language->load( $topic );
		if( empty( $section ) )
			return $language->getWords( $topic );
		return $language->getSection( $topic, $section );
	}

	/**
	 *	Sends mail to directly email address using configured transport.
	 *	ATTENTION: You SHOULD NOT use this method unless you REALLY NEED to.
	 *	Please use one of these methods instead: sendToUser(int $userId), sendTo(obj $user)
	 *	@access		protected
	 *	@param		string		$email		Target email address
	 *	@return		boolean		TRUE if success
	 *	@todo		kriss: Notwendigkeit dieser Methode prüfen.
	 */
	protected function sendToAddress( $email ){
		if( $this->mail instanceof \CeusMedia\Mail\Message )
			$this->mail->addRecipient( $email );
		else
			$this->mail->setReceiver( $email );
		return $this->transport->send( $this->mail, TRUE );
	}

	/**
	 *	Sets HTML body part of mail.
	 *	Applies mail template and adds rendered content as new mail part.
	 *	Stores given (generated) and rendered contents.
	 *	@access		protected
	 *	@param		string		$content	HTML mail body to set
	 *	@param		integer		$templateId		ID of mail template to use in favour
	 *	@return		self
	 */
	protected function setHtml( $content, $templateId = 0 ){
		if( !$templateId && isset( $this->data['mailTemplateId' ] ) )
			$templateId	= $this->data['mailTemplateId' ];
		$contentFull	= $this->applyTemplateToHtml( $content, $templateId );

		if( !preg_match( '/(<html>|<head>)/', $contentFull ) ){
			$page	= $this->getPage();
			$page->addBody( $contentFull );

	/*		$page->addMetaTag( 'name', 'viewport', join( ', ', array(
				'width=device-width',
				'initial-scale=1.0',
				'minimum-scale=0.75',
				'maximum-scale=2.0',
				'user-scalable=yes',
			) ) );*/

			$classes	= array_merge( array( 'mail' ), $this->bodyClasses );
			$options	= $this->env->getConfig()->getAll( 'module.ui_css_panel.', TRUE );
			if( count( $options->getAll() ) )
				$classes[]	= 'content-panel-style-'.$options->get( 'style' );
			$contentFull	= $page->build( array( 'class' => $classes ) );
		}
		$this->contents['htmlGenerated']	= $content;
		$this->contents['htmlRendered']		= $contentFull;
		$this->mail->addHTML( $contentFull, 'UTF-8', $this->encodingHtml );
		return $this;
	}

	/**
	 *	Sets plain text body part of mail.
	 *	Applies mail template and adds rendered content as new mail part.
	 *	Stores given (generated) and rendered contents.
	 *	@access		protected
	 *	@param		string		$content		Plain text mail body to set
	 *	@param		integer		$templateId		ID of mail template to use in favour
	 *	@return		self
	 */
	protected function setText( $content, $templateId = 0 ){
		if( !$templateId && isset( $this->data['mailTemplateId' ] ) )
			$templateId	= $this->data['mailTemplateId' ];
		$contentFull	= $this->applyTemplateToText( $content, $templateId );
		$this->contents['textGenerated']	= $content;
		$this->contents['textRendered']		= $contentFull;
		$this->mail->addText( $contentFull, 'UTF-8', $this->encodingText );
		return $this;
	}
}
?>
