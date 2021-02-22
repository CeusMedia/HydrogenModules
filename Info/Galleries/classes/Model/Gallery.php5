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
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_Gallery extends CMF_Hydrogen_Model
{
	protected $name		= 'galleries';

	protected $columns	= array(
		'galleryId',
		'status',
		'rank',
		'path',
		'title',
		'description',
		'timestamp',
	);

	protected $primaryKey	= 'galleryId';

	protected $indices		= array(
		'status',
		'rank',
		'path',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
