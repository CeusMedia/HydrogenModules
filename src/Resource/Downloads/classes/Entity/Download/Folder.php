<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Download_Folder extends Entity
{
	public int|string $downloadFolderId;
	public int|string $parentId		= 0;
	public int $type				= Model_Download_Folder::TYPE_DEFAULT;
	public int $rank;
	public string $title;
	public ?string $description		= NULL;
	public int $nrFolders			= 0;
	public int $nrFiles				= 0;
	public int $createdAt;
	public ?int $modifiedAt			= NULL;

	//  --  NOT PERSISTENT  --  //

	public ?Entity_Download_Folder $parent	= NULL;

	protected static array $mandatoryFields	= [
		'rank',
		'size',
		'title',
		'uploadedAt',
	];
}

