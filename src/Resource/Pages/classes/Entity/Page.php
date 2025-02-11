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
	public string $access		= 'public';
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
	public int $modifiedAt		= 0;

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
	 *	@param		array			$array
	 *	@return		void
	 */
	public static function presetDynamicValues( array & $array ): void
	{
		$array['createdAt']	= time();
	}
}

