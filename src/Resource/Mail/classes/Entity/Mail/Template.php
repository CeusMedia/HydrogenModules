<?php

use CeusMedia\Common\Exception\Data\Missing as DataMissingException;

/**
 * @property int|NULL $used
 * @property bool $activeByModule
 */
class Entity_Mail_Template
{
	public int|string|NULL $mailTemplateId		= NULL;
	public int $status							= Model_Mail_Template::STATUS_NEW;
	public string|NULL $language				= NULL;
	public string $title;
	public string|NULL $plain					= NULL;
	public string|NULL $html					= NULL;
	public string $css							= '';
	public string|NULL $styles					= NULL;
	public string|NULL $images					= NULL;
	public int $createdAt;
	public int|NULL $modifiedAt					= NULL;

	public static function createFromArray( array $array ): self
	{
		$obj = new self();
		$obj->createdAt		= time();

		if( !array_key_exists( 'title', $array ) )
			throw new DataMissingException( 'Field "title" is missing' );
		foreach( $array as $key => $value )
			$obj->{$key}	= $value;
		return $obj;
	}
}