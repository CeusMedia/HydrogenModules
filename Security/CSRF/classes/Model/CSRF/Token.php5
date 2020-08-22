<?php
/**
 *	Data model of CSRF tokens.
 *	@category		none
 *	@package		none
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
*	Data model of CSRF tokens.
 *	@category		none
 *	@package		none
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_CSRF_Token extends CMF_Hydrogen_Model
{
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
