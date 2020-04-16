<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
class Model_Newsletter_Reader extends CMF_Hydrogen_Model {

	const STATUS_DEACTIVATED	= -2;
	const STATUS_UNREGISTERED	= -1;
	const STATUS_REGISTERED		= 0;
	const STATUS_CONFIRMED		= 1;

	protected $name		= 'newsletter_readers';
	protected $columns	= array(
		'newsletterReaderId',
		'status',
		'email',
		'gender',
		'prefix',
		'firstname',
		'surname',
		'institution',
		'registeredAt',
	);
	protected $primaryKey	= 'newsletterReaderId';
	protected $indices		= array(
		'status',
		'email',
		'firstname',
		'surname',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
