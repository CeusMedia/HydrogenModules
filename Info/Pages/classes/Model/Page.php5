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

	const TYPE_CONTENT		= 0;
	const TYPE_BRANCH		= 1;
	const TYPE_MODULE		= 2;
	const TYPE_COMPONENT	= 3;

	const STATUS_DISABLED	= -1;
	const STATUS_HIDDEN		= 0;
	const STATUS_VISIBLE	= 1;

	protected $name		= 'pages';
	protected $columns	= array(
		'pageId',
		'parentId',
		'type',
		'scope',
		'status',
		'rank',
		'identifier',
		'controller',
		'action',
		'access',
		'title',
		'content',
		'format',
		'description',
		'keywords',
		'changefreq',
		'priority',
		'icon',
		'template',
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
		'controller',
		'action',
		'access',
		'format',
		'changefreq',
		'priority',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
