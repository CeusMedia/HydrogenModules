<?php
class Model_Test_Table extends CMF_Hydrogen_Model
{
	protected $name			= 'test_table';
	protected $fields		= array(
		'testId',
		'title',
		'timestamp',
	);
	protected $primaryKey	= 'testId';
	protected $indices		= array();
}
?>
