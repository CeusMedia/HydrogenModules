<?php
class Model_Image extends CMF_Hydrogen_Model{
	protected $name			= 'images';
	protected $columns		= array(
		'imageId',
		'companyId',
		'branchId',
		'creditId',
		'status',
		'title',
//		'timestamp',
	);
	protected $primaryKey	= 'imageId';
	protected $indices		= array(
		'companyId',
		'branchId',
		'creditId',
  		'status',
	);
}
?>