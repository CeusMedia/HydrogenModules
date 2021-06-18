<?php
use CeusMedia\Mail\Mailbox;
use CeusMedia\Mail\Mailbox\Mail;
use CeusMedia\Mail\Mailbox\Search;
use CeusMedia\Mail\Message;

class Logic_Import_Connector_Mailbox extends Logic_Import_Connector_Abstract implements Logic_Import_Connector_Interface
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
		$this->mailbox	= new Mailbox(
			$this->connection->hostName,
			$this->connection->authUsername,
			$this->connection->authPassword
		);
		$this->mailbox->setSecure( TRUE, TRUE );
		$this->mailbox->connect();
		return $this;
	}

	public function find( $conditions, $orders = array(), $limit = array() ): array
	{
		$list		= [];
		$mailIds    = $this->mailbox->index( $conditions );
		$mailIds	= array_slice( $mailIds, $limit[0], $limit[1] );
		foreach( $mailIds as $mailId ){
			$message	= $this->mailbox->getMailAsMessage( $mailId );
			$list[$mailId]	= $message;
/*			if( !$message->hasAttachments() )
				continue;
			foreach( $message->getAttachments() as $part ){
				$list[$mailId]	= $part->getContent();
				break;
			}*/
		}
		return $list;
	}

	public function disconnect()
	{
		$this->mailbox->disconnect();
	}

	public function renameTo( $id, $newName )
	{

	}

	public function moveTo( $id, $target )
	{

	}
}
