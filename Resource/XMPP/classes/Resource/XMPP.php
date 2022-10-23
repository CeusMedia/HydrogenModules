<?php

use CeusMedia\Common\Net\XMPP\JID as XmppJid;
use CeusMedia\Common\Net\XMPP\MessageSender as XmppMessageSender;
use CeusMedia\HydrogenFramework\Environment;

class Resource_XMPP{

	protected $env;
	protected $options;

	public function __construct( Environment $env ){
		$this->env		= $env;
		$this->options	= $env->getConfig()->getAll( 'module.resource_xmpp.', TRUE );
	}

	static public function ___onModulesInit( Environment $env ){
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
			$sender	= new XmppMessageSender();
			$sender->setEncryption( (bool) $this->options->get( 'encryption' ) );
			$sender->connect( new XmppJid( $senderJid ), $senderPassword );
			$sender->setReceiver( new XmppJid( $receiverJid ) );
			$sender->sendMessage( $message );
		}
		catch( Exception $e ){
			throw new RuntimeException( 'Sending message failed: '.$e->getMessage(), 0, $e );
		}
	}
}
?>
