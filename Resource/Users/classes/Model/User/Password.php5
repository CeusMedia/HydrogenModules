<?php
/**
 *	User Password Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		206 Ceus Media
 *	@version		$Id$
 */
/**
 *	User Password Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		206 Ceus Media
 *	@version		$Id$
 */
class Model_User_Password extends CMF_Hydrogen_Model {

	protected $name		= 'user_passwords';
	protected $columns	= array(
		'userPasswordId',
		'userId',
		'algo',
		'status',
		'salt',
		'hash',
		'failsLast',
		'failsTotal',
		'createdAt',
		'failedAt',
		'usedAt',
		'revokedAt',
	);
	protected $primaryKey	= 'userPasswordId';
	protected $indices		= array(
		'userId',
		'status',
		'salt',
		'hash',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
