<?php
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
class Model_User_Setting extends CMF_Hydrogen_Model {

	protected $name			= 'user_settings';
	protected $columns		= array(
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
	protected $indices		= array(
		'moduleId',
		'managerId',
		'userId',
		'key',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function applyConfig(){
		$config		= $this->env->getConfig()->getAll();
		$userId		= $this->env->getSession()->get( 'userId' );
		$model		= new Model_User_Setting( $this->env );

		$settings	= $model->getAllByIndex( 'userId', $userId );
		foreach( $settings as $setting ){
			$key	= 'module.'.strtolower( $setting->moduleId ).'.'.$setting->key;
			$value	= $this->castValue( gettype( $config[$key] ), $setting->value );
		}
		$config[$key]	= $value;
		return new ADT_List_Dictionary( $config );
	}

	public function castValue( $type, $value ){
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
?>