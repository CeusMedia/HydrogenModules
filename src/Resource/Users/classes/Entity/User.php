<?php

/** @property Entity_Role $role */
/** @property Entity_Group[] $groups */
/** @property ?object $avatar */
/** @property ?array $settings */
class Entity_User
{
	public int|string $userId;
	public int|string $accountId;
	public int|string $roleId;
	public int|string $roomId;
	public int|string $companyId;
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
}