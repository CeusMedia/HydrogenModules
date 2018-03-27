<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
class Model_News extends CMF_Hydrogen_Model {

	const STATUS_HIDDEN		= -1;
	const STATUS_NEW		= 0;
	const STATUS_PUBLIC		= 1;

	protected $name		= 'news';
	protected $columns	= array(
		'newsId',
		'status',
		'type',
		'title',
		'content',
		'columns',
		'startsAt',
		'endsAt',
		'createdAt',
	);
	protected $primaryKey	= 'newsId';
	protected $indices		= array(
		'status',
		'type',
		'title',
		'startsAt',
		'endsAt',
		'createdAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
