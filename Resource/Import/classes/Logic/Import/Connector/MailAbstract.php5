<?php
use CeusMedia\Mail\Mailbox;
use CeusMedia\Mail\Mailbox\Connection;
use CeusMedia\Mail\Mailbox\Mail;
use CeusMedia\Mail\Mailbox\Search;
use CeusMedia\Mail\Message;

abstract class Logic_Import_Connector_MailAbstract extends Logic_Import_Connector_Abstract implements Logic_Import_Connector_Interface
{
	protected $connection;

	protected $mailbox;

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
		$connection	= new Connection(
			$this->connection->hostName,
			$this->connection->authUsername,
			$this->connection->authPassword
		);
		$connection->setSecure( TRUE, TRUE )->connect();
		$this->mailbox	= new Mailbox( $connection );
		return $this;
	}

	public function disconnect()
	{
		if( !$this->connection || !$this->mailbox )
			throw new RuntimeException( 'No connection set') ;
		$this->mailbox->disconnect();

	}

	public function renameTo( $id, $newName )
	{

	}

	public function moveTo( $id, $target )
	{

	}

	public function getFolders( bool $recursive = FALSE ): array
	{
		return $this->mailbox->getFolders( $recursive );
	}
}
