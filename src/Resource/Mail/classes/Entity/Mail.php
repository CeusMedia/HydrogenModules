<?php

/**
 * @property int|NULL $usedLibrary
 * @property string|NULL $objectInflated
 * @property Mail_Abstract|NULL $objectInstance
 * @property string|NULL $objectSerial
 * @property string|NULL $rawInflated
 */
class Entity_Mail
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

	public static function createFromArray( array $array ): Entity_Mail
	{
		$obj = new Entity_Mail();
		foreach( $array as $key => $value )
			$obj->{$key}	= $value;
		return $obj;
	}
}