<?php

class Entity_User_Token
{
	public int|string $userTokenId;
	public int|string $userId;
	public int $status				= Model_User_Token::STATUS_NEW;
	public string $scope;
	public string $token;
	public string $createdAt;
	public ?string $usedAt			= NULL;
	public ?string $revokedAt		= NULL;
}