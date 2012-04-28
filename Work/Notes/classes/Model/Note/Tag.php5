<?php
class Model_Note_Tag extends CMF_Hydrogen_Model{

	protected $name		= 'note_tags';
	protected $columns	= array(
		'noteTagId',
		'noteId',
		'tagId',
		'createdAt',
	);
	protected $primaryKey	= 'noteTagId';
	protected $indices		= array(
		'noteId',
		'tagId'
	 );
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function  __construct( CMF_Hydrogen_Environment_Abstract $env, $id = NULL ){
		parent::__construct( $env, $id );
	}
}
?>
