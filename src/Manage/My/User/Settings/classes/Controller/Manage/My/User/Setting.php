<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_My_User_Setting extends Controller
{
	protected Model_User_Setting $model;
	protected ?string $userId;

	/**
	 *	@return		void
	 */
	public function index(): void
	{
		$this->addData( 'from', $this->env->getRequest()->get( 'from' ) );
		$this->addData( 'userId', $this->userId );												//  assign ID of current user
		$this->addData( 'modules', $this->env->getModules()->getAll() );
		$this->addData( 'settings', $this->model->getAllByIndex( 'userId', $this->userId ) );		//  get all user settings from database
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$configKey
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function reset( string $moduleId, string $configKey ): void
	{
		$from		= $this->env->getRequest()->get( 'from' );
		$indices	= [																				//  prepare indices for search for user setting in database
			'userId'	=> $this->userId,
			'moduleId'	=> $moduleId,
			'key'		=> $configKey
		];
		$this->model->removeByIndices( $indices );													//  remove user setting
		if( $from )
			$this->restart( $from );
		$this->restart( NULL, TRUE );												//  @todo: make another redirect possible
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function update(): void
	{
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'update' );
		$count		= 0;
		foreach( $this->env->getModules()->getAll() as $module ){									//  iterate modules
			foreach( $module->config as $config ){													//  iterate module config pairs
				if( 'user' !== $config->protected )													//  config pair is not writable for user settings
					continue;																		//  skip this config pair
				$key	= $module->id.'::'.str_replace( '.', '_', $config->key );		//  key name of form input
				if( !$request->has( $key ) )														//  no value for current config pair is in form request
					continue;																		//  so skip this one
				$value		= $this->model->castValue( $config->type, $request->get( $key ) );		//  convert sent input value to type of config value
				if( str_ends_with( $config->key . "|" . $config->type, 'password' ) )				//  pair is a password or pair key ends with 'password'
					if( !strlen( trim( $value ) ) )													//  no newer password entered
						continue;																	//  do not save empty password

				$indices	= [																		//  prepare indices for search for user setting in database
					'userId'	=> $this->userId,
					'moduleId'	=> $module->id,
					'key'		=> $config->key
				];
				/** @var Entity_User_Settings $setting */
				$setting	= $this->model->getByIndices( $indices );								//  search for user setting of this config pair

				if( $value === $config->value ){													//  new value matches config value
					if( $setting )																	//  a user setting has been stored
						//  @todo this line make no sense - check this and skip if equal!
						$this->model->remove( $setting->userSettingId );							//  remove user setting from database
				}
				else{
					if( substr_count( $value, "\n" ) )										//  multiple lines from textarea
						$value	= str_replace( "\n", ",", $value );					//  combine to comma separated
					if( in_array( $config->type, ['bool', 'boolean'] ) )							//  type of config value is boolean
						$value	= (int) $value;														//  convert to integer for database
					if( in_array( $config->type, ['integer', 'float'] ) && $config->values ){				//  type of  config value is a number
						if( preg_match( "/^([0-9]+)-([0-9]+)$/", trim( $config->values[0] ) ) ){	//  first (and hopefully only) value is a range (min-max)
							$parts	= explode( "-", $config->values[0] );
							$min	= (float) current( $parts );
							$max	= (float) end( $parts );
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
						$data	= [																	//  prepare data
							'value'			=> $value,
							'modifiedAt'	=> time(),
						];
						$this->model->edit( $setting->userSettingId, $data );						//  modify user setting in database
					}
					else{																			//  no user setting has been stored yet
						$this->model->add( [														//  add user setting to database
							'moduleId'		=> $module->id,
							'managerId'		=> $this->userId,
							'userId'		=> $this->userId,
							'key'			=> $config->key,
							'value'			=> $value,
							'createdAt'		=> time(),
							'modifiedAt'	=> time(),
						] );
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		parent::__onInit();
		$this->model	= new Model_User_Setting( $this->env );
		$this->userId	= $this->env->getSession()->get( 'auth_user_id' );
	}
}
