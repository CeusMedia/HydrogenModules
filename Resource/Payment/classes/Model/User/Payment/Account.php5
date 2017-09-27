<?php
/**
 *	User Payment Account Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2016 Ceus Media
 *	@version		$Id$
 */
/**
 *	User Payment Account Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2016 Ceus Media
 *	@version		$Id$
 */
class Model_User_Payment_Account extends CMF_Hydrogen_Model {

	protected $name		= 'user_payment_accounts';
	protected $columns	= array(
		'userPaymentAccountId',
		'userId',
		'paymentAccountId',
		'provider',
		'createdAt',
	);
	protected $primaryKey	= 'userPaymentAccountId';
	protected $indices		= array(
		'userId',
		'paymentAccountId',
		'provider',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
