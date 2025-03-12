<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Download_File extends Entity
{
	public int|string $downloadFileId;
	public int|string $downloadFolderId;
	public int $rank;
	public int $size;
	public string $title;
	public ?string $description				= NULL;
	public int $nrDownloads					= 0;
	public int $uploadedAt;
	public ?int $downloadedAt				= NULL;

	//  --  NOT PERSISTENT  --  //

	public ?Entity_Download_Folder $folder	= NULL;

	protected static array $mandatoryFields	= [
		'downloadFolderId',
		'rank',
		'size',
		'title',
		'uploadedAt',
	];
}
