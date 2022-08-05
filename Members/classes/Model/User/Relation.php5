<?php
/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 */
class Model_User_Relation extends Model
{
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
