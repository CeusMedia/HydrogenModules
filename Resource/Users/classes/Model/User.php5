<?php
/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
class Model_User extends CMF_Hydrogen_Model {

	protected $name		= 'users';
	protected $columns	= array(
		'userId',
		'accountId',
		'roleId',
		'roomId',
		'companyId',
		'status',
		'email',
		'username',
		'password',																					//  @todo remove after old user password support decayed
		'gender',
		'salutation',
		'firstname',
		'surname',
		'country',
		'postcode',
		'city',
		'street',
		'number',
		'phone',
		'fax',
		'createdAt',
		'modifiedAt',
		'loggedAt',
		'activeAt',
	);
	protected $primaryKey	= 'userId';
	protected $indices		= array(
		'accountId',
		'roleId',
		'roomId',
		'companyId',
		'status',
		'username',
		'email',
		'gender',
		'country',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function migrate(){
		$dbc		= $this->env->getDatabase();

		/**
		 *		Merge Street and Number.
		 */
		$query		= "SELECT * FROM `%susers` WHERE LENGTH(number) > 0";
		$query		= sprintf( $query, (string) $dbc->getPrefix() );
		$users		= $dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		if( count( $users ) ){
			$modelUser	= new Model_User( $this->env );
			foreach( $users as $user )
				$modelUser->edit( $user->userId,  array(
					'street'	=> $user->street.' '.$user->number,
					'number'	=> NULL,
				) );
		}

		/**
		 *		Set Country to 'DE'.
		 */
		$query		= "SELECT * FROM `%susers` WHERE LENGTH(country) = 0";
		$query		= sprintf( $query, (string) $dbc->getPrefix() );
		$users		= $dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		if( count( $users ) ){
			$modelUser	= new Model_User( $this->env );
			foreach( $users as $user )
				$modelUser->edit( $user->userId,  array(
					'country'	=> 'DE',
				) );
		}
	}
}
?>
