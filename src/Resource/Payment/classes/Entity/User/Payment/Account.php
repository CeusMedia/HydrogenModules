<?php

class Entity_User_Payment_Account extends \CeusMedia\HydrogenFramework\Entity
{
	public int|string $userPaymentAccountId		= 0;
	public int|string $userId;
	public string $paymentAccountId;
	public string $provider;
	public int $createdAt;

	protected static array $mandatoryFields		= [
		'userId',
		'paymentAccountId',
		'provider',
		'createdAt',
	];
}