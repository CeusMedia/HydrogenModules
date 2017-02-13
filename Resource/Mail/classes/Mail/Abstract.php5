<?php
/**
 *	Abstract mail class.
 *	Prepares mail and mail transport object.
 *	Sends generated mail using configured mail transport.
 *	Attention: This class needs to be extended by method generate().
 */
abstract class Mail_Abstract{

	/**	@var		CMF_Hydrogen_Environment_Abstract	$env			Environment object */
	protected $env;
	/**	@var		Net_Mail							$mail			Mail objectm, build on construction */
	public $mail;
	/** @var		UI_HTML_PageFrame					$page			Empty page oject for HTML mails */
	protected $page;
	/** @var		Net_Mail_Transport_Abstract			$transport		Mail transport object, build on construction */
	protected $transport;
	/** @var		CMF_Hydrogen_View					$view			General view instance */
	protected $view;

	public $content;

	protected $baseUrl;

	protected $addedStyles	= array();

	/**
	 *	Contructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env			Environment object
	 *	@param		array								$data			Map of template mail data
	 *	@param		boolean								$defaultStyle	Flag: load default mail style file
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env, $data = array(), $defaultStyle = TRUE ){
		$this->setEnv( $env );
		$this->mail		= new \CeusMedia\Mail\Message();
		$this->view		= new CMF_Hydrogen_View( $env );
		$this->page		= new UI_HTML_PageFrame();
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
		$this->baseUrl	= isset( $env->baseUrl ) ? $env->baseUrl : NULL;

		$config		= $this->env->getConfig();
		if( !$this->baseUrl && $config->get( 'app.base.url' ) )
			$this->baseUrl = $config->get( 'app.base.url' );
		$this->page->setBaseHref( $this->baseUrl );
//		$this->mail->setSender( $config->get( 'module.resource_mail.sender.system' ) );

		if( $this->env->getModules()->has( 'UI_Bootstrap' ) ){
			$localPath	= $this->env->getConfig()->get( 'module.ui_bootstrap.local.path' );
			$version	= $this->env->getConfig()->get( 'module.ui_bootstrap.version' );
			$path		= sprintf( $localPath, $version );
			$this->addCommonStyle( $path.'/css/bootstrap.min.css' );
			$this->addCommonStyle( $path.'/css/bootstrap.responsive.min.css' );
		}
		else if( $defaultStyle )
			$this->addThemeStyle( 'mail.min.css' );

//		$this->addScriptFile( 'mail.min.js' );
		$this->initTransport();
		$this->mail->setSender( $this->options->get( 'sender.system' ) );
		$this->content	= $this->generate( $data );
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

	public function addAttachment( $filePath, $mimeType ){
		$attachment	= new \CeusMedia\Mail\Part\Attachment();
		$attachment->setFile( $filePath, $mimeType );
//		$this->mail->addAttachmentFile( $filePath, $mimeType );
		$this->mail->addAttachment( $attachment );
	}

	/**
	 *	Adds HTML body part to mail.
	 *	@access		protected
	 *	@param		string		$html		HTML mail body to add to mail
	 *	@return		void
	 *	@see		http://wiki.apache.org/spamassassin/Rules/BASE64_LENGTH_78_79
	 */
	protected function addHtmlBody( $html ){
/*		$base64	= base64_encode( $html );
		$body	= new Net_Mail_Body( $base64, Net_Mail_Body::TYPE_HTML, 'base64' );
		$body->wrapWords( 76 );
		$this->mail->addBody( $body );
*/
		$this->mail->addHTML( $html, 'UTF-8', 'base64' );
	}

	/**
	 *	Adds plain text body part to mail.
	 *	@access		protected
	 *	@param		string		$text		Plain text mail body to add to mail
	 *	@return		void
	 */
	protected function addTextBody( $text ){
/*		$base64	= base64_encode( $text );
		$body	= new Net_Mail_Body( $base64, Net_Mail_Body::TYPE_PLAIN, 'base64' );
		$body->wrapWords( 76 );
		$this->mail->addBody( $body );*/
		$this->mail->addText( $text, 'UTF-8', 'base64' );
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

	/**
	 *	Create mail body and sets subject and body on mail object.
	 *	@abstract
	 *	@access		protected
	 *	@param		array		$data		Map of body template data
	 *	@return		string		HTML or text rendered for mail
	 *	@example
	 *		$words			= $this->env->getLanguage()->getWords( 'auth', 'mails' );
	 *		$data['config']	= $this->env->getConfig()->getAll();
	 *
	 *		$subject		= $words['mails']['onRegister'];
	 *		$body			= $this->view->loadContentFile( 'mails/auth/register', $data );
	 *
	 *		$this->mail->setSubject( $subject );
	 *		$this->mail->addBody( new Net_Mail_Body( $body, Net_Mail_Body::TYPE_PLAIN ) );
	 *
	 */
	abstract protected function generate( $data = array() );

	/**
	 *	Returns set subject of mail.
	 *	@access		public
	 *	@return		string		Subject set for mail
	 */
	public function getSubject(){
		return $this->mail->getSubject();
	}

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

	public function initTransport(){
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
				$this->transport->setVerbose( FALSE );
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
	 *	@param		stdClass		$user		User model object
	 *	@return		void
	 */
	public function sendTo( $user ){
		if( is_array( $user ) )
			$user	= (object) $user;
		if( empty( $user->email ) )
			throw new RuntimeException( 'User object invalid: no email address' );
		$this->sendToAddress( $user->email );
	}

	/**
	 *	Sends mail to directly email address using configured transport.
	 *	ATTENTION: You SHOULD NOT use this method unless you REALLY NEED to.
	 *	Please use one of these methods instead: sendToUser(int $userId), sendTo(obj $user)
	 *	@access		protected
	 *	@param		string		$email		Target email address
	 *	@return		void
	 *	@todo		kriss: Notwendigkeit dieser Methode prüfen.
	 */
	protected function sendToAddress( $email ){
		if( $this->mail instanceof \CeusMedia\Mail\Message )
			$this->mail->addRecipient( $email );
		else
			$this->mail->setReceiver( $email );
		$this->transport->send( $this->mail, TRUE );
	}

	/**
	 *	Sends mail to user by its user ID.
	 *	Attention: Module "Users" must be installed to use this feature.
	 *	@access		public
	 *	@param		integer		$userId		ID of user to send mail to
	 *	@return		void
	 */
	public function sendToUser( $userId ){
		if( !$this->env->getModules()->has( 'Resource_Users' ) )
			throw new RuntimeException( 'Module "Resource_Users" is not installed' );
		$model	= new Model_User( $this->env );
		$user	= $model->get( $userId );
		if( !$user )
			throw new RuntimeException( 'User with ID '.$userId.' is not existing' );
		$this->sendTo( $user );
	}

	public function setEnv( CMF_Hydrogen_Environment_Abstract $env ){
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
		$this->mail->setSubject( $subject );
	}
}
?>
