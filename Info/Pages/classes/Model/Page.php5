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
class Model_Page extends CMF_Hydrogen_Model {

	protected $name		= 'pages';
	protected $columns	= array(
		'pageId',
		'parentId',
		'type',
		'scope',
		'status',
		'rank',
		'identifier',
		'module',
		'title',
		'content',
		'description',
		'keywords',
		'createdAt',
		'modifiedAt'
	);
	protected $primaryKey	= 'pageId';
	protected $indices		= array(
		'parentId',
		'type',
		'scope',
		'status',
		'identifier',
		'module',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
