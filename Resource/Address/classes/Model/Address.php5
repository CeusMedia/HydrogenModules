<?php
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 */
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 */
class Model_Address extends CMF_Hydrogen_Model {

	const TYPE_LOCATION		= 1;
	const TYPE_BILLING		= 2;
	const TYPE_DELIVERY		= 4;

	protected $name		= 'addresses';
	protected $columns	= array(
		'addressId',
		'relationId',
		'relationType',
		'type',
		'country',
		'state',
		'region',
		'city',
		'postcode',
		'street',
		'latitude',
		'longitude',
		'phone',
		'email',
		'institution',
		'firstname',
		'surname',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'addressId';
	protected $indices		= array(
		'relationId',
		'relationType',
		'type',
		'latitude',
		'longitude',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>