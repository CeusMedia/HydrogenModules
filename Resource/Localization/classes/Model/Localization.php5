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
class Model_Localization extends CMF_Hydrogen_Model {

	protected $name		= 'localizations';
	protected $columns	= array(
		'localizationId',
		'language',
		'id',
		'content',
	);
	protected $primaryKey	= 'localizationId';
	protected $indices		= array(
		'language',
		'id',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
