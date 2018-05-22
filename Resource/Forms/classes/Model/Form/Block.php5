<?php
class Model_Form_Block extends CMF_Hydrogen_Model{

	protected $columns		= array(
		'blockId',
		'title',
		'identifier',
		'content',
	);
	protected $indices		= array(
		'identifier',
	);
	protected $primaryKey	= 'blockId';
	protected $name			= 'form_blocks';
	protected $fetchMode	= PDO::FETCH_OBJ;
}

