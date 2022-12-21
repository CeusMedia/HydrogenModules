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
	protected string $name		= 'user_relations';

	protected array $columns	= array(
		'userRelationId',
		'fromUserId',
		'toUserId',
		'type',
		'status',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'userRelationId';

	protected array $indices		= array(
		'fromUserId',
		'toUserId',
		'type',
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
