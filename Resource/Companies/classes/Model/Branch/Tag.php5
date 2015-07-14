<?php
class Model_Branch_Tag extends CMF_Hydrogen_Model{
	protected $name			= 'branch_tags';
	protected $columns		= array(
		'branchTagId',
		'branchId',
		'label',
		'createdAt',
	);
	protected $primaryKey	= 'branchTagId';
	protected $indices		= array(
		'branchId',
		'label',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
