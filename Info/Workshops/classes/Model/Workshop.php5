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
class Model_Workshop extends CMF_Hydrogen_Model {

	const IMAGE_ALIGN_H_AUTO	= 0;
	const IMAGE_ALIGN_H_LEFT	= 1;
	const IMAGE_ALIGN_H_CENTER	= 2;
	const IMAGE_ALIGN_H_RIGHT	= 3;

	const IMAGE_ALIGN_V_AUTO	= 0;
	const IMAGE_ALIGN_V_TOP		= 1;
	const IMAGE_ALIGN_V_CENTER	= 2;
	const IMAGE_ALIGN_V_BOTTOM	= 3;

	const RANK_HIGHEST			= 1;
	const RANK_HIGH				= 2;
	const RANK_NORMAL			= 3;
	const RANK_LOW				= 4;
	const RANK_LOWEST			= 5;

	const STATUS_DISABLED		= -2;
	const STATUS_DEACTIVATED	= -1;
	const STATUS_NEW			= 0;
	const STATUS_ACTIVATED		= 1;
	const STATUS_OUTDATED		= 2;

	protected $name		= 'workshops';
	protected $columns	= array(
		'workshopId',
		'status',
		'rank',
		'title',
		'abstract',
		'description',
		'image',
		'imageAlignH',
		'imageAlignV',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'workshopId';
	protected $indices		= array(
		'status',
		'rank',
		'createdAt',
		'modifiedAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
