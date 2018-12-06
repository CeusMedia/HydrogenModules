<?php
/**
 *	OAuth Provider Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	OAuth Provider Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
class Model_Oauth_Provider extends CMF_Hydrogen_Model {

	const STATUS_INACTIVE		= -1;
	const STATUS_NEW			= 0;
	const STATUS_ACTIVE			= 1;

	protected $name		= 'oauth_providers';
	protected $columns	= array(
		'oauthProviderId',
		'status',
		'rank',
		'clientId',
		'clientSecret',
		'composerPackage',
		'className',
		'options',
		'scopes',
		'title',
		'icon',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'oauthProviderId';
	protected $indices		= array(
		'status',
		'clientId',
		'clientSecret',
		'composerPackage',
		'className',
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
