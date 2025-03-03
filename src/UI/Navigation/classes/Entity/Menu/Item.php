<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Menu_Item extends Entity
{
/*	public const ACCESS_NONE		= 'none';
	public const ACCESS_PUBLIC		= 'public';
	public const ACCESS_OUTSIDE		= 'outside';
	public const ACCESS_INSIDE		= 'inside';
	public const ACCESS_ACL			= 'acl';
	public const ACCESSES			= [
		self::ACCESS_NONE,
		self::ACCESS_PUBLIC,
		self::ACCESS_OUTSIDE,
		self::ACCESS_INSIDE,
		self::ACCESS_ACL,
	];*/

	public const TYPE_ITEM				= 'item';
	public const TYPE_MENU				= 'menu';

	public int|string|NULL $parent		= NULL;
	public string $type					= self::TYPE_ITEM;
	public int $scope					= 0;
	public string $path					= '';
	public string $link					= '';
	public string $label				= '';
	public ?string $language			= NULL;
	public int $rank					= 0;
	public bool $active					= FALSE;
	public string $icon					= '';
	public ?string $chapter				= NULL;

	public array $items					= [];

	public static function fromPageEntity( Entity_Page $page, array $data = [] ): static
	{
		$instance	= new self();
		foreach( $page->toArray() as $key => $value ){
			switch( $key ){
				default:
					if( property_exists( $instance, $key ) )
						$instance->$key	= $value;
			}
		}
		foreach( $data as $key => $value )
			if( property_exists( $instance, $key ) )
				$instance->$key	= $value;
		return $instance;
	}
}