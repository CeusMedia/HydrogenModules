<?php
declare(strict_types=1);

use CeusMedia\Common\Exception\Data\Missing as DataMissingException;
use CeusMedia\HydrogenFramework\Entity;

/**
 * @property array $parts
 */
class Entity_Mail extends Entity
{
	public int|string|NULL $mailId		= NULL;
	public int|string $senderId			= 0;
	public int|string $receiverId		= 0;
	public int|string|NULL $templateId	= 0;
	public int $status					= Model_Mail::STATUS_NEW;
	public int $attempts				= 0;
	public string $language				= '';
	public string $receiverAddress;
	public string|NULL $receiverName	= NULL;
	public string $senderAddress;
	public string $subject;
	public string $mailClass;
	public string|NULL $object			= NULL;
	public string|NULL $raw				= NULL;
	public int $compression				= Model_Mail::COMPRESSION_UNKNOWN;
	public int $enqueuedAt;
	public int|NULL $attemptedAt		= NULL;
	public int|NULL $sentAt				= NULL;

	public Mail_Abstract|NULL $objectInstance	= NULL;
	public string|NULL $objectSerial			= NULL;
	public string|NULL $rawInflated				= NULL;

	protected static function checkValues( array $data ): void
	{
		if( !array_key_exists( 'receiverAddress', $data ) )
			throw new DataMissingException( 'Field "receiverAddress" is missing' );
		if( !array_key_exists( 'senderAddress', $data ) )
			throw new DataMissingException( 'Field "senderAddress" is missing' );
		if( !array_key_exists( 'subject', $data ) )
			throw new DataMissingException( 'Field "subject" is missing' );
		if( !array_key_exists( 'mailClass', $data ) )
			throw new DataMissingException( 'Field "mailClass" is missing' );
		if( !array_key_exists( 'enqueuedAt', $data ) )
			throw new DataMissingException( 'Field "enqueuedAt" is missing' );
	}
}