<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

/** @property ?object $avatar */
/** @property ?array $settings */
/** @property ?Entity_Address $addressDelivery */
/** @property ?Entity_Address $addressBilling */
class Entity_User extends Entity
{
	public int|string $userId;
	public int|string $accountId	= 0;
	public int|string $roleId		= 0;
	public int|string $roomId		= 0;
	public int|string $companyId	= 0;
	public int $status				= Model_User::STATUS_UNCONFIRMED;
	public string $email;
	public string $username;
	public string $password;																					//  @todo remove after old user password support decayed
	public int $gender				= Model_User::GENDER_UNKNOWN;
	public ?string $salutation		= NULL;
	public ?string $firstname		= NULL;
	public ?string $surname			= NULL;
	public ?string $country			= NULL;
	public ?string $postcode		= NULL;
	public ?string $city			= NULL;
	public ?string $street			= NULL;
	public ?string $number			= NULL;
	public ?string $phone			= NULL;
	public ?string $fax				= NULL;
	public string $createdAt;
	public ?string $modifiedAt		= NULL;
	public ?string $loggedAt		= NULL;
	public ?string $activeAt		= NULL;

	public ?Entity_Role $role		= NULL;

	/** @var Entity_Group[] $groups  */
	public array $groups			= [];
}