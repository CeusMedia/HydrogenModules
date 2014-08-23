<?php
/**
 *	OAuth Code Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	OAuth Code Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
class Model_Oauth_Application extends CMF_Hydrogen_Model {

	const STATUS_REMOVED		= -1;
	const STATUS_DISABLED	= 0;
	const STATUS_ENABLED		= 1;
	
	const TYPE_PUBLIC		= 0;
	const TYPE_CONFIDENTIAL	= 1;
		
	protected $name		= 'oauth_applications';
	protected $columns	= array(
		'oauthApplicationId',
		'userId',
		'type',
		'status',
		'clientId',
		'clientSecret',
		'title',
		'description',
		'url',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'oauthApplicationId';
	protected $indices		= array(
		'userId',
		'type',
		'status',
		'clientId',
		'clientSecret',
		'url',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function getNewClientId( $salt = NULL, $pepper = NULL ){
		do{
			$pos	= round( rand(0, 1) * 20 );
			$id		= substr( md5( $salt.'_'.microtime( TRUE ).'_'.$pepper ), $pos, 12 );
		}
		while( $this->getByIndex( 'clientId', $id ) );
		return $id;
	}

	public function getNewClientSecret( $salt = NULL, $pepper = NULL ){
		return hash( "sha256", $salt.'_'.microtime( TRUE ).'_'.$pepper );		
	}

	public function remove( $id ){
		$modelAccess	= new Model_Oauth_AccessToken( $this->env );
		$modelCode		= new Model_Oauth_Code( $this->env );
		$modelRefresh	= new Model_Oauth_RefreshToken( $this->env );
		$modelAccess->removeByIndex( 'oauthApplicationId', $id );
		$modelCode->removeByIndex( 'oauthApplicationId', $id );
		$modelRefresh->removeByIndex( 'oauthApplicationId', $id );
		parent::remove( $id );
	}
}
?>
