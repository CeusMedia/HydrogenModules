<?php
/**
 *	Data Model of Customers.
 *	@category		none
 *	@package		none
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			23.03.2015
 *	@version		3.0
 */
/**
 *	Data Model of Customers.
 *	@category		none
 *	@package		none
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			23.03.2015
 *	@version		3.0
 */
class Model_CSRF_Token extends CMF_Hydrogen_Model {

	protected $name		= 'csrf_tokens';
	protected $columns	= array(
		"tokenId",
    	"status",
    	"token",
		"sessionId",
		"ip",
		"formName",
		"timestamp",
	);
	protected $primaryKey	= 'tokenId';
	protected $indices		= array(
		"status",
    	"token",
    	"sessionId",
		"ip",
		"formName",
		"timestamp",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
