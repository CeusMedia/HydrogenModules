<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_My_User_Setting extends Hook
{
	/**
	 *	...
	 *	@static
	 *	@param		Environment		$env		Environment instance
	 *	@param		object			$context	Event call context object
	 *	@param		object			$module		Event call module object
	 *	@param		array			$data		Payload map
	 *	@return		void
	 */
	static public function onSessionInit( Environment $env, $context, $module, $data = [] )
	{
		if( $env->has( 'session' ) ){															//  environment has session support
			if( ( $userId = $env->getSession()->get( 'auth_user_id' ) ) ){						//  an user is logged in
				$config	= Model_User_Setting::applyConfigStatic( $env, $userId, FALSE );		//  apply user configuration
				$env->set( 'config', $config );											//  override config by user config
			}
		}
	}

	/**
	 *	...
	 *	@static
	 *	@param		Environment		$env		Environment instance
	 *	@param		object			$context	Event call context object
	 *	@param		object			$module		Event call module object
	 *	@param		array			$data		Payload map
	 *	@return		void
	 */
	static public function onViewRegisterTab( Environment $env, $context, $module, $data = [] )
	{
		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user/setting' );			//  load words
		$context->registerTab( 'setting', $words->module['tab'], 4 );							//  register main tab
	}

	/**
	 *	...
	 *	@static
	 *	@param		Environment		$env		Environment instance
	 *	@param		object			$context	Event call context object
	 *	@param		object			$module		Event call module object
	 *	@param		object			$payload	Payload object
	 *	@return		void
	 */
	static public function onUserRemove( Environment $env, $context, $module, $payload )
	{
		$payload	= (object) $payload;
		if( !empty( $payload->userId ) ){
			$model	= new Model_User_Setting( $env );
			$count	= $model->removeByIndex( 'userId', $payload->userId );
		}
		if( isset( $payload->counts ) )
			$payload->counts['Manage_My_User_Settings']	= (object) ['entities' => $count];
	}

	/**
	 *	...
	 *	Disabled, since resolution to module setting labels is not implemented.
	 *	@static
	 *	@param		Environment		$env		Environment instance
	 *	@param		object			$context	Event call context object
	 *	@param		object			$module		Event call module object
	 *	@param		array			$data		Payload map
	 *	@return		void
	 *	@todo		active, once config key can be translated labels @see View_Manage_My_User_Setting::getModuleWords
	 */
	static public function onListUserRelations( Environment $env, $context, $module, $data )
	{
		return;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Manage_My_User_Setting::onListUserRelations" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$words	= $env->getLanguage()->getWords( 'manage/my/user/setting' );

		$data->activeOnly	= $data->activeOnly ?? FALSE;
		$data->linkable		= $data->linkable ?? FALSE;

		$list		= [];
		$model		= new Model_User_Setting( $env );
		$settings	= $model->getAllByIndex( 'userId', $data->userId );
		foreach( $settings as $setting ){
			$list[]		= (object) [
				'id'		=> $data->linkable ? '#'.$setting->key : NULL,
				'label'		=> $setting->moduleId.' :: '.$setting->key,
			];
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
