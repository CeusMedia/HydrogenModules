<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Shop_Order extends Entity
{
	public int|string $orderId;
	public int|string $customerId;
	public string $sessionId				= '';
	public int|string|NULL $userId			= NULL;
	public string $options					= '';
	public string|NULL $paymentMethod		= NULL;
	public int|string|NULL $paymentId		= NULL;
	public int $status						= Model_Shop_Order::STATUS_NEW;
	public string $currency					= 'EUR';
	public float $price;
	public float $priceTaxed;
	public int $createdAt;
	public int|NULL $modifiedAt				= NULL;

	public static array $mandatoryFields	= [
		'customerId',
		'sessionId',
		'price',
		'priceTaxed',
		'createdAt',
	];

	public ?Entity_User $customer			= NULL;
	public ?array $positions				= NULL;
	public ?object $shipping				= NULL;
	public ?object $payment					= NULL;
	public ?array $taxes					= NULL;
}