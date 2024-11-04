<?php

/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

use CeusMedia\Mail\Mailbox\Connection as MailboxConnection;
use CeusMedia\Mail\Mailbox\Upload as MailboxUpload;
use CeusMedia\Mail\Message;

class Job_Mail_Sent extends Job_Abstract
{
	protected Model_Mail $model;

	protected Logic_Mail $logicMail;

	protected array $statusesHandledMails	= [
		Model_Mail::STATUS_SENT,																//  status: 2
		Model_Mail::STATUS_RECEIVED,															//  status: 3
		Model_Mail::STATUS_OPENED,																//  status: 4
		Model_Mail::STATUS_REPLIED,																//  status: 5
	];

	/**
	 *	Transfers sent mails from database table to IMAP folder.
	 *	Mails to be transferred can be filtered by minimum age and mail class(es).
	 *	Supports dry mode.
	 *
	 *	Parameters:
	 *		--age=PERIOD
	 *			- minimum age of mail to delete
	 *			- DateInterval period without starting P and without any time elements
	 *			- see: https://www.php.net/manual/en/dateinterval.construct.php
	 *			- example: 1Y (1 year), 2M (2 months), 3D (3 days)
	 *			- optional, default: 1Y
	 *		--class=CLASSNAME[,CLASSNAME]
	 *			- name of mail class to focus on
	 *			- without prefix 'Mail_'
	 *			- can be several, separated by comma
	 *			- example: Newsletter (for class Mail_Newsletter)
	 *			- example: Newsletter,Form_Manager_Filled
	 *			- default: empty, meaning all mail classes
	 *		--limit=NUMBER
	 *			- maximum number of mails to work on
	 *			- optional, default: 1000
	 *		--offset=NUMBER
	 *			- offset if using limit
	 *			- optional, default: 0
	 *
	 *	@access		public
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		DateMalformedIntervalStringException
	 */
	public function pull(): void
	{
		$age		= strtoupper( $this->parameters->get( '--age', '1Y' ) );
		$threshold	= date_create()->sub( new DateInterval( 'P'.$age ) );

		$class		= $this->parameters->get( '--class', NULL );
		if( NULL !== $class ){
			$class	= preg_split( '/\s*,\s*/', $class );
			foreach( $class as $nr => $mailClassName )
				if( !preg_match( '/\\\/', $mailClassName ) )
					$class[$nr]	= 'Mail_'.$mailClassName;
		}
		$mailIds	= $this->logicMail->getQueuedMails( [
			'status'		=> $this->statusesHandledMails,
			'mailClass'		=> $class,
			'enqueuedAt' 	=> '< '.$threshold->format( 'U' ),
		], ['mailId' => 'ASC'], [
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		], ['mailId'] );
		$nrMails	= count( $mailIds );
		if( $this->dryMode ){
			$this->out( 'DRY RUN - no changes will be made.' );
			$this->out( 'Would try to transfer '.$nrMails.' sent mails to IMAP mailbox folder.' );
			return;
		}

		$config		= $this->env->getModules()->get( 'Resource_Sent' )->getConfigAsDictionary();

		/** @var object{hostname: string, username: string, password: string, secure: bool, folder: string} $connect */
		$connect	= (object) $config->getAll( 'connect.' );

		$connection	= MailboxConnection::getInstance( $connect->hostname )
			->setAuth( $connect->username, $connect->password )
			->setSecure( $connect->secure );
		$upload		= new MailboxUpload( $connection, $connect->folder, MailboxUpload::FLAG_SEEN );

		$database	= $this->env->getDatabase();
		$database->beginTransaction();
		$nrMailsTransferred	= 0;
		/** @var int $nr */
		/** @var int|string $mailId */
		foreach( $mailIds as $nr => $mailId ){
			/** @var Entity_Mail $mail */
			$mail	= $this->model->get( $mailId );
			if( '' !== trim( $mail->raw ?? '' ) ){							//  use existing stored raw message
				$this->logicMail->decompressMailRaw( $mail );						//  ... but decompress it ...
				$done	= $upload->storeRawMessage( $mail->rawInflated );			//  ... before upload to IMAP folder
			}
			else{																	//  no raw message available
				$mail	= $this->logicMail->getMail( $mailId );						//  load mail with decompression
				$done	= $upload->storeMessage( $mail->objectInstance->mail );		//  upload mail message
			}
			if( $done ){
				$this->logicMail->removeMail( $mailId );							//  remove original mail from database
				$nrMailsTransferred++;
			}
			$this->showProgress( $nr + 1, $nrMails );
		}
		$database->commit();
		$this->out( 'Transferred '.$nrMailsTransferred.' sent mails from database to IMAP mailbox folder.' );

	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->logicMail	= new Logic_Mail( $this->env );
	}
}