<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_Workshop extends Model
{
	public const IMAGE_ALIGN_H_AUTO		= 0;
	public const IMAGE_ALIGN_H_LEFT		= 1;
	public const IMAGE_ALIGN_H_CENTER	= 2;
	public const IMAGE_ALIGN_H_RIGHT	= 3;

	public const IMAGE_ALIGN_V_AUTO		= 0;
	public const IMAGE_ALIGN_V_TOP		= 1;
	public const IMAGE_ALIGN_V_CENTER	= 2;
	public const IMAGE_ALIGN_V_BOTTOM	= 3;

	public const RANK_HIGHEST			= 1;
	public const RANK_HIGH				= 2;
	public const RANK_NORMAL			= 3;
	public const RANK_LOW				= 4;
	public const RANK_LOWEST			= 5;

	public const STATUS_DISABLED		= -2;
	public const STATUS_DEACTIVATED		= -1;
	public const STATUS_NEW				= 0;
	public const STATUS_ACTIVATED		= 1;
	public const STATUS_OUTDATED		= 2;
	public const STATUS_CLOSED			= 3;

	protected string $name			= 'workshops';

	protected array $columns		= [
		'workshopId',
		'status',
		'rank',
		'title',
		'abstract',
		'description',
		'image',
		'imageAlignH',
		'imageAlignV',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'workshopId';

	protected array $indices		= [
		'status',
		'rank',
		'createdAt',
		'modifiedAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
