<?php
/**
 *	User Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: User.php 1798 2010-12-04 18:55:59Z christian.wuerker $
 */
/**
 *	User Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: User.php 1798 2010-12-04 18:55:59Z christian.wuerker $
 */
class Model_User extends CMF_Hydrogen_Model {

	protected $name		= 'users';
	protected $columns	= array(
		'userId',
		'roleId',
		'roomId',
		'status',
		'email',
		'username',
		'password',
		'createdAt',
		'modifiedAt',
		'loggedAt',
		'activeAt',
	);
	protected $primaryKey	= 'userId';
	protected $indices		= array(
		'roleId',
		'roomId',
		'status',
		'username',
		'email',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
