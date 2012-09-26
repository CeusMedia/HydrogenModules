<?php
class Controller_Manage_My_User_Setting extends CMF_Hydrogen_Controller{
	
	public function index(){
		$userId		= $this->env->getSession()->get( 'userId' );
		$model		= new Model_User_Setting( $this->env );

		$this->addData( 'settings', $model->getAllByIndex( 'userId', $userId ) );					//  get all user settings from database
	}

	public function reset( $moduleId, $configKey ){
		$model		= new Model_User_Setting( $this->env );
		$indices	= array(																		//  prepare indices for search for user setting in database
			'userId'	=> $this->env->getSession()->get( 'userId' ),
			'moduleId'	=> $moduleId,
			'key'		=> $configKey
		);
		$setting	= $model->removeByIndices( $indices );											//  remove user setting
		$this->restart( NULL, TRUE );																//  @todo: make another redirect possible
	}
	
	public function update(){
		$request	= $this->env->getRequest();
		$userId		= $this->env->getSession()->get( 'userId' );
		$model		= new Model_User_Setting( $this->env );
		foreach( $this->env->getModules()->getAll() as $module ){									//  iterate modules
			foreach( $module->config as $config ){													//  iterate module config pairs
				if( $config->protected !== "user" )													//  config pair is not writable for user settings
					continue;																		//  skip this config pair
				$key	= $module->id.'::'.str_replace( '.', '_', $config->key );					//  key name of form input
				if( !$request->has( $key ) )														//  no value for current config pair is in form request
					continue;																		//  so skip this one
				$value		= $model->castValue( $config->type, $request->get( $key ) );			//  convert sent input value to type of config value

				$indices	= array(																//  prepare indices for search for user setting in database
					'userId'	=> $userId,
					'moduleId'	=> $module->id,
					'key'		=> $config->key
				);
				$setting	= $model->getByIndices( $indices );										//  search for user setting of this config pair

				if( $value === $config->value ){													//  new value matches config value
					if( $setting )																	//  a user setting has been stored
						$model->remove( $setting->userSettingId );									//  remove user setting from database
				}
				else{
					if( in_array( $config->type, array( 'bool', 'boolean' ) ) )						//  type of config value is boolean
						$value	= (int) $value;														//  convert to integer for database
					if( $setting ){																	//  a user setting has been stored
						$data	= array(															//  prepare data
							'value'			=> $value,
							'modifiedAt'	=> time(),
						);
						$model->edit( $setting->userSettingId, $data );								//  modify user setting in database
					}
					else{																			//  no user setting has been stored yet
						$data	= array(															//  prepare data
							'moduleId'		=> $module->id,
							'userId'		=> $userId,
							'key'			=> $config->key,
							'value'			=> $value,
							'createdAt'		=> time(),
						);
						$model->add( $data );														//  add user setting to database
					}
				}
			}
		}
		$this->restart( NULL, TRUE );																//  @todo: make another redirect possible
	}
}
?>