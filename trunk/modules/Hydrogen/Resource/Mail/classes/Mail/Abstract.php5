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
	protected $mail;
	/** @var		UI_HTML_PageFrame					$page			Empty page oject for HTML mails */
	protected $page;
	/** @var		Net_Mail_Transport_Abstract			$transport		Mail transport object, build on construction */
	protected $transport;
	/** @var		CMF_Hydrogen_View					$view			General view instance */
	protected $view;
	
	/**
	 *	Contructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment object
	 *	@param		araray								$data		Map of template mail data
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env, $data = array() ){
		$this->env	= $env;
		$this->mail	= new Net_Mail();
		$this->view	= new CMF_Hydrogen_View( $env );
		$this->page	= new UI_HTML_PageFrame();

		$config		= $this->env->getConfig();
		$this->page->setBaseHref( $config->get( 'app.base.url' ) );
		$this->addThemeStyle( 'mail.min.css' );
		$this->addScriptFile( 'mail.min.js' );
		
		switch( strtolower( $config->get( 'module.resource_mail.transport.type' ) ) ){
			case 'smtp':
				$hostname	= $config->get( 'module.resource_mail.transport.hostname' );
				$username	= $config->get( 'module.resource_mail.transport.username' );
				$password	= $config->get( 'module.resource_mail.transport.password' );
				$this->transport	= new Net_Mail_Transport_SMTP( $hostname );
				$this->transport->setAuthUsername( $username );
				$this->transport->setAuthPassword( $password );
				break;
			case 'local':
			case 'default':
			case 'sendmail':
				$this->transport	= new Net_Mail_Transport_Default();
				break;
			default:
				throw new RuntimeException( 'No mail transport configured' );
		}
		$this->mail->setSender( $config->get( 'module.resource_mail.sender.system' ) );
		$this->generate( $data ); 
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
		$script	= File_Reader::load( $filePath );
		$script	= str_replace( '(/lib/', '(http://'.getEnv( 'HTTP_HOST' ).'/lib/', $script );
		$tag	= UI_HTML_Tag::create( 'script', $script, array( 'type' => 'text/javascript' ) );
		$this->page->addHead( $tag );
		return TRUE;
	}
	
	protected function addStyle( $filePath ){
		if( !file_exists( $filePath ) )
			return FALSE;
		$style	= File_Reader::load( $filePath );
		$style	= str_replace( '(/lib/', '(http://'.getEnv( 'HTTP_HOST' ).'/lib/', $style );
		$tag	= UI_HTML_Tag::create( 'style', $style, array( 'type' => 'text/css' ) );
		$this->page->addHead( $tag );
		return TRUE;
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
	 *	@return		void
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
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		string		$topic		Locale file key, eg. test/my
	 *	@param		string		$section	Section in locale file
	 *	@return		void
	 */
	protected function getWords( $topic, $section = NULL ){
		if( empty( $section ) )
			return $this->env->getLanguage()->getWords( $topic );
		return $this->env->getLanguage()->getSection( $topic, $section );
	}

	/**
	 *	Sends mail to an email address.
	 *	@access		public
	 *	@param		stdClass		$user		User model object
	 *	@return		void
	 */
	public function sendTo( $user ){
		if( empty( $user->email ) )
			RuntimeException( 'User object invalid: no email address' );
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
		$this->mail->setReceiver( $email );
		$this->transport->send( $this->mail );
	}

	/**
	 *	Sends mail to user by its user ID.
	 *	Attention: Module "Users" must be installed to use this feature.
	 *	@access		public
	 *	@param		integer		$userId		ID of user to send mail to
	 *	@return		void
	 */
	public function sendToUser( $userId ){
		if( !$this->env->getModules()->has( 'Users' ) )
			throw new RuntimeException( 'Module "Users" is not installed' );
		$model	= new Model_User( $this->env );
		$user	= $model->get( $userId );
		if( !$user )
			throw new RuntimeException( 'User with ID '.$userId.' is not existing' );
		$this->sendTo( $user );
	}
}
?>