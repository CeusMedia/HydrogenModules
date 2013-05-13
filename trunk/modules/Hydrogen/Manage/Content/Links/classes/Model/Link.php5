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
class Model_Link extends CMF_Hydrogen_Model {

	protected $name		= 'links';
	protected $columns	= array(
		'linkId',
		'status',
		'uri',
		'title',
		'createdAt',
		'checkedAt',
	);
	protected $primaryKey	= 'linkId';
	protected $indices		= array(
		'status',
		'uri',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
