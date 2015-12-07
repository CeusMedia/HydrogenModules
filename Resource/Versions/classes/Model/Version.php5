<?php
/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Versions.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015 Ceus Media
 */
/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Versions.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015 Ceus Media
 */
class Model_Version extends CMF_Hydrogen_Model {

	protected $name		= 'versions';
	protected $columns	= array(
		'versionId',
		'userId',
		'module',
		'id',
		'version',
		'content',
		'timestamp',
	);
	protected $primaryKey	= 'versionId';
	protected $indices		= array(
		'userId',
		'module',
		'id',
		'version',
		'timestamp',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
