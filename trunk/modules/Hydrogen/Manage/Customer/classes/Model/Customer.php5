<?php
class Model_Customer extends CMF_Hydrogen_Model{
	protected $name			= 'customers';
	protected $columns		= array(
		'customerId',
		'creatorId',
		'size',
		'type',
		'title',
		'description',
		'country',
		'city',
		'postcode',
		'street',
		'nr',
		'url',
		'longitude',
		'latitude',
		'contact',
		'email',
		'phone',
		'fax',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'customerId';
	protected $indizes		= array();
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>