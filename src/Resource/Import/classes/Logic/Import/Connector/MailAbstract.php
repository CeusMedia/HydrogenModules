<?php

use CeusMedia\Mail\Mailbox;
use CeusMedia\Mail\Mailbox\Connection;

abstract class Logic_Import_Connector_MailAbstract extends Logic_Import_Connector_Abstract implements Logic_Import_Connector_Interface
{
	/**	@var	Mailbox|NULL		$mailbox */
	protected ?Mailbox $mailbox		= NULL;

	/**	@var	Connection			$resource */
	protected Connection $resource;

	/**
	 *	@return		self
	 */
	public function connect(): self
	{
		if( !$this->connection )
			throw new RuntimeException( 'No connection set' );
		if( !$this->connection->hostName )
			throw new RuntimeException( 'No mail host defined' );
//		if( !$this->connection->hostPath )
//			die( 'Error: No mailbox address defined.' );
		if( !$this->connection->authUsername )
			throw new RuntimeException( 'No mailbox user name defined' );
		if( !$this->connection->authPassword )
			throw new RuntimeException( 'No mailbox user password defined' );
		$this->resource	= new Connection(
			$this->connection->hostName,
			$this->connection->authUsername,
			$this->connection->authPassword
		);
		$this->resource->setSecure();
		$this->resource->connect();
		$this->mailbox	= new Mailbox( $this->resource );
		return $this;
	}

	/**
	 *	@return		void
	 */
	public function disconnect(): void
	{
		if( !$this->connection || !$this->mailbox )
			throw new RuntimeException( 'No connection set') ;
		$this->resource->disconnect();
	}

	/**
	 *	@param		int			$mailId
	 *	@param		string		$newName
	 *	@return		bool
	 */
	public function renameTo( int $mailId, string $newName ): bool
	{
		return FALSE;
	}

	/**
	 *	@param		integer		$mailId			Mail UID
	 *	@param		string		$targetFolder	Target folder, encoded as UTF-8, will be encoded to UTF-7-IMAP internally
	 *	@return		bool
	 */
	public function moveTo( int $mailId, string $targetFolder ): bool
	{
		return $this->mailbox->moveMail( $mailId, $targetFolder, TRUE );
	}

	/**
	 *	@param		bool		$recursive
	 *	@return		array
	 */
	public function getFolders( bool $recursive = FALSE ): array
	{
		return $this->mailbox->getFolders( $recursive );
	}
}
