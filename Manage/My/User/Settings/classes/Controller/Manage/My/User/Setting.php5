<?php
class Controller_Manage_My_User_Setting extends CMF_Hydrogen_Controller{

	protected $module;
	protected $userId;

	public function __onInit(){
		parent::__onInit();
		$this->model	= new Model_User_Setting( $this->env );
		$this->userId	= $this->env->getSession()->get( 'userId' );
	}

	public function index(){
		$this->addData( 'from', $this->env->getRequest()->get( 'from' ) );
		$this->addData( 'userId', $this->userId );													//  assign ID of current user
		$this->addData( 'modules', $this->env->getModules()->getAll() );
		$this->addData( 'settings', $this->model->getAllByIndex( 'userId', $this->userId ) );		//  get all user settings from database
	}

	public function reset( $moduleId, $configKey ){
		$from		= $this->env->getRequest()->get( 'from' );
		$model		= new Model_User_Setting( $this->env );
		$indices	= array(																		//  prepare indices for search for user setting in database
			'userId'	=> $this->userId,
			'moduleId'	=> $moduleId,
			'key'		=> $configKey
		);
		$model->removeByIndices( $indices );														//  remove user setting
		if( $from )
			$this->restart( $from );
		$this->restart( NULL, TRUE );																//  @todo: make another redirect possible
	}

	public function update(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'update' );
		$count		= 0;
		foreach( $this->env->getModules()->getAll() as $module ){									//  iterate modules
			foreach( $module->config as $config ){													//  iterate module config pairs
				if( $config->protected !== "user" )													//  config pair is not writable for user settings
					continue;																		//  skip this config pair
				$key	= $module->id.'::'.str_replace( '.', '_', $config->key );					//  key name of form input
				if( !$request->has( $key ) )														//  no value for current config pair is in form request
					continue;																		//  so skip this one
				$value		= $this->model->castValue( $config->type, $request->get( $key ) );		//  convert sent input value to type of config value
				if( preg_match( "/password$/", $config->key."|".$config->type ) )					//  pair is a password or pair key ends with 'password'
					if( !strlen( trim( $value ) ) )													//  no newer password entered
					continue;																		//  do not save empty password

				$indices	= array(																//  prepare indices for search for user setting in database
					'userId'	=> $this->userId,
					'moduleId'	=> $module->id,
					'key'		=> $config->key
				);
				$setting	= $this->model->getByIndices( $indices );								//  search for user setting of this config pair

				if( $value === $config->value ){													//  new value matches config value
					if( $setting )																	//  a user setting has been stored
						//  @todo this line make no sense - check this and skip if equal!
						$this->model->remove( $setting->userSettingId );							//  remove user setting from database
				}
				else{
					if( substr_count( $value, "\n" ) )												//  multiple lines from textarea
						$value	= str_replace( "\n", ",", $value );									//  combine to comma separated
					if( in_array( $config->type, array( 'bool', 'boolean' ) ) )						//  type of config value is boolean
						$value	= (int) $value;														//  convert to integer for database
					if( in_array( $config->type, array( 'integer', 'float' ) ) && $config->values ){	//  type of  config value is a number
						if( preg_match( "/^([0-9]+)-([0-9]+)$/", trim( $config->values[0] ) ) ){	//  first (and hopefully only) value is a range (min-max)
							$min	= (float) array_shift( explode( "-", $config->values[0] ) );
							$max	= (float) array_pop( explode( "-", $config->values[0] ) );
							if( $value < $min )
								$messenger->noteError( $words->msgErrorNumberTooSmall );
							if( $value > $max )
								$messenger->noteError( $words->msgErrorNumberTooLarge );
						}
					}
					if( $messenger->gotError() )
						continue;
					$count++;
					if( $setting ){																	//  a user setting has been stored
						$data	= array(															//  prepare data
							'value'			=> $value,
							'modifiedAt'	=> time(),
						);
						$this->model->edit( $setting->userSettingId, $data );						//  modify user setting in database
					}
					else{																			//  no user setting has been stored yet
						$this->model->add( array(													//  add user setting to database
							'moduleId'		=> $module->id,
							'managerId'		=> $this->userId,
							'userId'		=> $this->userId,
							'key'			=> $config->key,
							'value'			=> $value,
							'createdAt'		=> time(),
							'modifiedAt'	=> time(),
						) );
					}
				}
			}
		}
		if( $count )
			$messenger->noteSuccess( $words->msgSuccess );
		if( $request->get( 'from' ) )
			$this->restart( $request->get( 'from' ) );
		$this->restart( NULL, TRUE );																//  @todo: make another redirect possible
	}
}
?>
