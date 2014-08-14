<?php
class Resource_XMPP{

	protected $env;
	protected $options;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env		= $env;
		$this->options	= $env->getConfig()->getAll( 'module.resource_xmpp.', TRUE );
	}

	static public function ___onModulesInit( CMF_Hydrogen_Environment_Abstract $env ){
		$env->set( 'xmpp', new self( $env ) );
		if( $env->modules )
			$env->modules->callHook( 'XMPP', 'init', $env );
	}

	protected function getDefaultSenderJid(){
		$sender			= $this->options->getAll( 'sender.', TRUE );
		$senderJid		= $sender->get( 'node' ).'@'.$sender->get( 'domain' );
		$senderJid		.= $sender->get( 'resource' ) ? '/'.$sender->get( 'resource' ) : '';
		return $senderJid;
	}

	public function sendMessageTo( $message, $receiverJid, $senderJid = NULL, $senderPassword = NULL ){
		if( strlen( trim( $senderJid ) ) && !strlen( trim( $senderPassword ) ) )
			throw new InvalidArgumentException( 'No password given' );

		if( !$senderJid ){
			$senderJid		= $this->getDefaultSenderJid();
			$senderPassword	= $this->options->get( 'sender.password' );
		}
		try{
			$sender	= new Net_XMPP_MessageSender();
			$sender->setEncryption( (bool) $this->options->get( 'encryption' ) );
			$sender->connect( new Net_XMPP_JID( $senderJid ), $senderPassword );
			$sender->setReceiver( new Net_XMPP_JID( $receiverJid ) );
			$sender->sendMessage( $message );
		}
		catch( Exception $e ){
			throw new RuntimeException( 'Sending message failed: '.$e->getMessage(), 0, $e );
		}
	}
}
?>
