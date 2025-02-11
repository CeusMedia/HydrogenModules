<?php
/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Versions.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Versions.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Version extends Model
{
	protected string $name			= 'versions';

	protected array $columns		= [
		'versionId',
		'userId',
		'module',
		'id',
		'version',
		'content',
		'timestamp',
	];

	protected string $primaryKey	= 'versionId';

	protected array $indices		= [
		'userId',
		'module',
		'id',
		'version',
		'timestamp',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
