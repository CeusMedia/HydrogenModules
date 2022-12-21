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
	protected string $name		= 'versions';

	protected array $columns	= array(
		'versionId',
		'userId',
		'module',
		'id',
		'version',
		'content',
		'timestamp',
	);

	protected string $primaryKey	= 'versionId';

	protected array $indices		= array(
		'userId',
		'module',
		'id',
		'version',
		'timestamp',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
