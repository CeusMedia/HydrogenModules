<?php
use CeusMedia\Mail\Mailbox;
use CeusMedia\Mail\Mailbox\Connection;

abstract class Logic_Import_Connector_MailAbstract extends Logic_Import_Connector_Abstract implements Logic_Import_Connector_Interface
{
	protected ?Mailbox $mailbox		= NULL;

	protected Connection $resource;

	public function connect()
	{
		if( !$this->connection )
			throw new RuntimeException( 'No connection set') ;
		if( !$this->connection->hostName )
			die( 'Error: No mail host defined.' );
//		if( !$this->connection->hostPath )
//			die( 'Error: No mailbox address defined.' );
		if( !$this->connection->authUsername )
			die( 'Error: No mailbox user name defined.' );
		if( !$this->connection->authPassword )
			die( 'Error: No mailbox user password defined.' );
		$this->resource	= new Connection(
			$this->connection->hostName,
			$this->connection->authUsername,
			$this->connection->authPassword
		);
		$this->resource->setSecure( TRUE, TRUE );
		$this->resource->connect();
		$this->mailbox	= new Mailbox( $this->resource );
		return $this;
	}

	public function disconnect()
	{
		if( !$this->connection || !$this->mailbox )
			throw new RuntimeException( 'No connection set') ;
		$this->resource->disconnect();
	}

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

	public function getFolders( bool $recursive = FALSE ): array
	{
		return $this->mailbox->getFolders( $recursive );
	}
}
