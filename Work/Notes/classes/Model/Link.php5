<?php

use CeusMedia\HydrogenFramework\Environment;

class Model_Link extends CMF_Hydrogen_Model
{
	protected $name		= 'links';

	protected $columns	= array(
		'linkId',
		'url',
		'createdAt',
		'lastAssignAt',
		'lastSearchAt',
	);

	protected $primaryKey	= 'linkId';

	protected $indices		= array(
		'url'
	);

	protected $fetchMode	= PDO::FETCH_OBJ;

	public function __construct( Environment $env, $id = NULL )
	{
		parent::__construct( $env, $id );
	}
}
