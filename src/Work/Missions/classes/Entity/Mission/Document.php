<?php

class Entity_Mission_Document
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