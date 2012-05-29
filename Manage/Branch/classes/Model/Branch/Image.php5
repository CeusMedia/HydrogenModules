<?php
class Model_Branch_Image extends CMF_Hydrogen_Model{
	protected $name			= 'branch_images';
	protected $columns		= array(
		'imageId',
		'branchId',
		'filename',
		'title',
		'uploadedAt',
	);
	protected $primaryKey	= 'imageId';
	protected $indices		= array(
		'branchId',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>