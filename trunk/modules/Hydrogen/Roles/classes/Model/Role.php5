<?php
/**
 *	Role Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Role.php 1760 2010-11-26 17:14:11Z christian.wuerker $
 */
/**
 *	Role Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Role.php 1760 2010-11-26 17:14:11Z christian.wuerker $
 */
class Model_Role extends CMF_Hydrogen_Model {

	const ACCESS_NONE		= 0;
	const ACCESS_ACL		= 64;
	const ACCESS_FULL		= 128;

	const REGISTER_DENIED	= 0;
	const REGISTER_HIDDEN	= 32;
	const REGISTER_VISIBLE	= 64;
	const REGISTER_DEFAULT	= 128;

	protected $name		= 'roles';
	protected $columns	= array(
		'roleId',
		'access',
		'register',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'roleId';
	protected $indices		= array(
		'access',
		'register',
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
