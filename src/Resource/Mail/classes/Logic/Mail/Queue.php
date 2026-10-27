<?php

/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Constant as ObjectConstants;
use CeusMedia\Common\Exception\Conversion as ConversionException;
use CeusMedia\Common\Exception\MissingExtension as MissingExtensionException;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\Mail\Message\Renderer as MailMessageRendererV2;

/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@todo		code doc
 */
class Logic_Mail_Queue extends Logic
{
	protected Logic_Mail_Compressor $compressor;
	protected Logic_Mail $logicMail;
	protected Model_Mail $modelQueue;
	protected Dictionary $options;

	/**
	 *	@return		int
	 */
	public function abortMailsWithTooManyAttempts(): int
	{
		$mails		= $this->modelQueue->getAll( [
			'status'	=> Model_Mail::STATUS_RETRY,
			'attempts'	=> '>= '.$this->options->get( 'retry.attempts' ),
		] );
		foreach( $mails as $mail )
			$this->modelQueue->edit( $mail->mailId, ['status' => Model_Mail::STATUS_FAILED] );
		return count( $mails );
	}

	/**
	 *	Returns number of mails in queue by given conditions.
	 *	@access		public
	 *	@param		array		$conditions		Map of column conditions to look for
	 *	@return		integer						Number of mails in queue matching conditions
	 */
	public function countQueue( array $conditions = [] ): int
	{
		return $this->modelQueue->count( $conditions );
	}

	/**
	 *	@param		Mail_Abstract $mail
	 *	@param		Entity_User $receiver
	 *	@param		int|string|NULL $senderId
	 *	@return		int|string
	 *	@throws		ReflectionException
	 */
	public function enqueueMailToUser( Mail_Abstract $mail, Entity_User $receiver, int|string|NULL $senderId = NULL ): int|string
	{
		return $this->enqueueMail( $mail, $receiver->language, $receiver, $senderId );
	}

	/**
	 *	Send prepared mail later.
	 *	@access		public
	 *	@param		Mail_Abstract			$mail			Mail instance to be queued
	 *	@param		string					$language		Language key
	 *	@param		integer|string|object	$receiver		User ID or data object of receiver (must have member 'email', should have 'userId' and 'username')
	 *	@param		string|NULL				$senderId		Optional: ID of sending user
	 *	@return		int|string								ID of queued mail
	 *	@throws		InvalidArgumentException				If receiver is neither an object nor an ID
	 *	@throws		InvalidArgumentException				If receiver object is missing "email"
	 *	@throws		ReflectionException
	 */
	public function enqueueMail( Mail_Abstract $mail, string $language, int|string|object $receiver, ?string $senderId = NULL ): int|string
	{
		$receiver	= $this->logicMail->checkReceiver( $receiver );

		$mailDataObject		= Entity_Mail::fromArray( [
			'senderId'			=> (int) $senderId,
			'receiverId'		=> $receiver->userId ?? 0,
			'templateId'		=> $mail->getTemplateId(),
			'priority'			=> $mail->getPriority(),
			'senderAddress'		=> $mail->mail->getSender()->getAddress(),
			'receiverAddress'	=> $receiver->email,
			'receiverName'		=> $receiver->username ?? NULL,
			'language'			=> strtolower( trim( $language ) ),
			'subject'			=> $mail->getSubject(),
			'mailClass'			=> get_class( $mail ),
			'compression'		=> Logic_Mail_Compressor::getRecommendedCompression(),
			'object'			=> NULL,
			'objectInstance'	=> $mail,
			'raw'				=> NULL,
			'toBeSentAt'		=> $mail->getToBeSentAt(),
			'enqueuedAt'		=> time(),
			'attemptedAt'		=> 0,
			'sentAt'			=> 0,
		] );

		Logic_Mail_Compressor::compressMailObject( $mailDataObject );
		$this->regenerateRaw( $mailDataObject );

		return $this->modelQueue->add( $mailDataObject, FALSE );
	}

	/**
	 *	Returns saved mail as uncompressed object by mail ID.
	 *	@access		public
	 *	@param		int|string		$mailId			ID of queued mail
	 *	@return		Entity_Mail						Mail object from queue
	 *	@throws		OutOfRangeException				if mail ID is not existing
	 *	@throws		MissingExtensionException		If extension for decompression (BZIP or GZIP) is not installed
	 *	@throws		ConversionException				If decompression (using BZIP or GZIP) or decoding (Base64) failed
	 *	@throws		ConversionException				If deserialization failed
	 */
	public function getMail( int|string $mailId ): Entity_Mail
	{
		/** @var Entity_Mail $mail */
		$mail	= $this->modelQueue->get( $mailId );
		if( !$mail )
			throw new OutOfRangeException( 'Invalid mail ID: '.$mailId );
		Logic_Mail_Compressor::decompressMailObject( $mail );
		Logic_Mail_Compressor::decompressMailRaw( $mail );
		if( !$mail->objectInstance )
			throw new RuntimeException( 'Deserialization of mail object failed' );
		if( !$mail->rawInflated )
			throw new RuntimeException( 'Decompression of raw mail failed' );
		return $mail;
	}

	/**
	 *	Returns list of mails in queue.
	 *	@access		public
	 *	@param		array			$conditions		Map of Conditions to include in SQL Query
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$limits			Map of Limits to include in SQL Query
	 *	@param		array			$columns		List of columns to get
	 *	@return		array
	 */
	public function getMails( array $conditions = [], array $orders = [], array $limits = [], array $columns = [] ): array
	{
		//  for performance
		if( isset( $limits[0] ) && isset( $limits[1] ) && $limits[0] > 0 ){
			$mailIds	= $this->modelQueue->getAll( $conditions, $orders, $limits, ['mailId'] );
			return $this->modelQueue->getAll( ['mailId' => $mailIds], $orders );
		}
		return $this->modelQueue->getAll( $conditions, $orders, $limits, $columns );
	}

	/**
	 *	Return map of mail classes used in all queued mails and the number of related mails.
	 *	@access		public
	 *	@param		array			$conditions		Map of Conditions to include in SQL Query
	 *	@return 	array
	 *	@todo		remove check for model method "getDistinct" after next minor framework release (0.8.8)
	 */
	public function getUsedMailClassNames( array $conditions = [] ): array
	{
		$list			= [];
		$orders			= ['mailClass' => 'ASC'];
		if( method_exists( $this->modelQueue, 'getDistinct' ) )
			$mailClassNames	= $this->modelQueue->getDistinct( 'mailClass', $conditions, $orders );
		else
			$mailClassNames	= array_values( $this->logicMail->getMailClassNames() );
		foreach( $mailClassNames as $mailClassName )
			$list[$mailClassName]	= $this->modelQueue->count( ['mailClass' => $mailClassName] );
		return $list;
	}

	/**
	 *	Remove mail by its ID.
	 *	@access		public
	 *	@param		int|string			$mailId			ID of mail to remove
	 *	@return		boolean
	 */
	public function removeMail( int|string $mailId ): bool
	{
		return $this->modelQueue->remove( $mailId );
	}

	/**
	 *	Send prepared mail right now.
	 *	Calls hook Mail::onSent (with mail ID as payload) after mail has given to transport.
	 *	@access		public
	 *	@param		Entity_Mail|int|string		$mailObjectOrId
	 *	@param		boolean			$forceResent	Flag: send mail again although last attempt was successful
	 *	@return		boolean
	 *	@throws		RuntimeException			if mail already has been sent or enqueued
	 *	@todo		use logging on exception (=sending mail failed)
	 *	@throws		ReflectionException
	 */
	public function sendMail( Entity_Mail|int|string $mailObjectOrId, bool $forceResent = FALSE ): bool
	{
		if( is_object( $mailObjectOrId ) )
			$mail	= $mailObjectOrId;
		else{
			/** @var ?Entity_Mail $mail */
			$mail	= $this->getMail( $mailObjectOrId );
		}
		if( $mail->status > Model_Mail::STATUS_SENDING && !$forceResent )
			throw new RuntimeException( 'Mail already has been sent' );

		Logic_Mail_Compressor::decompressMailObject( $mail );

		$this->modelQueue->edit( $mail->mailId, [
			'status'		=> Model_Mail::STATUS_SENDING,
			'attempts'		=> $mail->attempts + 1,
			'attemptedAt'	=> time()
		] );
		try{
			$this->sendMailAndInform( $mail );
			return TRUE;
		}
		catch( Exception $e ){
//			remark( $e->getMessage() );
			$this->modelQueue->edit( $mail->mailId, [
				'status'	=> Model_Mail::STATUS_RETRY,
//				'error'		=> $e->getMessage(),
			] );
			throw new RuntimeException( 'Mail could not been sent: '.$e->getMessage(), 0, $e );
		}
	}

	/**
	 *	Set mail status.
	 *	New status will be validated against allowed status transitions.
	 *	@access		public
	 *	@param		Mail_Abstract|int|string	$mail		Mail object or ID
	 *	@param		integer					$status		Status to set, will be validated against allowed status transitions
	 *	@return 	boolean
	 *	@throws		DomainException			if given status is invalid
	 *	@throws		DomainException			if transition to new status is not allowed
	 *	@throws		ReflectionException
	 */
	public function setMailStatus( Mail_Abstract|int|string $mail, int $status ): bool
	{
		$mail			= $this->getMailFromObjectOrId( $mail );
		$mail->status	= (int) $mail->status;
		$modelStatuses	= ObjectConstants::staticGetAll( 'Model_Mail', 'STATUS_' );
		$statusMap		= array_flip( $modelStatuses );
		if( !in_array( $status, array_values( $modelStatuses ) ) )
			throw new DomainException( 'Invalid status: '.$status );
		if( $status === $mail->status )
			return FALSE;
		if( !in_array( $status, Model_Mail::$transitions[$mail->status] ) )
			throw new DomainException( 'Transition from status '.$statusMap[$mail->status].' to '.$statusMap[$status]. ' is not allowed' );
		return (bool) $this->modelQueue->edit( $mail->mailId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
	}

	//  --  STATIC PUBLIC  --  //

	//  --  PROTECTED  --  //


	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->compressor		= Logic_Mail_Compressor::getInstance( $this->env );
		$this->logicMail		= Logic_Mail::getInstance( $this->env );

		/*  --  INIT QUEUE  --  */
		$this->modelQueue		= new Model_Mail( $this->env );

		/*  --  DEFAULT PATHS  --  */
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
	}

	/**
	 *	@param		object|int|string		$mailObjectOrId
	 *	@return		object
	 */
	public function getMailFromObjectOrId( object|int|string $mailObjectOrId ): object
	{
		if( is_object( $mailObjectOrId ) )
			return $mailObjectOrId;
		if( !preg_match( '/^[0-9]+$/', $mailObjectOrId ) )
			throw new InvalidArgumentException( 'Arguments must be mail ID or mail object' );
		return $this->getMail( (int) $mailObjectOrId );
	}

	/**
	 *	Renders compressed raw mail message and applies it to given mail object for storage in database.
	 *	@param		Entity_Mail		$mail
	 *	@return		void
	 */
	public function regenerateRaw( Entity_Mail $mail ): void
	{
		$raw		= MailMessageRendererV2::render( $mail->objectInstance->mail );
		$mail->raw	= Logic_Mail_Compressor::compressString( $raw, $mail->compression );
	}

	/**
	 *	@param		Entity_Mail				$mail
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		RuntimeException		if module Resource_Users is not installed
	 *	@throws		RuntimeException		if user ID is not existing
	 *	@throws		RuntimeException		if user has no email address
	 *	@throws		RuntimeException		if message has no mail sender
	 *	@throws		RuntimeException		if message has no mail receivers
	 *	@throws		RuntimeException		if message has no mail body parts
	 *	@throws		RuntimeException		if connection to SMTP server failed
	 *	@throws		RuntimeException		if sending mail failed
	 */
	protected function sendMailAndInform( Entity_Mail $mail ): void
	{
		$transport	= Logic_Mail_Transporter::getInstance( $this->env )->getTransportByConfig();

		if( !empty( $mail->receiverId ) )
			$mail->objectInstance->sendToUserId( $mail->receiverId );
		else
			$mail->objectInstance->sendTo( (object) [
				'email'		=> $mail->receiverAddress,
				'username'	=> $mail->receiverName,
			] );

		$this->modelQueue->edit( $mail->mailId, [
			'status'		=> Model_Mail::STATUS_SENT,
//			'bytesSent'		=> mb_strlen( MailMessageRendererV2::render( $mail->objectInstance->mail ) ),
			'sentAt'		=> time()
		] );
		$payload	= [
			'mailId'	=> $mail->mailId,
			'mail'		=> $mail
		];
		$this->env->getCaptain()->callHookWithPayload( 'Mail', 'sent', $this, $payload );
	}
}
if( !class_exists( 'PHP_Incomplete_Class' ) ){
	class PHP_Incomplete_Class{}
}
