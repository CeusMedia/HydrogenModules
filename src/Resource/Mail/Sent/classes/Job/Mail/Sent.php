<?php

/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

use CeusMedia\Mail\Address as MailAddress;
use CeusMedia\Mail\Mailbox\Connection as MailboxConnection;
use CeusMedia\Mail\Mailbox\Upload as MailboxUpload;
use CeusMedia\Mail\Message\Header\Field as MailHeaderField;

class Job_Mail_Sent extends Job_Abstract
{
	const STRATEGY_BUILD		= 'build';
	const STRATEGY_RAW			= 'raw';

	protected Model_Mail $modelMail;

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
		$strategy	= strtoupper( $this->parameters->get( '--strategy', self::STRATEGY_BUILD ) );

		$mailIds	= $this->pickMailIds();
		$nrMails	= count( $mailIds );
		if( $this->dryMode ){
			$this->out( 'DRY RUN - no changes will be made.' );
			$this->out( 'Would try to transfer '.$nrMails.' sent mails to IMAP mailbox folder.' );
			return;
		}

		$config		= $this->env->getModules()->get( 'Resource_Mail_Sent' )->getConfigAsDictionary();

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
			$mail	= $this->modelMail->get( $mailId );
			try{
				$done	= match( $strategy ){
					'raw'		=> $this->copyMailFromRaw( $mail, $upload ),
					'build'		=> $this->copyMailByRebuild( $mailId, $upload ),
					default		=> FALSE,
				};
				if( $done ){
					$this->logicMail->removeMail( $mailId );							//  remove original mail from database
					$nrMailsTransferred++;
				}
			}
			catch( Exception $e ){
				$this->logException( $e );
			}
			catch( Throwable $t ){
				$this->log( 'Mail #'.$mailId.': '.$t->getMessage() );
//				$this->logicMail->decompressMailObject( $mail, FALSE );
//				// ...
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
		$this->modelMail	= new Model_Mail( $this->env );
		require_once $this->env->path.'/vendor/ceus-media/common/src/compat8.php';					//  enable compatibility mode
	}

	/**
	 *	Strategy: Use mail object from database and rebuild raw message for IMAP folder.
	 *	Extends raw message by TO and DATE headers.
	 *	This is the currently working strategy.
	 *	@param		int|string			$mailId
	 *	@param		MailboxUpload		$upload
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function copyMailByRebuild( int|string $mailId, MailboxUpload $upload ): bool
	{
		$mail		= $this->logicMail->getMail( $mailId );					//  load mail with decompression
		$message	= $mail->objectInstance->mail;

		$recipient	= MailAddress::getInstance( $mail->receiverAddress );
		if( '' !== ( $mail->receiverName ?? '' ) )
			$recipient->setName( $mail->receiverName );
		$message->addHeader( new MailHeaderField( 'To', $recipient->get() ) );
		$message->addHeader( new MailHeaderField( 'Date', date( 'r', $mail->sentAt ) ) );
		return $upload->storeMessage( $message );						//  upload mail message
	}

	/**
	 *	Strategy: Use raw message from database and send it.
	 *	Deprecation: This is incomplete. TO and DATE are missing. Do not use!
	 *	@param		Entity_Mail			$mail
	 *	@param		MailboxUpload		$upload
	 *	@return		bool
	 */
	protected function copyMailFromRaw( Entity_Mail $mail, MailboxUpload $upload ): bool
	{
		if( '' === trim( $mail->raw ?? '' ) )
			return FALSE;
		$this->logicMail->decompressMailRaw( $mail );						//  ... but decompress it ...
		return $upload->storeRawMessage( $mail->rawInflated );			//  ... before upload to IMAP folder
	}

	/**
	 *	Applies given CLI parameters to pick a list of IDs of matching mail entities
	 *	@return		array<int|string>		List of IDs of found mail entities
	 *	@throws		DateMalformedIntervalStringException
	 */
	protected function pickMailIds(): array
	{
		$age		= strtoupper( $this->parameters->get( '--age', '1Y' ) );
		$threshold	= date_create()->sub( new DateInterval( 'P'.$age ) );

		$class		= $this->parameters->get( '--class' );
		if( NULL !== $class ){
			$class	= preg_split( '/\s*,\s*/', $class );
			foreach( $class as $nr => $mailClassName )
				if( !preg_match( '/\\\/', $mailClassName ) )
					$class[$nr]	= 'Mail_'.$mailClassName;
		}
		return $this->logicMail->getQueuedMails( [
			'status'		=> $this->statusesHandledMails,
			'mailClass'		=> $class,
			'enqueuedAt' 	=> '< '.$threshold->format( 'U' ),
		], ['mailId' => 'ASC'], [
			max( 0, (int) $this->parameters->get( '--offset', '0' ) ),
			max( 1, (int) $this->parameters->get( '--limit', '1000' ) ),
		], ['mailId'] );
	}
}