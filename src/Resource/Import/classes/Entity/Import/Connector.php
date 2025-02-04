<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Import_Connector extends Entity
{
	public int|string $importConnectorId;
	public int|string $creatorId			= 0;
	public int $status						= Model_Import_Connector::STATUS_DISABLED;
	public int $type						= Model_Import_Connector::TYPE_UNKNOWN;
	public string $className;
	public string $title;
	public ?string $description				= NULL;
	public ?string $mimeTypes				= NULL;
	public int $createdAt;
	public int $modifiedAt;

	protected static function presetDynamicValues( array & $array ): void
	{
		$array['createdAt']		= time();
		$array['modifiedAt']	= time();
	}
}