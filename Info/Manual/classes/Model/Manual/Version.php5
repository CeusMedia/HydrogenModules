<?php
class Model_Manual_Version extends CMF_Hydrogen_Model{

	const TYPE_PAGE			= 0;
	const TYPE_CATEGORY		= 1;

	protected $name			= 'manual_versions';
	protected $columns		= array(
		'manualVersionId',
		'userId',
		'objectId',
		'type',
		'version',
		'object',
		'timestamp',
	);
	protected $primaryKey	= 'manualVersionId';
	protected $indices		= array(
		'userId',
		'objectId',
		'type',
		'version',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
