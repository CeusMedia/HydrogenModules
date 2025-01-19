<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

/**
 * @property ?Entity_Manual_Category $category
 */
class Entity_Manual_Page extends Entity
{
	public int|string $manualPageId;
	public int|string $manualCategoryId;
	public int|string|NULL $creatorId		= NULL;
	public int|string $parentId				= 0;
	public int $status						= Model_Manual_Page::STATUS_NEW;
	public int $format						= Model_Manual_Page::FORMAT_TEXT;
	public int $version						= 1;
	public ?int $rank						= NULL;
	public string $title;
	public string $content;
	public string $createdAt;
	public ?string $modifiedAt				= NULL;
}