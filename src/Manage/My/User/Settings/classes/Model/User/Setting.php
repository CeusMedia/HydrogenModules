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

	protected array $columns		= [
		'userSettingId',
		'moduleId',
		'managerId',
		'userId',
		'key',
		'value',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'userSettingId';

	protected array $indices		= [
		'moduleId',
		'managerId',
		'userId',
		'key',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

	public function applyConfig( $userId = NULL, bool $hidePasswords = TRUE ): void
	{
		$config = $this->env->getConfig();

//		$config		= $this->env->getConfig()->getAll();
		if( $userId === NULL )
			$userId		= $this->env->getSession()->get( 'auth_user_id' );
		$model		= new Model_User_Setting( $this->env );

		$settings	= $model->getAllByIndex( 'userId', $userId );
		foreach( $settings as $setting ){
			$key	= 'module.'.strtolower( $setting->moduleId ).'.'.$setting->key;
			if( $config->has( $key ) ){
				$type	= gettype( $config->get( $key ) );
				$value	= $this->castValue( $type, $setting->value );
				$config->set( $key, $value );
			}
		}
		if( $hidePasswords )
			foreach( $config->getAll() as $key => $value )
				if( preg_match( "/password/", $key ) )
					$config->set( $key, (bool) $value );
	}

	public static function applyConfigStatic( Environment $env, $userId = NULL, bool $hidePasswords = TRUE ): void
	{
		$model	= new Model_User_Setting( $env );
		$model->applyConfig( $userId, $hidePasswords );
	}

	public function castValue( string $type, $value )
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
