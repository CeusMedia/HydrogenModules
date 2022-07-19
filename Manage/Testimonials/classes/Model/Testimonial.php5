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
	protected $name		= 'testimonials';

	protected $columns	= array(
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

	protected $primaryKey	= 'testimonialId';

	protected $indices		= array(
		'status',
		'rank',
		'rating',
		'username',
		'email',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
