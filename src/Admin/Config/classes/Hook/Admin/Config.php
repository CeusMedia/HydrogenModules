<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Admin_Config extends Hook
{
	/**
	 * @todo finish impl
	 */
	public static function onAdminConfigRegisterTab( Environment $env, object $context, $module, array & $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'admin/config' );				//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );									//  register index tab
//		$context->registerTab( 'module', $words->tabs['module'], 1 );							//  register module tab
//		$context->registerTab( 'direct', $words->tabs['direct'], 1 );							//  register direct tab
	}
}
