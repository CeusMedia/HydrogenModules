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
class Model_Catalog_Gallery_Category extends CMF_Hydrogen_Model {

	protected $name		= 'catalog_gallery_categories';
	protected $columns	= array(
		'galleryCategoryId',
		'parentId',
		'status',
		'rank',
		'path',
		'title',
		'price',
		'image',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'galleryCategoryId';
	protected $indices		= array(
		'parentId',
		'status',
		'path',
		'rank',
		'createdAt',
		'modifiedAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
