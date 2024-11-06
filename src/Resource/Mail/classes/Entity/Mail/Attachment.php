<?php

use CeusMedia\Common\Exception\Data\Missing as DataMissingException;

class Entity_Mail_Attachment
{
	public int|string|NULL $mailAttachmentId	= NULL;
	public int $status							= Model_Mail_Attachment::STATUS_INACTIVE;
	public string $language;
	public string $className;
	public string $filename;
	public string $mimeType;
	public int $countAttached				= 0;
	public int $createdAt;

	public static function createFromArray( array $array ): self
	{
		$obj = new self();

		if( !array_key_exists( 'title', $array ) )
			throw new DataMissingException( 'Field "title" is missing' );
		if( !array_key_exists( 'title', $array ) )
			throw new DataMissingException( 'Field "title" is missing' );
		if( !array_key_exists( 'title', $array ) )
			throw new DataMissingException( 'Field "title" is missing' );
		if( !array_key_exists( 'title', $array ) )
			throw new DataMissingException( 'Field "title" is missing' );

		foreach( $array as $key => $value )
			$obj->{$key}	= $value;
		return $obj;
	}


}