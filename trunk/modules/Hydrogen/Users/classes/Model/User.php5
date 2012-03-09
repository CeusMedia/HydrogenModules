<?php
/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
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
