<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Import_Connection extends Entity
{
	public int|string $importConnectionId;
	public int|string $importConnectorId;
	public int|string $creatorId			= 0;
	public int $status						= Model_Import_Connection::STATUS_DISABLED;
	public string $hostName;
	public int $hostPort					= 0;
	public string $hostPath;
	public int $authType					= Model_Import_Connection::AUTH_TYPE_NONE;
	public ?string $authKey					= NULL;
	public ?string $authUsername			= NULL;
	public ?string $authPassword			= NULL;
	public string $title;
	public ?string $description				= NULL;
	public int $createdAt;
	public int $modifiedAt;

	/**
	 *	Applies preset values dynamically created on manual construction.
	 *	@param		array		$array		Data array to work on
	 *	@return		array
	 */
	protected static function presetDynamicValues( array $array ): array
	{
		$array['createdAt']		= time();
		$array['modifiedAt']	= time();
		return $array;
	}
}