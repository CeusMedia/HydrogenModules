<?php
/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Versions.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Versions.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015 Ceus Media
 */
class Model_Version extends Model
{
	protected $name		= 'versions';

	protected $columns	= array(
		'versionId',
		'userId',
		'module',
		'id',
		'version',
		'content',
		'timestamp',
	);

	protected $primaryKey	= 'versionId';

	protected $indices		= array(
		'userId',
		'module',
		'id',
		'version',
		'timestamp',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
