<?php
declare(strict_types=1);

use CeusMedia\Common\Exception\Data\Missing as DataMissingException;
use CeusMedia\HydrogenFramework\Entity;

class Entity_Mail_Attachment extends Entity
{
	public int|string|NULL $mailAttachmentId	= NULL;
	public int $status							= Model_Mail_Attachment::STATUS_INACTIVE;
	public string $language;
	public string $className;
	public string $filename;
	public string $mimeType;
	public int $countAttached				= 0;
	public int $createdAt;

	protected static function checkValues( array $data ): void
	{
		if( !array_key_exists( 'language', $data ) )
			throw new DataMissingException( 'Field "language" is missing' );
		if( !array_key_exists( 'className', $data ) )
			throw new DataMissingException( 'Field "className" is missing' );
		if( !array_key_exists( 'filename', $data ) )
			throw new DataMissingException( 'Field "filename" is missing' );
		if( !array_key_exists( 'mimeType', $data ) )
			throw new DataMissingException( 'Field "mimeType" is missing' );
	}
}