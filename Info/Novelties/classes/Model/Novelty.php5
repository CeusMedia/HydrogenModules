<?php
/**
 *	Model.
 *	@version		$Id$
 */
/**
 *	Model.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class Model_Novelty extends CMF_Hydrogen_Model{

	/**	@var	$name		string		Table name without prefix of database connection */
	protected $name			= "novelties";

	/**	@var	$name		string		List of columns within table */
	protected $columns		= array(
		'noveltyId',
		'userId',
		'entryId',
		'type',
		'timestamp',
	);

	/**	@var	$name		string		Name of column with primary key */
	protected $primaryKey	= "noveltyId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected $indices		= array(
		'userId',
		'entryId',
		'type',
		'timestamp',
	);

	/**	@var	$fetchMode	interger	Fetch mode, see PDO documentation */
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
