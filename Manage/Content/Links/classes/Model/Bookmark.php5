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

	protected $name		= 'bookmarks';
	protected $columns	= array(
		'bookmarkId',
		'status',
		'uri',
		'title',
		'createdAt',
		'checkedAt',
		'usedAt',
	);
	protected $primaryKey	= 'bookmarkId';
	protected $indices		= array(
		'status',
		'uri',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
