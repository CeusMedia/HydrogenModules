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
class Model_Bookmark extends CMF_Hydrogen_Model {

	const STATUS_REMOVED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_ACTIVE		= 1;
	const STATUS_ARCHIVED	= 2;

	protected $name		= 'bookmarks';
	protected $columns	= array(
		'bookmarkId',
		'userId',
		'status',
		'visits',
		'url',
		'title',
		'description',
		'pageTitle',
		'pageDescription',
		'fulltext',
		'createdAt',
		'modifiedAt',
		'visitedAt',
	);
	protected $primaryKey	= 'bookmarkId';
	protected $indices		= array(
		'userId',
		'status',
		'url',
		'createdAt',
		'modifiedAt',
		'visitedAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
