<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_Testimonial extends Model
{
	protected string $name		= 'testimonials';

	protected array $columns	= array(
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
	);

	protected string $primaryKey	= 'testimonialId';

	protected array $indices		= array(
		'status',
		'rank',
		'rating',
		'username',
		'email',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
