<?php
class Model_Manual_Page extends CMF_Hydrogen_Model{

	const FORMAT_HTML		= 0;
	const FORMAT_MARKDOWN	= 1;

	const STATUS_ARCHIVED	= -9;
	const STATUS_OUTDATED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_CHANGED	= 1;
	const STATUS_ACTIVE		= 2;
	const STATUS_LOCKED		= 3;

	protected $name			= 'manual_pages';
	protected $columns		= array(
		'manualPageId',
		'manualCategoryId',
		'creatorId',
		'status',
		'format',
		'title',
		'content',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'manualPageId';
	protected $indices		= array(
		'manualCategoryId',
		'creatorId',
		'status',
		'format',
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
