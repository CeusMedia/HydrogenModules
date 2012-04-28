<?php
class Model_TermLog extends CMF_Hydrogen_Model{

	protected $name		= 'term_log';
	protected $columns	= array(
		'termLogId',
		'userId',
		'count',
		'content',
		'createdAt',
		'modifiedAt'
	);
	protected $primaryKey	= 'termLogId';
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function  __construct( CMF_Hydrogen_Environment_Abstract $env, $id = NULL ){
		parent::__construct( $env, $id );
	}
}
?>