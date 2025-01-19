<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Mission_Document extends Entity
{
	public int|string $missionDocumentId;
	public int|string $missionId;
	public int|string $userId;
	public string $mimeType;
	public int $size;
	public string $filename;
	public string $hashname;
	public string $createdAt;
	public ?string $modifiedAt			= NULL;
	public ?string $accessedAt			= NULL;
}