<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_My_User_Setting extends Hook
{
	/**
	 *	...
	 *	@return		void
	 */
	public function onSessionInit(): void
	{
		if( !$this->env->has( 'session' ) )													//  environment has no session support
			return;
		$userId	= (int) $this->env->getSession()->get( 'auth_user_id', '' );
		if( 0 !== $userId ){																			//  a user is logged in
			$config	= Model_User_Setting::applyConfigStatic( $this->env, $userId, FALSE );	//  apply user configuration
			foreach( $this->env->getConfig() as $key => $value )
				if( $config->has( $key ) && $config->get( $key ) !== $value )
					$this->env->getConfig()->set( $key, $value );
		}
	}

	/**
	 *	...
	 *	@return		void
	 */
	public function onViewRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/my/user/setting' );	//  load words
		$this->context->registerTab( 'setting', $words->module['tab'], 4 );							//  register main tab
	}

	/**
	 *    ...
	 *	@return        void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onUserRemove(): void
	{
		$payload	= (object) $this->getPayload();
		$count		= 0;
		if( !empty( $payload->userId ) ){
			$model	= new Model_User_Setting( $this->env );
			$count	= $model->removeByIndex( 'userId', $payload->userId );
		}
		if( isset( $payload->counts ) )
			$payload->counts['Manage_My_User_Settings']	= (object) ['entities' => $count];
		$payload	= get_object_vars( $payload );
		$this->setPayload( $payload );
	}

	/**
	 *	...
	 *	Disabled, since resolution to module setting labels is not implemented.
	 *	@return		void
	 *	@todo		active, once config key can be translated labels @see View_Manage_My_User_Setting::getModuleWords
	 *	@throws		ReflectionException
	 */
	public function onListUserRelations(): void
	{
		return;
		if( empty( $this->payload['userId'] ) ){
			$message	= 'Hook "Manage_My_User_Setting::onListUserRelations" is missing user ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$words	= $env->getLanguage()->getWords( 'manage/my/user/setting' );

		$this->payload['activeOnly']	= $this->payload['activeOnly'] ?? FALSE;
		$this->payload['linkable']		= $this->payload['linkable'] ?? FALSE;

		$list		= [];
		$model		= new Model_User_Setting( $this->env );
		$settings	= $model->getAllByIndex( 'userId', $this->payload['userId'] );
		foreach( $settings as $setting ){
			$list[]		= (object) [
				'id'		=> $this->payload['linkable'] ? '#'.$setting->key : NULL,
				'label'		=> $setting->moduleId.' :: '.$setting->key,
			];
		}

		if( [] === $list )
			return;
		View_Helper_ItemRelationLister::enqueueRelations(
			$this->payload,																	//  hook content data
			$module,																				//  module called by hook
			'entity',																			//  relation type: entity or relation
			$list,																					//  list of related items
			$words['helper-relations']['heading'],													//  label of type of related items
			'Manage_My_User_Setting',														//  controller of entity
			'edit'																			//  action to view or edit entity
		);
	}
}
