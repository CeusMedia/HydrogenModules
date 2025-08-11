<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Shop_Bridge_Article extends Entity
{
	public int|string $id;
	public string $link;
	/** @var object{relative: string, absolute: string} $picture */
	public object $picture;
	/** @var object{one: float, all: float} $price */
	public object $price;
	/** @var object{rate: int|float, one: float, all: float} $tax */
	public object $tax;
	/** @var object{one: float, all: float} $weight */
	public object $weight;
	public string $title;
	public string $description;
	public string $bridge;
	public string $bridgeId;

	public bool $single	= FALSE;
	public object|NULL $raw	= NULL;

	/**
	 *	Applies preset values dynamically created on manual construction.
	 *	@param		array		$array		Data array to work on
	 *	@return		array
	 */
	protected static function presetDynamicValues( array $array ): array
	{
		$array['picture']	= (object) [
			'relative'	=> '',
			'absolute'	=> '',
		];
		$array['price']		= (object) [
			'rate'		=> 0,
			'one'		=> .0,
			'all'		=> .0,
		];
		$array['tax']		= (object) [
			'one'		=> .0,
			'all'		=> .0,
		];
		$array['weight']	= (object) [
			'one'		=> .0,
			'all'		=> .0,
		];
		return $array;
	}
}