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

	protected $name		= 'news';
	protected $columns	= array(
		'newsId',
		'status',
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
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
