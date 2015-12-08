<?php
class Model_User_Avatar extends CMF_Hydrogen_Model{
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
	protected $indizes		= array(
		'userId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
