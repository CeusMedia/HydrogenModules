<?php
class Hook_Admin_Config extends CMF_Hydrogen_View_Hook
{
	/**
	 * @todo finish impl
	 */
	public static function onAdminConfigRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'admin/config' );						//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );									//  register index tab
//		$context->registerTab( 'module', $words->tabs['module'], 1 );							//  register module tab
//		$context->registerTab( 'direct', $words->tabs['direct'], 1 );							//  register direct tab
	}
}
