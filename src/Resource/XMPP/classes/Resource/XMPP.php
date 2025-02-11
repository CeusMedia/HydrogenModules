<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\XMPP\JID as XmppJid;
use CeusMedia\Common\Net\XMPP\MessageSender as XmppMessageSender;
use CeusMedia\HydrogenFramework\Environment;

class Resource_XMPP
{
	protected Environment $env;
	protected Dictionary $options;

	public function __construct( Environment $env ){
		$this->env		= $env;
		$this->options	= $env->getConfig()->getAll( 'module.resource_xmpp.', TRUE );
	}

	/**
	 *	@param		Environment		$env
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public static function ___onModulesInit( Environment $env ): void
	{
		$env->set( 'xmpp', new self( $env ) );
		if( $env->getModules()->count() )
			$env->getModules()->callHook( 'XMPP', 'init', $env );
	}

	/**
	 *	@return		string
	 */
	protected function getDefaultSenderJid(): string
	{
		$sender			= $this->options->getAll( 'sender.', TRUE );
		$senderJid		= $sender->get( 'node' ).'@'.$sender->get( 'domain' );
		$senderJid		.= $sender->get( 'resource' ) ? '/'.$sender->get( 'resource' ) : '';
		return $senderJid;
	}

	/**
	 *	@param		string		$message
	 *	@param		string		$receiverJid
	 *	@param		?string		$senderJid
	 *	@param		?string		$senderPassword
	 *	@return		void
	 */
	public function sendMessageTo( string $message, string $receiverJid, ?string $senderJid = NULL, ?string $senderPassword = NULL ): void
	{
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
