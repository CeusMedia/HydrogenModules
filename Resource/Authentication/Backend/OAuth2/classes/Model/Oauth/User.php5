<?php
/**
 *	OAuth Provider User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	OAuth Provider User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
class Model_Oauth_User extends CMF_Hydrogen_Model
{
	protected $name		= 'oauth_users';

	protected $columns	= array(
		'oauthUserId',
		'oauthProviderId',
		'oauthId',
		'localUserId',
		'timestamp',
	);

	protected $primaryKey	= 'oauthUserId';

	protected $indices		= array(
		'oauthProviderId',
		'oauthId',
		'localUserId',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
