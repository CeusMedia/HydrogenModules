<?php

use CeusMedia\HydrogenFramework\Environment;

class Model_Note_Link extends CMF_Hydrogen_Model
{
	protected $name		= 'note_links';

	protected $columns	= array(
		'noteLinkId',
		'noteId',
		'linkId',
		'title',
		'createdAt',
	);

	protected $primaryKey	= 'noteLinkId';

	protected $indices		= array(
		'noteId',
		'linkId'
	);

	protected $fetchMode	= PDO::FETCH_OBJ;

	public function  __construct( Environment $env, $id = NULL )
	{
		parent::__construct( $env, $id );
	}
}
