<?php
/**
 *	Role Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Role.php 1490 2010-10-07 08:37:18Z christian.wuerker $
 */
/**
 *	Role Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Role.php 1490 2010-10-07 08:37:18Z christian.wuerker $
 */
class Model_Role_Right extends CMF_Hydrogen_Model {

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