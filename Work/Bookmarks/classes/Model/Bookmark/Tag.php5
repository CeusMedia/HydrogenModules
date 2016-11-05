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
class Model_Bookmark_Tag extends CMF_Hydrogen_Model {

	protected $name		= 'bookmark_tags';
	protected $columns	= array(
		'bookmarkTagId',
		'bookmarkId',
		'userId',
		'title',
		'createdAt',
		'relatedAt',
		'usedAt',
	);
	protected $primaryKey	= 'bookmarkTagId';
	protected $indices		= array(
		'bookmarkId',
		'userId',
		'createdAt',
		'relatedAt',
		'usedAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
