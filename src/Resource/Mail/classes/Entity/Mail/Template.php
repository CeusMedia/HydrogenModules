<?php
declare(strict_types=1);

use CeusMedia\Common\Exception\Data\Missing as DataMissingException;
use CeusMedia\HydrogenFramework\Entity;

/**
 * @property int|NULL $used
 * @property bool $activeByModule
 */
class Entity_Mail_Template extends Entity
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

	public int|NULL $used						= NULL;
	public bool $activeByModule					= FALSE;

	protected static function checkValues( array $data ): void
	{
		if( !array_key_exists( 'title', $data ) )
			throw new DataMissingException( 'Field "title" is missing' );
	}
}