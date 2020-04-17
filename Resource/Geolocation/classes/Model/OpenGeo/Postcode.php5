<?php
class Model_OpenGeo_Postcode extends CMF_Hydrogen_Model{

	protected $name			= 'postcodes';
	protected $columns		= array(
		'postcodeId',
		'postcode',
		'city',
		'latitude',
		'longitude',
	);
	protected $primaryKey	= 'postcodeId';
	protected $indices		= array(
		'postcode',
		'city',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
