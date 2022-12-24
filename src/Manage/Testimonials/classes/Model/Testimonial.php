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
class Model_Testimonial extends Model
{
	protected string $name			= 'testimonials';

	protected array $columns		= [
		'testimonialId',
		'status',
		'rank',
		'rating',
		'username',
		'email',
		'abstract',
		'title',
		'description',
		'timestamp',
	];

	protected string $primaryKey	= 'testimonialId';

	protected array $indices		= [
		'status',
		'rank',
		'rating',
		'username',
		'email',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
