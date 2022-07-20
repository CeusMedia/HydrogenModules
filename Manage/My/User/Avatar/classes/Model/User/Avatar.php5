<?php

use CeusMedia\HydrogenFramework\Model;

class Model_User_Avatar extends Model
{
	protected $name			= 'user_avatars';

	protected $columns		= array(
		'userAvatarId',
		'userId',
		'status',
		'filename',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'userAvatarId';

	protected $indices		= array(
		'userId',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
