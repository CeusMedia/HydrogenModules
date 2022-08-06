<?php
/**
 *	Abstract mail class.
 *	Prepares mail and mail transport object.
 *	Sends generated mail using configured mail transport.
 *	Attention: This class needs to be extended by method generate().
 */
abstract class Mail_Abstract
{
	/**	@var		object							$mail			Mail objectm, build on construction */
	public $mail;

	/**	@var		CMF_Hydrogen_Environment		$env			Environment object */
	protected $env;

	/**	@var		ADT_List_Dictionary				$config			Application configuration object */
	protected $config;

	/**	@var		ADT_List_Dictionary				$options		Module configuration object */
	protected $options;

	/** @var		Logic_Mail						$logicMail		Mail logic object */
	protected $logicMail;

	/** @var		UI_HTML_PageFrame				$page			Empty page oject for HTML mails */
	protected $page;

	/** @var		object							$transport		Mail transport object, build on construction */
	protected $transport;

	/** @var		CMF_Hydrogen_View				$view			General view instance */
	protected $view;

	/** @var		Model_Mail_Template				$modelTemplate	Mail template model object */
	protected $modelTemplate;

	/** @var		string							$baseUrl		Application base URL */
	protected $baseUrl;

	/** @var		array							$bodyClasses	List of Stylesheet files to integrate into HTML body */
	protected $addedStyles				= [];

	/** @var		array							$bodyClasses	List of classes to apply to HTML body */
	protected $bodyClasses				= [];

	/** @var		array							$data			Data assigned for mail body generation, for HTML and text */
	protected $data						= [];

	/** @var		array							$contents		Map of generated and rendered contents */
	protected $contents					= [
		'htmlGenerated'		=> '',
		'htmlRendered'		=> '',
		'textGenerated'		=> '',
		'textRendered'		=> '',
	];

	/** @var		integer							$templateId		ID of template to force to use on rendering of mail contents */
	protected $templateId				= 0;

	/** @var		string							$encodingHtml	Default encoding for HTML */
	protected $encodingHtml				= 'quoted-printable';

	/** @var		string							$encodingHtml	Default encoding for subject */
	protected $encodingSubject			= 'quoted-printable';

	/** @var		string							$encodingHtml	Default encoding for text */
	protected $encodingText				= 'quoted-printable';

	/**
	 *	Contructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env			Environment object
	 *	@param		array						$data			Map of template mail data
	 *	@param		boolean						$defaultStyle	Flag: load default mail style file
	 *	@todo		resolve todos below after all modules have adjusted
	 */
	public function __construct( CMF_Hydrogen_Environment $env, $data = [], bool $defaultStyle = TRUE )
	{
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
	public function __sleep()
	{
		return ['mail', 'page'/*, 'logicMail'/*, 'transport', 'options'*/];
	}

/*	public function __wakeup(){
		return $this->initTransport();
		$this->logicMail		= $this->env->getLogic()->get( 'Mail' );
	}*/

	public function addAttachment( string $filePath, ?string $mimeType = NULL, ?string $encoding = NULL, ?string $fileName = NULL )
	{
		$libraries		= $this->logicMail->detectAvailableMailLibraries();
		$library		= $this->logicMail->detectMailLibraryFromMailObjectInstance( $this );

		if( !$library || !( $libraries & $library ) )
			throw new RuntimeException( 'Mail was created by a mail library which is not supported' );

		switch( $library ){
			case Logic_Mail::LIBRARY_COMMON:
				$this->mail->addAttachmentFile( $filePath, $mimeType );
				break;
			case Logic_Mail::LIBRARY_MAIL_V1:
				$this->mail->addFile( $filePath, $mimeType, $encoding, $fileName );
				break;
			case Logic_Mail::LIBRARY_MAIL_V2:
				$this->mail->addAttachment( $filePath, $mimeType, $encoding, $fileName );
				break;
		}
		return $this;
	}

	public function getAttachments(): array
	{
		$list			= [];
		$libraries		= $this->logicMail->detectAvailableMailLibraries();
		$library		= $this->logicMail->detectMailLibraryFromMailObjectInstance( $this );

		if( !$library || !( $libraries & $library ) )
			throw new RuntimeException( 'Mail was created by a mail library which is not supported' );

		foreach( $this->mail->getParts() as $part ){
			switch( $library ){
				case Logic_Mail::LIBRARY_COMMON:
					if( $part instanceof Net_Mail_Attachment )
						$list[]	= $part;
					break;
				case Logic_Mail::LIBRARY_MAIL_V1:
					if( $part instanceof CeusMedia\Mail\Part\Attachment )
						$list[]	= $part;
					break;
				case Logic_Mail::LIBRARY_MAIL_V2:
					if( $part->isAttachment() )
						$list[]	= $part;
					break;
			}
		}
		return $list;
	}

	/**
	 *	Returns generated or rendered HTML or plain text content.
	 *	@param		string		$key		Content key (htmlGenerated, htmlRendered, textGenerated, textRendered)
	 *	@return		string|NULL
	 *	@throws		DomainException			if content key is not valid
	 */
	public function getContent( string $key ): ?string
	{
		if( !array_key_exists( $key, $this->contents ) )
			throw new DomainException( 'Invalid content key' );
		return $this->contents[$key];
	}

	public function getPage()
	{
		return $this->page;
	}

	/**
	 *	Returns set subject of mail.
	 *	@access		public
	 *	@return		string		Subject set for mail
	 */
	public function getSubject(): string
	{
		return $this->mail->getSubject();
	}

	public function getTemplateId()
	{
		$template	= $this->getTemplateToUse( 0, TRUE, FALSE );
		if( $template )
			return $template->mailTemplateId;
		return 0;
	}

	public function initTransport( bool $verbose = FALSE ): self
	{
		if( empty( $this->logicMail ) )
			$this->logicMail	= $this->env->getLogic()->get( 'Mail' );
		$libraries	= $this->logicMail->detectAvailableMailLibraries();
		$options	= $this->env->getConfig()->getAll( 'module.resource_mail.transport.', TRUE );
		switch( strtolower( $options->get( 'type' ) ) ){
			case 'smtp':
				if( $libraries & ( Logic_Mail::LIBRARY_MAIL_V1 | Logic_Mail::LIBRARY_MAIL_V2 ) ){
					$this->transport	= \CeusMedia\Mail\Transport\SMTP::getInstance(
						$options->get( 'hostname' ),
						$options->get( 'port' ),
						$options->get( 'username' ),
						$options->get( 'password' )
					);
				}
				else if( $libraries & Logic_Mail::LIBRARY_COMMON ){
					$hostname	= $options->get( 'hostname' );
					$port		= $options->get( 'port' );
					$this->transport	= new Net_Mail_Transport_SMTP( $hostname, $port );
					$this->transport->setAuthUsername( $options->get( 'username' ) );
					$this->transport->setAuthPassword( $options->get( 'password' ) );
				}
				else
					throw new RuntimeException( 'No supported mail library available' );
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
		return $this;
	}

	/**
	 *	Sends mail to an email address.
	 *	@access		public
	 *	@param		stdClass	$user		User model object
	 *	@return		boolean		TRUE if success
	 */
	public function sendTo( $user )
	{
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
	public function sendToUser( $userId ): bool
	{
		if( !$this->env->getModules()->has( 'Resource_Users' ) )
			throw new RuntimeException( 'Module "Resource_Users" is not installed' );
		$model	= new Model_User( $this->env );
		$user	= $model->get( $userId );
		if( !$user )
			throw new RuntimeException( 'User with ID '.$userId.' is not existing' );
		return $this->sendTo( $user );
	}

	public function setEnv( CMF_Hydrogen_Environment $env ): self
	{
		$this->env		= $env;
		return $this;
	}

	/**
	 *	Sets address of mail sender.
	 *	@access		public
	 *	@param		string		$sender		Mail address of sender
	 *	@return		self
	 */
	public function setSender( $sender ): self
	{
		$this->mail->setSender( $sender );
		return $this;
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
	 *	@return		self
	 */
	public function setSubject( string $subject, bool $usePrefix = TRUE, bool $useTemplate = TRUE ): self
	{
		$template	= $this->options->get( 'subject.template' );
		$prefix		= $this->options->get( 'subject.prefix' );

		if( $useTemplate && strlen( trim( $template ) ) )
			$subject	= sprintf( $template, $subject );
		if( $usePrefix && strlen( trim( $prefix ) ) )
			$subject	= trim( $prefix ).' '.$subject;

		$host	= '';
		if( $this->env instanceof CMF_Hydrogen_Environment_Remote ){
			$logic	= Logic_Frontend::getInstance( $this->env );
			$host	= parse_url( $logic->getUrl(), PHP_URL_HOST );
		}
		else if( $this->env instanceof CMF_Hydrogen_Environment_Web ){
			$host	= $this->env->host ?? parse_url( $this->baseUrl, PHP_URL_HOST );
		}

		$subject	= UI_Template::renderString( $subject, ['app' => [
			'title'	=> $this->env->getConfig()->get( 'app.name' ),
			'host'	=> $host,
		]] );
		$this->mail->setSubject( $subject, $this->encodingSubject );
		return $this;
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
	protected function __onInit()
	{
	}

	protected function addBodyClass( string $class ): self
	{
		if( strlen( trim( $class ) ) )
			$this->bodyClasses[]	= $class;
		return $this;
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
	protected function addHtmlBody( $html )
	{
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
	protected function addTextBody( string $text ): self
	{
		CMF_Hydrogen_Deprecation::getInstance()
			->setVersion( $this->env->getModules()->get( 'Resource_Mail' )->version )
			->setErrorVersion( '0.8.9' )
			->setExceptionVersion( '0.9' )
			->message( 'Abstract_Mail::addTextBody is deprecated, use ::setText instead' );
		$this->setText( $text );
		return $this;
	}

	protected function addPrimerStyle( string $fileName ): bool
	{
		$config		= $this->env->getConfig();
		$path		= $config->get( 'path.themes' ).$config->get( 'layout.primer' ).'/css/';
		return $this->addStyle( $path.$fileName );
	}

	protected function addScriptFile( string $fileName ): bool
	{
		$filePath	= $this->env->getConfig()->get( 'path.scripts' ).$fileName;
		if( !file_exists( $filePath ) )
			return FALSE;
		$script	= FS_File_Reader::load( $filePath );
		$tag	= UI_HTML_Tag::create( 'script', $script, ['type' => 'text/javascript'] );
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
	protected function addStyle( string $filePath ): bool
	{
		if( !file_exists( $filePath ) )
			return FALSE;
		if( in_array( $filePath, $this->addedStyles ) )
			return FALSE;
		$style	= FS_File_Reader::load( $filePath );
//		$style	= str_replace( '(/lib/', '(http://'.getEnv( 'HTTP_HOST' ).'/lib/', $style );

		$path	= dirname( $filePath );
		$style	= str_replace( '(../../../', '('.$this->env->url.dirname( dirname( dirname( $path ) ) ).'/', $style );
		$style	= str_replace( '(../../', '('.$this->env->url.dirname( dirname( $path ) ).'/', $style );
		$style	= str_replace( '(../', '('.$this->env->url.dirname( $path ).'/', $style );
		$tag	= UI_HTML_Tag::create( 'style', $style, ['type' => 'text/css'] );
		$this->page->addHead( $tag );
		$this->addedStyles[]	= $filePath;
		return TRUE;
	}

	protected function addCommonStyle( string $filePath ): bool
	{
		$config		= $this->env->getConfig();
		$path		= $config->get( 'path.themes' ).'/common/css/';
		return $this->addStyle( $path.$filePath );
	}

	protected function addThemeStyle( string $filePath ): bool
	{
		$config		= $this->env->getConfig();
		$path		= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/css/';
		return $this->addStyle( $path.$filePath );
	}

	protected function applyTemplateToHtml( string $content, $templateId = NULL ): string
	{
		$messenger	= $this->env->getMessenger();
		$libraries	= $this->logicMail->detectAvailableMailLibraries();
		$template	= $this->getTemplateToUse( $templateId, TRUE, FALSE );
		if( !$template )
			return $content;
		$wordsMain		= $this->env->getLanguage()->getWords( 'main' );
		$baseUrl		= $this->env->getBaseUrl();
		$replacements	= [
			'content'		=> $content,
			'template.css'	=> $template->css,
			'app.email'		=> $this->env->getConfig()->get( 'app.email' ),
			'app.url'		=> $baseUrl,
			'app.host'		=> parse_url( $baseUrl, PHP_URL_HOST ),
			'app.path'		=> rtrim( parse_url( $baseUrl, PHP_URL_PATH ), '/' ),
			'app.title'		=> $wordsMain['main']['title'],
		];
		$contentFull	= $template->html;
		foreach( $replacements as $key => $value )
		 	$contentFull	= str_replace( '[#'.$key.'#]', $value, $contentFull );

		if( $template->images ){
			if( strlen( trim( $template->images ) ) && preg_match( "/^[a-z0-9]/", $template->images ) )
				$template->images	= json_encode( explode( ",", $template->images ) );
			foreach( json_decode( $template->images, TRUE ) as $nr => $image ){
				if( preg_match( '/^http/', $image ) )
					throw new Exception( 'Not implemented yet' );
				else{
					if( !file_exists( $this->env->uri.$image ) ){
//						throw new RuntimeException( 'Loading image from "'.$image.'" failed' );
						$messenger->noteError( 'Loading image from "'.$this->env->uri.$image.'" failed.' );
						continue;
					}
				}
				if( $libraries & Logic_Mail::LIBRARY_MAIL_V1 )
					$this->mail->addHtmlImage( 'image'.( $nr + 1), $this->env->uri.$image );
				else if( $libraries & Logic_Mail::LIBRARY_MAIL_V2 )
					$this->mail->addInlineImage( 'image'.( $nr + 1), $this->env->uri.$image, NULL, 'base64' );
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
						$messenger->noteError( 'Loading mail style from "'.$style.'" failed.' );
						continue;
					}
				}
				else{
					if( !file_exists( $this->env->uri.$style ) ){
						$messenger->noteError( 'Loading mail style from "'.$this->env->uri.$style.'" failed.' );
						continue;
					}
					$content	= FS_File_Reader::load( $this->env->uri.$style );
				}
	//			$content	= preg_replace( '/\/\*.*\*\//su', '', $content );
				$styleTag	= UI_HTML_Tag::create( 'style', $content, ['type' => 'text/css'] );
				$this->page->addHead( $styleTag );
			}
		}
		if( $template->css ){
			$styleTag	= UI_HTML_Tag::create( 'style', $template->css, ['type' => 'text/css'] );
			$this->page->addHead( $styleTag );
		}
		return $contentFull;
	}

	/**
	 *	Applies detected mail template to generated mail content, if available.
	 *
	 *	@access		protected
	 *	@param		string		$content		...
	 *	@param		integer		$templateId		ID of template to use in favor of defaults (must be usable)
	 *	@return		string						Fully rendered content
	 */
	protected function applyTemplateToText( string $content, $templateId = NULL ): string
	{
		$template	= $this->getTemplateToUse( $templateId, TRUE, FALSE );
		if( !$template )
			return $content;
		$wordsMain		= $this->env->getLanguage()->getWords( 'main' );
		$baseUrl		= $this->env->getBaseUrl();
		$replacements	= [
			'content'		=> $content,
			'template.css'	=> $template->css,
			'app.email'		=> $this->env->getConfig()->get( 'app.email' ),
			'app.url'		=> $baseUrl,
			'app.host'		=> parse_url( $baseUrl, PHP_URL_HOST ),
			'app.path'		=> rtrim( parse_url( $baseUrl, PHP_URL_PATH ), '/' ),
			'app.title'		=> $wordsMain['main']['title'],
		];
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
	 *		return $this;
	 */
	abstract protected function generate(): self;

	/**
	 *	Get template to use.
	 *	Having a template ID set, this template will be forced.
	 *	Needed for preview and testing.
	 *
	 *	Otherwise detects best template to use by looking for:
	 *	- given template ID, realizing mail settings of mail class of a module
	 *	- active mail template ID within database
	 *	- default mail template ID of mail resource module of frontend application (if considered)
	 *	- default mail template ID of mail resource module
	 *	The first of these templates being usable will be stored and returned.
	 *	@access		public
	 *	@param		integer		$preferredTemplateId	Template ID to override database and module defaults, if usable
	 *	@param		boolean		$considerFrontend		Flag: consider mail resource module of frontend, if available
	 *	@param		boolean		$strict					Flag: throw exception if something goes wrong
	 *	@return		objects		Model entity object of detected mail template
	 */
	protected function getTemplateToUse( $preferredTemplateId = 0, bool $considerFrontend = FALSE, bool $strict = TRUE )
	{
		if( $this->templateId )
			if( ( $template = $this->modelTemplate->get( $this->templateId ) ) )
				return $template;
		return $this->logicMail->detectTemplateToUse( $preferredTemplateId, $considerFrontend, $strict );
	}

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		string		$topic		Locale file key, eg. test/my
	 *	@param		string|NULL	$section	Section in locale file
	 *	@return		array
	 */
	protected function getWords( string $topic, ?string $section = NULL ): array
	{
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
	protected function sendToAddress( $email )
	{
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
	protected function setHtml( string $content, $templateId = 0 ): self
	{
		if( !$templateId && isset( $this->data['mailTemplateId' ] ) )
			$templateId	= $this->data['mailTemplateId' ];

		$contentFull	= $this->applyTemplateToHtml( $content, $templateId );

		if( !preg_match( '/(<html>|<head>)/', $contentFull ) ){
			$page	= $this->getPage();
			$page->addBody( $contentFull );

	/*		$page->addMetaTag( 'name', 'viewport', join( ', ', [
				'width=device-width',
				'initial-scale=1.0',
				'minimum-scale=0.75',
				'maximum-scale=2.0',
				'user-scalable=yes',
			] ) );*/

			$classes	= array_merge( ['mail'], $this->bodyClasses );
			$options	= $this->env->getConfig()->getAll( 'module.ui_css_panel.', TRUE );
			if( count( $options->getAll() ) )
				$classes[]	= 'content-panel-style-'.$options->get( 'style' );
			$contentFull	= $page->build( ['class' => $classes] );
		}
		$this->contents['htmlGenerated']	= $content;
		$this->contents['htmlRendered']		= $contentFull;
		$this->mail->addHTML( $contentFull, 'UTF-8', $this->encodingHtml );
		return $this;
	}

	/**
	 *	Sets ID of mail template to force.
	 *	Needed for preview and testing.
	 *	Set to 0 to return to detection mode.
	 *	@access		protected
	 *	@param		integer		$templateId		Forced template ID
	 *	@return 	self
	 */
	protected function setTemplateId( $templateId )
	{
		$this->templateId	= $templateId;
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
	protected function setText( string $content, $templateId = 0 ): self
	{
		if( !$templateId && isset( $this->data['mailTemplateId' ] ) )
			$templateId	= $this->data['mailTemplateId' ];
		$contentFull	= $this->applyTemplateToText( $content, $templateId );
		$this->contents['textGenerated']	= $content;
		$this->contents['textRendered']		= $contentFull;
		$this->mail->addText( $contentFull, 'UTF-8', $this->encodingText );
		return $this;
	}
}
