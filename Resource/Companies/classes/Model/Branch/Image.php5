<?php
class Model_Branch_Image extends CMF_Hydrogen_Model{
	protected $name			= 'branch_images';
	protected $columns		= array(
		'branchImageId',
		'branchId',
		'type',
		'filename',
		'title',
		'uploadedAt',
	);
	protected $primaryKey	= 'branchImageId';
	protected $indices		= array(
		'branchId',
		'type',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
