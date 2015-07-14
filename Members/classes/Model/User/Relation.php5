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
class Model_User_Relation extends CMF_Hydrogen_Model {

	protected $name		= 'user_relations';
	protected $columns	= array(
		'userRelationId',
		'fromUserId',
		'toUserId',
		'type',
		'status',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'userRelationId';
	protected $indices		= array(
		'fromUserId',
		'toUserId',
		'type',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
