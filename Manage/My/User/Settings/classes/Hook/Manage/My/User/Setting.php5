<?php
class Hook_Manage_My_User_Setting extends CMF_Hydrogen_Hook{

	/**
	 *	...
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Event call context object
	 *	@param		object						$module		Event call module object
	 *	@param		array						$data		Payload map
	 *	@return		void
	 */
	static public function onSessionInit( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( $env->has( 'session' ) ){															//  environment has session support
			if( ( $userId = $env->getSession()->get( 'userId' ) ) ){							//  an user is logged in
				$config	= Model_User_Setting::applyConfigStatic( $env, $userId, FALSE );		//  apply user configuration
				$env->set( 'config', $config, TRUE );											//  override config by user config
			}
		}
	}

	/**
	 *	...
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Event call context object
	 *	@param		object						$module		Event call module object
	 *	@param		array						$data		Payload map
	 *	@return		void
	 */
	static public function onViewRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user/setting' );			//  load words
		$context->registerTab( 'setting', $words->module['tab'], 4 );							//  register main tab
	}

	/**
	 *	...
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Event call context object
	 *	@param		object						$module		Event call module object
	 *	@param		object						$payload	Payload object
	 *	@return		void
	 */
	static public function onUserRemove( CMF_Hydrogen_Environment $env, $context, $module, $payload ){
		$payload	= (object) $payload;
		if( !empty( $payload->userId ) ){
			$model	= new Model_User_Setting( $env );
			$count	= $model->removeByIndex( 'userId', $payload->userId );
		}
		if( isset( $payload->counts ) )
			$payload->counts['Manage_My_User_Settings']	= (object) array( 'entities' => $count );
	}

	/**
	 *	...
	 *	Disabled, since resolution to module setting labels is not implemented.
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Event call context object
	 *	@param		object						$module		Event call module object
	 *	@param		array						$data		Payload map
	 *	@return		void
	 *	@todo		active, once config key can be translated labels @see View_Manage_My_User_Setting::getModuleWords
	 */
	static public function onListUserRelations( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		return;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Manage_My_User_Setting::onListUserRelations" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$words	= $env->getLanguage()->getWords( 'manage/my/user/setting' );

		$data->activeOnly	= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$data->linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;

		$list		= array();
		$model		= new Model_User_Setting( $env );
		$settings	= $model->getAllByIndex( 'userId', $data->userId );
		foreach( $settings as $setting ){
			$list[]		= (object) array(
				'id'		=> $data->linkable ? '#'.$setting->key : NULL,
				'label'		=> $setting->moduleId.' :: '.$setting->key,
			);
		}

		if( $list )
			View_Helper_ItemRelationLister::enqueueRelations(
				$data,																			//  hook content data
				$module,																		//  module called by hook
				'entity',																		//  relation type: entity or relation
				$list,																			//  list of related items
				$words['helper-relations']['heading'],											//  label of type of related items
				'Manage_My_User_Setting',														//  controller of entity
				'edit'																			//  action to view or edit entity
			);
	}
}
