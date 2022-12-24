<?php

use CeusMedia\HydrogenFramework\Model;

class Model_User_Avatar extends Model
{
	protected string $name			= 'user_avatars';

	protected array $columns		= [
		'userAvatarId',
		'userId',
		'status',
		'filename',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'userAvatarId';

	protected array $indices		= [
		'userId',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
