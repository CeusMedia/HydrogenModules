<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Page extends Entity
{
	public int $pageId			= 0;
	public int $parentId		= 0;
//	public int $moduleId		= 0;
	public int $status			= 0;
	public int $type			= 0;
	public int $scope			= 0;
	public int $rank			= 0;
	public ?string $identifier	= NULL;
	public string $fullpath		= '';
	public ?string $controller	= NULL;
	public ?string $action		= NULL;
	public string $access		= Model_Page_ByDatabase::ACCESS_PUBLIC;
	public string $title		= '';
	public ?string $content		= NULL;
	public string $format		= 'HTML';
	public ?string $description	= NULL;
	public ?string $keywords	= NULL;
	public string $changefreq	= 'weekly';
	public float $priority		= 0.5;
	public ?string $icon		= NULL;
	public ?string $template	= NULL;
	public int $createdAt		= 0;
	public ?int $modifiedAt		= NULL;

	/** @var Entity_Page[] $pages */
	public array $pages			= [];

	/** @var Entity_Page[] $parents */
	public array $parents		= [];

	/** @var array $arguments */
	public array $arguments		= [];

	/** @var ?object $dispatcher */
	public ?object $dispatcher	= NULL;

	public ?string $path		= NULL;

	/**
	 *	Applies preset values dynamically created on manual construction.
	 *	@param		array		$array		Data array to work on
	 *	@return		array
	 */
	public static function presetDynamicValues( array $array ): array
	{
		$array['createdAt']	= time();
		return $array;
	}
}

