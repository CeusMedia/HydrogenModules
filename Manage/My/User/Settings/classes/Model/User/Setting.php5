<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Model;

/**
 *	Types:
 *	0	- Promotion
 *	1	- Invitation
 *
 *	States:
 *	-2	- cancelled
 *	-1	- outdated
 *	0	- new (used on invite mode)
 *	1	- sent
 *	2	- used
 */
class Model_User_Setting extends Model
{
	protected string $name			= 'user_settings';

	protected array $columns		= array(
		'userSettingId',
		'moduleId',
		'managerId',
		'userId',
		'key',
		'value',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'userSettingId';

	protected array $indices		= array(
		'moduleId',
		'managerId',
		'userId',
		'key',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;

	public function applyConfig( $userId = NULL, $hidePasswords = TRUE )
	{
		$config		= $this->env->getConfig()->getAll();
		if( $userId === NULL )
			$userId		= $this->env->getSession()->get( 'auth_user_id' );
		$model		= new Model_User_Setting( $this->env );

		$settings	= $model->getAllByIndex( 'userId', $userId );
		foreach( $settings as $setting ){
			$key	= 'module.'.strtolower( $setting->moduleId ).'.'.$setting->key;
			if( array_key_exists( $key, $config ) ){
				$value	= $this->castValue( gettype( $config[$key] ), $setting->value );
				$config[$key]	= $value;
			}
		}
		if( $hidePasswords )
			foreach( $config as $key => $value )
				if( preg_match( "/password/", $key ) )
					$config[$key]	= (bool) $value;
		return new Dictionary( $config );
	}

	static public function applyConfigStatic( Environment $env, $userId = NULL, $hidePasswords = TRUE )
	{
		$model	= new Model_User_Setting( $env );
		return $model->applyConfig( $userId, $hidePasswords );
	}

	public function castValue( $type, $value )
	{
		switch( $type ){
			case 'bool':
			case 'boolean':
				$value	= (boolean) $value;
				break;
			case 'integer':
				$value	= (integer) $value;
				break;
			case 'float':
				$value	= (float) $value;
				break;
		}
		return $value;
	}
}
