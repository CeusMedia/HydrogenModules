<?php
/**
 *	Role Right Model for ACL.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin.Role
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Role Right Model for ACL.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin.Role
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
class Model_Role_Right extends CMF_Hydrogen_Model{

	protected $name		= 'role_rights';
	protected $columns	= array(
		'roleRightId',
		'roleId',
		'controller',
		'action',
		'timestamp',
	);
	protected $primaryKey	= 'roleRightId';
	protected $indices		= array(
		'roleId',
		'controller',
		'action',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>