<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Shop_Order_Position extends Entity
{
	public int|string $positionId;
	public int|string $orderId;
	public int|string $bridgeId;
	public int|string $articleId;
	public int|string|NULL $userId			= NULL;

	public int $status						= Model_Shop_Order_Position::STATUS_NEW;
	public int $quantity					= 1;
	public string $currency					= 'EUR';
	public float $price						= .0;
	public float $priceTaxed				= .0;

	public int $createdAt;
	public int|NULL $modifiedAt				= NULL;

	public static array $mandatoryFields	= [
		'orderId',
		'bridgeId',
		'articleId',
		'quantity',
		'price',
		'priceTaxed',
	];

	public ?object $article		= NULL;
}
