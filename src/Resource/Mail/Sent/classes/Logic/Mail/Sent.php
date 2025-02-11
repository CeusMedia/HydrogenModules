<?php

use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\Mail\Mailbox as Mailbox;
use CeusMedia\Mail\Mailbox\Connection as MailboxConnection;
use CeusMedia\Mail\Mailbox\Mail;
use CeusMedia\Mail\Mailbox\Search as MailboxSearch;
use CeusMedia\Mail\Message as MailMessage;

class Logic_Mail_Sent extends Logic
{
	protected MailboxConnection $connection;

	/**
	 *	Returns list of mail objects, holding a mail ID and a connection.
	 *	A mail object is a container to lazy load parts of the original mail from server.
	 *	@access		public
	 *	@param		array|NULL		$criteria		List of IMAP search criteria, not supported atm.
	 *	@param		int				$limit			...
	 *	@param		int				$offset			...
	 *	@return		array<int|string,Mail>			List of mail IDs and objects
	 */
	public function getAll( array $criteria = NULL, int $limit = 10, int $offset = 0 ): array
	{
		$search		= new MailboxSearch();
		$search->setLimit( min( 1000, max( 1, abs( $limit ) ) ) );
		$search->setOffset( abs( $offset ) );

		$mailbox	= new Mailbox( $this->connection );
		return $mailbox->performSearch( $search );
	}

	protected function __onInit(): void
	{
		parent::__onInit(); // TODO: Change the autogenerated stub

		$config		= $this->env->getModules()->get( 'Resource_Sent' )->getConfigAsDictionary();

		/** @var object{hostname: string, username: string, password: string, secure: bool, folder: string} $connect */
		$connect	= (object) $config->getAll( 'connect.' );

		$this->connection	= MailboxConnection::getInstance( $connect['hostname'] )
			->setAuth( $connect['username'], $connect['password'] )
			->setSecure( $connect['secure'] );
	}
}