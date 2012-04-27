<?php
/**
 *	Abstract mail class.
 *	Prepares mail and mail transport object.
 *	Sends generated mail using configured mail transport.
 *	Attention: This class needs to be extended by method generate().
 */
abstract class Mail_Abstract{

	/**	@var		Net_Mail					$mail			Mail objectm, build on construction */
	protected $mail;
	/** @var		Net_Mail_Transport_Abstract	$transport		Mail transport object, build on construction */
	protected $transport;
	
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
		
		$config	= $this->env->getConfig();
		switch( strtolower( $config->get( 'module.mail.transport.type' ) ) ){
			case 'smtp':
				$hostname	= $config->get( 'module.mail.transport.hostname' );
				$username	= $config->get( 'module.mail.transport.username' );
				$password	= $config->get( 'module.mail.transport.password' );
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
		$this->mail->setSender( $config->get( 'module.mail.sender.system' ) );
		$this->generate( $data ); 
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
	 *	Sends mail to email address using configured transport.
	 *	@access		public
	 *	@param		string		$email		Target email address
	 *	@return		void
	 */
	public function sendToAddress( $email ){
		$this->mail->setReceiver( $email );
		$this->transport->send( $this->mail );
	}

	public function sendToUser( $userId ){
		
	}
}
?>